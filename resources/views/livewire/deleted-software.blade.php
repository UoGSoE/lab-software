<div>
    <flux:heading size="xl">Software Pending Deletion</flux:heading>

    <flux:separator class="mt-6 mb-6" />
    <flux:table>
        <flux:table.columns>
            <flux:table.column width="50%">Package</flux:table.column>
            <flux:table.column width="10%">Version</flux:table.column>
            <flux:table.column width="10%">O/S</flux:table.column>
            <flux:table.column width="10%"></flux:table.column>
        </flux:table.columns>

        @foreach ($software as $package)
            <flux:table.row>
                <flux:table.cell>
                    @if ($package->removed_at) 
                        <flux:badge variant="pill" color="green" class="cursor-pointer" aria-label="Unmark for removal" wire:click="unmarkForRemoval({{ $package->id }})">Restore</flux:badge> 
                    @endif 
                    {{ $package->name }}
                </flux:table.cell>
                <flux:table.cell>{{ $package->version }}</flux:table.cell>
                <flux:table.cell>{{ $package->operatingSystems }}</flux:table.cell>
                <flux:table.cell>
                    <flux:button size="sm" inset icon="trash" variant="danger" wire:click="deleteSoftware({{ $package->id }})"></flux:button>
                </flux:table.cell>
            </flux:table.row>
        @endforeach
    </flux:table>
</div>
