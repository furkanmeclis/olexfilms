<x-filament-panels::page>
    <form wire:submit="save">
        {{ $this->form }}
        
        <div class="fi-form-actions flex items-center justify-end gap-x-3 mt-6">
            <button 
                type="submit" 
                class="fi-btn fi-btn-color-success fi-btn-size-md inline-flex items-center justify-center gap-x-1.5 rounded-lg border border-transparent px-3 py-2 text-sm font-semibold shadow-sm transition duration-75 focus-visible:outline-none disabled:pointer-events-none disabled:opacity-50 bg-success-600 text-white hover:bg-success-500 focus-visible:ring-2 focus-visible:ring-success-500 dark:bg-success-500 dark:hover:bg-success-400 dark:focus-visible:ring-success-400"
            >
                Kaydet
            </button>
        </div>
    </form>
</x-filament-panels::page>
