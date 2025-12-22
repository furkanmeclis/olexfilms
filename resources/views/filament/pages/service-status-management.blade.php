<x-filament-panels::page>
    <div class="space-y-6">
        {{-- Hizmet Arama Formu --}}
        <form wire:submit="searchService">
            {{ $this->form }}
            
            <div class="fi-form-actions flex items-center justify-end gap-x-3 mt-6">
                <button 
                    type="submit" 
                    class="fi-btn fi-btn-color-primary fi-btn-size-md inline-flex items-center justify-center gap-x-1.5 rounded-lg border border-transparent px-3 py-2 text-sm font-semibold shadow-sm transition duration-75 focus-visible:outline-none disabled:pointer-events-none disabled:opacity-50"
                >
                    <x-filament::icon icon="heroicon-o-magnifying-glass" class="h-4 w-4" />
                    Ara
                </button>
            </div>
        </form>

        @if($this->service)
            {{-- Servis Detayları Infolist --}}
            <div class="fi-section rounded-xl bg-white p-6 shadow-sm ring-1 ring-gray-950/5 dark:bg-gray-900 dark:ring-white/10">
                <h3 class="text-lg font-semibold mb-4">Servis Detayları</h3>
                {{ $this->serviceInfolist }}
            </div>

            {{-- Log Ekleme Formu --}}
            <form wire:submit="addLog">
                @php
                    $logSection = $this->form->getComponent('Log Ekleme');
                @endphp
                @if($logSection)
                    {{ $logSection }}
                    
                    <div class="fi-form-actions flex items-center justify-end gap-x-3 mt-6">
                        <button 
                            type="submit" 
                            class="fi-btn fi-btn-color-success fi-btn-size-md inline-flex items-center justify-center gap-x-1.5 rounded-lg border border-transparent px-3 py-2 text-sm font-semibold shadow-sm transition duration-75 focus-visible:outline-none disabled:pointer-events-none disabled:opacity-50 bg-success-600 text-white hover:bg-success-500 focus-visible:ring-2 focus-visible:ring-success-500 dark:bg-success-500 dark:hover:bg-success-400 dark:focus-visible:ring-success-400"
                        >
                            <x-filament::icon icon="heroicon-o-plus-circle" class="h-4 w-4" />
                            Log Ekle
                        </button>
                    </div>
                @endif
            </form>

            {{-- Servis Geçmişi Tablosu --}}
            <div class="fi-section rounded-xl bg-white p-6 shadow-sm ring-1 ring-gray-950/5 dark:bg-gray-900 dark:ring-white/10">
                <h3 class="text-lg font-semibold mb-4">Servis Geçmişi</h3>
                {{ $this->table }}
            </div>
        @else
            <div class="fi-section rounded-xl bg-white p-6 shadow-sm ring-1 ring-gray-950/5 dark:bg-gray-900 dark:ring-white/10">
                <div class="text-center py-12">
                    <x-filament::icon icon="heroicon-o-magnifying-glass" class="mx-auto h-12 w-12 text-gray-400" />
                    <h3 class="mt-4 text-sm font-semibold text-gray-900 dark:text-white">Hizmet Bulunamadı</h3>
                    <p class="mt-2 text-sm text-gray-500 dark:text-gray-400">Hizmet numarası ile arama yaparak servis bilgilerini görüntüleyebilirsiniz.</p>
                </div>
            </div>
        @endif
    </div>
</x-filament-panels::page>

