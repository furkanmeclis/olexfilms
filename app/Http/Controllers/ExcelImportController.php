<?php

namespace App\Http\Controllers;

use App\Models\Dealer;
use App\Models\Product;
use App\Models\ProductCategory;
use App\Services\ExcelImportService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Session;
use Maatwebsite\Excel\Facades\Excel;

class ExcelImportController extends Controller
{
    public function index()
    {
        $products = Product::where('is_active', true)->orderBy('name')->get();
        $dealers = Dealer::where('is_active', true)->orderBy('name')->get();
        $categories = ProductCategory::where('is_active', true)->orderBy('name')->get();

        return view('excel-import.index', compact('products', 'dealers', 'categories'));
    }

    public function preview(Request $request)
    {
        $request->validate([
            'excel_file' => 'required|file|mimes:xlsx,xls|max:10240', // 10MB max
            'default_product_id' => 'nullable|exists:products,id',
            'default_dealer_id' => 'nullable|exists:dealers,id',
        ]);

        try {
            $file = $request->file('excel_file');
            $defaultProductId = $request->input('default_product_id');
            $defaultDealerId = $request->input('default_dealer_id');

            // Excel dosyasını oku
            $data = Excel::toArray([], $file);
            $rows = collect($data[0]); // İlk sheet'i al

            if ($rows->isEmpty()) {
                return back()->with('error', 'Excel dosyası boş veya geçersiz.');
            }

            // Excel Import Service'i oluştur
            $importService = new ExcelImportService($defaultProductId, $defaultDealerId);

            // Parse ve eşleştirme yap (import yapmaz)
            $previewData = $importService->parseAndMatch($rows);

            if (! $previewData['success']) {
                return back()->with('error', $previewData['error'] ?? 'Excel dosyası işlenirken bir hata oluştu.');
            }

            // Preview data'yı session'a kaydet
            Session::put('excel_import_preview_data', [
                'rows' => $previewData['rows'],
                'statistics' => $previewData['statistics'],
                'default_product_id' => $defaultProductId,
                'default_dealer_id' => $defaultDealerId,
            ]);

            return view('excel-import.preview', [
                'previewData' => $previewData,
                'categories' => ProductCategory::where('is_active', true)->orderBy('name')->get(),
            ]);

        } catch (\Exception $e) {
            Log::error('Excel preview hatası', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return back()->with('error', 'Excel dosyası işlenirken bir hata oluştu: ' . $e->getMessage());
        }
    }

    public function confirm(Request $request)
    {
        $previewData = Session::get('excel_import_preview_data');

        if (! $previewData || ! isset($previewData['rows'])) {
            return redirect()->route('excel-import.index')
                ->with('error', 'Preview verisi bulunamadı. Lütfen tekrar deneyin.');
        }

        try {
            // Excel Import Service'i oluştur
            $importService = new ExcelImportService(
                $previewData['default_product_id'] ?? null,
                $previewData['default_dealer_id'] ?? null
            );

            // Preview'dan gelen rows array'ini direkt processPreviewData'a gönder
            // (matched_product ve matched_dealer bilgileri zaten mevcut)
            $result = $importService->processPreviewData($previewData['rows'], auth()->id());

            // Session'ı temizle
            Session::forget('excel_import_preview_data');

            if ($result['error_count'] > 0) {
                $message = "İşlem tamamlandı. Başarılı: {$result['success_count']}, Hata: {$result['error_count']}";
                return redirect()->route('excel-import.index')
                    ->with('warning', $message)
                    ->with('import_result', $result);
            }

            $message = "İşlem başarıyla tamamlandı! {$result['success_count']} adet stok işlendi, {$result['orders_created']} adet sipariş oluşturuldu.";
            return redirect()->route('excel-import.index')
                ->with('success', $message)
                ->with('import_result', $result);

        } catch (\Exception $e) {
            Log::error('Excel import hatası', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            Session::forget('excel_import_preview_data');

            return redirect()->route('excel-import.index')
                ->with('error', 'Excel import sırasında bir hata oluştu: ' . $e->getMessage());
        }
    }

    public function createProduct(Request $request): JsonResponse
    {
        $request->validate([
            'category_id' => 'required|exists:product_categories,id',
            'name' => 'required|string|max:255',
            'sku' => 'required|string|max:255|unique:products,sku',
            'row_index' => 'nullable|integer',
        ]);

        try {
            $product = Product::create([
                'category_id' => $request->input('category_id'),
                'name' => $request->input('name'),
                'sku' => $request->input('sku'),
                'price' => 0, // Default price
                'is_active' => true,
            ]);

            $product->load('category');

            // Eğer row_index varsa, session'daki preview data'yı güncelle
            if ($request->has('row_index')) {
                $previewData = Session::get('excel_import_preview_data');
                if ($previewData && isset($previewData['rows'][$request->input('row_index')])) {
                    $previewData['rows'][$request->input('row_index')]['matched_product'] = [
                        'id' => $product->id,
                        'name' => $product->name,
                        'sku' => $product->sku,
                    ];
                    $previewData['rows'][$request->input('row_index')]['product_match_status'] = 'matched';
                    $previewData['rows'][$request->input('row_index')]['validation_errors'] = array_filter(
                        $previewData['rows'][$request->input('row_index')]['validation_errors'] ?? [],
                        fn($error) => ! str_contains($error, 'Ürün bulunamadı')
                    );
                    Session::put('excel_import_preview_data', $previewData);
                }
            }

            return response()->json([
                'success' => true,
                'product' => [
                    'id' => $product->id,
                    'name' => $product->name,
                    'sku' => $product->sku,
                    'category' => $product->category->name,
                ],
                'message' => 'Ürün başarıyla oluşturuldu.',
            ]);

        } catch (\Exception $e) {
            Log::error('Hızlı ürün oluşturma hatası', [
                'error' => $e->getMessage(),
                'data' => $request->all(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Ürün oluşturulurken bir hata oluştu: ' . $e->getMessage(),
            ], 422);
        }
    }
}

