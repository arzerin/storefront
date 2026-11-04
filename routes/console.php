<?php

use App\Console\Commands\AbandonedCart;
use App\Console\Commands\RemoveInactiveSessionCarts;
use Illuminate\Support\Facades\Schedule;

Schedule::command(AbandonedCart::class)->dailyAt('13:00');
Schedule::command(RemoveInactiveSessionCarts::class)->weekly();
