<?php

namespace App\Http\Controllers\Api\User;

use App\Http\Controllers\Controller;
use App\Http\Services\BuyOrderService;
use App\Http\Services\StopLimitService;
use App\Http\Validators\BuyOrderValidator;
use App\Http\Validators\StopLimitValidators;
use Illuminate\Http\Request;

class BuyOrderController extends Controller
{
    /**
     * Place limit buy order
     * @param BuyOrderValidator $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function placeBuyLimitOrderApp(BuyOrderValidator $request)
    {
        $request->merge([
            'is_market'=>0
        ]);
        if ($request->trade_coin_id == $request->base_coin_id) {
            response()->json( [
                'status' => false,
                'message' => __('Base coin and trade coin should be different'),
            ]);
        }
        $response = app(BuyOrderService::class)->create($request);
        return response()->json($response);
    }

    /**
     * place market buy order
     * @param BuyOrderValidator $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function placeBuyMarketOrderApp(BuyOrderValidator $request)
    {
        $request->merge([
            'is_market'=>1
        ]);
        if ($request->trade_coin_id == $request->base_coin_id) {
            response()->json( [
                'status' => false,
                'message' => __('Base coin and trade coin should be different'),
            ]);
        }
        $response = app(BuyOrderService::class)->create($request);
        return response()->json($response);
    }

    /**
     * place stop limit buy order
     * @param StopLimitValidators $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function placeBuyStopLimitOrderApp(StopLimitValidators $request)
    {
        $request->merge([
            'order'=>'buy'
        ]);
        if ($request->trade_coin_id == $request->base_coin_id) {
            response()->json( [
                'status' => false,
                'message' => __('Base coin and trade coin should be different'),
            ]);
        }
        $service = new StopLimitService();
        $response =  $service->create($request);
        return response()->json($response);
    }

}
