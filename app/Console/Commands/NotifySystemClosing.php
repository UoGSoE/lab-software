<?php

namespace App\Console\Commands;

use App\Mail\SystemClosing;
use App\Models\AcademicSession;
use App\Models\Setting;
use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Mail;

class NotifySystemClosing extends Command
{
    protected $signature = 'labsoftware:notify-closing-deadline';

    protected $description = 'Notify users that the system is closing';

    public function handle(): int
    {
        $setting = Setting::getSetting('notifications_closing_date');

        if (! $setting) {
            $this->error('No setting found for closing date');

            return 1;
        }

        try {
            $date = $setting->toDate();

        } catch (\Exception $e) {
            $this->error('Invalid date for closing date');

            return 1;
        }

        if (! $date) {
            $this->error('No date found for closing date');

            return 1;
        }
        $alertDate = $date->subDays(Setting::getSetting('notifications_system_reminder_days', 7)->value);
        
        if (! $alertDate->isToday()) {
            return 0;
        }

        // working on the assumption that the users who are not admins (should just be IT staff) are the ones who need to be notified
        $usersWithNoSignoffs = User::with('courses')->where('is_admin', '=', false)->get()
            ->filter(fn ($user) => $user->courses->isEmpty());

        foreach ($usersWithNoSignoffs as $user) {
            Mail::to($user)->later(now()->addMinutes(rand(1, 30)), new SystemClosing($user));
        }

        return 0;
    }
}
