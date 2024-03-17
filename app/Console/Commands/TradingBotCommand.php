<?php

namespace App\Console\Commands;

use App\Http\Services\TradingBotService;
use App\Jobs\BotOrderJob;
use App\User;
use Illuminate\Console\Command;

class TradingBotCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'trading:bot';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Default trading bot that place buy and sell order ';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        storeBotException('TradingBotCommand running', date('Y-m-d H:i:s'));
        $botProcess = settings()['bot_order_place_process'] ? settings()['bot_order_place_process'] : BOT_ORDER_PROCESS_REDIS;
        if ($botProcess == BOT_ORDER_PROCESS_REDIS) {
            storeBotException('TradingBotCommand', 'redis');
            dispatch(new BotOrderJob())->onQueue('market-bot');
        } else {
            storeBotException('TradingBotCommand', 'direct command ');
            $service = new TradingBotService();
            $response = $service->placeBotOrder(1);
        }
        storeBotException('TradingBotCommand end', date('Y-m-d H:i:s'));
    }
}
