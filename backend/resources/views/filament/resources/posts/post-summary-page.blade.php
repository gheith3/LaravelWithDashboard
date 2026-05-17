<div class="space-y-6 p-2">

    {{--
        Option A — Filament schema components
        Powered by the content(Schema $schema) method in your PHP class.
    --}}
    {{ $this->content }}


    {{--
        Option B — Plain HTML / Alpine / Tailwind
        The parent record is available via $this->ownerRecord
    --}}
    {{-- <div class="rounded-xl border border-gray-200 bg-white p-6 dark:border-gray-700 dark:bg-gray-900">
        <h3 class="text-lg font-semibold text-gray-900 dark:text-white">
            {{ $this->ownerRecord->name }}
        </h3>
    </div> --}}

</div>
