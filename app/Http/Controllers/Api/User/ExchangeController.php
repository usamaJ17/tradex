<?php

namespace App\Http\Controllers\Api\User;

use App\Http\Controllers\Controller;
use App\Http\Services\CoinPairService;
use App\Http\Services\DashboardService;
use App\Http\Services\Logger;
use App\Http\Services\TradingViewChartService;
use App\Model\AdminSetting;
use App\Model\CoinPair;
use http\Client;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ExchangeController extends Controller
{
    private $service;
    private $coinPairService;
    private $logger;
    public function __construct()
    {
        $this->service = new DashboardService();
        $this->coinPairService = new CoinPairService();
        $this->logger = new Logger();
    }

    public function appExchangeGetAllPair()
    {
        $pairservice = new CoinPairService();
        $pairs = $pairservice->getAllCoinPairs()['data'];
        return responseData(true,__('Success'),$pairs);
    }

    /**
     * specific exchange dashboard data
     * @param Request $request
     * @param $pair
     * @return array
     */
    public function appExchangeDashboard(Request $request, $pair=null){
        $data['title'] = __('Exchange');
        $data['success'] = true;
        $data['message'] = __("Success");
        $data['broadcast_port'] = env('BROADCAST_PORT');
        $data['app_key'] = env('PUSHER_APP_KEY');
        $data['cluster'] = env('PUSHER_APP_CLUSTER');
        if(Auth::guard('api')->check())  {
            create_coin_wallet(getUserId());
        }
        $data['pair_status'] = true;
        if(isset($pair)) {
            $ar =  explode('_',$pair);
            if (empty($request->base_coin_id) || empty($request->trade_coin_id)) {
                $tradeCoinId = get_coin_id($ar[0]);
                $baseCoinId = get_coin_id($ar[1]);

                if (checkPair($baseCoinId,$tradeCoinId)) {
                    $request->merge([
                        'base_coin_id' => $baseCoinId,
                        'trade_coin_id' => $tradeCoinId,
                    ]);
                } else {
                    $data['pair_status'] = false;
                    $firstPair = getFirstPair();
                    if ($firstPair) {
                        $request->merge([
                            'base_coin_id' => $firstPair->parent_coin_id,
                            'trade_coin_id' => $firstPair->child_coin_id,
                        ]);
                    } else {
                        $request->merge([
                            'base_coin_id' => $baseCoinId,
                            'trade_coin_id' => $tradeCoinId,
                        ]);
                    }
                }
            }
        } else {
            $request->merge([
                'base_coin_id' => get_default_base_coin_id(),
                'trade_coin_id' => get_default_trade_coin_id(),
            ]);
        }

        $request->merge([
            'dashboard_type' => 'dashboard'
        ]);
        if (checkPair($request->base_coin_id,$request->trade_coin_id)) {
            $pairservice = new CoinPairService();
            $data['pairs'] = $pairservice->getAllCoinPairs()['data'];
            $data['order_data'] = $this->service->getOrderData($request)['data'];
            $data['fees_settings'] = $this->userFeesSettings();
            $data['last_price_data'] = $this->service->getDashboardMarketTradeDataTwo($request->base_coin_id, $request->trade_coin_id,2);

        } else {
            $data['success'] = false;
            $data['message'] = __("Pair not found");
        }
        return $data;
    }

    // get fees settings
    public function userFeesSettings()
    {
        if(Auth::guard('api')->check())  {
            $fees = calculated_fee_limit(getUserId());
        } else {
            $fees = [];
        }
        return $fees;
    }
    /**
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function getExchangeAllOrdersApp(Request $request){
        $data = [
            'success' => false,
            'data' => [],
            'message'=>__('Something went wrong')
        ];
        try {
            $response = $this->service->getOrders($request)['data'];
            $data = [
                'success' => true,
                'data' => $response,
                'message' => 'All Orders'
            ];
            return response()->json($data);
        } catch (\Exception $e) {
            $this->logger->log('getExchangeAllOrders', $e->getMessage());
            return response()->json($data);
        }
    }

    /**
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function getExchangeAllBuyOrdersApp(Request $request)
    {
        $response = [
            'success' => false,
            'data' => [],
            'message'=>__('Something went wrong')
        ];
        try {
            $data['title'] = __('All Open Buy Order History of '.$request->trade_coin_type.'/'.$request->base_coin_type);
            $data['type'] = 'buy';
            $data['sub_menu'] = 'buy_order';
            $data['tradeCoinId'] = get_coin_id($request->trade_coin_type);
            $data['baseCoinId'] = get_coin_id($request->base_coin_type);
            $data['items'] = $this->service->getOrders($request)['data']['orders'];
            $response = [
                'success' => true,
                'data' => $data,
                'message' => 'All Buy Orders'
            ];
            return response()->json($response);
        } catch (\Exception $e) {
            $this->logger->log('getExchangeAllBuyOrdersApp', $e->getMessage());
            return response()->json($response);
        }
    }

    /**
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function getExchangeAllSellOrdersApp(Request $request)
    {
        $response = [
            'success' => false,
            'data' => [],
            'message'=>__('Something went wrong')
        ];
        try {
            $data['title'] = __('All Open Sell Order History of '.$request->trade_coin_type.'/'.$request->base_coin_type);
            $data['type'] = 'sell';
            $data['sub_menu'] = 'buy_order';
            $data['tradeCoinId'] = get_coin_id($request->trade_coin_type);
            $data['baseCoinId'] = get_coin_id($request->base_coin_type);
            $data['items'] = $this->service->getOrders($request)['data']['orders'];
            $response = [
                'success' => true,
                'data' => $data,
                'message' => 'All Sell Orders'
            ];
            return response()->json($response);
        } catch (\Exception $e) {
            $this->logger->log('getExchangeAllSellOrdersApp', $e->getMessage());
            return response()->json($response);
        }
    }

    /**
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function getExchangeMarketTradesApp(Request $request)
    {
        $data = [
            'success' => false,
            'data' => [],
            'message'=>__('Something went wrong')
        ];
        try {
            $response = $this->service->getMarketTransactions($request)['data'];
            $data = [
                'success' => true,
                'data' => $response,
                'message'=>'All Market Trades'
            ];
            return response()->json($data);
        } catch (\Exception $e) {
            $this->logger->log('getExchangeMarketOrders', $e->getMessage());
            return response()->json($data);
        }
    }

    /**
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function getMyExchangeOrdersApp(Request $request)
    {
        $data = [
            'success' => false,
            'data' => [],
            'message'=>__('Something went wrong')
        ];
        try {
            $response = $this->service->getMyOrders($request)['data'];
            $data = [
                'success' => true,
                'data' => $response,
                'message' => __('My Exchange Orders')
            ];
            return response()->json($data);
        } catch (\Exception $e) {
            $this->logger->log('getMyExchangeOrders', $e->getMessage());
            return response()->json($data);
        }
    }

    /**
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function getMyExchangeTradesApp(Request $request)
    {
        $data = [
            'success' => false,
            'data' => [],
            'message'=>__('Something went wrong')
        ];
        try {
            $response = $this->service->getMyTradeHistory($request)['data'];
            $data = [
                'success' => true,
                'data' => $response,
                'message' => __('My Exchange Trades')
            ];
            return response()->json($data);
        } catch (\Exception $e) {
            $this->logger->log('getMyExchangeTrades', $e->getMessage());
            return response()->json($data);
        }
    }

    public function getExchangeChartDataApp(Request $request){
        $service = new DashboardService();
        if (empty($request->base_coin_id) || empty($request->trade_coin_id)) {
            $tradeCoinId = $service->_getTradeCoin();
            $baseCoinId = $service->_getBaseCoin();
            $request->merge([
                'base_coin_id' => $baseCoinId,
                'trade_coin_id' => $tradeCoinId,
            ]);
        }
        $interval = $request->input('interval', 1440);
        $baseCoinId = $request->base_coin_id;
        $tradeCoinId = $request->trade_coin_id;
        $startTime = $request->input('start_time', strtotime(now()) - 864000);
        $endTime = $request->input('end_time', strtotime(now()));
        $chartService = new TradingViewChartService();
        if($startTime >= $endTime){
            return response()->json([
                'success' => false,
                'message' => __('start.time.is.always.big.than.end.time')
            ]);
        }
        $data = $chartService->getChartData($startTime, $endTime, $interval, $baseCoinId, $tradeCoinId);

        $response = [
            'success' => true,
            'message' => __('Success'),
            'dataType' => 'own',
            'data' => $data
        ];
        return $response;

    }


    public function deleteMyOrderApp(Request $request)
    {
        $dashboardService = new DashboardService();
        $response = $dashboardService->deleteOrder($request);

        return response()->json($response);
    }

}
