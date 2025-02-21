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
        <flux:select wire:model.live="filters.school" placeholder="Choose school..." label="Filter by school">
            <flux:option value="">All</flux:option>
            <flux:option value="ENG">Engineering</flux:option>
            <flux:option value="COMP">CompSci</flux:option>
            <flux:option value="PHAS">PHAS</flux:option>
            <flux:option value="MATH">Maths & Stats</flux:option>
            <flux:option value="GES">GES</flux:option>
            <flux:option value="CHEM">Chemistry</flux:option>
        </flux:select>

        <flux:input wire:model.live="filters.course" label="Course" placeholder="Eg, ENG1234" autofocus />

        <flux:input wire:model.live="filters.software" label="Software" placeholder="Eg, StarCCM" />
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
                    <flux:columns>
                        <flux:column width="50%">Package</flux:column>
                        <flux:column width="10%">Version</flux:column>
                        <flux:column class="hidden md:table-cell" width="10%">O/S</flux:column>
                        <flux:column class="hidden md:table-cell" width="20%">Lab</flux:column>
                        <flux:column width="10%"></flux:column>
                    </flux:columns>

                    @foreach ($course->software as $software)
                        <flux:row>
                            <flux:cell>{{ $software->name }}</flux:cell>
                            <flux:cell>{{ $software->version }}</flux:cell>
                            <flux:cell class="hidden md:table-cell">{{ $software->operatingSystems }}</flux:cell>
                            <flux:cell class="hidden md:table-cell">{{ $software->location }}</flux:cell>
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
        </flux:card>
    @endforeach

    @if ($courses->hasPages())
        <flux:separator class="mt-6 mb-6" />
        {{ $courses->links() }}
    @endif

    <!-- add software modal -->
    <flux:modal name="add-software" variant="flyout" class="space-y-6">
        <div>
            <flux:heading size="lg">Add new software</flux:heading>
            <flux:subheading>Please fill in the details below.</flux:subheading>
        </div>

        <form wire:submit="addSoftware" class="space-y-6">
            <flux:input label="Name of package (required)" placeholder="Eg, StarCCM+" wire:model="newSoftware.name" required/>

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

            <flux:input wire:model.blur="newSoftware.course_code" label="Course code (required)" placeholder="Eg, ENG1234" required />
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
</div>
