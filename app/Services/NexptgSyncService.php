<?php

namespace App\Services;

use App\Models\NexptgApiUser;
use App\Models\NexptgHistory;
use App\Models\NexptgHistoryMeasurement;
use App\Models\NexptgReport;
use App\Models\NexptgReportMeasurement;
use App\Models\NexptgReportTire;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class NexptgSyncService
{
    /**
     * Sync NexPTG data
     */
    public function sync(array $data, NexptgApiUser $apiUser): void
    {
        DB::transaction(function () use ($data, $apiUser) {
            if (isset($data['reports']) && is_array($data['reports'])) {
                $this->syncReports($data['reports'], $apiUser);
            }

            if (isset($data['history']) && is_array($data['history'])) {
                $this->syncHistories($data['history']);
            }
        });
    }

    /**
     * Sync reports data
     */
    protected function syncReports(array $reports, NexptgApiUser $apiUser): void
    {
        foreach ($reports as $reportData) {
            try {
                $externalId = $reportData['id'] ?? null;

                if (! $externalId) {
                    Log::warning('NexPTG sync: Report without external_id skipped', $reportData);
                    continue;
                }

                // Check if report already exists
                $report = NexptgReport::firstOrNew(['external_id' => $externalId]);

                // Fill report metadata
                $report->fill([
                    'api_user_id' => $apiUser->id,
                    'name' => $reportData['name'] ?? '',
                    'date' => $this->parseTimestamp($reportData['date'] ?? null),
                    'calibration_date' => $this->parseTimestamp($reportData['calibrationDate'] ?? null),
                    'device_serial_number' => $reportData['deviceSerialNumber'] ?? '',
                    'model' => $reportData['model'] ?? null,
                    'brand' => $reportData['brand'] ?? null,
                    'type_of_body' => $reportData['typeOfBody'] ?? null,
                    'capacity' => $reportData['capacity'] ?? null,
                    'power' => $reportData['power'] ?? null,
                    'vin' => $reportData['vin'] ?? null,
                    'fuel_type' => $reportData['fuelType'] ?? null,
                    'year' => $reportData['year'] ?? null,
                    'unit_of_measure' => $reportData['unitOfMeasure'] ?? null,
                    'extra_fields' => $reportData['extraFields'] ?? null,
                    'comment' => $reportData['comment'] ?? null,
                ]);

                $report->save();

                // Sync measurements (data - external)
                if (isset($reportData['data']) && is_array($reportData['data'])) {
                    $this->syncReportMeasurements($report->id, $reportData['data'], false);
                }

                // Sync measurements (dataInside - internal)
                if (isset($reportData['dataInside']) && is_array($reportData['dataInside'])) {
                    $this->syncReportMeasurements($report->id, $reportData['dataInside'], true);
                }

                // Sync tires
                if (isset($reportData['tires']) && is_array($reportData['tires'])) {
                    $this->syncReportTires($report->id, $reportData['tires']);
                }
            } catch (\Exception $e) {
                Log::error('NexPTG sync: Error syncing report', [
                    'report_id' => $reportData['id'] ?? null,
                    'error' => $e->getMessage(),
                ]);
                throw $e;
            }
        }
    }

    /**
     * Sync report measurements
     */
    protected function syncReportMeasurements(int $reportId, array $measurementsData, bool $isInside): void
    {
        // Delete existing measurements for this report and is_inside flag
        NexptgReportMeasurement::where('report_id', $reportId)
            ->where('is_inside', $isInside)
            ->delete();

        foreach ($measurementsData as $placeData) {
            $placeId = $placeData['placeId'] ?? null;

            if (! $placeId) {
                continue;
            }

            $components = $placeData['data'] ?? [];

            foreach ($components as $component) {
                $partType = $component['type'] ?? null;
                $values = $component['values'] ?? [];

                foreach ($values as $valueData) {
                    $value = $this->parseValue($valueData['value'] ?? null);
                    $interpretation = $this->parseInteger($valueData['interpretation'] ?? null);
                    $substrateType = $valueData['type'] ?? null;
                    $timestamp = $this->parseTimestamp($valueData['timestamp'] ?? null);
                    $position = $this->parseInteger($valueData['position'] ?? null);

                    // Skip invalid measurements (e.g., "-" values)
                    if ($value === null && $timestamp === null) {
                        continue;
                    }

                    NexptgReportMeasurement::create([
                        'report_id' => $reportId,
                        'is_inside' => $isInside,
                        'place_id' => $placeId,
                        'part_type' => $partType,
                        'value' => $value,
                        'interpretation' => $interpretation,
                        'substrate_type' => $substrateType,
                        'timestamp' => $timestamp,
                        'position' => $position,
                    ]);
                }
            }
        }
    }

    /**
     * Sync report tires
     */
    protected function syncReportTires(int $reportId, array $tires): void
    {
        // Delete existing tires for this report
        NexptgReportTire::where('report_id', $reportId)->delete();

        foreach ($tires as $tireData) {
            NexptgReportTire::create([
                'report_id' => $reportId,
                'width' => $tireData['width'] ?? null,
                'profile' => $tireData['profile'] ?? null,
                'diameter' => $tireData['diameter'] ?? null,
                'maker' => $tireData['maker'] ?? null,
                'season' => $tireData['season'] ?? null,
                'section' => $tireData['section'] ?? null,
                'value1' => $this->parseDecimal($tireData['value1'] ?? null),
                'value2' => $this->parseDecimal($tireData['value2'] ?? null),
            ]);
        }
    }

    /**
     * Sync histories data
     */
    protected function syncHistories(array $histories): void
    {
        foreach ($histories as $historyData) {
            try {
                $externalId = $historyData['id'] ?? null;

                if (! $externalId) {
                    Log::warning('NexPTG sync: History without external_id skipped', $historyData);
                    continue;
                }

                // Check if history already exists
                $history = NexptgHistory::firstOrNew(['external_id' => $externalId]);
                $history->name = $historyData['name'] ?? '';
                $history->save();

                // Sync history measurements
                if (isset($historyData['data']) && is_array($historyData['data'])) {
                    $this->syncHistoryMeasurements($history->id, $historyData['data']);
                }
            } catch (\Exception $e) {
                Log::error('NexPTG sync: Error syncing history', [
                    'history_id' => $historyData['id'] ?? null,
                    'error' => $e->getMessage(),
                ]);
                throw $e;
            }
        }
    }

    /**
     * Sync history measurements
     */
    protected function syncHistoryMeasurements(int $historyId, array $measurements): void
    {
        // Delete existing measurements for this history
        NexptgHistoryMeasurement::where('history_id', $historyId)->delete();

        foreach ($measurements as $measurementData) {
            $value = $this->parseInteger($measurementData['value'] ?? null);
            $interpretation = $this->parseInteger($measurementData['interpretation'] ?? null);
            $substrateType = $measurementData['type'] ?? null;
            $date = $this->parseTimestamp($measurementData['date'] ?? null);

            // Skip invalid measurements
            if ($value === null && $date === null) {
                continue;
            }

            NexptgHistoryMeasurement::create([
                'history_id' => $historyId,
                'value' => $value,
                'interpretation' => $interpretation,
                'substrate_type' => $substrateType,
                'date' => $date,
            ]);
        }
    }

    /**
     * Parse timestamp from Unix epoch (seconds) to Carbon
     */
    protected function parseTimestamp($timestamp): ?Carbon
    {
        if ($timestamp === null || $timestamp === '' || $timestamp === 0 || $timestamp === -1) {
            return null;
        }

        try {
            return Carbon::createFromTimestamp($timestamp);
        } catch (\Exception $e) {
            Log::warning('NexPTG sync: Invalid timestamp', ['timestamp' => $timestamp]);
            return null;
        }
    }

    /**
     * Parse value (can be string or integer, may be "-")
     */
    protected function parseValue($value)
    {
        if ($value === null || $value === '' || $value === '-') {
            return null;
        }

        if (is_numeric($value)) {
            return (float) $value;
        }

        return null;
    }

    /**
     * Parse integer value
     */
    protected function parseInteger($value): ?int
    {
        if ($value === null || $value === '' || $value === '-') {
            return null;
        }

        if (is_numeric($value)) {
            return (int) $value;
        }

        return null;
    }

    /**
     * Parse decimal value
     */
    protected function parseDecimal($value): ?float
    {
        if ($value === null || $value === '' || $value === '-') {
            return null;
        }

        if (is_numeric($value)) {
            return (float) $value;
        }

        return null;
    }
}

