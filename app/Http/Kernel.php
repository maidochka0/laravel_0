<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;
use Illuminate\Support\Facades\Log;

class Kernel extends ConsoleKernel
{
    protected $commands = [
        'App\Console\Commands\HelpCenter',
        'php artisan migrate',
    ];
    protected function schedule(Schedule $schedule)
    {
        $schedule->call(function(){
            print("HELLO");
        })->dailyAt('21:51')->timezone('Asia/Kolkata');

        $schedule->exec("echo 2")->everyMinute();
        $schedule->command('php artisan migrate')->everyMinute();

        $schedule->call(function () {
            Log::info("Обновление данных выполнено в " . now());
        })->everyMinute()->sendOutputTo('schedule_output.log');

        $schedule->call(function () {
            // Ваша логика обновления данных
            echo "Обновление данных выполнено в " . now() . "\n";
        })->everyMinute()->sendOutputTo('schedule_output.log');//->twiceDaily(1, 13);
    }

    protected function commands()
    {
        // Здесь вы можете зарегистрировать команды artisan
        $this->load(__DIR__.'/Commands');

        require base_path('routes/console.php');
    }
}