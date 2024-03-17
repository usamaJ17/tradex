<?php

namespace App\Http\Repositories;

use App\Model\CoinSetting;

class CoinSettingRepository extends CommonRepository
{

    function __construct($model)
    {
        parent::__construct($model);
    }

    public function getCoinSettingData($coinId)
    {
        $coinSetting = $this->createCoinSetting($coinId);
        return CoinSetting::join('coins', 'coins.id', '=','coin_settings.coin_id')->select('coins.*', 'coin_settings.*','coin_settings.id as coin_setting_id')
            ->where(['coin_settings.coin_id' => $coinSetting->coin_id])
            ->first();
    }

    public function createCoinSetting($coinId)
    {
        return CoinSetting::firstOrCreate(['coin_id' => $coinId], []);
    }

}
