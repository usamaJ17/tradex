<?php

namespace App\Http\Repositories;
use App\Model\CoinPair;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class CoinPairRepository extends CommonRepository
{
    function __construct($model) {
        parent::__construct($model);
    }

    function getAllCoinPairs()
    {

        if(Auth::check()) {
            $coinPairs = CoinPair::select('coin_pairs.id','parent_coin_id','child_coin_id','coin_pairs.volume',
                'coin_pairs.is_token','coin_pairs.bot_trading','coin_pairs.initial_price','coin_pairs.bot_possible',
                DB::raw("visualNumberFormat(price) as last_price"), DB::raw("TRUNCATE(`change`,2) as price_change"),"high","low"
                ,'child_coin.coin_type as child_coin_name','child_coin.coin_icon as icon','parent_coin.coin_type as parent_coin_name'
                ,'child_coin.name as child_full_name','parent_coin.name as parent_full_name'
                ,'wallets.user_id', DB::raw("visualNumberFormat(wallets.balance) as balance")
                , DB::raw('visualNumberFormat(price*balance) as est_balance')
                , DB::raw('CONCAT(child_coin.coin_type,"_",parent_coin.coin_type) as pair_bin')
                , DB::raw('CONCAT(child_coin.coin_type,"_",parent_coin.coin_type) as coin_pair_coin')
                ,DB::raw("CASE WHEN favourite_coin_pairs.id IS NOT NULL THEN 1 ELSE 0 END AS is_favorite"))
                ->join('coins as child_coin', ['coin_pairs.child_coin_id' => 'child_coin.id'])
                ->join('coins as parent_coin', ['coin_pairs.parent_coin_id' => 'parent_coin.id'])
                ->join('wallets', ['coin_pairs.child_coin_id' => 'wallets.coin_id'])
                ->leftJoin('favourite_coin_pairs', ['favourite_coin_pairs.coin_pairs_id' => 'coin_pairs.id', 'favourite_coin_pairs.user_id' => DB::raw(getUserId())])
                ->where(['wallets.user_id' => getUserId(),'coin_pairs.status' => STATUS_ACTIVE])
                ->orderBy('is_default', 'desc')
                ->get();
        } else {
            $coinPairs = CoinPair::select('coin_pairs.id','parent_coin_id','child_coin_id','coin_pairs.volume',
                'coin_pairs.is_token','coin_pairs.bot_trading','coin_pairs.initial_price','coin_pairs.bot_possible',
                DB::raw("visualNumberFormat(price) as last_price"), DB::raw("TRUNCATE(`change`,2) as price_change"),"high","low"
                ,'child_coin.coin_type as child_coin_name','child_coin.coin_icon as icon','parent_coin.coin_type as parent_coin_name'
                ,'child_coin.name as child_full_name','parent_coin.name as parent_full_name'
                , DB::raw("visualNumberFormat(0) as balance")
                , DB::raw('visualNumberFormat(0) as est_balance')
                , DB::raw('CONCAT(child_coin.coin_type,"_",parent_coin.coin_type) as pair_bin')
                , DB::raw('CONCAT(child_coin.coin_type,"_",parent_coin.coin_type) as coin_pair_coin')
                ,DB::raw("CASE WHEN favourite_coin_pairs.id IS NOT NULL THEN 1 ELSE 0 END AS is_favorite"))
                ->join('coins as child_coin', ['coin_pairs.child_coin_id' => 'child_coin.id'])
                ->join('coins as parent_coin', ['coin_pairs.parent_coin_id' => 'parent_coin.id'])
                ->leftJoin('favourite_coin_pairs', ['favourite_coin_pairs.coin_pairs_id' => 'coin_pairs.id', 'favourite_coin_pairs.user_id' => DB::raw(0)])
                ->where(['coin_pairs.status' => STATUS_ACTIVE])
                ->orderBy('is_default', 'desc')
                ->get();
        }

        $coinPairs->each(function ($coin) {
            $coin->icon = show_image_path($coin->icon,'coin/');
        });
        $data = $coinPairs->toArray();
        return $data;
    }

    function getCoinPairsByBaseCoin($baseCoinId)
    {
        $coins = CoinPair::select('child_coin_id as id', 'full_name', 'coin_type', DB::raw("visualNumberFormat(price) as price"), 'change',
            DB::raw("visualNumberFormat(volume) as volume"),DB::raw("visualNumberFormat(high) as high"),DB::raw("visualNumberFormat(low) as low"))
            ->join('coins', 'coins.id', '=', 'coin_pairs.child_coin_id')
            ->where('parent_coin_id', $baseCoinId)
            ->where(['coin_pairs.status' => 1]);
        return $coins;
    }

    function getCoinPairsData($baseCoinId,$tradeCoinId)
    {
        if(Auth::guard('api')->check()) {
            $coins = CoinPair::select('coin_pairs.pair_decimal','coin_pairs.is_token','coin_pairs.bot_trading','coin_pairs.id','parent_coin_id', 'child_coin_id', DB::raw("visualNumberFormat(price) as last_price"),
                DB::raw("visualNumberFormat(wallets.balance) as balance"), 'change as price_change', 'volume', 'high', 'low'
                , 'child_coin.coin_type as child_coin_name', 'parent_coin.coin_type as parent_coin_name'
                , 'child_coin.name as child_full_name', 'parent_coin.name as parent_full_name')
                ->join('coins as child_coin', ['coin_pairs.child_coin_id' => 'child_coin.id'])
                ->join('coins as parent_coin', ['coin_pairs.parent_coin_id' => 'parent_coin.id'])
                ->join('wallets', ['coin_pairs.child_coin_id' => 'wallets.coin_id', 'wallets.user_id' => DB::raw(getUserId())])
                ->where('parent_coin_id', $baseCoinId)
                ->where('child_coin_id', $tradeCoinId)
                ->where(['coin_pairs.status' => STATUS_ACTIVE])
                ->first();
        }else{
            $coins = CoinPair::select('coin_pairs.pair_decimal','coin_pairs.is_token','coin_pairs.bot_trading','coin_pairs.id','parent_coin_id', 'child_coin_id', DB::raw("visualNumberFormat(price) as last_price"),
                DB::raw("visualNumberFormat(0) as balance"), 'change as price_change', 'volume', 'high', 'low'
                , 'child_coin.coin_type as child_coin_name', 'parent_coin.coin_type as parent_coin_name'
                , 'child_coin.name as child_full_name', 'parent_coin.name as parent_full_name')
                ->join('coins as child_coin', ['coin_pairs.child_coin_id' => 'child_coin.id'])
                ->join('coins as parent_coin', ['coin_pairs.parent_coin_id' => 'parent_coin.id'])
                ->where('parent_coin_id', $baseCoinId)
                ->where('child_coin_id', $tradeCoinId)
                ->where(['coin_pairs.status' => STATUS_ACTIVE])
                ->first();
        }

        return $coins;
    }

    function getLandingCoinPairs($type)
    {
        $setting = settings();
        $limit = $setting['pair_assets_list'] ?? 5;
        $baseCoin = $setting['pair_assets_base_coin'] ?? 'USDT';
        if($type == 'asset') {
            $coins = CoinPair::select('parent_coin_id', 'child_coin_id', DB::raw("visualNumberFormat(price) as last_price"),
                DB::raw("visualNumberFormat(0) as balance"), 'change as price_change', 'volume', 'high', 'low'
                , 'child_coin.coin_type as child_coin_name', 'parent_coin.coin_type as parent_coin_name'
                , 'child_coin.name as child_full_name', 'parent_coin.name as parent_full_name','child_coin.coin_icon')
                ->join('coins as child_coin', ['coin_pairs.child_coin_id' => 'child_coin.id'])
                ->join('coins as parent_coin', ['coin_pairs.parent_coin_id' => 'parent_coin.id'])
                ->where(['coin_pairs.status' => STATUS_ACTIVE])
                ->where(['parent_coin.coin_type' => $baseCoin])
                ->orderBy('coin_pairs.volume', 'desc')
                ->limit($limit)
                ->get();

            $coins->map(function($query){
                if(isset($query->coin_icon))
                {
                    $query->coin_icon = show_image_path($query->coin_icon,'coin/');
                }
            });
        } elseif($type == '24hour') {
            $coins = CoinPair::select('parent_coin_id', 'child_coin_id', DB::raw("visualNumberFormat(price) as last_price"),
                DB::raw("visualNumberFormat(0) as balance"), 'change as price_change', 'volume', 'high', 'low'
                , 'child_coin.coin_type as child_coin_name', 'parent_coin.coin_type as parent_coin_name'
                , 'child_coin.name as child_full_name', 'parent_coin.name as parent_full_name','child_coin.coin_icon')
                ->join('coins as child_coin', ['coin_pairs.child_coin_id' => 'child_coin.id'])
                ->join('coins as parent_coin', ['coin_pairs.parent_coin_id' => 'parent_coin.id'])
                ->where(['coin_pairs.status' => STATUS_ACTIVE])
                ->where(['parent_coin.coin_type' => $baseCoin])
                ->where('coin_pairs.updated_at', '>=', Carbon::now()->subDay())
                ->limit($limit)
                ->get();

            $coins->map(function($query){
                if(isset($query->coin_icon))
                {
                    $query->coin_icon = show_image_path($query->coin_icon,'coin/');
                }
            });
        } else {
            $coins = CoinPair::select('parent_coin_id', 'child_coin_id', DB::raw("visualNumberFormat(price) as last_price"),
                DB::raw("visualNumberFormat(0) as balance"), 'change as price_change', 'volume', 'high', 'low'
                , 'child_coin.coin_type as child_coin_name', 'parent_coin.coin_type as parent_coin_name'
                , 'child_coin.name as child_full_name', 'parent_coin.name as parent_full_name','child_coin.coin_icon')
                ->join('coins as child_coin', ['coin_pairs.child_coin_id' => 'child_coin.id'])
                ->join('coins as parent_coin', ['coin_pairs.parent_coin_id' => 'parent_coin.id'])
                ->where(['coin_pairs.status' => STATUS_ACTIVE])
                ->where(['parent_coin.coin_type' => $baseCoin])
                ->orderBy('coin_pairs.updated_at', 'desc')
                ->limit($limit)
                ->get();

            $coins->map(function($query){
                if(isset($query->coin_icon))
                {
                    $query->coin_icon = show_image_path($query->coin_icon,'coin/');
                }
            });
        }

        return $coins;
    }

    function getCoinPairsDataBot($baseCoinId,$tradeCoinId)
    {
        return CoinPair::select('parent_coin_id', 'child_coin_id', DB::raw("visualNumberFormat(price) as last_price"))
            ->where('parent_coin_id', $baseCoinId)
            ->where('child_coin_id', $tradeCoinId)
            ->first();
    }

    function getCoinPairsDataWithUser($baseCoinId,$tradeCoinId,$userId)
    {
        if($userId == 1) {
            $coins = CoinPair::select('parent_coin_id', 'child_coin_id', DB::raw("visualNumberFormat(price) as last_price"),
            DB::raw("visualNumberFormat(0) as balance"), 'change as price_change', 'volume', 'high', 'low'
            , 'child_coin.coin_type as child_coin_name', 'parent_coin.coin_type as parent_coin_name'
            , 'child_coin.name as child_full_name', 'parent_coin.name as parent_full_name')
            ->join('coins as child_coin', ['coin_pairs.child_coin_id' => 'child_coin.id'])
            ->join('coins as parent_coin', ['coin_pairs.parent_coin_id' => 'parent_coin.id'])
            ->where('parent_coin_id', $baseCoinId)
            ->where('child_coin_id', $tradeCoinId)
            ->where(['coin_pairs.status' => STATUS_ACTIVE])
            ->first();
        } else {
            $coins = CoinPair::select('parent_coin_id', 'child_coin_id', DB::raw("visualNumberFormat(price) as last_price"),
            DB::raw("visualNumberFormat(wallets.balance) as balance"), 'change as price_change', 'volume', 'high', 'low'
            , 'child_coin.coin_type as child_coin_name', 'parent_coin.coin_type as parent_coin_name'
            , 'child_coin.name as child_full_name', 'parent_coin.name as parent_full_name')
            ->join('coins as child_coin', ['coin_pairs.child_coin_id' => 'child_coin.id'])
            ->join('coins as parent_coin', ['coin_pairs.parent_coin_id' => 'parent_coin.id'])
            ->join('wallets', ['coin_pairs.child_coin_id' => 'wallets.coin_id', 'wallets.user_id' => DB::raw($userId)])
            ->where('parent_coin_id', $baseCoinId)
            ->where('child_coin_id', $tradeCoinId)
            ->where(['coin_pairs.status' => STATUS_ACTIVE])
            ->first();
        }

        return $coins;
    }

    function getFutureTradeCoinPairsData($baseCoinId,$tradeCoinId)
    {
        if(Auth::guard('api')->check()) {
            $coins = CoinPair::select('coin_pairs.id','parent_coin_id', 'child_coin_id', DB::raw("visualNumberFormat(price) as last_price"),
                DB::raw("visualNumberFormat(future_wallets.balance) as balance"), 'change as price_change', 'volume', 'high', 'low'
                , 'child_coin.coin_type as child_coin_name', 'parent_coin.coin_type as parent_coin_name'
                , 'child_coin.name as child_full_name', 'parent_coin.name as parent_full_name',
                 'coin_pairs.maintenance_margin_rate','coin_pairs.minimum_amount_future','coin_pairs.leverage_fee',
                 'coin_pairs.max_leverage')
                ->join('coins as child_coin', ['coin_pairs.child_coin_id' => 'child_coin.id'])
                ->join('coins as parent_coin', ['coin_pairs.parent_coin_id' => 'parent_coin.id'])
                ->join('future_wallets', ['coin_pairs.child_coin_id' => 'future_wallets.coin_id', 'future_wallets.user_id' => DB::raw(getUserId())])
                ->where('parent_coin_id', $baseCoinId)
                ->where('child_coin_id', $tradeCoinId)
                ->where(['coin_pairs.status' => STATUS_ACTIVE,'coin_pairs.enable_future_trade' => STATUS_ACTIVE])
                ->first();
        }else{
            $coins = CoinPair::select('coin_pairs.id','parent_coin_id', 'child_coin_id', DB::raw("visualNumberFormat(price) as last_price"),
                DB::raw("visualNumberFormat(0) as balance"), 'change as price_change', 'volume', 'high', 'low'
                , 'child_coin.coin_type as child_coin_name', 'parent_coin.coin_type as parent_coin_name'
                , 'child_coin.name as child_full_name', 'parent_coin.name as parent_full_name',
                'coin_pairs.maintenance_margin_rate','coin_pairs.minimum_amount_future','coin_pairs.leverage_fee',
                 'coin_pairs.max_leverage')
                ->join('coins as child_coin', ['coin_pairs.child_coin_id' => 'child_coin.id'])
                ->join('coins as parent_coin', ['coin_pairs.parent_coin_id' => 'parent_coin.id'])
                ->where('parent_coin_id', $baseCoinId)
                ->where('child_coin_id', $tradeCoinId)
                ->where(['coin_pairs.status' => STATUS_ACTIVE,'coin_pairs.enable_future_trade' => STATUS_ACTIVE])
                ->first();
        }

        return $coins;
    }

    function getAllFutureCoinPairs()
    {

        if(Auth::check()) {
            $coinPairs = CoinPair::select('coin_pairs.id','parent_coin_id','child_coin_id','coin_pairs.volume',
                'coin_pairs.is_token','coin_pairs.bot_trading','coin_pairs.initial_price','coin_pairs.bot_possible',
                DB::raw("visualNumberFormat(price) as last_price"), DB::raw("TRUNCATE(`change`,2) as price_change"),"high","low"
                ,'child_coin.coin_type as child_coin_name','child_coin.coin_icon as icon','parent_coin.coin_type as parent_coin_name'
                ,'child_coin.name as child_full_name','parent_coin.name as parent_full_name'
                ,'future_wallets.user_id', DB::raw("visualNumberFormat(future_wallets.balance) as balance")
                , DB::raw('visualNumberFormat(price*balance) as est_balance')
                , DB::raw('CONCAT(child_coin.coin_type,"_",parent_coin.coin_type) as pair_bin')
                , DB::raw('CONCAT(child_coin.coin_type,"_",parent_coin.coin_type) as coin_pair_coin')
                ,DB::raw("CASE WHEN favourite_coin_pairs.id IS NOT NULL THEN 1 ELSE 0 END AS is_favorite"))
                ->join('coins as child_coin', ['coin_pairs.child_coin_id' => 'child_coin.id'])
                ->join('coins as parent_coin', ['coin_pairs.parent_coin_id' => 'parent_coin.id'])
                ->join('future_wallets', ['coin_pairs.child_coin_id' => 'future_wallets.coin_id'])
                ->leftJoin('favourite_coin_pairs', ['favourite_coin_pairs.coin_pairs_id' => 'coin_pairs.id', 'favourite_coin_pairs.user_id' => DB::raw(getUserId())])
                ->where('coin_pairs.enable_future_trade',STATUS_ACTIVE)
                ->where(['future_wallets.user_id' => getUserId(),'coin_pairs.status' => STATUS_ACTIVE])
                ->get();
        } else {
            $coinPairs = CoinPair::select('coin_pairs.id','parent_coin_id','child_coin_id','coin_pairs.volume',
                'coin_pairs.is_token','coin_pairs.bot_trading','coin_pairs.initial_price','coin_pairs.bot_possible',
                DB::raw("visualNumberFormat(price) as last_price"), DB::raw("TRUNCATE(`change`,2) as price_change"),"high","low"
                ,'child_coin.coin_type as child_coin_name','child_coin.coin_icon as icon','parent_coin.coin_type as parent_coin_name'
                ,'child_coin.name as child_full_name','parent_coin.name as parent_full_name'
                , DB::raw("visualNumberFormat(0) as balance")
                , DB::raw('visualNumberFormat(0) as est_balance')
                , DB::raw('CONCAT(child_coin.coin_type,"_",parent_coin.coin_type) as pair_bin')
                , DB::raw('CONCAT(child_coin.coin_type,"_",parent_coin.coin_type) as coin_pair_coin')
                ,DB::raw("CASE WHEN favourite_coin_pairs.id IS NOT NULL THEN 1 ELSE 0 END AS is_favorite"))
                ->join('coins as child_coin', ['coin_pairs.child_coin_id' => 'child_coin.id'])
                ->join('coins as parent_coin', ['coin_pairs.parent_coin_id' => 'parent_coin.id'])
                ->leftJoin('favourite_coin_pairs', ['favourite_coin_pairs.coin_pairs_id' => 'coin_pairs.id', 'favourite_coin_pairs.user_id' => DB::raw(0)])
                ->where(['coin_pairs.status' => STATUS_ACTIVE,'coin_pairs.enable_future_trade' => STATUS_ACTIVE])
                ->get();
        }

        $coinPairs->each(function ($coin) {
            $coin->icon = show_image_path($coin->icon,'coin/');
        });
        $data = $coinPairs->toArray();
        return $data;
    }
}

