@php
    use App\Enums\CarPartEnum;
    use App\Services\GenerateCarSvg;
    
    $state = $getState();
    $labels = CarPartEnum::getLabels();
    
    $selectedParts = [];
    if (is_array($state)) {
        $selectedParts = $state;
    } elseif (is_string($state)) {
        // Handle JSON string or comma-separated
        $decoded = json_decode($state, true);
        if (json_last_error() === JSON_ERROR_NONE) {
            $selectedParts = $decoded;
        } else {
            $selectedParts = array_filter(array_map('trim', explode(',', $state)));
        }
    }
    
    $svgService = new GenerateCarSvg();
    $svgBase64 = $svgService->fillCar($selectedParts, true);
@endphp

<x-dynamic-component
    :component="$getEntryWrapperView()"
    :entry="$entry"
>
    <div class="car-part-view space-y-4">
        <!-- SVG Car -->
        @if(!empty($selectedParts))
            <div class="flex justify-center">
                <img 
                    src="{{ $svgBase64 }}" 
                    alt="Araç Parçaları" 
                    class="max-w-full h-auto"
                    style="max-width: 600px;"
                />
            </div>
        @else
            <div class="flex justify-center items-center p-8 bg-gray-100 dark:bg-gray-800 rounded-lg">
                <p class="text-gray-500 dark:text-gray-400">Seçili parça bulunmamaktadır.</p>
            </div>
        @endif

        <!-- Selected Parts List -->
        @if(!empty($selectedParts))
            <div class="space-y-2">
                <h4 class="font-semibold text-sm text-gray-700 dark:text-gray-300">Seçili Parçalar:</h4>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-2">
                    @foreach($selectedParts as $partId)
                        @if(isset($labels[$partId]))
                            <div class="flex items-center gap-2 p-2 rounded bg-gray-50 dark:bg-gray-800">
                                <div 
                                    class="w-3 h-3 rounded-full"
                                    style="background-color: {{ str_starts_with($partId, 'body_') ? '#1a8f14' : '#3db5ff' }}"
                                ></div>
                                <span class="text-sm text-gray-700 dark:text-gray-300">
                                    {{ $labels[$partId] }}
                                </span>
                            </div>
                        @endif
                    @endforeach
                </div>
            </div>
        @endif
    </div>
</x-dynamic-component>

