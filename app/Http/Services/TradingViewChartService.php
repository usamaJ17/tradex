<?php

namespace App\Http\Services;

use App\Http\Repositories\TradingViewChartRepository;
use App\Model\FifteenMinute;
use App\Model\FiveMinute;
use App\Model\FourHour;
use App\Model\OneDay;
use App\Model\ThirtyMinute;
use App\Model\TwoHour;

class TradingViewChartService
{
    public $repository;

    public function __construct()
    {
        $this->repository = new TradingViewChartRepository();
        $this->logger = app(Logger::class);
    }

    public function getChartData($startTime,$endTime, $interval, $baseCoinId, $tradeCoinId,$trade = null){


        switch ($interval) {
            case 5:{
                return $this->_5minData($baseCoinId, $tradeCoinId, $startTime, $endTime,$trade);
                break;
            }
            case 15:{
                return $this->_15minData($baseCoinId, $tradeCoinId, $startTime, $endTime,$trade);
                break;
            }
            case 30:{
                return $this->_30minData($baseCoinId, $tradeCoinId, $startTime, $endTime,$trade);
                break;
            }
            case 120:{
                return $this->_2hourData($baseCoinId, $tradeCoinId, $startTime, $endTime,$trade);
                break;
            }
            case 240:{
                return $this->_4hourData($baseCoinId, $tradeCoinId, $startTime, $endTime,$trade);
                break;
            }
            case 1440:{
                return $this->_1dayData($baseCoinId, $tradeCoinId, $startTime, $endTime,$trade);
                break;
            }
            default:
                return [];
        }
    }

    private function _5minData($baseCoinId, $tradeCoinId, $startTime, $endTime,$trade)
    {
        return $this->repository->getChartData(FiveMinute::class, $baseCoinId, $tradeCoinId, $startTime, $endTime,$trade);
    }

    private function _15minData($baseCoinId, $tradeCoinId, $startTime, $endTime,$trade)
    {
        return $this->repository->getChartData(FifteenMinute::class, $baseCoinId, $tradeCoinId, $startTime, $endTime,$trade);
    }

    private function _30minData($baseCoinId, $tradeCoinId, $startTime, $endTime,$trade)
    {
        return $this->repository->getChartData(ThirtyMinute::class, $baseCoinId, $tradeCoinId, $startTime, $endTime,$trade);
    }

    private function _2hourData($baseCoinId, $tradeCoinId, $startTime, $endTime,$trade)
    {
        return $this->repository->getChartData(TwoHour::class, $baseCoinId, $tradeCoinId, $startTime, $endTime,$trade);
    }

    private function _4hourData($baseCoinId, $tradeCoinId, $startTime, $endTime,$trade)
    {
        return $this->repository->getChartData(FourHour::class, $baseCoinId, $tradeCoinId, $startTime, $endTime,$trade);
    }

    private function _1dayData($baseCoinId, $tradeCoinId, $startTime, $endTime,$trade)
    {
        return $this->repository->getChartData(OneDay::class, $baseCoinId, $tradeCoinId, $startTime, $endTime,$trade);
    }
    public function updateCandleData($transaction){
//        $this->logger->log('updateCandleData Start', $transaction);
        $price = $transaction->price;
        $volume = $transaction->total;
        $baseCoinId = $transaction->base_coin_id;
        $tradeCoinId = $transaction->trade_coin_id;

        $transactionTime = strtotime($transaction->created_at);
//        $this->logger->log('updateCandleData $transactionTime', $transactionTime);

        $intervalTime5min = $transactionTime-($transactionTime%300);
        $intervalTime15min = $transactionTime-($transactionTime%900);
        $intervalTime30min = $transactionTime-($transactionTime%1800);
        $intervalTime2hour = $transactionTime-($transactionTime%7200);
        $intervalTime4hour = $transactionTime-($transactionTime%14400);
        $intervalTime1day = $transactionTime-($transactionTime%86400);

        $this->insert5minCandle($price,$volume, $baseCoinId, $tradeCoinId, $intervalTime5min);
        $this->insert15minCandle($price,$volume, $baseCoinId, $tradeCoinId, $intervalTime15min);
        $this->insert30minCandle($price,$volume, $baseCoinId, $tradeCoinId, $intervalTime30min);
        $this->insert2hourCandle($price,$volume, $baseCoinId, $tradeCoinId, $intervalTime2hour);
        $this->insert4hourCandle($price,$volume, $baseCoinId, $tradeCoinId, $intervalTime4hour);
        $this->insert1dayCandle($price,$volume, $baseCoinId, $tradeCoinId, $intervalTime1day);

//        $this->logger->log('updateCandleData End', $transaction);

    }
    public function insert5minCandle($price,$volume, $baseCoinId, $tradeCoinId, $intervalTime){
        $_5minCandle = $this->repository->getCandle(FiveMinute::class,['base_coin_id' => $baseCoinId,'trade_coin_id'=> $tradeCoinId, 'interval' => $intervalTime])->first();
        $last5minCandle = FiveMinute::where(['base_coin_id' => $baseCoinId,'trade_coin_id'=> $tradeCoinId])->orderBy('interval','DESC')->first();
        if(is_null($_5minCandle)){
            $open = is_null($last5minCandle) ? $price : $last5minCandle->close;
            $close  = $price;
            $high = $price > $open ? $price : $open;
            $low = $price < $open ? $price : $open;
            $data = array('base_coin_id' => $baseCoinId,'trade_coin_id'=> $tradeCoinId, 'interval' => $intervalTime, 'open' => $open, 'volume' => $volume, 'close' => $close, 'high' => $high, 'low' => $low);
            $this->repository->createNewCandle(FiveMinute::class, $data);
        } else {
            $close = $price;
            $high = $_5minCandle->high < $price ? $price : $_5minCandle->high;
            $low = $_5minCandle->low > $price ? $price : $_5minCandle->low;
            $volume = $_5minCandle->volume + $volume;
            $this->repository->updateCandle(FiveMinute::class, ['id' => $_5minCandle->id], ['close' => $close, 'high' => $high, 'low' => $low, 'volume' => $volume]);
        }
    }

    public function insert15minCandle($price,$volume, $baseCoinId, $tradeCoinId, $intervalTime)
    {
        $_15minCandle = $this->repository->getCandle(FifteenMinute::class,['base_coin_id' => $baseCoinId,'trade_coin_id'=> $tradeCoinId, 'interval' => $intervalTime])->first();
        $last15minCandle = FifteenMinute::where(['base_coin_id' => $baseCoinId,'trade_coin_id'=> $tradeCoinId])->orderBy('interval','DESC')->first();
        if(is_null($_15minCandle)){
            $open = is_null($last15minCandle) ? $price : $last15minCandle->close;
            $close  = $price;
            $high = $price > $open ? $price : $open;
            $low = $price < $open ? $price : $open;
            $data = array('base_coin_id' => $baseCoinId,'trade_coin_id'=> $tradeCoinId, 'interval' => $intervalTime, 'open' => $open,'volume' => $volume, 'close' => $close, 'high' => $high, 'low' => $low);
            $this->repository->createNewCandle(FifteenMinute::class, $data);
        } else {
            $close = $price;
            $volume = $_15minCandle->volume + $volume;
            $high = $_15minCandle->high < $price ? $price : $_15minCandle->high;
            $low = $_15minCandle->low > $price ? $price : $_15minCandle->low;
            $this->repository->updateCandle(FifteenMinute::class, ['id' => $_15minCandle->id], ['close' => $close, 'high' => $high,'volume' => $volume, 'low' => $low]);
        }
    }

    public function insert30minCandle($price, $volume, $baseCoinId, $tradeCoinId, $intervalTime)
    {
//        $this->logger->log('insert30minCandle', $intervalTime);
        $_30minCandle = $this->repository->getCandle(ThirtyMinute::class,['base_coin_id' => $baseCoinId,'trade_coin_id'=> $tradeCoinId, 'interval' => $intervalTime])->first();
        $last30minCandle = ThirtyMinute::where(['base_coin_id' => $baseCoinId,'trade_coin_id'=> $tradeCoinId])->orderBy('interval','DESC')->first();
        if(is_null($_30minCandle)){
            $open = is_null($last30minCandle) ? $price : $last30minCandle->close;
            $close  = $price;
            $high = $price > $open ? $price : $open;
            $low = $price < $open ? $price : $open;
            $data = array('base_coin_id' => $baseCoinId,'trade_coin_id'=> $tradeCoinId, 'interval' => $intervalTime, 'open' => $open, 'volume' => $volume,'close' => $close, 'high' => $high, 'low' => $low);
            $this->repository->createNewCandle(ThirtyMinute::class, $data);
        } else {
            $volume = $_30minCandle->volume + $volume;
            $close = $price;
            $high = $_30minCandle->high < $price ? $price : $_30minCandle->high;
            $low = $_30minCandle->low > $price ? $price : $_30minCandle->low;
            $this->repository->updateCandle(ThirtyMinute::class, ['id' => $_30minCandle->id], ['close' => $close,'volume' => $volume, 'high' => $high, 'low' => $low]);
        }
    }

    public function insert2hourCandle($price,$volume, $baseCoinId, $tradeCoinId, $intervalTime)
    {
        $_2hourCandle = $this->repository->getCandle(TwoHour::class,['base_coin_id' => $baseCoinId,'trade_coin_id'=> $tradeCoinId, 'interval' => $intervalTime])->first();
        $last2hourCandle = TwoHour::where(['base_coin_id' => $baseCoinId,'trade_coin_id'=> $tradeCoinId])->orderBy('interval','DESC')->first();
        if(is_null($_2hourCandle)){
            $open = is_null($last2hourCandle) ? $price : $last2hourCandle->close;
            $close  = $price;
            $high = $price > $open ? $price : $open;
            $low = $price < $open ? $price : $open;
            $data = array('base_coin_id' => $baseCoinId,'trade_coin_id'=> $tradeCoinId, 'interval' => $intervalTime, 'open' => $open,'volume' => $volume, 'close' => $close, 'high' => $high, 'low' => $low);
            $this->repository->createNewCandle(TwoHour::class, $data);
        } else {
            $volume = $_2hourCandle->volume + $volume;
            $close = $price;
            $high = $_2hourCandle->high < $price ? $price : $_2hourCandle->high;
            $low = $_2hourCandle->low > $price ? $price : $_2hourCandle->low;
            $this->repository->updateCandle(TwoHour::class, ['id' => $_2hourCandle->id], ['close' => $close, 'high' => $high,'volume' => $volume, 'low' => $low]);
        }
    }

    public function insert4hourCandle($price,$volume, $baseCoinId, $tradeCoinId, $intervalTime)
    {
        $_4hourCandle = $this->repository->getCandle(FourHour::class,['base_coin_id' => $baseCoinId,'trade_coin_id'=> $tradeCoinId, 'interval' => $intervalTime])->first();
        $last4hourCandle = FourHour::where(['base_coin_id' => $baseCoinId,'trade_coin_id'=> $tradeCoinId])->orderBy('interval','DESC')->first();
        if(is_null($_4hourCandle)){
            $open = is_null($last4hourCandle) ? $price : $last4hourCandle->close;
            $close  = $price;
            $high = $price > $open ? $price : $open;
            $low = $price < $open ? $price : $open;
            $data = array('base_coin_id' => $baseCoinId,'trade_coin_id'=> $tradeCoinId, 'interval' => $intervalTime, 'open' => $open,'volume' => $volume, 'close' => $close, 'high' => $high, 'low' => $low);
            $this->repository->createNewCandle(FourHour::class, $data);
        } else {
            $volume = $_4hourCandle->volume + $volume;
            $close = $price;
            $high = $_4hourCandle->high < $price ? $price : $_4hourCandle->high;
            $low = $_4hourCandle->low > $price ? $price : $_4hourCandle->low;
            $this->repository->updateCandle(FourHour::class, ['id' => $_4hourCandle->id], ['close' => $close, 'high' => $high,'volume' => $volume, 'low' => $low]);
        }
    }

    public function insert1dayCandle($price,$volume, $baseCoinId, $tradeCoinId, $intervalTime)
    {
        $_1dayCandle = $this->repository->getCandle(OneDay::class,['base_coin_id' => $baseCoinId,'trade_coin_id'=> $tradeCoinId, 'interval' => $intervalTime])->first();
        $last1dayCandle = OneDay::where(['base_coin_id' => $baseCoinId,'trade_coin_id'=> $tradeCoinId])->orderBy('interval','DESC')->first();
        if(is_null($_1dayCandle)){
            $open = is_null($last1dayCandle) ? $price : $last1dayCandle->close;
            $close  = $price;
            $high = $price > $open ? $price : $open;
            $low = $price < $open ? $price : $open;
            $data = array('base_coin_id' => $baseCoinId,'trade_coin_id'=> $tradeCoinId, 'interval' => $intervalTime, 'open' => $open, 'volume' => $volume,'close' => $close, 'high' => $high, 'low' => $low);
            $this->repository->createNewCandle(OneDay::class, $data);
        } else {
            $volume = $_1dayCandle->volume + $volume;
            $close = $price;
            $high = $_1dayCandle->high < $price ? $price : $_1dayCandle->high;
            $low = $_1dayCandle->low > $price ? $price : $_1dayCandle->low;
            $this->repository->updateCandle(OneDay::class, ['id' => $_1dayCandle->id], ['close' => $close, 'high' => $high,'volume' => $volume, 'low' => $low]);
        }
    }
}
