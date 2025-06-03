<div>
    <flux:heading size="xl">Users</flux:heading>

    <flux:separator class="mt-6 mb-6" />
    <div class="flex flex-row gap-4 items-center justify-between">
        <flux:input type="text" name="search" wire:model.live="search" placeholder="Search" class="w-1/8"/>
        <flux:checkbox label="Only show admins?" name="show_admins" wire:model.live="onlyAdmins" />
        <flux:checkbox label="Only show not signed off?" name="show_missing" wire:model.live="onlyMissing"/>
    </div>

    <flux:separator class="mt-6 mb-6" />

    <flux:table :paginate="$users">
        <flux:table.columns>
            <flux:table.column sortable :sorted="$sortBy === 'surname'" :direction="$sortDirection" wire:click="sort('surname')" class="!text-gray-400">Name</flux:table.column>
            <flux:table.column sortable :sorted="$sortBy === 'email'" :direction="$sortDirection" wire:click="sort('email')" class="!text-gray-400">Email</flux:table.column>
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
                        <flux:menu>
                            <flux:menu.item icon="magnifying-glass" wire:click="showUserDetails({{ $user->id }})">Details</flux:menu.item>
                            <flux:menu.item wire:click="toggleAdmin({{ $user->id }})" icon="pencil">@if ($user->is_admin) Remove admin rights @else Make admin @endif</flux:menu.item>
                        </flux:menu>
                    </flux:dropdown>
                </flux:table.cell>
            </flux:table.row>
        @endforeach
    </flux:table>
    <flux:modal name="user-details" variant="flyout">
            <div class="space-y-6">
                <div>
                    <flux:heading size="lg">User details</flux:heading>
                </div>
                @if ($userDetails)
                    <flux:text>Username: {{ $userDetails['username'] }}</flux:text>
                    <flux:text>Email: {{ $userDetails['email'] }}</flux:text>
                    <flux:text>Surname: {{ $userDetails['surname'] }}</flux:text>
                    <flux:text>Forenames: {{ $userDetails['forenames'] }}</flux:text>
                    <flux:text>Is admin: {{ $userDetails['is_admin'] ? 'Yes' : 'No' }}</flux:text>
                @endif
                <div class="flex">
                    <flux:spacer />

                    <flux:button type="submit" variant="primary" wire:click="closeUserDetails">Close</flux:button>
                </div>
            </div>
        </flux:modal>
</div>
