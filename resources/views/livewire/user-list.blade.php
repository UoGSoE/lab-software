<div>
    <flux:heading size="xl">Users</flux:heading>

    <flux:separator class="mt-6 mb-6" />

    <div class="flex flex-row gap-4 items-center">
        <flux:input type="text" label="Search" name="search" wire:model.live="search" />
        <flux:checkbox label="Only show admins?" name="show_admins" wire:model.live="onlyAdmins" />
        <flux:checkbox label="Only show not signed off?" name="show_missing" wire:model.live="onlyMissing" />
    </div>

    <flux:separator class="mt-6 mb-6" />

    <flux:table :paginate="$users">
        <flux:table.columns>
            <flux:table.column>Name</flux:table.column>
            <flux:table.column>Email</flux:table.column>
            <flux:table.column>Signed off</flux:table.column>
            <flux:table.column>Actions</flux:table.column>
        </flux:table.columns>
        @foreach ($users as $user)
            <flux:table.row>
                <flux:table.cell>@if ($user->is_admin) <flux:badge color="emerald">Admin</flux:badge> @endif {{ $user->surname }}, {{ $user->forenames }}</flux:table.cell>
                <flux:table.cell><a href="mailto:{{ $user->email }}">{{ $user->email }}</a></flux:table.cell>
                <flux:table.cell>@foreach($user->courses as $course) <flux:badge>{{ $course->code }} {{ $course->pivot->created_at->format('d/m/y') }}</flux:badge>@endforeach</flux:table.cell>
                <flux:table.cell>
                    <flux:dropdown>
                        <flux:button icon="ellipsis-horizontal" variant="ghost" inset />
                        <flux:navmenu>
                            <flux:navmenu.item icon="magnifying-glass">Details</flux:navmenu.item>
                            <flux:navmenu.item href="#" icon="pencil">@if ($user->is_admin) Remove admin rights @else Make admin @endif</flux:navmenu.item>
                            <flux:menu.separator />
                            <flux:navmenu.item href="#" icon="trash" variant="danger">Delete</flux:navmenu.item>
                        </flux:navmenu>
                    </flux:dropdown>
                </flux:table.cell>
            </flux:table.row>
        @endforeach
    </flux:table>
</div>
