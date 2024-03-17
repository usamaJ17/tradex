<?php

namespace App\Http\Controllers\Api\User;

use App\Http\Controllers\Controller;
use App\Http\Services\SellOrderService;
use App\Http\Services\StopLimitService;
use App\Http\Validators\SellOrderValidator;
use App\Http\Validators\StopLimitValidators;
use Illuminate\Http\Request;

class SellOrderController extends Controller
{
    /**
     * @param SellOrderValidator $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function placeSellLimitOrderApp(SellOrderValidator $request)
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
        $response = app(SellOrderService::class)->create($request);
        if ($response['status'] == false) {
            return response()->json($response);
        }

        return response()->json($response);
    }

    /**
     * @param SellOrderValidator $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function placeSellMarketOrderApp(SellOrderValidator $request)
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
        $response = app(SellOrderService::class)->create($request);
        if ($response['status'] == false) {
            return response()->json($response);
        }

        return response()->json($response);
    }

    /**
     * @param StopLimitValidators $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function placeStopLimitSellOrderApp(StopLimitValidators $request)
    {
        $request->merge([
            'order'=>'sell'
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
