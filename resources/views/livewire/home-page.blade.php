<div>
    <div class="flex flex-col md:flex-row justify-between gap-2 items-center">
        <div class="flex flex-row gap-2 items-center">
            <div>
                <flux:heading size="xl">COSE teaching software</flux:heading>
                <flux:subheading>Academic session 2025/2026</flux:subheading>
            </div>
        </div>
        <div class="flex flex-col md:flex-row gap-2 items-center">
            <flux:button variant="primary">All good!</flux:button>
            <flux:modal.trigger name="add-software">
                <flux:button icon="plus" variant="filled">Request new software</flux:button>
            </flux:modal.trigger>
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

        <flux:input wire:model.live="filters.course" label="Course" placeholder="Eg, ENG1234" />

        <flux:input wire:model.live="filters.software" label="Software" placeholder="Eg, StarCCM" />
    </div>

    <flux:separator class="mt-6" />

    @foreach ($courses as $course)
        <flux:card class="space-y-6 mt-6" wire:key="course-{{ $course->id }}">
            <div>
                <flux:heading size="lg">{{ $course->code }}</flux:heading>
                <flux:subheading>{{ $course->title }}</flux:subheading>
            </div>

            <div>
                <flux:table>
                    <flux:columns>
                        <flux:column width="50%">Package</flux:column>
                        <flux:column width="10%">Version</flux:column>
                        <flux:column width="10%">O/S</flux:column>
                        <flux:column width="20%">Location</flux:column>
                        <flux:column width="10%"></flux:column>
                    </flux:columns>

                    @foreach ($course->software as $software)
                        <flux:row>
                            <flux:cell>{{ $software->name }}</flux:cell>
                            <flux:cell>{{ $software->version }}</flux:cell>
                            <flux:cell>{{ $software->os }}</flux:cell>
                            <flux:cell>{{ $software->location }}</flux:cell>
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

            <flux:autocomplete wire:model="newSoftware.course_code" label="Course code (required)" placeholder="Eg, ENG1234" required>
                @foreach ($courseCodes as $courseCode)
                    <flux:autocomplete.item>{{ $courseCode }}</flux:autocomplete.item>
                @endforeach
            </flux:autocomplete>

            <flux:checkbox.group wire:model="newSoftware.os" label="Operating System">
                <flux:checkbox label="Windows" value="Windows" />
                <flux:checkbox label="Mac" value="Mac" />
                <flux:checkbox label="Linux" value="Linux" />
            </flux:checkbox.group>

            <flux:checkbox.group wire:model="newSoftware.building" label="Building">
                <flux:checkbox label="Engineering" value="Engineering" />
                <flux:checkbox label="Physics" value="Physics" />
                <flux:checkbox label="Maths" value="Maths" />
                <flux:checkbox label="Chemistry" value="Chemistry" />
                <flux:checkbox label="Geoscience" value="Geoscience" />
                <flux:checkbox label="Computer Science" value="Computer Science" />
            </flux:checkbox.group>

            <flux:input label="Lab" placeholder="Eg, 100" wire:model="newSoftware.lab" />

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
