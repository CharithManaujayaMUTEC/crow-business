<?php

use Illuminate\Support\Facades\Schedule;

Schedule::command('crow:recurring-billing')->dailyAt('00:05');
Schedule::command('crow:payment-reminders')->dailyAt('08:00');
Schedule::command('crow:github-sync --scheduled')->hourly();
