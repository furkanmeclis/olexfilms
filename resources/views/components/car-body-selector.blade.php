@props(['availableParts' => [], 'selectedParts' => []])

<div 
    x-data="{ 
        selectedParts: @js($selectedParts),
        availableParts: @js($availableParts),
        togglePart(part) {
            if (this.selectedParts.includes(part)) {
                this.selectedParts = this.selectedParts.filter(p => p !== part);
            } else {
                this.selectedParts.push(part);
            }
            // Livewire'a bildir
            @this.set('data.applied_parts', this.selectedParts);
        }
    }"
    class="car-body-selector"
>
    <div class="grid grid-cols-2 gap-4">
        @foreach(\App\Enums\CarPartEnum::cases() as $part)
            @if(empty($availableParts) || in_array($part->value, $availableParts))
                <label
                    class="flex items-center space-x-2 p-3 border rounded cursor-pointer"
                    :class="selectedParts.includes('{{ $part->value }}') ? 'bg-blue-100 border-blue-500' : 'bg-white border-gray-300'"
                    @click="togglePart('{{ $part->value }}')"
                >
                    <input
                        type="checkbox"
                        x-model="selectedParts"
                        :value="'{{ $part->value }}'"
                        class="w-4 h-4 text-blue-600"
                    />
                    <span>{{ \App\Enums\CarPartEnum::getLabels()[$part->value] }}</span>
                </label>
            @endif
        @endforeach
    </div>
</div>

