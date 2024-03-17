<?php

namespace App\Http\Repositories;


use App\Model\Transaction;
use Illuminate\Support\Facades\DB;

class TransactionRepository extends CommonRepository
{
    function __construct($model)
    {
        parent::__construct($model);
    }

    public function getOrders()
    {
        return DB::select("SELECT buy_user.email as buy_user_email, sell_user.email as sell_user_email, base_coin_table.coin_type as base_coin, trade_coin_table.coin_type as trade_coin, price, amount, total, transaction_id, remove_from_chart, transactions.created_at FROM transactions
              join users as buy_user on buy_user.id = transactions.buy_user_id
              join users as sell_user on sell_user.id = transactions.sell_user_id
              join coins as base_coin_table on base_coin_id = base_coin_table.id
              join coins as trade_coin_table on trade_coin_id = trade_coin_table.id"
        );
    }

    public function getOrdersQuery()
    {
        return DB::table('transactions')
                ->select(DB::raw("buy_user.email as buy_user_email, sell_user.email as sell_user_email, base_coin_table.coin_type as base_coin, trade_coin_table.coin_type as trade_coin, price, amount, total, transaction_id, remove_from_chart, transactions.created_at"))
                ->join(DB::raw("users as buy_user"), "buy_user.id", "=", "transactions.buy_user_id")
                ->join(DB::raw("users as sell_user"), "sell_user.id", "=", "transactions.sell_user_id")
                ->join(DB::raw("coins as base_coin_table"), "base_coin_id", "=", "base_coin_table.id")
                ->join(DB::raw("coins as trade_coin_table"), "trade_coin_id", "=", "trade_coin_table.id");
    }


    public function getMyTradeHistory($select, $where, $orWhere = null, $duration = null)
    {
        return Transaction::select($select)->where($where) ->orWhere(function($query) use ($orWhere){
            $query->where($orWhere);
        })->where('created_at', '>=', $duration)->orderBy('id', 'DESC');
    }
    public function getMyAllTradeHistory($select, $where, $orWhere = null, $order_data)
    {
        $query_final = Transaction::join(DB::raw('coins as bc'),['bc.id' => 'base_coin_id'])
                                    ->join(DB::raw('coins as tc'),['tc.id' => 'trade_coin_id'])
                                    ->select($select)
                                    ->where($where) 
                                    ->when(isset($orWhere) ,function($query) use ($orWhere){
                                        $query->where($orWhere);
                                    })
                                    ->when(isset($order_data['search']), function($query) use($order_data){
                                        $query->where('amount', 'LIKE', '%'.$order_data['search'].'%')
                                                ->orWhere('price', 'LIKE', '%'.$order_data['search'].'%')
                                                ->orWhere('transaction_id', 'LIKE', '%'.$order_data['search'].'%')
                                                ->orWhere('bc.coin_type', 'LIKE', '%'.$order_data['search'].'%')
                                                ->orWhere('tc.coin_type', 'LIKE', '%'.$order_data['search'].'%');
                                    });

        if(!empty($order_data['column_name']) && !empty($order_data['order_by'])){
            $query_final->orderBy($order_data['column_name'], $order_data['order_by']);
        }else{
            $query_final->orderBy('transactions.id', 'DESC');
        }
        return $query_final;
    }

    public function getAllTradeHistory($where)
    {
        return Transaction::select(DB::raw("visualNumberFormat(amount) as amount"), DB::raw("visualNumberFormat(price) as price"),DB::raw("visualNumberFormat(last_price) as last_price"), 'price_order_type', DB::raw("visualNumberFormat(total) as total"), DB::raw("TIME(created_at) as time"))->where($where)->orderBy('id', 'DESC');
    }
    public function getLastTrade($where)
    {
        return Transaction::select(DB::raw("visualNumberFormat(amount) as amount"), DB::raw("visualNumberFormat(price) as price"),DB::raw("visualNumberFormat(last_price) as last_price"), 'price_order_type', DB::raw("visualNumberFormat(total) as total"), DB::raw("TIME(created_at) as time"))->where($where)->orderBy('id', 'DESC')->first();
    }
}
