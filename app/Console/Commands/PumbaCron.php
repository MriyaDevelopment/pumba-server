<?php

namespace App\Console\Commands;

use App\Http\Controllers\AlertController;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;

class PumbaCron extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'command:pumba';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $now = Carbon::now();

        $now->toIso8601String();
        $hms = substr($now, 11);
        $hm = substr($hms, 0,-3);

        Http::post('https://api.tlgr.org/bot5906683048:AAHrBp6aWLbbNX9V4puNHbvMSTDQYZERPyM/sendMessage', [
            'chat_id' => '-1001752492520',
            'text' => "Schedule Task every minute, time to UTC (toIso8601) {$hm}"
        ]);
        return Command::SUCCESS;
    }
}
