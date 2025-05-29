<div>
    <flux:card class="space-y-6 mt-6">
        <div>
            <flux:heading size="lg">Some other export?</flux:heading>
            <flux:subheading>Maybe?</flux:subheading>
        </div>

        <div>
            <p>We're working on a few more exports, but if you need something else, let us know.</p>
        </div>
    </flux:card>

    @if (session()->has('success'))
        <div class="mt-6">
            <flux:callout variant="success" icon="check-circle" heading="Success" dismissible>
                {{ session('success') }}
            </flux:callout>
        </div>
    @endif

    <flux:separator class="mt-6 mb-6" />

    <div class="flex flex-col md:flex-row gap-6">

        <div class="flex-1 space-y-6">
            <flux:heading size="lg">Export</flux:heading>
            <flux:subheading>Export all records to an Excel file.</flux:subheading>
            <flux:button wire:click="export" variant="primary">Export</flux:button>
        </div>

        <div class="flex-1 space-y-6">
            <flux:heading size="lg">Import</flux:heading>
            <flux:subheading>Import records from an Excel file.</flux:subheading>
            <form action="{{ route('import-software') }}" method="post" enctype="multipart/form-data" class="space-y-6">
                @csrf
                <flux:input type="file" name="importFile" />
                <flux:button type="submit" variant="primary">Import</flux:button>
                <flux:error name="importFile" />
            </form>
        </div>
    </div>



</div>
