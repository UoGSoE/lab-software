<?php

use Illuminate\Support\Facades\Schedule;

Schedule::command('labsoftware:notify-system-open')->dailyAt('07:45');
