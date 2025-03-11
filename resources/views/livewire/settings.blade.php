<div>
    <flux:heading size="xl">Settings</flux:heading>

    <flux:separator class="mt-6 mb-6" />

    <div class="grid auto-cols-max grid-flow-row md:grid-flow-col gap-4">
        <div class="">
            <flux:card>
                <form class="space-y-6">
                    <flux:heading size="lg">Software request period</flux:heading>
                    <flux:input type="date" value="2025-04-08" class="w-full" label="Allow users to change their requirements from" name="open_date" />
                    <flux:input type="date" value="2025-04-25" class="w-full" label="Until" name="close_date" />

                    <flux:separator />

                    <flux:input type="number" label="Number of days before those dates to send reminders" name="reminder_days" value="7" min="0" />

                    <flux:button type="submit">Update</flux:button>
                </form>
            </flux:card>
        </div>

        <div class="min-w-[300px]">
            <flux:card class="space-y-6">
                <div class="flex flex-row justify-between items-center">
                    <flux:heading size="lg">Schools</flux:heading>
                    <flux:modal.trigger name="create-new-school">
                        <flux:button type="button" icon="plus"></flux:button>
                    </flux:modal.trigger>
                </div>

                <ul class="space-y-2">
                    @foreach ($schools as $school)
                        <li class="flex flex-row justify-between items-center">
                            <span>{{ $school->name }} <flux:badge>{{ $school->course_prefix }}</flux:badge></span>
                            <span>
                                <flux:button type="button" icon="pencil" wire:click="editSchool({{ $school->id }})"></flux:button>
                                <flux:button type="button" icon="trash" wire:confirm="Are you sure you want to delete this school ({{ $school->name }})? This action cannot be undone." wire:click="deleteSchool({{ $school->id }})"></flux:button>
                            </span>
                        </li>
                    @endforeach
                </ul>
            </flux:card>
        </div>

        <div class="min-w-[300px]">
            <flux:card class="space-y-6">
                <div class="flex flex-row justify-between items-center">
                    <flux:heading size="lg">Academic sessions</flux:heading>
                    <flux:modal.trigger name="create-new-session">
                        <flux:button type="button" icon="plus"></flux:button>
                    </flux:modal.trigger>
                </div>

                <ul class="space-y-2">
                    @foreach ($academicSessions as $academicSession)
                        <li>{{ $academicSession->name }} @if ($academicSession->is_default) <flux:badge inset="top bottom">Default</flux:badge>@endif</li>
                    @endforeach
                </ul>
            </flux:card>
        </div>
    </div>

    <flux:modal name="create-new-session" variant="flyout" class="space-y-6">
        <form wire:submit="createNewSession" class="space-y-6">
            <flux:heading>Create new academic session</flux:heading>

            <div class="grid grid-cols-2 gap-4">
                <flux:input type="number" wire:model="newSessionNameStart" label="Start year" required />
                <flux:input type="number" wire:model="newSessionNameEnd" label="End year" required />
            </div>

            <flux:checkbox wire:model="newSessionIsDefault" label="Set as default session?" />

            <div class="flex flex-row justify-end gap-2">
                <flux:button variant="primary" type="submit">Create</flux:button>
                <flux:button type="button" x-on:click="$flux.modal('create-new-session').close()">Cancel</flux:button>
            </div>
        </form>
    </flux:modal>

    <flux:modal name="create-new-school" variant="flyout" class="space-y-6">
        <form wire:submit="createNewSchool" class="space-y-6">
            <flux:heading>Create new school</flux:heading>

            <flux:input type="text" label="School name" name="name" wire:model="newSchoolName" required />
            <flux:input type="text" label="Course prefix" description="(Eg, 'ENG', 'MATH')" name="course_prefix" wire:model="newSchoolCoursePrefix" required />

            <div class="flex flex-row justify-end gap-2">
                <flux:button variant="primary" type="submit">Create</flux:button>
                <flux:button type="button" x-on:click="$flux.modal('create-new-school').close()">Cancel</flux:button>
            </div>
        </form>
    </flux:modal>

    <flux:modal name="edit-school" variant="flyout" class="space-y-6">
        <form wire:submit="updateSchool" class="space-y-6">
            <flux:heading>Edit school</flux:heading>

            <flux:input type="text" label="School name" name="name" wire:model="editSchoolName" required />
            <flux:input type="text" label="Course prefix" description="(Eg, 'ENG', 'MATH')" name="course_prefix" wire:model="editSchoolCoursePrefix" required />

            <div class="flex flex-row justify-end gap-2">
                <flux:button variant="primary" type="submit">Update</flux:button>
                <flux:button type="button" x-on:click="$flux.modal('edit-school').close()">Cancel</flux:button>
            </div>
        </form>
    </flux:modal>

    <flux:modal name="delete-school" variant="flyout" class="space-y-6">
        <form wire:submit="deleteSchool" class="space-y-6">
            <flux:heading>Delete school</flux:heading>

            <flux:subheading>Are you sure you want to delete this school? This action cannot be undone.</flux:subheading>

            <div class="flex flex-row justify-end gap-2">
                <flux:button variant="danger" type="submit">Delete</flux:button>
                <flux:button type="button" x-on:click="$flux.modal('delete-school').close()">Cancel</flux:button>
            </div>
        </form>
    </flux:modal>
