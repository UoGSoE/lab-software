<div>
    <flux:heading size="xl">Users</flux:heading>

    <flux:separator class="mt-6 mb-6" />

    <div class="flex flex-row gap-4 items-center">
        <flux:input type="text" label="Search" name="search" />
        <flux:checkbox label="Only show admins?" name="show_admins" />
    </div>

    <flux:separator class="mt-6 mb-6" />

    <flux:table :paginate="$users">
        <flux:columns>
            <flux:column>Surname</flux:column>
            <flux:column>Forenames</flux:column>
            <flux:column>Email</flux:column>
            <flux:column>Actions</flux:column>
        </flux:columns>
        @foreach ($users as $user)
            <flux:row>
                <flux:cell>@if ($user->is_admin) <flux:badge color="emerald">Admin</flux:badge> @endif {{ $user->surname }}</flux:cell>
                <flux:cell>{{ $user->forenames }}</flux:cell>
                <flux:cell><a href="mailto:{{ $user->email }}">{{ $user->email }}</a></flux:cell>
                <flux:cell>
                    <flux:dropdown>
                        <flux:button icon="ellipsis-horizontal" variant="ghost" inset />
                        <flux:navmenu>
                            <flux:navmenu.item icon="magnifying-glass">Details</flux:navmenu.item>
                            <flux:navmenu.item href="#" icon="pencil">@if ($user->is_admin) Remove admin rights @else Make admin @endif</flux:navmenu.item>
                            <flux:menu.separator />
                            <flux:navmenu.item href="#" icon="trash" variant="danger">Delete</flux:navmenu.item>
                        </flux:navmenu>
                    </flux:dropdown>
                </flux:cell>
            </flux:row>
        @endforeach
    </flux:table>
</div>
