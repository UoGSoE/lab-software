<?php

namespace App\Console\Commands;

use App\Models\User;
use App\Models\Setting;
use App\Mail\SystemClosing;
use App\Models\AcademicSession;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Mail;

class NotifySystemClosing extends Command
{
    protected $signature = 'labsoftware:notify-closing-deadline';

    protected $description = 'Notify users that the system is closing';

    public function handle()
    {
        $academicSession = AcademicSession::getDefault();

        $setting = Setting::forAcademicSession($academicSession)
            ->where('key', 'notifications.initial_nag_message')
            ->first();

        if (!$setting) {
            $this->error('No setting found for initial nag message');
            return 1;
        }

        try {
            $date = $setting->toDate();
        } catch (\Exception $e) {
            $this->error('Invalid date for initial nag message');
            return 1;
        }

        if (!$date) {
            $this->error('No date found for initial nag message');
            return 1;
        }

        $date = $date->addDays(30);
        if (! $date->isToday()) {
            return 0;
        }

        $usersWithNoSignoffs = User::forAcademicSession($academicSession)->with('courses')->get()
            ->filter(fn ($user) => $user->courses->isEmpty());

        foreach ($usersWithNoSignoffs as $user) {
            Mail::to($user)->later(now()->addMinutes(rand(1, 30)), new SystemClosing($user));
        }
    }
}
