<?php

namespace App\Http\Controllers\Api\User;

use App\Http\Controllers\Controller;
use App\Http\Services\AffiliationService;
use App\Http\Services\BuyOrderService;
use App\Http\Services\SellOrderService;
use App\Http\Services\StopLimitService;
use App\Http\Services\TradeReferralService;
use App\Http\Services\TransactionService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ReportController extends Controller
{
    /*
   *
   * All Buy Orders History
   * getAllOrdersHistoryBuy
   *
   * Show the list of specified resource.
   * @return \Illuminate\Http\Response
   *
   */
    public function getAllOrdersHistoryBuyApp(Request $request)
    {
        $limit = $request->per_page ?? 5;
        $order_data['column_name'] = $request->column_name ?? '';
        $order_data['order_by'] = $request->order_by ?? '';
        $order_data['search'] = $request->search;
        
        $data['title'] = __('Buy Order History');
        $buyService = new BuyOrderService();
        $data['type'] = 'buy';
        $data['sub_menu'] = 'buy_order';
        $data['items'] = $buyService->getAllOrderHistory($order_data)->paginate($limit);
        $response = [
            'success' => true,
            'data' => $data,
            'message'=>__('Buy Order History')
        ];
        return response()->json($response);
    }

    /*
   *
   * All Sell Orders History
   * getAllOrdersHistorySell
   *
   * Show the list of specified resource.
   * @return \Illuminate\Http\Response
   *
   */
    public function getAllOrdersHistorySellApp(Request $request)
    {
        $limit = $request->per_page ?? 5;
        $order_data['column_name'] = $request->column_name ?? '';
        $order_data['order_by'] = $request->order_by ?? '';
        $order_data['search'] = $request->search;

        $data['title'] = __('Sell Order History');
        $data['type'] = 'sell';
        $data['sub_menu'] = 'sell_order';
        $sellService = new SellOrderService();
        $data['items'] = $sellService->getAllOrderHistory($order_data)->paginate($limit);
        $response = [
            'success' => true,
            'data' => $data,
            'message'=>__('Sell Order History')
        ];
        return response()->json($response);
    }

    /*
   *
   * All Sell buy transaction Orders History
   * getAllTransactionHistory
   *
   * Show the list of specified resource.
   * @return \Illuminate\Http\Response
   *
   */
    public function getAllTransactionHistoryApp(Request $request)
    {
        $limit = $request->per_page ?? 5;
        $order_data['column_name'] = $request->column_name ?? '';
        $order_data['order_by'] = $request->order_by ?? '';
        $order_data['search'] = $request->search;
        
        $data['title'] = __('Transaction History');
        $data['sub_menu'] = 'transaction';
        $sellService = new TransactionService();
        $data['items'] = $sellService->getMyAllTransactionHistory(Auth::id(),$order_data)->paginate($limit);
        $response = [
            'success' => true,
            'data' => $data,
            'message'=>__('Transaction History')
        ];
        return response()->json($response);
    }

    /**
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function getExchangeAllStopLimitOrdersApp(Request $request)
    {
        $response = [
            'success' => false,
            'data' => [],
            'message'=>__('Something went wrong')
        ];
        try {
            $service = new StopLimitService();
            $data['items'] = $service->getMyStopLimitOrders($request);
            $response = [
                'success' => true,
                'data' => $data,
                'message' => __('All stop limit order')
            ];
            return response()->json($response);
        } catch (\Exception $e) {
            storeException('getExchangeAllStopLimitOrdersApp', $e->getMessage());
            return response()->json($response);
        }
    }

    public function getReferralHistory(Request $request)
    {
        $limit = isset($request->limit)? $request->limit :25;
        $offset = isset($request->page)? $request->page : 1;

        if(!isset($request->type))
        {
            $response = ['success'=>false, 'message'=>__('Type is required!')];
            
        }elseif($request->type == REFERRAL_TYPE_WITHDRAWAL)
        {
            $affiliationService = new AffiliationService;
            $response = $affiliationService->getWithdrawalReferralHistoryWithPaginate($limit, $offset, $request->search);

        }elseif($request->type == REFERRAL_TYPE_TRADE)
        {
            $tradeReferralService = new TradeReferralService;
            $response = $tradeReferralService->getAllReferralHistoryWithPaginate($limit, $offset, $request->search);
        }else{
            $response = ['success'=>false, 'message'=>__('Invalid Type')];
        }

        return response()->json($response);
    }
}
