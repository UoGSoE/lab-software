<div>
    <flux:button>Add software</flux:button>

    <flux:separator class="mt-6 mb-6" />

    <flux:table>
        <flux:columns>
            <flux:column sortable :sorted="$sortBy === 'name'" :direction="$sortDirection" wire:click="sort('name')" width="50%">Package</flux:column>
            <flux:column sortable width="10%">Version</flux:column>
            <flux:column sortable width="10%">O/S</flux:column>
            <flux:column sortable :sorted="$sortBy === 'location'" :direction="$sortDirection" wire:click="sort('location')" width="20%">Location</flux:column>
            <flux:column width="10%"></flux:column>
        </flux:columns>

        @foreach ($software as $package)
            <flux:row>
                <flux:cell>{{ $package->name }}</flux:cell>
                <flux:cell>{{ $package->version }}</flux:cell>
                <flux:cell>{{ $package->os }}</flux:cell>
                <flux:cell>{{ $package->location }}</flux:cell>
                <flux:cell>
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
                </flux:cell>
            </flux:row>
        @endforeach
    </flux:table>
</div>
