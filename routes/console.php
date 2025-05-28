<?php

use Illuminate\Support\Facades\Schedule;

Schedule::command('labsoftware:notify-system-open')->dailyAt('07:45');
Schedule::command('labsoftware:notify-closing-deadline')->dailyAt('07:48');
