<?php

namespace App\Http\Services;


use App\Http\Repositories\AdminSettingRepository;
use App\Http\Repositories\SettingRepository;
use App\Model\AdminSetting;
use App\Model\Announcement;
use App\Model\Coin;
use App\Model\CoinPair;
use App\Model\LandingBanner;
use App\Model\UserNavbar;
use Illuminate\Support\Facades\DB;


class AdminSettingService extends CommonService
{
    public $model = AdminSetting::class;
    public $repository = AdminSettingRepository::class;
    public $logger;
    public function __construct()
    {
        parent::__construct($this->model,$this->repository);
        $this->logger = new Logger();
    }

    public function generalSetting($data)
    {
        $admin_setting_repo = new AdminSettingRepository();
        try {
            foreach ($data as $key => $val) {
                $admin_setting_repo->updateOrCreate($key, $val);
            }

            return ['success' => true, 'data' => $data, 'message' => 'updated.successfully'];
        } catch (\Exception $e) {

            return ['success' => false, 'data' => [], 'message' => 'something.went.wrong'];
        }
    }

    public function apiCredentialsUpdate($data)
    {
        $admin_setting_repo = new AdminSettingRepository();

        try {

            if (isset($data['coin_id'][0])) {

                for ($i = 0; $i < count($data['coin_id']); $i++) {

                    if (!empty($data['coin_id'][$i])) {
                        $coin_id = decryptId($data['coin_id'][$i]);

                        if (is_numeric($coin_id) && is_numeric($data['withdrawal_fee_method'][$i]) && is_numeric($data['withdrawal_fee_percent'][$i]) && is_numeric($data['withdrawal_fee_fixed'][$i])) {
                            $admin_setting_repo->ApiCredentialsUpdateOrCreate($coin_id, $data['api_service'][$i], $data['withdrawal_fee_method'][$i], $data['withdrawal_fee_percent'][$i], $data['withdrawal_fee_fixed'][$i]);
                        }
                    }
                }
                return ['success' => true, 'data' => $data, 'message' => 'updated.successfully'];
            } else {
                return ['success' => false, 'data' => [], 'message' => 'coin.not.valid'];
            }
        } catch (\Exception $e) {
            return ['success' => false, 'data' => [], 'message' => __('Something Went wrong.')];
        }
    }

    public function tradeSetting($data)
    {

        try {

            foreach ($data as $key => $val) {
                $labelName = str_replace('_', ' ', $key);
                $value = [
                    'value' => $val,
//                    'label' => $labelName,
//                    'type' => 'text'
                ];

                $this->updateOrCreateTrade($key, $value);
            }

            return ['success' => true, 'data' => $data, 'message' => __('Updated Successfully.')];
        } catch (\Exception $e) {

            return ['success' => false, 'data' => [], 'message' => __('Something Went wrong.' . $e->getMessage())];
        }
    }

    public function savePairSetting($request)
    {
        $setting_repo = new AdminSettingRepository();
        try {
            if ($request->parent_coin_id == $request->child_coin_id) {
                return ['success' => false, 'message' => __('Same coin pair is not possible')];
            }
            $request->merge(['is_token' => checkNetworkCoinPrice($request->parent_coin_id,$request->child_coin_id)]);
            $coinPair = CoinPair::where(['parent_coin_id' => $request->parent_coin_id, 'child_coin_id'=> $request->child_coin_id])->first();

            // if(! isset($request->pair_decimal)) return responseData(false, __("Pair decimal is missing!"));
            if(isset($request->pair_decimal)  && !( is_numeric($request->pair_decimal))) 
                return responseData(false, __("Pair decimal is invalid!"));

            if(isset($request->pair_decimal) && ($request->pair_decimal < 2 || $request->pair_decimal > 8)) 
                return responseData(false, __("Pair decimal should be between "));

            if (isset($request->edit_id)) {
                if (isset($coinPair) && ($coinPair->id != decrypt($request->edit_id))) {
                    return ['success' => false, 'message' => __('This coin pair already exist')];
                }

                $setting_repo->updateOrCreateCoinPair($request, $request->edit_id);
                $message = __('Updated Successfully.');
            } else {
                if (isset($coinPair)) {
                    return ['success' => false, 'message' => __('This coin pair already exist')];
                }

                $a = $setting_repo->updateOrCreateCoinPair($request);
                if($a) {
                    $message = __('Added Successfully.');
                } else {
                    $message = __('Get price failed, please add the price manually');
                }
            }

            return ['success' => true, 'message' => $message];
        } catch (\Exception $e) {
            storeException('savePairSetting', $e->getMessage());
            return ['success' => false, 'data' => [], 'message' => __('Something went wrong')];
        }
    }

    public function changeCoinPairStatus($request)
    {
        try {
            $pair = CoinPair::find(decrypt($request->active_id));
            $success = false;
            $message = __('Pair not found');
            if (isset($pair)) {
                if ($pair->status == STATUS_ACTIVE) {
                    $pair->update(['status' => STATUS_DEACTIVE]);
                } else {
                    $pair->update(['status' => STATUS_ACTIVE]);
                }
                $success = true;
                $message = __('Status updated successfully');
            }

            return ['success' => $success, 'message' => $message];
        } catch (\Exception $e) {
            storeException('changeCoinPairStatus', $e->getMessage());
            return ['success' => false, 'message' => __('Something went wrong')];
        }
    }

    public function changeCoinPairDefaultStatus($request)
    {
        try {
            $pair = CoinPair::find(decrypt($request->active_id));
            $success = false;
            $message = __('Pair not found');
            if (isset($pair)) {
                if ($pair->is_default == STATUS_ACTIVE) {
                    $pair->update(['is_default' => STATUS_DEACTIVE]);
                } else {
                    $pair->update(['is_default' => STATUS_ACTIVE]);
                }

                if($pair->is_default == STATUS_ACTIVE)
                {
                    CoinPair::where('id', '<>', $pair->id)->update(['is_default' => STATUS_DEACTIVE]);
                }
                $success = true;
                $message = __('Default Status is updated successfully!');
            }

            return ['success' => $success, 'message' => $message];
        } catch (\Exception $e) {
            storeException('changeCoinPairStatus', $e->getMessage());
            return ['success' => false, 'message' => __('Something went wrong')];
        }
    }

    public function changeCoinPairBotStatus($request)
    {
        try {
            $pair = CoinPair::find(decrypt($request->active_id));
            $success = false;
            $message = __('Pair not found');
            if (isset($pair)) {
                if ($pair->bot_trading == STATUS_ACTIVE) {
                    $pair->update(['bot_trading' => STATUS_DEACTIVE]);
                } else {
                    $pair->update(['bot_trading' => STATUS_ACTIVE]);
                }
                $success = true;
                $message = __('Status updated successfully');
            }

            return ['success' => $success, 'message' => $message];
        } catch (\Exception $e) {
            storeException('changeCoinPairBotStatus', $e->getMessage());
            return ['success' => false, 'message' => __('Something went wrong')];
        }
    }

    public function updateOrCreateTrade($slug, $value)
    {
        return AdminSetting::updateOrCreate(['slug' => $slug], $value);
    }

    public function saveAnnouncement($request)
    {
        $response = ['success' => false, 'message' => __('Something went wrong')];
        try {
            $data = [
                'title'=> $request->title,
                'description'=> $request->details,
                'status'=> $request->status
            ];
            $slug = make_unique_slug($request->title,'announcements');
            if (empty($request->edit_id)) {
                $data['slug'] = $slug;
            }
            $old_img = '';
            if (!empty($request->edit_id)) {
                $item = Announcement::where(['id'=>$request->edit_id])->first();
                if(isset($item) && (!empty($item->image))) {
                    $old_img = $item->image;
                }
            }
            if (!empty($request->image)) {
                $icon = uploadFile($request->image,IMG_PATH,$old_img);
                if ($icon != false) {
                    $data['image'] = $icon;
                }
            }
            if(!empty($request->edit_id)) {
                Announcement::where(['id'=>$request->edit_id])->update($data);
                $response = ['success' => true, 'message' => __('Announcement updated successfully!')];
            } else {
                Announcement::create($data);
                $response = ['success' => true, 'message' => __('Announcement created successfully!')];
            }
        } catch (\Exception $e) {
            $this->logger->log('saveAnnouncement', $e->getMessage());
            $response = ['success' => false, 'message' => __('Something went wrong')];
        }
        return $response;
    }

    public function saveBanner($request)
    {
        $response = ['success' => false, 'message' => __('Something went wrong')];
        try {
            $data = [
                'title'=> $request->title,
                'description'=> $request->body,
                'status'=> $request->status
            ];
            $slug = make_unique_slug($request->title,'landing_banners');
            if (empty($request->edit_id)) {
                $data['slug'] = $slug;
            }
            $old_img = '';
            if (!empty($request->edit_id)) {
                $item = LandingBanner::where(['id'=>$request->edit_id])->first();
                if(isset($item) && (!empty($item->image))) {
                    $old_img = $item->image;
                }
            }
            if (!empty($request->image)) {
                $icon = uploadFile($request->image,IMG_PATH,$old_img);
                if ($icon != false) {
                    $data['image'] = $icon;
                }
            }
            if(!empty($request->edit_id)) {
                LandingBanner::where(['id'=>$request->edit_id])->update($data);
                $response = ['success' => true, 'message' => __('Banner updated successfully!')];
            } else {
                LandingBanner::create($data);
                $response = ['success' => true, 'message' => __('Banner created successfully!')];
            }
        } catch (\Exception $e) {
            $this->logger->log('saveBanner', $e->getMessage());
            $response = ['success' => false, 'message' => __('Something went wrong')];
        }
        return $response;
    }

    // delete coin pair
    public function coinPairsDeleteProcess($id)
    {
        try {
            $coinPair = CoinPair::find($id);
            if ($coinPair) {
                $check = checkCoinPairDeleteCondition($coinPair);
                if ($check['success'] == false) {
                    return ['success' => false, 'message' => $check['message']];
                }
                DB::table('coin_pairs')->where(['id' => $id])->delete();
                $response = ['success' => true, 'message' => __('Pair deleted successfully')];
            } else {
                $response = ['success' => false, 'message' => __('Pair not found')];
            }
        } catch (\Exception $e) {
            storeException('coinPairsDeleteProcess', $e->getMessage());
            $response = ['success' => false, 'message' => __('Something went wrong')];
        }
        return $response;
    }

    // update chart data from api
    public function coinPairsChartUpdate($id)
    {
        try {
            $chartApi = new ChartThirdPartyApiService();
            $coinPair = CoinPair::where(['id' => decryptId($id),'is_chart_updated' => STATUS_PENDING])->first();
            if ($coinPair) {
                $apiData = $chartApi->updateDataFromCryptoCompare($coinPair->parent_coin_id,$coinPair->child_coin_id);
                if ($apiData == TRUE) {
                    $coinPair->update(['is_chart_updated' => STATUS_SUCCESS]);
                    $response = responseData(true,__('Coin pair data added successfully'));
                } else {
                    $response = responseData(false,__('Data added failed'));
                }
            } else {
                $response = responseData(false,__('Coin pair not found'));
            }
        } catch (\Exception $e) {
            storeException('coinPairsChartUpdate', $e->getMessage());
            $response = responseData(false);
        }
        return $response;
    }

    public function saveThemeColorSettings($request)
    {
        try {
            $admin_setting_repo = new SettingRepository();
            $admin_setting_repo->saveAdminSetting($request);
            $custom_color = 'custom_color';
            $custom_color_value = STATUS_ACTIVE;
            $admin_setting_repo->updateOrCreate($custom_color, $custom_color_value);

            $response = responseData(true, __('Theme Color Updated Successfully!'));
        } catch (\Exception $e) {
            storeException('saveThemeColorSettings', $e->getMessage());
            $response = responseData(false, __('Something went wrong!'));
        }
        return $response;
    }

    public function saveThemeSettings($request)
    {
        try {
            $admin_setting_repo = new SettingRepository();
            $admin_setting_repo->saveAdminSetting($request);
            $response = responseData(true, __('Theme Updated Successfully!'));
        } catch (\Exception $e) {
            storeException('saveThemeSettings', $e->getMessage());
            $response = responseData(false, __('Something went wrong!'));
        }
        return $response;
    }

    public function resetThemeColorSettings()
    {
        try {
            $admin_setting_repo = new SettingRepository();
            $theme_colors = AdminSetting::where('slug','like', '%user_%')->get();
            if(isset($theme_colors ))
            {
                foreach($theme_colors as $theme_color)
                {
                    $admin_setting_repo->updateOrCreate($theme_color->slug, '');
                }
            }

            $custom_color = 'custom_color';
            $custom_color_value = STATUS_DEACTIVE;
            $admin_setting_repo->updateOrCreate($custom_color, $custom_color_value);

            $response = responseData(true, __('Theme Color reset Successfully!'));
        } catch (\Exception $e) {
            storeException('saveThemeColorSettings', $e->getMessage());
            $response = responseData(false, __('Something went wrong!'));
        }
        return $response;
    }

    public function themeNavebarSettingsSave($request){
        try {
            $menu = UserNavbar::find($request->id);
            if ($request->type) {
                $menu->title = $request->value;
            }else{
                $menu->status = !$menu->status;
            }
            $menu->save();
            return responseData(true, __('Navbar updated Successfully!'));
        } catch (\Exception $e) {
            storeException('saveThemeColorSettings', $e->getMessage());
            return responseData(false, __('Something went wrong!'));
        }
    }

    public function changeFutureTradeStatus($request)
    {
        try {
            $pair = CoinPair::find(decrypt($request->active_id));
            $success = false;
            $message = __('Pair not found');
            if (isset($pair)) {

                $coinType = Coin::find($pair->parent_coin_id)->coin_type;

                $pair->margin_type = $coinType == 'USDT'? FUTURE_TRADE_COIN_TYPE_USDM :FUTURE_TRADE_COIN_TYPE_COINM;
                if ($pair->enable_future_trade == STATUS_ACTIVE) {
                    $pair->enable_future_trade = STATUS_DEACTIVE;
                } else {
                    $pair->enable_future_trade = STATUS_ACTIVE;
                }

                $pair->save();

                $success = true;
                $message = __('Status updated successfully');
            }

            return ['success' => $success, 'message' => $message];
        } catch (\Exception $e) {
            storeException('changeFutureTradeStatus', $e->getMessage());
            return ['success' => false, 'message' => __('Something went wrong')];
        }
    }


}
