<?php

namespace App\Jobs;

use App\Http\Services\TradingBotService;
use App\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class BotOrderJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct()
    {
        //
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        storeBotException('BotOrderJob running at', date('Y-m-d H:i:s'));
        $service = new TradingBotService();
        // $user = User::where(['role' => USER_ROLE_ADMIN,'status' => STATUS_ACTIVE, 'is_default' => STATUS_ACTIVE])->first();
        // if ($user) {
        //     $userData = $user;
        // } else {
        //     $userData = User::where(['role' => USER_ROLE_ADMIN])->first();
        // }

        $response = $service->placeBotOrder(1);
        storeBotException('BotOrderJob end at', date('Y-m-d H:i:s'));
    }
}
