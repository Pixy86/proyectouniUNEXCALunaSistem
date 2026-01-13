<div>
    <style>
        .fi-ta-actions .fi-icon-btn {
            width: 2.25rem !important;
            height: 2.25rem !important;
        }
        .fi-ta-actions .fi-icon-btn svg {
            width: 1.25rem !important;
            height: 1.25rem !important;
        }
    </style>
    <div class="bg-white border border-zinc-200 rounded-xl shadow-sm dark:bg-zinc-900 dark:border-zinc-700">
        {{ $this->table }}
    </div>

    <x-filament-actions::modals />
</div>
