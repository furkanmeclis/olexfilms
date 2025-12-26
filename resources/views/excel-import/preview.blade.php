@extends('components.layouts.app')

@section('content')
<div class="min-h-screen bg-gray-50 dark:bg-gray-900 py-8">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <!-- Header -->
        <div class="mb-6">
            <h1 class="text-3xl font-bold text-gray-900 dark:text-white">Excel Import Önizleme</h1>
            <p class="mt-2 text-sm text-gray-500 dark:text-gray-400">Verileri kontrol edin ve onaylayın</p>
        </div>

        <!-- Statistics Cards -->
        <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-6">
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 p-4">
                <p class="text-sm font-medium text-gray-500 dark:text-gray-400">Toplam Satır</p>
                <p class="text-2xl font-bold text-gray-900 dark:text-white">{{ $previewData['statistics']['total_rows'] ?? 0 }}</p>
            </div>
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 p-4">
                <p class="text-sm font-medium text-gray-500 dark:text-gray-400">Eşleşen Ürün</p>
                <p class="text-2xl font-bold text-green-600 dark:text-green-400">{{ $previewData['statistics']['matched_products'] ?? 0 }}</p>
            </div>
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 p-4">
                <p class="text-sm font-medium text-gray-500 dark:text-gray-400">Eşleşen Bayi</p>
                <p class="text-2xl font-bold text-green-600 dark:text-green-400">{{ $previewData['statistics']['matched_dealers'] ?? 0 }}</p>
            </div>
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 p-4">
                <p class="text-sm font-medium text-gray-500 dark:text-gray-400">Tahmini Sipariş</p>
                <p class="text-2xl font-bold text-yellow-600 dark:text-yellow-400">{{ $previewData['statistics']['estimated_orders'] ?? 0 }}</p>
            </div>
        </div>

        <!-- Preview Table -->
        <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-900/50">
                <h2 class="text-lg font-semibold text-gray-900 dark:text-white">İşlenecek Satırlar</h2>
            </div>

            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                    <thead class="bg-gray-50 dark:bg-gray-900/50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Satır</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Ürün Kodu</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Ürün</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Eşleşen Ürün</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Bayi</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Eşleşen Bayi</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Durum</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">İşlem</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                        @foreach($previewData['rows'] as $index => $row)
                            <tr class="hover:bg-gray-50 dark:hover:bg-gray-700/50" id="row-{{ $index }}">
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-white">
                                    {{ $row['row_index'] }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900 dark:text-white">
                                    {{ $row['urun_kodu'] ?? '-' }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">
                                    {{ $row['urun'] ?? '-' }}
                                </td>
                                <td class="px-6 py-4 text-sm">
                                    @if($row['product_match_status'] === 'matched')
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800 dark:bg-green-900/30 dark:text-green-300">
                                            {{ $row['matched_product']['name'] ?? '-' }} ({{ $row['matched_product']['sku'] ?? '-' }})
                                        </span>
                                    @else
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800 dark:bg-red-900/30 dark:text-red-300">
                                            Eşleşmedi
                                        </span>
                                    @endif
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">
                                    {{ $row['bayi'] ?? '-' }}
                                </td>
                                <td class="px-6 py-4 text-sm">
                                    @if($row['dealer_match_status'] === 'matched')
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800 dark:bg-green-900/30 dark:text-green-300">
                                            {{ $row['matched_dealer']['name'] ?? '-' }}
                                        </span>
                                    @else
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800 dark:bg-yellow-900/30 dark:text-yellow-300">
                                            Eşleşmedi
                                        </span>
                                    @endif
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm">
                                    @if(empty($row['validation_errors']))
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800 dark:bg-green-900/30 dark:text-green-300">
                                            ✓ Hazır
                                        </span>
                                    @else
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800 dark:bg-red-900/30 dark:text-red-300">
                                            ✗ Hata
                                        </span>
                                    @endif
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                    @if($row['product_match_status'] === 'not_matched')
                                        <button 
                                            type="button"
                                            onclick="toggleProductForm({{ $index }})"
                                            class="text-primary-600 hover:text-primary-900 dark:text-primary-400 dark:hover:text-primary-300"
                                        >
                                            Yeni Ürün Oluştur
                                        </button>
                                    @endif
                                </td>
                            </tr>
                            <!-- Inline Product Form -->
                            @if($row['product_match_status'] === 'not_matched')
                                <tr id="product-form-row-{{ $index }}" class="hidden bg-gray-50 dark:bg-gray-900/30">
                                    <td colspan="8" class="px-6 py-4">
                                        <div class="bg-white dark:bg-gray-800 rounded-lg border border-gray-200 dark:border-gray-700 p-4">
                                            <h4 class="text-sm font-semibold text-gray-900 dark:text-white mb-4">Yeni Ürün Oluştur</h4>
                                            <form id="product-form-{{ $index }}" class="grid grid-cols-1 md:grid-cols-3 gap-4" onsubmit="createProduct(event, {{ $index }})">
                                                @csrf
                                                <input type="hidden" name="row_index" value="{{ $index }}">
                                                <div>
                                                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                                        Kategori <span class="text-danger-500">*</span>
                                                    </label>
                                                    <select 
                                                        name="category_id" 
                                                        required
                                                        class="block w-full rounded-lg border-gray-300 dark:border-gray-600 
                                                               bg-white dark:bg-gray-700 text-gray-900 dark:text-white shadow-sm 
                                                               focus:border-yellow-500 focus:ring-yellow-500 text-sm"
                                                    >
                                                        <option value="">Seçiniz...</option>
                                                        @foreach($categories as $category)
                                                            <option value="{{ $category->id }}">{{ $category->name }}</option>
                                                        @endforeach
                                                    </select>
                                                </div>
                                                <div>
                                                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                                        Ürün Adı <span class="text-red-500">*</span>
                                                    </label>
                                                    <input 
                                                        type="text" 
                                                        name="name" 
                                                        value="{{ $row['urun'] ?? '' }}"
                                                        required
                                                        class="block w-full rounded-lg border-gray-300 dark:border-gray-600 
                                                               bg-white dark:bg-gray-700 text-gray-900 dark:text-white shadow-sm 
                                                               focus:border-yellow-500 focus:ring-yellow-500 text-sm"
                                                    >
                                                </div>
                                                <div>
                                                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                                        SKU <span class="text-red-500">*</span>
                                                    </label>
                                                    <input 
                                                        type="text" 
                                                        name="sku" 
                                                        value="{{ $row['urun_sku'] ?? '' }}"
                                                        required
                                                        class="block w-full rounded-lg border-gray-300 dark:border-gray-600 
                                                               bg-white dark:bg-gray-700 text-gray-900 dark:text-white shadow-sm 
                                                               focus:border-yellow-500 focus:ring-yellow-500 text-sm"
                                                    >
                                                </div>
                                                <div class="md:col-span-3 flex justify-end gap-2">
                                                    <button 
                                                        type="button"
                                                        onclick="toggleProductForm({{ $index }})"
                                                        class="px-4 py-2 text-sm font-medium text-gray-700 dark:text-gray-300 bg-white dark:bg-gray-700 border border-gray-300 dark:border-gray-600 rounded-lg hover:bg-gray-50 dark:hover:bg-gray-600"
                                                    >
                                                        İptal
                                                    </button>
                                                    <button 
                                                        type="submit"
                                                        class="px-4 py-2 text-sm font-medium text-white bg-yellow-600 hover:bg-yellow-700 rounded-lg transition-colors"
                                                    >
                                                        Oluştur
                                                    </button>
                                                </div>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                            @endif
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Action Buttons -->
        <div class="mt-6 flex justify-between items-center">
            <a 
                href="{{ route('excel-import.index') }}"
                class="inline-flex items-center gap-2 px-4 py-2 text-sm font-medium text-gray-700 dark:text-gray-300 bg-white dark:bg-gray-800 border border-gray-300 dark:border-gray-600 rounded-lg hover:bg-gray-50 dark:hover:bg-gray-700"
            >
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                </svg>
                Geri Dön
            </a>

            <form action="{{ route('excel-import.confirm') }}" method="POST" id="confirm-form">
                @csrf
                <button 
                    type="submit"
                    class="inline-flex items-center gap-2 px-6 py-2.5 text-sm font-semibold text-white bg-yellow-600 hover:bg-yellow-700 rounded-lg shadow-sm hover:shadow-md transition duration-200"
                >
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                    </svg>
                    Onayla ve Import Et
                </button>
            </form>
        </div>
    </div>
</div>

<script>
function toggleProductForm(rowIndex) {
    const formRow = document.getElementById(`product-form-row-${rowIndex}`);
    if (formRow) {
        formRow.classList.toggle('hidden');
    }
}

function createProduct(event, rowIndex) {
    event.preventDefault();
    
    const form = event.target;
    const formData = new FormData(form);
    const submitButton = form.querySelector('button[type="submit"]');
    const originalText = submitButton.innerHTML;
    
    // Loading state
    submitButton.disabled = true;
    submitButton.innerHTML = '<span class="animate-spin">⏳</span> Oluşturuluyor...';
    
    fetch('{{ route("excel-import.create-product") }}', {
        method: 'POST',
        body: formData,
        headers: {
            'X-Requested-With': 'XMLHttpRequest',
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            // Başarılı - satırı güncelle
            const row = document.getElementById(`row-${rowIndex}`);
            const productCell = row.querySelector('td:nth-child(4)');
            const statusCell = row.querySelector('td:nth-child(7)');
            const actionCell = row.querySelector('td:nth-child(8)');
            
            productCell.innerHTML = `<span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800 dark:bg-green-900/30 dark:text-green-300">
                ${data.product.name} (${data.product.sku})
            </span>`;
            
            statusCell.innerHTML = `<span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800 dark:bg-green-900/30 dark:text-green-300">
                ✓ Hazır
            </span>`;
            
            actionCell.innerHTML = '';
            
            // Form satırını gizle
            toggleProductForm(rowIndex);
            
            // Başarı mesajı göster
            alert('Ürün başarıyla oluşturuldu!');
        } else {
            alert('Hata: ' + data.message);
            submitButton.disabled = false;
            submitButton.innerHTML = originalText;
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Bir hata oluştu. Lütfen tekrar deneyin.');
        submitButton.disabled = false;
        submitButton.innerHTML = originalText;
    });
}

// Confirm form submit
document.getElementById('confirm-form').addEventListener('submit', function(e) {
    if (!confirm('Import işlemini başlatmak istediğinize emin misiniz?')) {
        e.preventDefault();
    }
});
</script>
@endsection

