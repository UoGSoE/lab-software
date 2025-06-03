<div>
    <flux:heading size="xl">College-wide software</flux:heading>

    <flux:separator class="mt-6 mb-6" />
    <flux:input type="text" name="search" wire:model.live="search" placeholder="Search" class="w-80"/>
    <flux:table>
        <flux:table.columns>
            <flux:table.column sortable :sorted="$sortBy === 'name'" :direction="$sortDirection" wire:click="sort('name')" width="50%">Package</flux:table.column>
            <flux:table.column sortable width="10%">Version</flux:table.column>
            <flux:table.column sortable width="10%">O/S</flux:table.column>
            <flux:table.column width="10%"></flux:table.column>
        </flux:table.columns>

        @foreach ($software as $package)
            <flux:table.row>
                <flux:table.cell>@if ($package->removed_at) <flux:badge variant="pill" color="red" class="cursor-pointer" title="Unmark for removal" aria-label="Unmark for removal" wire:click="unmarkForRemoval({{ $package->id }})">Marked for removal</flux:badge> @endif {{ $package->name }}</flux:table.cell>
                <flux:table.cell>{{ $package->name }}</flux:table.cell>
                <flux:table.cell>{{ $package->version }}</flux:table.cell>
                <flux:table.cell>{{ $package->operatingSystems }}</flux:table.cell>
                <flux:table.cell>
                    <flux:dropdown>
                        <flux:button icon="ellipsis-horizontal" variant="ghost" inset />
                        <flux:menu>
                            <flux:menu.item icon="magnifying-glass">Details</flux:menu.item>
                            <flux:menu.item href="#" icon="pencil">Change</flux:menu.item>
                            <flux:menu.item href="#" icon="document-duplicate">New copy</flux:menu.item>
                            <flux:menu.separator />
                            <flux:menu.item href="#" icon="trash" variant="danger" wire:click="removeSoftware({{ $package->id }})">Delete</flux:menu.item>
                        </flux:menu>
                    </flux:dropdown>
                </flux:table.cell>
            </flux:table.row>
        @endforeach
    </flux:table>
</div>
