<div>
    <div class="flex flex-col md:flex-row justify-between gap-2 items-center">
        <div class="flex flex-row gap-2 items-center">
            <div>
                <flux:heading size="xl">COSE teaching software</flux:heading>
                <flux:subheading class="text-center md:text-left">Session {{ $academicSession }}</flux:subheading>
            </div>
        </div>
        <div class="flex flex-col md:flex-row gap-2 items-center">
            <flux:button icon="plus" variant="filled" wire:click="requestNewSoftware(null)">Request new software</flux:button>
        </div>
    </div>

    <flux:separator class="mt-6" />

    <!-- filters -->
    <div class="flex flex-col md:flex-row gap-2 pt-6">
        <flux:select wire:model.live="filters.school" label="Filter by school">
            <flux:select.option value="">All</flux:select.option>
            @foreach ($availableSchools as $school)
                <flux:select.option value="{{ $school->course_prefix }}">{{ $school->name }}</flux:select.option>
            @endforeach
        </flux:select>

        <flux:input wire:model.live="filters.course" label="Course" placeholder="Eg, ENG1234" autofocus />
    </div>

    <flux:separator class="mt-6" />

    @foreach ($courses as $course)
        <flux:card class="space-y-6 mt-6" wire:key="course-{{ $course->id }}">
            <div class="flex flex-col md:flex-row gap-2 justify-between">
                <div class="space-y-6">
                    <div class="flex flex-row gap-2 justify-between items-center">
                        <flux:heading size="lg">{{ $course->code }}</flux:heading>
                        <flux:button
                            :variant="$course->signed_off ? 'ghost' : 'filled'"
                            wire:click="signOff({{ $course->id }})"
                            :disabled="$course->signed_off"
                            class="block md:hidden"
                            inset
                        >
                        @if ($course->signed_off)
                            Signed off
                        @else
                            Sign off
                        @endif
                        </flux:button>
                    </div>
                    <flux:subheading>{{ $course->title }}</flux:subheading>
                </div>
                <div class="flex flex-col md:flex-row gap-2 items-center">
                    <flux:button
                        :variant="$course->signed_off ? 'ghost' : 'filled'"
                        wire:click="signOff({{ $course->id }})"
                        :disabled="$course->signed_off"
                        class="hidden md:block"
                    >
                        @if ($course->signed_off)
                            Signed off
                        @else
                            Sign off
                        @endif
                    </flux:button>
                    <flux:button variant="filled" icon="plus" wire:click="requestNewSoftware({{ $course->id }})">Request new software</flux:button>
                </div>
            </div>

            <div>
                <flux:table>
                    <flux:table.columns>
                        <flux:table.column width="50%">Package</flux:table.column>
                        <flux:table.column width="10%">Version</flux:table.column>
                        <flux:table.column class="hidden md:table-cell" width="10%">O/S</flux:table.column>
                        <flux:table.column class="hidden md:table-cell" width="20%">Lab</flux:table.column>
                        <flux:table.column width="10%"></flux:table.column>
                    </flux:table.columns>

                    @foreach ($course->software as $software)
                        <flux:table.row>
                            <flux:table.cell>{{ $software->name }}</flux:table.cell>
                            <flux:table.cell>{{ $software->version }}</flux:table.cell>
                            <flux:table.cell class="hidden md:table-cell">{{ $software->operatingSystems }}</flux:table.cell>
                            <flux:table.cell class="hidden md:table-cell">{{ $software->location }}</flux:table.cell>
                            <flux:table.cell>
                                <flux:dropdown>
                                    <flux:button icon="ellipsis-horizontal" variant="ghost" inset />
                                    <flux:navmenu>
                                        <flux:navmenu.item icon="magnifying-glass" wire:click="viewSoftwareDetails({{ $software->id }})">
                                            Details
                                        </flux:navmenu.item>
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
        </flux:card>
    @endforeach

    @if ($courses->hasPages())
        <flux:separator class="mt-6 mb-6" />
        {{ $courses->links() }}
    @endif

    <!-- add software modal -->
    <flux:modal name="add-software" variant="flyout" class="space-y-6">
        <div>
            <flux:heading size="lg">@if ($newSoftware['id']) Edit @else Add @endif software</flux:heading>
            <flux:subheading>Please fill in the details below.</flux:subheading>
        </div>

        <form wire:submit="addSoftware" class="space-y-6">
            <input type="hidden" wire:model="newSoftware.id" />
            <flux:input label="Name of package (required)" placeholder="Eg, StarCCM+" wire:model="newSoftware.name" />

            <flux:field>
                <flux:input label="Version" placeholder="Eg, 2024.01.01" wire:model="newSoftware.version" />
                <flux:description>(Leave blank if you don't know or it doesn't matter)</flux:description>
            </flux:field>

            <flux:checkbox.group wire:model="newSoftware.os" label="Operating System">
                <flux:checkbox label="Windows" value="Windows" />
                <flux:checkbox label="Mac" value="Mac" />
                <flux:checkbox label="Linux" value="Linux" />
                <flux:checkbox label="BSD" value="BSD" />
            </flux:checkbox.group>

            <flux:input wire:model.blur="newSoftware.course_code" label="Course code (required)" placeholder="Eg, ENG1234"  />
            @if ($newSoftware['course_code'] && !$courseCodes->contains(strtoupper(trim($newSoftware['course_code']))))
                <flux:description class="-mt-2">(New course code will be created)</flux:description>
            @endif

            <flux:input label="Lab (if known)" placeholder="Eg, Rankine 329" wire:model="newSoftware.lab" />

            <flux:textarea label="Configuration" placeholder="Any additional configuration you want to add, plugins, etc." wire:model="newSoftware.config" />

            <flux:textarea label="Notes" placeholder="Any extra information you want to add? A website link, a note about the software, etc." wire:model="newSoftware.notes" />

            <flux:checkbox label="Free software?" wire:model="newSoftware.is_free" />

            <div class="flex">
                <flux:spacer />

                <div class="flex flex-row justify-end gap-2">
                    <flux:button type="submit" variant="primary">Submit request</flux:button>
                    <flux:button type="button" x-on:click="$flux.modal('add-software').close()">Cancel</flux:button>
                </div>
            </div>
        </form>
    </flux:modal>

    <!-- software details modal -->
    <flux:modal name="software-details" variant="flyout" class="space-y-6">
        <div>
            <flux:heading size="lg">Software details</flux:heading>
        </div>

        <flux:card class="space-y-6">
            <div>
                <flux:heading>Basic details</flux:heading>
            </div>

            <div>
                <ul>
                    <li>
                        <flux:icon.bolt>{{ $softwareDetails->name }}</flux:icon.bolt>
                    </li>
                    <li>
                        <flux:icon.bolt>{{ $softwareDetails->version ?? 'N/A' }}</flux:icon.bolt>
                    </li>
                    <li>
                        <flux:icon.bolt>{{ $softwareDetails->operating_systems }}</flux:icon.bolt>
                    </li>
                </ul>
            </div>
        </flux:card>

        @if ($softwareDetails->config || $softwareDetails->notes)
        <flux:card class="space-y-6">
            <div>
                <flux:heading>Notes</flux:heading>
            </div>

            @if ($softwareDetails->config)
                <div class="p-4 bg-gray-100 rounded-lg">
                    <flux:heading size="sm">Configuration</flux:heading>
                    {{ $softwareDetails->config }}
                </div>
            @endif

            @if ($softwareDetails->notes)
                <div class="p-4 bg-gray-100 rounded-lg">
                    <flux:heading size="sm">Notes</flux:heading>
                    {{ $softwareDetails->notes }}
                </div>
            @endif
        </flux:card>
        @endif
    </flux:modal>

    <flux:modal name="view-software-details" class="sm:w-full">
        <div class="space-y-6">
            <div class="space-y-2">
                <flux:heading size="lg">Software details</flux:heading>
                <flux:text>
                    <span class="flex flex-row gap-2 items-center">
                        <flux:icon.user size="sm" />
                        @if ($softwareDetails->createdBy)
                            {{ $softwareDetails->createdBy?->full_name }} : <a href="mailto:{{ $softwareDetails->createdBy?->email }}">{{ $softwareDetails->createdBy?->email }}</a>
                        @else
                            N/A
                        @endif
                    </span>

                </flux:text>
            </div>
            <div class="flex flex-col md:flex-row gap-6">
                <flux:card class="space-y-6">
                    <div>
                        <flux:heading>Basic details</flux:heading>
                        <flux:text>* {{ $softwareDetails->name }}</flux:text>
                        <flux:text>* {{ $softwareDetails->version ?? 'N/A' }}</flux:text>
                        <flux:text>* {{ $softwareDetails->operating_systems }}</flux:text>
                    </div>

                    <div>
                        <flux:heading>Lab</flux:heading>
                        <flux:text>{{ $softwareDetails->lab ?? 'N/A' }}</flux:text>
                    </div>

                    <div>
                        <flux:heading>Location</flux:heading>
                        <flux:text>{{ $softwareDetails->location ?? 'N/A' }}</flux:text>
                    </div>

                    <div>
                        <flux:heading>Free software?</flux:heading>
                        <flux:text>{{ $softwareDetails->is_free ? 'Yes' : 'No' }}</flux:text>
                    </div>

                    <div>
                        <flux:heading>Licence type</flux:heading>
                        <flux:text>{{ $softwareDetails->licence_type ?? 'N/A' }}</flux:text>
                    </div>

                    <div>
                        <flux:heading>Licence details</flux:heading>
                        <flux:text>{{ $softwareDetails->licence_details ?? 'N/A' }}</flux:text>
                    </div>
                </flux:card>

                <flux:card class="space-y-6 flex-1">
                    <div>
                        <flux:heading>Notes</flux:heading>
                        <flux:text>{{ $softwareDetails->notes ?? 'N/A' }}</flux:text>
                    </div>

                    <div>
                        <flux:heading>Configuration</flux:heading>
                        <flux:text>{{ $softwareDetails->config ?? 'N/A' }}</flux:text>
                    </div>
                </flux:card>
            </div>

            <div class="flex justify-end">
                <flux:button x-on:click="$flux.modal('view-software-details').close()">Close</flux:button>
            </div>
        </div>
    </flux:modal>
</div>
