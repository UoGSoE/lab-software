<div>
    <flux:button wire:click="export">Export</flux:button>
    <form action="{{ route('import-software') }}" method="post" enctype="multipart/form-data">
        @csrf
        <flux:input type="file" name="importFile" />
        <flux:button icon="arrow-down" type="submit">Import</flux:button>
        <flux:error name="importFile" />
    </form>

    <flux:separator class="mt-6 mb-6" />

    <flux:card class="space-y-6 mt-6">
        <div>
            <flux:heading size="lg">Some other export?</flux:heading>
            <flux:subheading>Maybe?</flux:subheading>
        </div>

        <div>
            <p>We're working on a few more exports, but if you need something else, let us know.</p>
        </div>
    </flux:card>
</div>
