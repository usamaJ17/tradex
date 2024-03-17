<?php

namespace App\Listeners;

use App\Events\OrderHasPlaced;
use App\Http\Repositories\BuyOrderRepository;
use App\Http\Services\DashboardService;
use App\Http\Services\Logger;
use App\Http\Services\BuySellTransactionService;
use App\Http\Services\TradingViewChartService;
use App\Model\Buy;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class StartProcessingOrder
{
    /**
     * Create the event listener.
     *
     * @return void
     */
    public function __construct()
    {
        //
    }

    /**
     * Handle the event.
     *
     * @param  object  $event
     * @return void
     */
    public function handle(OrderHasPlaced $event)
    {
//        storeBotException('StartProcessingOrder start ', date('Y-m-d H:i:s'));
        try{
            $type = Str::singular($event->order->getTable());
            $isMarket = ($type == 'buy' && $event->order->request_amount != 0) ? $event->order->is_market : 0;
            if($isMarket){
                $service = new BuySellTransactionService();
                $repo = new BuyOrderRepository(Buy::class);
                $order = $repo->getDocs(['id' => $event->order->id, 'status' => 0])->first();
                $orderId = $order->id;
                $beingProcessingOrders = $service->_getBeingProcessingOrders($order, $type);
                storeBotException('$beingProcessingOrders', json_encode($beingProcessingOrders));
                if ($beingProcessingOrders->isEmpty()) {
                    $message = __("No :orderType order found for this :type order.", ['orderType' => 'sell' , 'type' => $type]);
                    storeBotException('Order', $message);
                    return;
                }
                $loop = 0;
                foreach ($beingProcessingOrders as $beingProcessingOrder) {
                    $order = $repo->getDocs(['id' => $orderId, 'status' => 0])->first();
                    if(!empty($order) && bccomp($order->request_amount , $order->processed_request_amount) == 1){
//                        storeBotException('buy order', json_encode($order));
//                        storeBotException('sell order', json_encode($beingProcessingOrder));
                        $price = $beingProcessingOrder->price;
//                        storeBotException('$beingProcessingOrder->price ', $beingProcessingOrder->price);
                        if($service->refundIfFeesZero($beingProcessingOrder,'sell',$price) && $service->refundIfFeesZeroMarket($order,$price)){
                            $temporaryFees = calculated_fee_limit($order->user_id);

                            if(bcmul(bcsub($beingProcessingOrder->amount, $beingProcessingOrder->processed),$price) > bcmul(bcsub($order->request_amount,$order->processed_request_amount),$price)){
                                $amount = bcsub($order->request_amount,$order->processed_request_amount);
                            }else{
                                $amount = bcsub($beingProcessingOrder->amount, $beingProcessingOrder->processed);
                            }
                            $input = [
                                'user_id' => $order->user_id,
                                'trade_coin_id' => $order->trade_coin_id,
                                'base_coin_id' => $order->base_coin_id,
                                'amount' => $amount,
                                'request_amount' => 0,
                                'processed' => 0,
                                'virtual_amount' => 0,
                                'price' => $price,
                                'btc_rate' => 0,
                                'is_market' => 1,
                                'category' => 1,
                                'maker_fees' => custom_number_format($temporaryFees['maker_fees']),
                                'taker_fees' => custom_number_format($temporaryFees['taker_fees']),
                                'is_conditioned' => 0,
                            ];

                            $order->increment('processed_request_amount',$amount);
                            $success = Buy::create($input);
//                            storeBotException('$success buy create == ', json_encode($success));
                        }else{
                            continue;
                        }
                    }else{
                      break;
                    }
                    $loop = $loop+1;
                }
//                $request = [];
//                $request['base_coin_id'] = $order->base_coin_id;
//                $request['trade_coin_id'] = $order->trade_coin_id;
//                $request['dashboard_type'] = 'dashboard';
//                $request['per_page'] = '';
//                $time = time();
//                $interval = 5;
//                $startTime = $time - 864000;
//                $endTime = $time;
//                $socket_data = [];
//                $d_service = new DashboardService();
//                $socket_data['trades'] = $d_service->getMarketTransactions((object) $request)['data'];
//                $socket_data['last_trade'] = $d_service->getMarketLastTransactions((object) $request)['data'];
//                $chartService = new TradingViewChartService();
//                $socket_data['chart'] = $chartService->getChartData($startTime, $endTime, $interval, $order->base_coin_id, $order->trade_coin_id,1);
//                $channel_name = 'trade-info-'.$order->base_coin_id.'-'.$order->trade_coin_id;
//                $event_name = 'process';
//                $socket_data['summary'] = $d_service->getOrderData((object) $request)['data'];
//                $socket_data['update_trade_history'] = false;
//                sendDataThroughWebSocket($channel_name,$event_name,$socket_data);
//                $socket_data = $d_service->getMyTradeHistory((object) $request)['data'];
//                $channel_name = 'trade-history-'.$order->base_coin_id.'-'.$order->trade_coin_id.'-'.$order->user_id;
//                sendDataThroughWebSocket($channel_name,$event_name,$socket_data);
            }else{
//                storeBotException('Normal Order', 'transaction processing');
                app(BuySellTransactionService::class)->process($event->order->id, $type);

            }
        }catch (\Exception $e){
            storeException('Event Error', $e->getMessage().'file'.$e->getFile().' line'.$e->getLine());
        }
//        storeBotException('Event End', "==============================");
    }
}
