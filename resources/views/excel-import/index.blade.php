@extends('components.layouts.app')

@section('content')
    <div class="min-h-screen bg-gray-50 dark:bg-gray-900 py-8">
        <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8">
            <div
                class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 overflow-hidden">
                <!-- Header -->
                <div class="px-6 py-5 border-b border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-900/50">
                    <div class="flex items-center justify-between">
                        <div>
                            <h1 class="text-2xl font-bold text-gray-900 dark:text-white">Excel Stok Import</h1>
                            <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Excel dosyanızı yükleyin, verileri
                                inceleyin ve onaylayın</p>
                        </div>
                    </div>
                </div>

                <!-- Content -->
                <div class="p-6">

                    @if (session('success'))
                        <div
                            class="mb-6 rounded-lg bg-success-50 dark:bg-success-900/20 border border-success-200 dark:border-success-800 p-4">
                            <div class="flex">
                                <div class="flex-shrink-0">
                                    <svg class="h-5 w-5 text-success-400" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd"
                                            d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z"
                                            clip-rule="evenodd" />
                                    </svg>
                                </div>
                                <div class="ml-3">
                                    <p class="text-sm font-medium text-success-800 dark:text-success-200">
                                        {{ session('success') }}</p>
                                </div>
                            </div>
                        </div>
                    @endif

                    @if (session('warning'))
                        <div
                            class="mb-6 rounded-lg bg-warning-50 dark:bg-warning-900/20 border border-warning-200 dark:border-warning-800 p-4">
                            <div class="flex">
                                <div class="flex-shrink-0">
                                    <svg class="h-5 w-5 text-warning-400" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd"
                                            d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z"
                                            clip-rule="evenodd" />
                                    </svg>
                                </div>
                                <div class="ml-3">
                                    <p class="text-sm font-medium text-warning-800 dark:text-warning-200">
                                        {{ session('warning') }}</p>
                                </div>
                            </div>
                        </div>
                    @endif

                    @if (session('error'))
                        <div
                            class="mb-6 rounded-lg bg-danger-50 dark:bg-danger-900/20 border border-danger-200 dark:border-danger-800 p-4">
                            <div class="flex">
                                <div class="flex-shrink-0">
                                    <svg class="h-5 w-5 text-danger-400" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd"
                                            d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z"
                                            clip-rule="evenodd" />
                                    </svg>
                                </div>
                                <div class="ml-3">
                                    <p class="text-sm font-medium text-danger-800 dark:text-danger-200">
                                        {{ session('error') }}</p>
                                </div>
                            </div>
                        </div>
                    @endif

                    @if ($errors->any())
                        <div
                            class="mb-6 rounded-lg bg-danger-50 dark:bg-danger-900/20 border border-danger-200 dark:border-danger-800 p-4">
                            <div class="flex">
                                <div class="flex-shrink-0">
                                    <svg class="h-5 w-5 text-danger-400" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd"
                                            d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z"
                                            clip-rule="evenodd" />
                                    </svg>
                                </div>
                                <div class="ml-3">
                                    <ul
                                        class="list-disc list-inside space-y-1 text-sm text-danger-800 dark:text-danger-200">
                                        @foreach ($errors->all() as $error)
                                            <li>{{ $error }}</li>
                                        @endforeach
                                    </ul>
                                </div>
                            </div>
                        </div>
                    @endif

                    @if (session('import_result'))
                        @php
                            $result = session('import_result');
                        @endphp
                        <div
                            class="mb-6 rounded-lg bg-primary-50 dark:bg-primary-900/20 border border-primary-200 dark:border-primary-800 p-4">
                            <h2 class="text-lg font-semibold mb-3 text-primary-900 dark:text-primary-100">Import Sonuçları
                            </h2>
                            <div class="space-y-2 text-sm">
                                <div class="grid grid-cols-3 gap-4">
                                    <div>
                                        <p class="text-gray-500 dark:text-gray-400">Başarılı</p>
                                        <p class="text-2xl font-bold text-success-600 dark:text-success-400">
                                            {{ $result['success_count'] ?? 0 }}</p>
                                    </div>
                                    <div>
                                        <p class="text-gray-500 dark:text-gray-400">Hatalı</p>
                                        <p class="text-2xl font-bold text-danger-600 dark:text-danger-400">
                                            {{ $result['error_count'] ?? 0 }}</p>
                                    </div>
                                    <div>
                                        <p class="text-gray-500 dark:text-gray-400">Oluşturulan Sipariş</p>
                                        <p class="text-2xl font-bold text-primary-600 dark:text-primary-400">
                                            {{ $result['orders_created'] ?? 0 }}</p>
                                    </div>
                                </div>

                                @if (isset($result['errors']) && count($result['errors']) > 0)
                                    <div class="mt-4 pt-4 border-t border-primary-200 dark:border-primary-700">
                                        <p class="font-semibold mb-2 text-primary-900 dark:text-primary-100">Hatalar:</p>
                                        <ul
                                            class="list-disc list-inside space-y-1 text-danger-600 dark:text-danger-400 text-sm max-h-40 overflow-y-auto">
                                            @foreach ($result['errors'] as $error)
                                                <li>{{ $error }}</li>
                                            @endforeach
                                        </ul>
                                    </div>
                                @endif

                                @if (isset($result['orders']) && count($result['orders']) > 0)
                                    <div class="mt-4 pt-4 border-t border-primary-200 dark:border-primary-700">
                                        <p class="font-semibold mb-2 text-primary-900 dark:text-primary-100">Oluşturulan
                                            Siparişler:</p>
                                        <ul
                                            class="list-disc list-inside space-y-1 text-sm text-primary-800 dark:text-primary-200">
                                            @foreach ($result['orders'] as $order)
                                                <li>
                                                    Sipariş #{{ $order->id }} -
                                                    {{ $order->dealer->name ?? 'Merkez' }}
                                                    ({{ $order->items ? $order->items->sum('quantity') : 0 }} adet ürün)
                                                </li>
                                            @endforeach
                                        </ul>
                                    </div>
                                @endif
                            </div>
                        </div>
                    @endif

                    <form action="{{ route('excel-import.preview') }}" method="POST" enctype="multipart/form-data"
                        class="space-y-6">
                        @csrf

                        <div class="space-y-6">
                            <!-- File Upload -->
                            <div>
                                <label for="excel_file"
                                    class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                    Excel Dosyası <span class="text-danger-500">*</span>
                                </label>
                                <div
                                    class="mt-1 flex justify-center px-6 pt-5 pb-6 border-2 border-gray-300 dark:border-gray-600 border-dashed rounded-lg hover:border-primary-400 dark:hover:border-primary-500 transition-colors">
                                    <div class="space-y-1 text-center">
                                        <svg class="mx-auto h-12 w-12 text-gray-400" stroke="currentColor" fill="none"
                                            viewBox="0 0 48 48">
                                            <path
                                                d="M28 8H12a4 4 0 00-4 4v20m32-12v8m0 0v8a4 4 0 01-4 4H12a4 4 0 01-4-4v-4m32-4l-3.172-3.172a4 4 0 00-5.656 0L28 28M8 32l9.172-9.172a4 4 0 015.656 0L28 28m0 0l4 4m4-24h8m-4-4v8m-12 4h.02"
                                                stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
                                        </svg>
                                        <div class="flex text-sm text-gray-600 dark:text-gray-400">
                                            <label for="excel_file"
                                                class="relative cursor-pointer bg-white dark:bg-gray-800 rounded-md font-medium text-primary-600 hover:text-primary-500 focus-within:outline-none focus-within:ring-2 focus-within:ring-offset-2 focus-within:ring-primary-500">
                                                <span>Dosya seçin</span>
                                                <input id="excel_file" name="excel_file" type="file" accept=".xlsx,.xls"
                                                    required class="sr-only" onchange="updateFileName(this)">
                                            </label>
                                            <p class="pl-1">veya sürükleyip bırakın</p>
                                        </div>
                                        <p class="text-xs text-gray-500 dark:text-gray-400" id="file-name">.xlsx veya .xls
                                            dosyası seçin</p>
                                        <p class="text-xs text-gray-500 dark:text-gray-400">Maksimum dosya boyutu: 10MB</p>
                                    </div>
                                </div>
                                <p class="mt-2 text-sm text-gray-500 dark:text-gray-400">
                                    Desteklenen formatlar: .xlsx, .xls (Maksimum: 10MB)
                                </p>
                            </div>

                            <script>
                                function updateFileName(input) {
                                    const fileName = input.files[0]?.name || '.xlsx veya .xls dosyası seçin';
                                    document.getElementById('file-name').textContent = fileName;
                                }
                            </script>

                            <!-- Default Options -->
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                <div>
                                    <label for="default_product_id"
                                        class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                        Varsayılan Ürün <span
                                            class="text-gray-500 dark:text-gray-400 text-xs">(Opsiyonel)</span>
                                    </label>
                                    <select id="default_product_id" name="default_product_id"
                                        class="block w-full rounded-lg border-gray-300 dark:border-gray-600 
                                       bg-white dark:bg-gray-700 text-gray-900 dark:text-white shadow-sm 
                                       focus:border-primary-500 focus:ring-primary-500 text-sm
                                       disabled:bg-gray-50 disabled:text-gray-500 disabled:cursor-not-allowed">
                                        <option value="">Seçiniz...</option>
                                        @foreach ($products as $product)
                                            <option value="{{ $product->id }}">{{ $product->name }}
                                                ({{ $product->sku }})
                                            </option>
                                        @endforeach
                                    </select>
                                    <p class="mt-2 text-sm text-gray-500 dark:text-gray-400">
                                        Eşleştirme yapılamazsa bu ürün kullanılacak
                                    </p>
                                </div>

                                <div>
                                    <label for="default_dealer_id"
                                        class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                        Varsayılan Bayi <span
                                            class="text-gray-500 dark:text-gray-400 text-xs">(Opsiyonel)</span>
                                    </label>
                                    <select id="default_dealer_id" name="default_dealer_id"
                                        class="block w-full rounded-lg border-gray-300 dark:border-gray-600 
                                       bg-white dark:bg-gray-700 text-gray-900 dark:text-white shadow-sm 
                                       focus:border-primary-500 focus:ring-primary-500 text-sm
                                       disabled:bg-gray-50 disabled:text-gray-500 disabled:cursor-not-allowed">
                                        <option value="">Seçiniz...</option>
                                        @foreach ($dealers as $dealer)
                                            <option value="{{ $dealer->id }}">{{ $dealer->name }}
                                                ({{ $dealer->dealer_code }})
                                            </option>
                                        @endforeach
                                    </select>
                                    <p class="mt-2 text-sm text-gray-500 dark:text-gray-400">
                                        Eşleştirme yapılamazsa bu bayi kullanılacak
                                    </p>
                                </div>
                            </div>

                            <!-- Info Box -->
                            <div
                                class="bg-primary-50 dark:bg-primary-900/20 border border-primary-200 dark:border-primary-800 rounded-lg p-4">
                                <div class="flex">
                                    <div class="flex-shrink-0">
                                        <svg class="h-5 w-5 text-primary-400" fill="currentColor" viewBox="0 0 20 20">
                                            <path fill-rule="evenodd"
                                                d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z"
                                                clip-rule="evenodd" />
                                        </svg>
                                    </div>
                                    <div class="ml-3 flex-1">
                                        <h3 class="text-sm font-semibold text-primary-800 dark:text-primary-200 mb-2">
                                            Excel Kolon Yapısı
                                        </h3>
                                        <p class="text-sm text-primary-700 dark:text-primary-300 mb-3">
                                            Excel dosyanızda aşağıdaki kolonlar bulunmalıdır (başlık satırı zorunludur):
                                        </p>
                                        <div class="grid grid-cols-1 md:grid-cols-2 gap-2">
                                            <div class="text-sm text-primary-700 dark:text-primary-300">
                                                <p class="font-medium mb-1">Zorunlu Kolonlar:</p>
                                                <ul class="space-y-1 text-xs">
                                                    <li class="flex items-center gap-1">
                                                        <span class="text-danger-500">•</span>
                                                        <strong>ÜRÜN KODU</strong> veya <strong>BARKOD</strong>
                                                    </li>
                                                </ul>
                                            </div>
                                            <div class="text-sm text-primary-700 dark:text-primary-300">
                                                <p class="font-medium mb-1">Opsiyonel Kolonlar:</p>
                                                <ul class="space-y-1 text-xs">
                                                    <li><strong>TARİH</strong>, <strong>KATEGORİ</strong>,
                                                        <strong>MARKA</strong>
                                                    </li>
                                                    <li><strong>ÜRÜN</strong> / <strong>ÜRÜN ADI</strong> (eşleştirme için)
                                                    </li>
                                                    <li><strong>ÜRÜN SKU</strong> (eşleştirme için)</li>
                                                    <li><strong>BAYİ</strong> / <strong>BAYİ ADI</strong> (eşleştirme için)
                                                    </li>
                                                    <li><strong>BAYİ KODU</strong> (eşleştirme için)</li>
                                                </ul>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="flex justify-end gap-3">
                                <button type="submit"
                                    class="inline-flex items-center gap-2 px-6 py-2.5 bg-yellow-600 hover:bg-yellow-700 text-white font-semibold rounded-lg 
                           transition duration-200 shadow-sm hover:shadow-md
                           focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-yellow-500">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z">
                                        </path>
                                    </svg>
                                    İncele ve Onayla
                                </button>
                            </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
    </div>
@endsection
