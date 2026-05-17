<?php

use Illuminate\Support\Facades\Schedule;
use Spatie\ScheduleMonitor\Models\MonitoredScheduledTaskLogItem;


// Artisan::command('inspire', function () {
//     $this->comment(Inspiring::quote());
// })->purpose('Display an inspiring quote');

/*
|--------------------------------------------------------------------------
| Scheduled Tasks
|--------------------------------------------------------------------------
|
| Define your scheduled tasks here. These will be run by the cron job
| that executes the "schedule:run" Artisan command.
|
|==========================================================================
| CRON JOB SETUP (Run on server):
|--------------------------------------------------------------------------
| Edit crontab: crontab -e
| Add this line:
| * * * * * cd /path/to/backend && php artisan schedule:run >> /dev/null 2>&1
|
| Or for logging:
| * * * * * cd /path/to/backend && php artisan schedule:run >> /var/log/laravel-schedule.log 2>&1
|==========================================================================
|
| Available Commands:
| - orders:expire-unpaid --minutes=30  (Runs every 5 min)
| - boarding-passes:expire --minutes=5 (Runs every 5 min)
| - carts:expire                        (Runs every 30 min)
| - db:backup                           (Runs daily at 3 AM)
| - items:import                        (Runs daily at 2 AM if enabled)
|
*/

// Schedule Items Master import if enabled
if (config('items.schedule.enabled', false)) {
    Schedule::command('items:import')
        ->cron(config('items.schedule.cron', '0 2 * * *'))
        ->withoutOverlapping()
        ->onOneServer()
        ->appendOutputTo(storage_path('logs/items-import.log'));
}


/*
|--------------------------------------------------------------------------
| Database Backup Jobs
|--------------------------------------------------------------------------
*/

// Daily database backup to S3 with 7-day retention
// Uses gzip compression to reduce storage size
Schedule::command('db:backup --compress')
    ->dailyAt('03:00')
    ->withoutOverlapping()
    ->appendOutputTo(storage_path('logs/db-backup.log'));

// Sync schedule with monitor after deployment
// Run this manually after each deploy: php artisan schedule-monitor:sync

// Prune old schedule monitor log items (older than 30 days)
Schedule::command('model:prune', ['--model' => MonitoredScheduledTaskLogItem::class])
    ->daily()
    ->at('01:00')
    ->withoutOverlapping();
