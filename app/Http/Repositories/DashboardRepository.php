<?php

namespace App\Http\Repositories;

use App\Model\SelectedCoinPair;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class DashboardRepository
{
    public function getOnOrderBalance($coinId,$userId = null)
    {
        if($userId == null){
            $userId = getUserId();
        }
        $buyTable = DB::table('buys')
            ->where(['buys.user_id' => $userId, 'buys.base_coin_id' => $coinId, 'buys.status' => '0', 'buys.is_market' => 0, 'buys.deleted_at' => null])
            ->select( DB::raw('SUM(TRUNCATE((buys.amount - buys.processed) * buys.price, 8)+ TRUNCATE((buys.amount - buys.processed) * buys.price, 8) * 0.01 * case when (buys.maker_fees > buys.taker_fees)  then (buys.maker_fees) else (buys.taker_fees) end ) as total'));
        $sellTable = DB::table('sells')
            ->where(['sells.user_id' => $userId, 'sells.trade_coin_id' => $coinId, 'sells.status' => '0', 'sells.is_market' => 0, 'sells.deleted_at' => null])
            ->select( DB::raw('TRUNCATE(SUM((sells.amount - sells.processed)), 8) as total'));
//        $conditionBuyTable = DB::table('condition_buys')
//            ->where(['condition_buys.user_id' => $userId, 'condition_buys.base_coin_id' => $coinId, 'condition_buys.status' => '0', 'condition_buys.deleted_at' => null])
//            ->select( DB::raw('SUM(TRUNCATE(condition_buys.amount * condition_buys.price, 8)) + SUM(TRUNCATE(condition_buys.amount * condition_buys.price, 8) * 0.01 * case when (condition_buys.maker_fees > condition_buys.taker_fees)  then (condition_buys.maker_fees) else (condition_buys.taker_fees) end ) as total'));
        $stopLimitBuyTable = DB::table('stop_limits as stop_limits_buys')
            ->where(['stop_limits_buys.user_id' => $userId, 'stop_limits_buys.base_coin_id' => $coinId, 'stop_limits_buys.status' => '0', 'stop_limits_buys.deleted_at' => null])
            ->select( DB::raw('SUM(TRUNCATE((stop_limits_buys.amount) * stop_limits_buys.limit_price, 8) + TRUNCATE((stop_limits_buys.amount) * stop_limits_buys.limit_price, 8) * 0.01 * case when (stop_limits_buys.maker_fees > stop_limits_buys.taker_fees) then (stop_limits_buys.maker_fees) else (stop_limits_buys.taker_fees) end ) as total'));
        $stopLimitSellTable = DB::table('stop_limits as stop_limits_sells')
            ->where(['stop_limits_sells.user_id' => $userId, 'stop_limits_sells.trade_coin_id' => $coinId, 'stop_limits_sells.status' => '0', 'stop_limits_sells.deleted_at' => null])
            ->select( DB::raw('TRUNCATE(SUM(stop_limits_sells.amount), 8) as total'));

        $totals = $buyTable->union($sellTable)->union($stopLimitBuyTable)->union($stopLimitSellTable)->get();

        $sum = 0;
        foreach ($totals as $total) {
            $sum = bcadd($sum, $total->total);
        }

        return $sum;
    }
    public function getDocs($params=[],$select=null,$orderBy=[],$with=[]){
        if($select == null){
            $select = ['*'];
        }
        $query = SelectedCoinPair::select($select);
        foreach($with as $wt) {
            $query = $query->with($wt);
        }
        foreach($params as $key => $value) {
            if(is_array($value)){
                $query->where($key,$value[0],$value[1]);
            }else{
                $query->where($key,'=',$value);
            }
        }
        foreach($orderBy as $key => $value) {
            $query->orderBy($key,$value);
        }

        return $query->get();
    }

    public function updateWhere($where=[], $update=[])
    {
        $query = SelectedCoinPair::query();
        foreach($where as $key => $value) {
            if(is_array($value)){
                $query->where($key,$value[0],$value[1]);
            }else{
                $query->where($key,'=',$value);
            }
        }
        return $query->update($update);
    }
}
