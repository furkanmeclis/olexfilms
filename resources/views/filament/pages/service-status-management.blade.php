<x-filament-panels::page>
    <div class="space-y-6">
        {{-- Hizmet Arama Formu --}}
        <form wire:submit="searchService">
            @php
                $searchSection = $this->form->getComponent('Hizmet Arama');
            @endphp
            {{ $searchSection }}
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
                {{ $logSection }}
            </form>

            {{-- Servis Geçmişi Tablosu --}}
            <div class="fi-section rounded-xl bg-white p-6 shadow-sm ring-1 ring-gray-950/5 dark:bg-gray-900 dark:ring-white/10">
                <h3 class="text-lg font-semibold mb-4">Servis Geçmişi</h3>
                {{ $this->serviceStatusLogsTable }}
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

