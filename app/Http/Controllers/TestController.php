<?php

namespace App\Http\Controllers;

use App\Http\Repositories\CustomTokenRepository;
use App\Http\Services\CoinPairService;
use App\Http\Services\ERC20TokenApi;
use App\Http\Services\MarketTradeService;
use App\Http\Services\PublicService;
use App\Jobs\MarketBotOrderJob;
use App\Model\Buy;
use App\Model\Coin;
use App\Model\Sell;
use App\Model\Transaction;
use App\User;
use Carbon\Carbon;
use GuzzleHttp\Client;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;

class TestController extends Controller
{
    //
    public function index(Request $request)
    {
        
    }
}
