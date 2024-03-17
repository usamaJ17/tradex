<?php
namespace App\Http\Services;

use App\Model\CoinPair;
use Illuminate\Support\Facades\DB;

class TradingBotService
{

    public function placeBotOrder($userId)
    {
        try {
            $coinPairs = CoinPair::select('coin_pairs.id','parent_coin_id as base_coin_id','child_coin_id as trade_coin_id','coin_pairs.volume',
                'coin_pairs.is_token','coin_pairs.bot_trading','coin_pairs.initial_price','coin_pairs.bot_possible',
                DB::raw("visualNumberFormat(price) as last_price"), DB::raw("TRUNCATE(`change`,2) as price_change"),"high","low"
                ,'child_coin.coin_type as trade_coin_type','parent_coin.coin_type as base_coin_type',
                'child_coin.coin_price as trade_coin_usd_rate', 'parent_coin.coin_price as base_coin_usd_rate'
                , DB::raw('CONCAT(child_coin.coin_type,"_",parent_coin.coin_type) as pair_bin')
                , DB::raw('CONCAT(child_coin.coin_type,"_",parent_coin.coin_type) as coin_pair_coin'))
                ->join('coins as child_coin', ['coin_pairs.child_coin_id' => 'child_coin.id'])
                ->join('coins as parent_coin', ['coin_pairs.parent_coin_id' => 'parent_coin.id'])
                ->where(['coin_pairs.status' => STATUS_ACTIVE, 'coin_pairs.bot_trading' => STATUS_ACTIVE])
                ->orderBy('coin_pairs.id', 'asc')
                ->get();
                // dd($coinPairs);
            if (isset($coinPairs[0])) {
                foreach($coinPairs as $pair) {
                    // updateAdminWalletBalance($user->id,$pair->base_coin_id,$pair->trade_coin_id,$pair->base_coin_type,$pair->trade_coin_type);
                    // storeBotException('pair loop pair => ', $pair->pair_bin);
                    $this->processSinglePairBotOrder($userId,$pair);
                }
            }
        } catch(\Exception $e) {
            storeException('placeBotOrder', $e->getMessage());
        }
    }

    // process single pair order
    public function processSinglePairBotOrder($user,$pair) {
        try {
            $service = new TradingBotOrderPlaceService();
            // storeBotException('processSinglePairBotOrder', 'start');
            // if ($pair->is_token == STATUS_ACTIVE) {
            //     storeBotException('processSinglePairBotOrder token', $pair->pair_bin);
            //     $orderData = $this->generateBotOrderPriceAndAmount($pair);
            //     storeBotException('pair order data',$orderData);
            //     if (
            //         $orderData &&
            //         (($orderData['order_1']['price'] && $orderData['order_1']['amount']) ||
            //         ($orderData['order_2']['price'] && $orderData['order_2']['amount']))
            //     ) {

            //         $placeOrder = $service->placeBotBuySellOrder($orderData,$pair,$user);
            //         // dd('$orderData found', $orderData);
            //     }
            // } else {
                $callApi = getPriceFromApi($pair->pair_bin);
                if ($callApi['success'] == true) {
                    $sellData = $callApi['data']['sellData'];
                    $buyData = $callApi['data']['buyData'];
                    storeBotException('ask data', json_encode($sellData));
                    storeBotException('bids data', json_encode($buyData));
                    $service->createMarketBuyOrder($pair,$buyData,$user);
                    $service->createMarketSellOrder($pair,$sellData,$user);
                } else {
                    storeBotException('callApi else', 'called');
                    storeBotException('processSinglePairBotOrder token else', $pair->pair_bin);
                    if ($pair->is_token == STATUS_ACTIVE){
                        $orderData = $this->generateBotOrderPriceAndAmount($pair);
                        storeBotException('pair order data else',$orderData);
                        if (
                            $orderData &&
                            (($orderData['order_1']['price'] && $orderData['order_1']['amount']) ||
                            ($orderData['order_2']['price'] && $orderData['order_2']['amount']))
                        ) {

                            $service->placeBotBuySellOrder($orderData,$pair,$user);
                        }
                    }
                }
            // }


            // storeBotException('processSinglePairBotOrder', 'executed');
            // storeBotException('processSinglePairBotOrder', 'end');
        } catch(\Exception $e) {
            storeException('processSinglePairOrder '.$pair->id, $e->getMessage());
        }
    }


    // generate order price and ammount
    public function generateBotOrderPriceAndAmount($pair) {
        // storeBotException('generateBotOrderPriceAndAmount', 'start');
        $order_1 = $this->generateBotCustomPrice($pair,TRADE_TYPE_BUY);
        $order_2 = $this->generateBotCustomPrice($pair,TRADE_TYPE_SELL);

        // storeBotException('generateBotOrderPriceAndAmount', 'end');
        $response = [
            'order_1' => $order_1,
            'order_2' => $order_2
        ];

        return $response;
    }

    // generate custom price
    public function generateBotCustomPrice(
        $pair,
        $tradeType,
        $operation = BOT_RANDOM_MARKET_PRICE,
        $percentLeftLen=0,
        $percentDecimal=0,
        $maxPercent=0,
        $minPercent=0
        ) {
            // storeBotException('generateBotCustomPrice', 'start');
        try {
            $randomPercentFactor = 0;
            $price = 0;
            $priceChange = 0;
            $marketPrice = 0;
            $coinPairDecimal = 8;

            if ($operation == BOT_RANDOM_MARKET_PRICE) {
                $operation = getRandOperation();
              }
            storeBotException('generateBotCustomPrice operation => ', $operation);
            storeBotException('tradeType => ', $tradeType);
            $amount = $this->generateCustomBotAmount($pair);
            storeBotException('generateCustomBotAmount => ', $amount);
            if ($amount > 0) {
                if (($tradeType == TRADE_TYPE_BUY && $operation == BOT_INCREASE_MARKET_PRICE) ||
                ($tradeType == TRADE_TYPE_SELL && $operation == BOT_DECREASE_MARKET_PRICE)) {

                    storeBotException('generateLessDiffPricePercentFactor => ', 'condition match');
                    $priceFactorRes = $this->generateLessDiffPricePercentFactor($pair);

                    $randomPercentFactor = $priceFactorRes['percentFactor'];

                    $response = $this->calculatePrice($pair,$randomPercentFactor,null,$operation);
                    storeBotException('generateCustomBotAmount generateLessDiffPricePercentFactor => ', $response);
                    $price = $response['price'];
                    $operation = $response['operation'];
                    $priceChange = $response['priceChange'];
                    $marketPrice = $response['marketPrice'];
                } else {
                    if ($operation != BOT_SYNC_MARKET_PRICE) {
                        $randomPercentFactor = $this->generateRandPricePercentFactor(
                            $percentLeftLen,
                            $percentDecimal,
                            $maxPercent,
                            $minPercent
                        );
                    }
                    $response = $this->calculatePrice($pair,$randomPercentFactor,null,$operation);
                    storeBotException('generateBotCustomPrice calculatePrice  response => ', $response);

                    $price = $response['price'];
                    $operation = $response['operation'];
                    $priceChange = $response['priceChange'];
                    $marketPrice = $response['marketPrice'];
                }

                $price = formatAmountDecimal($price,$coinPairDecimal);
                storeBotException(' formatAmountDecimal price => ',$price);
                if($operation == BOT_SYNC_MARKET_PRICE) {
                    $curr_market_price = CoinPair::find($pair->id)->price;

                    if ($curr_market_price > $price) {
                        $tradeType = TRADE_TYPE_SELL;
                    } elseif($curr_market_price < $price) {
                        $tradeType = TRADE_TYPE_BUY;
                    }

                    if ($curr_market_price != $price) {
                        storeBotException(' $curr_market_price price => ',$curr_market_price);
                        storeBotException(' $price price => ',$price);
                        $amount += bcmul($amount,bcdiv(1000,100,8),8);
                        storeBotException(' $price amount => ',$amount);
                    }
                }
                $amount = formatAmountDecimal($amount,$coinPairDecimal);

                // storeBotException('coin pair => ',$pair->pair_bin);
                // storeBotException('order type => ',$tradeType);
                // storeBotException('market price => ',$marketPrice);
                // storeBotException('randomPercentFactor => ',$randomPercentFactor);
                // storeBotException('operation => ',$operation);
                // storeBotException('priceChange => ',$priceChange);
                // storeBotException('price => ',$price);
                // storeBotException('amount => ',$amount);
            }
            $resData = [
                'price' => $price,
                'amount' => $amount,
                'orderType' => $tradeType
            ];

            return $resData;
        } catch (\Exception $e) {
            storeException('generateBotCustomPrice '.$pair->id, $e->getMessage());
        }
    }

    // generate bot custom ammount
    public function generateCustomBotAmount($pair) {
        $result = 0;
        try {
            // storeBotException('generateCustomBotAmount trade_coin_type => ',$pair->trade_coin_type);
            $usdPrice = getConvertAmount($pair,$pair->trade_coin_type,'USD',1);
            // storeBotException('generateCustomBotAmount usdPrice => ',$usdPrice);
            $dividendFactor = getRandomIntFromRange(10,1000);
            // storeBotException('generateCustomBotAmount dividendFactor => ',$dividendFactor);
            $result = bcdiv($dividendFactor,$usdPrice,8);
            // storeBotException('generateCustomBotAmount result => ',$result);
        } catch(\Exception $e) {
            storeException('generateCustomBotAmount '.$pair->id, $e->getMessage());
        }
        return $result;
    }

    // generate less different price percent factor
    public function generateLessDiffPricePercentFactor($pair,$price=null,$randDiff=null,$randStart=null,$randEnd=null) {
        if(!$price) {
            $price = CoinPair::find($pair->id)->price;
        }
        $percentFactor = 0;
        $leftLength = getLeftSideLength($price);
        if($price >= 1) {
            if($leftLength >= 3) {
                $randStart = $randStart ? $randStart : generateNumOfZeros($leftLength - 3);
                $maxRandEnd = generateNumOfZeros($leftLength - 2);
                $wouldBeRandEnd = null;
                if($randDiff) {
                    $wouldBeRandEnd = bcadd($randStart,$randDiff,8);
                    if($wouldBeRandEnd > $maxRandEnd) {
                        $randEnd = $maxRandEnd;
                    }
                }
                $randEnd = $randEnd ?? $wouldBeRandEnd ?? $maxRandEnd;
                $dividendFactor = getRandomIntFromRange($randStart,$randEnd);
                $percentFactor = bcdiv($dividendFactor,$price);
            } else {
                $randStart = $randStart ?? generateNumOfZeros($leftLength - 1);

                $maxRandEnd = generateNumOfZeros($leftLength);
                $wouldBeRandEnd = null;
                if ($randDiff) {
                    $wouldBeRandEnd = bcadd($randStart, $randDiff,8);
                    if ($wouldBeRandEnd > $maxRandEnd) $randEnd = $maxRandEnd;
                }
                $randEnd = $randEnd ?? $wouldBeRandEnd ?? $maxRandEnd;

                $dividendFactor = getRandomIntFromRange($randStart, $randEnd);
                $percentFactor = bcdiv($dividendFactor,$price,8);
            }
        } else {
            $len_zeros = getConsecutiveZeroLength($price);
            $randStart = $randStart ?? generateNumOfZeros($len_zeros);

            $maxRandEnd = generateNumOfZeros($len_zeros + 1);
            $wouldBeRandEnd = null;
            if ($randDiff) {
                $wouldBeRandEnd = bcadd($randStart, $randDiff);
                if ($wouldBeRandEnd > $maxRandEnd) $randEnd = $maxRandEnd;
            }

            $randEnd = $randEnd ?? $wouldBeRandEnd ?? $maxRandEnd;

            $multiplierFactor = getRandomIntFromRange($randStart, $randEnd);
            $percentFactor = bcmul($multiplierFactor,$price,8);
        }
        $data = [
            'percentFactor' => $percentFactor,
            'price' => $price
        ];
        storeBotException('generateLessDiffPricePercentFactor data  => ', $data);
        return $data;
    }

    // calculate price
    public function calculatePrice($pair,$randomPercentFactor,$marketPrice=null,$operation=BOT_RANDOM_MARKET_PRICE) {
        $price = 0;
        $priceChange = 0;
        if(!$marketPrice) {
            $marketPrice = CoinPair::find($pair->id)->price;
        }
        if ($operation == BOT_RANDOM_MARKET_PRICE) {
            $operation = getRandOperation();
        }
        storeBotException('calculatePrice rand operation => ', $operation);
        if ($operation == BOT_SYNC_MARKET_PRICE) {
            $response = $this->processOperationNeutral($pair);
            $price = $response['price'];
            $priceChange = $response['priceChange'];
        } else {

            $priceChange = bcmul($marketPrice,bcdiv($randomPercentFactor,100,8),8);
            if ($operation == BOT_INCREASE_MARKET_PRICE) {
                $price = bcadd($marketPrice,$priceChange,8);
            } elseif($operation == BOT_DECREASE_MARKET_PRICE) {
                $price = bcsub($marketPrice,$priceChange);
            }
        }

        $res = [
            'price' => $price,
            'marketPrice' => $marketPrice,
            'priceChange' => $priceChange,
            'operation' => $operation,
        ];
        storeBotException('calculatePrice', $res);
        return $res;
    }

    // process operation neutral
    public function processOperationNeutral($pair) {
        // here trying to generate a price, which will be much close to current price from global market
        $data = $this->generatePriceUsingGlobalPrice($pair,BOT_SYNC_MARKET_PRICE,RATE_SOURCE_EXTERNAL);
        $res = [
            'price' => $data['price'],
            'priceChange' => $data['priceChange']
        ];
        return $res;
    }

    // generate price using global price
    public function generatePriceUsingGlobalPrice(
        $pair,
        $operation=BOT_RANDOM_MARKET_PRICE,
        $source=RATE_SOURCE_DB,
        $marketPrice=null
    ) {
        if(!$marketPrice) {
            $marketPrice = CoinPair::find($pair->id)->price;
        }
        if ($operation == BOT_RANDOM_MARKET_PRICE) {
            $operation = getRandOperation();
        }
        $price = 0;
        $priceChange = 0;

        $globalPrice = getConvertAmount($pair,$pair->trade_coin_type,$pair->base_coin_type,1,$source);
        if ($operation == BOT_SYNC_MARKET_PRICE) {
            $price = $globalPrice;
        } else {
            $percentFactor = $this->generateLessDiffPricePercentFactor($pair,$globalPrice,1000);
            $change = bcmul($globalPrice,bcdiv($percentFactor['percentFactor'],100,8),8);
            if ($operation == BOT_INCREASE_MARKET_PRICE) {
                $price = bcadd($globalPrice,$change,8);
            } elseif ($operation == BOT_DECREASE_MARKET_PRICE) {
                $price = bcsub($globalPrice,$change);
            }
        }

        $priceChange = $priceChange = abs($price - $marketPrice);

        $res = [
            'price' => $price,
            'marketPrice' => $marketPrice,
            'priceChange' => $priceChange,
            'operation' => $operation,
        ];

        storeBotException('generatePriceUsingGlobalPrice', $res);
        return $res;
    }

    public function generateRandPricePercentFactor(
        $percent_left_len,
        $percent_decimal,
        $max_percent,
        $min_percent
      ) {
        $percent_left_len = $percent_left_len
          ? $percent_left_len
          : PERCENT_LEFT_LEN;
        $percent_decimal = $percent_decimal
          ? $percent_decimal
          : PERCENT_DECIMAL;
        $max_percent = $max_percent
          ? $max_percent
          : MAX_PERCENT;
        $min_percent = $min_percent
          ? $min_percent
          : MIN_PERCENT;

        $randomPercentFactor =
          getRandomDecimalNumber($percent_left_len, $percent_decimal) ||
          $min_percent;
        if ($randomPercentFactor > $max_percent) {
          $randomPercentFactor = getRandomIntFromRange(1, 5) || 3;
        }

        storeBotException('generateRandPricePercentFactor => ', $randomPercentFactor);
        return $randomPercentFactor;
      }
}
