<?php

namespace App\Http\Services;

use App\Http\Repositories\CoinPairRepository;
use App\Model\CoinPair;
use App\Model\SelectedCoinPair;
use Illuminate\Support\Facades\Log;

class CoinPairService extends BaseService
{
    public $model = CoinPair::class;
    public $repository = CoinPairRepository::class;
    public $logger;

    public function __construct()
    {
        parent::__construct($this->model, $this->repository);
        $this->logger = app(Logger::class);
    }

    public function _setDefaultCoinPair($id)
    {
        return SelectedCoinPair::create(['user_id' => $id,'trade_coin_id' => 1, 'base_coin_id' => 2]);
    }

    public function getAllCoinPairs()
    {
        $response = [
            'status' => false,
            'message' =>__('Data not found'),
            'data' => []
        ];
        try {
            $pairs = $this->object->getAllCoinPairs();

            $coinPairs = [];
            if (isset($pairs[0])) {
                foreach ($pairs as $pair) {
                    $coinPairs[] = [
                        "coin_pair_id" => $pair['id'],
                        "coin_pair_name" => $pair['child_coin_name'].'/'.$pair['parent_coin_name'],
                        "coin_pair" => $pair['child_coin_name'].'_'.$pair['parent_coin_name'],
                        "parent_coin_id" => $pair['parent_coin_id'],
                        "child_coin_id" => $pair['child_coin_id'],
                        "last_price" => $pair['last_price'],
                        "price_change" => $pair['price_change'],
                        "child_coin_name" => $pair['child_coin_name'],
                        "icon" => $pair['icon'],
                        "parent_coin_name" => $pair['parent_coin_name'],
                        "user_id" => $pair['user_id'] ?? '',
                        "balance" => $pair['balance'] ?? 0,
                        "est_balance" => $pair['est_balance'],
                        "is_favorite" => $pair['is_favorite'],
                        "high" => $pair['high'],
                        "low" => $pair['low'],
                        "volume" => $pair['volume'],
                        'pair_name' => $pair['coin_pair_coin'],
                        'bot_trading' => $pair['bot_trading']
                    ];
                }
            }
            $response = [
                'status' => true,
                'message' =>__('Data get successfully'),
                'data' => $coinPairs
            ];

            return $response;
        } catch (\Exception $e) {
            Log::info('get all coin pairs exception -> '.$e->getMessage());
            return $response;
        }
    }
    public function getAllCoinPairsData()
    {
        $response = [
            'status' => false,
            'message' =>__('Data not found'),
            'data' => []
        ];
        try {
            $pairs = $this->object->getAllCoinPairs();
            $response = [
                'status' => true,
                'message' =>__('Data get successfully'),
                'data' => $pairs
            ];

            return $response;
        } catch (\Exception $e) {
            storeException('get all coin pairs exception -> ',$e->getMessage());
            return $response;
        }
    }

    public function getCoinPairDetails($id)
    {
        $coinPairDetails = CoinPair::with(['parent_coin', 'child_coin'])->find($id);

        if(isset($coinPairDetails))
        {
            $response = responseData(true, __('Coin Pair details!'), $coinPairDetails);
        }else{
            $response = responseData(false, __('Invalid Request!'));
        }

        return $response;
    }

    public function coinPairFutureSettingUpdate($request)
    {

        $id = decrypt($request->id);

        $coinPairDetails = CoinPair::find($id);
        if(isset($coinPairDetails))
        {
            $leverageValues = explode(',',$request->leverage);

            $coinPairDetails->minimum_amount_future = $request->minimum_amount_future;
            $coinPairDetails->maintenance_margin_rate = $request->maintenance_margin_rate;
            $coinPairDetails->leverage_fee = $request->leverage_fee;
            $coinPairDetails->max_leverage = $request->max_leverage;
            $coinPairDetails->save();

            $response = responseData(true, __('Coin pair future trade setting is updated!'));
        }else{
            $response = responseData(false, __('Invalid Request!'));
        }
        return $response;
    }

    public function getAllFutureCoinPairs()
    {
        $response = [
            'status' => false,
            'message' =>__('Data not found'),
            'data' => []
        ];
        try {
            $pairs = $this->object->getAllFutureCoinPairs();

            $coinPairs = [];
            if (isset($pairs[0])) {
                foreach ($pairs as $pair) {
                    $coinPairs[] = [
                        "coin_pair_id" => $pair['id'],
                        "coin_pair_name" => $pair['child_coin_name'].'/'.$pair['parent_coin_name'],
                        "coin_pair" => $pair['child_coin_name'].'_'.$pair['parent_coin_name'],
                        "parent_coin_id" => $pair['parent_coin_id'],
                        "child_coin_id" => $pair['child_coin_id'],
                        "last_price" => $pair['last_price'],
                        "price_change" => $pair['price_change'],
                        "child_coin_name" => $pair['child_coin_name'],
                        "icon" => $pair['icon'],
                        "parent_coin_name" => $pair['parent_coin_name'],
                        "user_id" => $pair['user_id'] ?? '',
                        "balance" => $pair['balance'] ?? 0,
                        "est_balance" => $pair['est_balance'],
                        "is_favorite" => $pair['is_favorite'],
                        "high" => $pair['high'],
                        "low" => $pair['low'],
                        "volume" => $pair['volume'],
                        'pair_name' => $pair['coin_pair_coin']
                    ];
                }
            }
            $response = [
                'status' => true,
                'message' =>__('Data get successfully'),
                'data' => $coinPairs
            ];

            return $response;
        } catch (\Exception $e) {
            Log::info('get all future coin pairs exception -> '.$e->getMessage());
            return $response;
        }
    }
}
