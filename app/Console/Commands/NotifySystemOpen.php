<?php

namespace App\Console\Commands;

use App\Models\User;
use App\Models\Setting;
use App\Mail\SystemOpen;
use App\Models\AcademicSession;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Mail;

class NotifySystemOpen extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'labsoftware:notify-system-open';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Notify users that the system is open';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $academicSession = AcademicSession::getDefault();

        $setting = Setting::where('key', 'notifications.system_open_date')
            ->first();

        if (!$setting) {
            $this->error('No setting found for system open date');
            return 1;
        }

        try {
            $date = $setting->toDate();
        } catch (\Exception $e) {
            $this->error('Invalid date for system open date');
            return 1;
        }

        if (!$date) {
            $this->error('No date found for system open date');
            return 1;
        }

        if (! $date->isToday()) {
            return 0;
        }

        $users = User::with('courses.software')->get();

        foreach ($users as $user) {
            Mail::to($user)->later(now()->addMinutes(rand(1, 30)), new SystemOpen($user));
        }
    }
}
