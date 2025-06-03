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
                <flux:table.cell>{{ $package->name }}</flux:table.cell>
                <flux:table.cell>{{ $package->version }}</flux:table.cell>
                <flux:table.cell>{{ $package->operatingSystems }}</flux:table.cell>
                <flux:table.cell>
                    <flux:dropdown>
                        <flux:button icon="ellipsis-horizontal" variant="ghost" inset />
                        <flux:navmenu>
                            <flux:navmenu.item icon="magnifying-glass">Details</flux:navmenu.item>
                            <flux:navmenu.item href="#" icon="pencil">Change</flux:navmenu.item>
                            <flux:navmenu.item href="#" icon="document-duplicate">New copy</flux:navmenu.item>
                            <flux:menu.separator />
                            <flux:navmenu.item href="#" icon="trash" variant="danger">Delete</flux:navmenu.item>
                        </flux:navmenu>
                    </flux:dropdown>
                </flux:table.cell>
            </flux:table.row>
        @endforeach
    </flux:table>
</div>
