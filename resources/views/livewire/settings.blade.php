<div>
    <flux:heading size="xl">Settings</flux:heading>

    <flux:separator class="mt-6 mb-6" />

    <div class="w-full max-w-sm min-w-[200px]">
        <flux:card>

            <form class="space-y-6">
                <flux:input type="date" value="2025-04-08" class="w-full" label="Allow users to change their requirements from" name="open_date" />
                <flux:input type="date" value="2025-04-25" class="w-full" label="Until" name="close_date" />

                <flux:separator />

                <flux:input type="number" label="Number of days before those dates to send reminders" name="reminder_days" value="7" min="0" />

                <flux:button type="submit">Update</flux:button>
            </form>
        </flux:card>
    </div>
</div>
