<?php

namespace App\Filament\Forms\Components;

use Closure;
use Filament\Forms\Components\Field;
use Filament\Schemas\Components\Utilities\Get;

class StockItemPicker extends Field
{
    protected string $view = 'filament.forms.components.stock-item-picker';
    
    protected array | Closure | null $appliedParts = null;
    protected int | Closure | null $dealerId = null;
    
    protected function setUp(): void
    {
        parent::setUp();

        $this->default(null);
        $this->dehydrated(true);
        
        // Reactive güncelleme için live() modifier
        $this->live(onBlur: false);
        
        // State değişikliklerini dinle
        // Not: dispatch() metodu form component'lerinde yok, Livewire watch ile dinleniyor
        
    }
    
    public function appliedParts(array | Closure | null $appliedParts): static
    {
        $this->appliedParts = $appliedParts;
        
        return $this;
    }
    
    public function getAppliedParts(?Get $get = null): array
    {
        // #region agent log
        $logPath = base_path('.cursor/debug.log');
        $logData = [
            'sessionId' => 'debug-session',
            'runId' => 'run1',
            'hypothesisId' => 'A',
            'location' => 'StockItemPicker::getAppliedParts',
            'message' => 'getAppliedParts called',
            'data' => ['get_is_null' => $get === null],
            'timestamp' => (int)(microtime(true) * 1000),
        ];
        file_put_contents($logPath, json_encode($logData) . "\n", FILE_APPEND);
        // #endregion
        
        if ($get === null) {
            return [];
        }
        
        // Wizard içinde Repeater içindeyken state erişimi
        // Önce wizard root'a çıkmayı dene
        $appliedParts = null;
        
        // Farklı yolları dene
        $paths = [
            'applied_parts',           // Direkt erişim
            '../applied_parts',        // Repeater parent
            '../../applied_parts',     // Wizard step
            '../../../applied_parts',  // Wizard root (fallback)
        ];
        
        $foundPath = null;
        foreach ($paths as $path) {
            try {
                $value = $get($path);
                // #region agent log
                $logData2 = [
                    'sessionId' => 'debug-session',
                    'runId' => 'run1',
                    'hypothesisId' => 'A',
                    'location' => 'StockItemPicker::getAppliedParts',
                    'message' => 'Path check',
                    'data' => ['path' => $path, 'value' => $value, 'value_type' => gettype($value)],
                    'timestamp' => (int)(microtime(true) * 1000),
                ];
                file_put_contents($logPath, json_encode($logData2) . "\n", FILE_APPEND);
                // #endregion
                
                if ($value !== null) {
                    $appliedParts = $value;
                    $foundPath = $path;
                    break;
                }
            } catch (\Exception $e) {
                // #region agent log
                $logData3 = [
                    'sessionId' => 'debug-session',
                    'runId' => 'run1',
                    'hypothesisId' => 'A',
                    'location' => 'StockItemPicker::getAppliedParts',
                    'message' => 'Path exception',
                    'data' => ['path' => $path, 'error' => $e->getMessage()],
                    'timestamp' => (int)(microtime(true) * 1000),
                ];
                file_put_contents($logPath, json_encode($logData3) . "\n", FILE_APPEND);
                // #endregion
                // Path geçersizse devam et
                continue;
            }
        }
        
        // Eğer hala null ise, closure'ı evaluate et
        if ($appliedParts === null && $this->appliedParts !== null) {
            $appliedParts = $this->evaluate($this->appliedParts, [
                'get' => $get,
            ]);
            // #region agent log
            $logData4 = [
                'sessionId' => 'debug-session',
                'runId' => 'run1',
                'hypothesisId' => 'A',
                'location' => 'StockItemPicker::getAppliedParts',
                'message' => 'Closure evaluated',
                'data' => ['appliedParts' => $appliedParts, 'foundPath' => $foundPath],
                'timestamp' => (int)(microtime(true) * 1000),
            ];
            file_put_contents($logPath, json_encode($logData4) . "\n", FILE_APPEND);
            // #endregion
        }
        
        // String ise JSON decode et
        if (is_string($appliedParts)) {
            $appliedParts = json_decode($appliedParts, true) ?? [];
        }
        
        // #region agent log
        $logData5 = [
            'sessionId' => 'debug-session',
            'runId' => 'run1',
            'hypothesisId' => 'A',
            'location' => 'StockItemPicker::getAppliedParts',
            'message' => 'Final result',
            'data' => ['appliedParts' => $appliedParts, 'is_array' => is_array($appliedParts), 'count' => is_array($appliedParts) ? count($appliedParts) : 0],
            'timestamp' => (int)(microtime(true) * 1000),
        ];
        file_put_contents($logPath, json_encode($logData5) . "\n", FILE_APPEND);
        // #endregion
        
        return is_array($appliedParts) ? $appliedParts : [];
    }
    
    public function dealerId(int | Closure | null $dealerId): static
    {
        $this->dealerId = $dealerId;
        
        return $this;
    }
    
    public function getDealerId(?Get $get = null): ?int
    {
        // #region agent log
        $logPath = base_path('.cursor/debug.log');
        $logData = [
            'sessionId' => 'debug-session',
            'runId' => 'run1',
            'hypothesisId' => 'B',
            'location' => 'StockItemPicker::getDealerId',
            'message' => 'getDealerId called',
            'data' => ['get_is_null' => $get === null],
            'timestamp' => (int)(microtime(true) * 1000),
        ];
        file_put_contents($logPath, json_encode($logData) . "\n", FILE_APPEND);
        // #endregion
        
        if ($get === null) {
            return null;
        }
        
        // Wizard içinde Repeater içindeyken state erişimi
        $dealerId = null;
        
        // Farklı yolları dene
        $paths = [
            'dealer_id',           // Direkt erişim (normal form)
            '../dealer_id',        // Repeater parent
            '../../dealer_id',     // Wizard step
            '../../../dealer_id',  // Wizard root (fallback)
        ];
        
        $foundPath = null;
        foreach ($paths as $path) {
            try {
                $value = $get($path);
                // #region agent log
                $logData2 = [
                    'sessionId' => 'debug-session',
                    'runId' => 'run1',
                    'hypothesisId' => 'B',
                    'location' => 'StockItemPicker::getDealerId',
                    'message' => 'Path check',
                    'data' => ['path' => $path, 'value' => $value, 'value_type' => gettype($value)],
                    'timestamp' => (int)(microtime(true) * 1000),
                ];
                file_put_contents($logPath, json_encode($logData2) . "\n", FILE_APPEND);
                // #endregion
                
                if ($value !== null) {
                    $dealerId = $value;
                    $foundPath = $path;
                    break;
                }
            } catch (\Exception $e) {
                // #region agent log
                $logData3 = [
                    'sessionId' => 'debug-session',
                    'runId' => 'run1',
                    'hypothesisId' => 'B',
                    'location' => 'StockItemPicker::getDealerId',
                    'message' => 'Path exception',
                    'data' => ['path' => $path, 'error' => $e->getMessage()],
                    'timestamp' => (int)(microtime(true) * 1000),
                ];
                file_put_contents($logPath, json_encode($logData3) . "\n", FILE_APPEND);
                // #endregion
                // Path geçersizse devam et
                continue;
            }
        }
        
        // Eğer hala null ise, closure'ı evaluate et
        if ($dealerId === null && $this->dealerId !== null) {
            try {
                $dealerId = $this->evaluate($this->dealerId, [
                    'get' => $get,
                ]);
                // #region agent log
                $logData4 = [
                    'sessionId' => 'debug-session',
                    'runId' => 'run1',
                    'hypothesisId' => 'B',
                    'location' => 'StockItemPicker::getDealerId',
                    'message' => 'Closure evaluated',
                    'data' => ['dealerId' => $dealerId, 'foundPath' => $foundPath],
                    'timestamp' => (int)(microtime(true) * 1000),
                ];
                file_put_contents($logPath, json_encode($logData4) . "\n", FILE_APPEND);
                // #endregion
            } catch (\Exception $e) {
                // #region agent log
                $logData5 = [
                    'sessionId' => 'debug-session',
                    'runId' => 'run1',
                    'hypothesisId' => 'B',
                    'location' => 'StockItemPicker::getDealerId',
                    'message' => 'Closure exception',
                    'data' => ['error' => $e->getMessage()],
                    'timestamp' => (int)(microtime(true) * 1000),
                ];
                file_put_contents($logPath, json_encode($logData5) . "\n", FILE_APPEND);
                // #endregion
                // Hata durumunda devam et
            }
        }
        
        // Record'dan al (edit sayfası için)
        if ($dealerId === null) {
            $record = $this->getRecord();
            if ($record && isset($record->dealer_id)) {
                $dealerId = $record->dealer_id;
                // #region agent log
                $logData6 = [
                    'sessionId' => 'debug-session',
                    'runId' => 'run1',
                    'hypothesisId' => 'B',
                    'location' => 'StockItemPicker::getDealerId',
                    'message' => 'DealerId from record',
                    'data' => ['dealerId' => $dealerId, 'record_id' => $record->id ?? null],
                    'timestamp' => (int)(microtime(true) * 1000),
                ];
                file_put_contents($logPath, json_encode($logData6) . "\n", FILE_APPEND);
                // #endregion
            }
        }
        
        // #region agent log
        $logData7 = [
            'sessionId' => 'debug-session',
            'runId' => 'run1',
            'hypothesisId' => 'B',
            'location' => 'StockItemPicker::getDealerId',
            'message' => 'Final result',
            'data' => ['dealerId' => $dealerId, 'is_numeric' => is_numeric($dealerId)],
            'timestamp' => (int)(microtime(true) * 1000),
        ];
        file_put_contents($logPath, json_encode($logData7) . "\n", FILE_APPEND);
        // #endregion
        
        return is_numeric($dealerId) ? (int) $dealerId : null;
    }
}

