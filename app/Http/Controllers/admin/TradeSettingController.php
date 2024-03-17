<?php

namespace App\Http\Controllers\admin;

use App\Http\Requests\Admin\CoinPairRequest;
use App\Http\Requests\FutureCoinPairRequest;
use App\Http\Services\AdminSettingService;
use App\Model\AdminSetting;
use App\Model\Coin;
use App\Model\CoinPair;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Services\CoinPairService;

class TradeSettingController extends Controller
{
    private $coinPairService;

    public function __construct()
    {
        $this->coinPairService = new CoinPairService;
    }
    
    /*
   *
   * coin pair List
   * Show the list of specified resource.
   * @return \Illuminate\Http\Response
   *
   */
    public function coinPairs(Request $request)
    {
        $data['title'] = __('Coin Pair List');
        $data['coins'] = Coin::where(['is_base'=>STATUS_ACTIVE, 'trade_status'=>STATUS_ACTIVE, 'status'=>STATUS_ACTIVE])->get();

        $data['items'] = CoinPair::orderBy('id','desc')->get();

        return view('admin.exchange.coin_pair.list', $data);
    }

    /**
     * saveCoinPairSettings
     *
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */

    public function saveCoinPairSettings(CoinPairRequest $request)
    {
        $adminSettingService = new AdminSettingService();
        $update = $adminSettingService->savePairSetting($request);

        if (isset($update) && $update['success'] == true) {
            return redirect()->back()->with(['success' => $update['message']]);
        }

        return redirect()->back()->with(['dismiss' => $update['message']]);
    }

    /**
     * changeCoinPairStatus
     *
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     *
     */
    public function changeCoinPairStatus(Request $request)
    {
        $adminSettingService = new AdminSettingService();
        $update = $adminSettingService->changeCoinPairStatus($request);

        return response()->json(['success' => $update['success'], 'message' => $update['message']]);
    }

    public function changeCoinPairDefaultStatus(Request $request)
    {
        $adminSettingService = new AdminSettingService();
        $update = $adminSettingService->changeCoinPairDefaultStatus($request);

        return response()->json(['success' => $update['success'], 'message' => $update['message']]);
    }

    public function changeCoinPairBotStatus(Request $request)
    {
        $adminSettingService = new AdminSettingService();
        $update = $adminSettingService->changeCoinPairBotStatus($request);

        return response()->json(['success' => $update['success'], 'message' => $update['message']]);
    }


    public function coinPairsDelete($id)
    {
        try {
            $coinId = decryptId($id);
            if(is_array($coinId)) {
                return redirect()->back()->with(['dismiss' => __('Coin pair not found')]);
            }
            $adminSettingService = new AdminSettingService();
            $update = $adminSettingService->coinPairsDeleteProcess($coinId);
            if ($update['success'] == true) {
                return redirect()->back()->with(['success' => $update['message']]);
            } else {
                return redirect()->back()->with(['dismiss' => $update['message']]);
            }
        } catch (\Exception $e) {
            storeException('coinPairsDelete', $e->getMessage());
            return redirect()->back()->with(['dismiss' => __('Something went wrong')]);
        }
    }

    /**
     * tradeFeesSettings
     *
     * Store a newly created resource in storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     *
     */
    public function tradeFeesSettings()
    {
        $limits = AdminSetting::where('slug', 'like', 'trade_limit_%')->get();
        $makers = [];
        $takers = [];
        $trades = [];
        $numbers = [];
        foreach ($limits as $data) {
            $numbers[] = explode('_', $data->slug)[2];
            $makers[] = 'maker_' . explode('_', $data->slug)[2];
            $takers[] = 'taker_' . explode('_', $data->slug)[2];
            $trades[] = 'trade_limit_' . explode('_', $data->slug)[2];
        }
        $allSlugs = array_merge($makers, $takers, $trades);
        $settings = allsetting($allSlugs);
        $formatData = [];

        foreach ($numbers as $number) {
            $formatData[$number] = [
                'trade_limit_' . $number => $settings['trade_limit_' . $number],
                'maker_' . $number => $settings['maker_' . $number],
                'taker_' . $number => $settings['taker_' . $number],
            ];
        }
        $data['settings'] = $formatData;

        return view('admin.exchange.trade.trade_fees_settings', $data);
    }


    /**
     * tradeFeesSettingSave
     *
     * Store a newly created resource in storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     *
     */

    public function tradeFeesSettingSave(Request $request)
    {
        $fields = [];
        foreach ($request->except('_token') as $key => $part) {
            $fields[] = $key;
        }
        $rules = array_fill_keys($fields, ['numeric']);
        $this->validate($request, $rules);
        if (!isset($request->trade_limit_1) || $request->trade_limit_1 != 0) {
            return redirect()->back()->with(['dismiss' => __('First limit must be 0')]);
        }
        $adminSettingService = new AdminSettingService();
        $update = $adminSettingService->tradeSetting($request->except('_token'));
        if (isset($update) && $update['success'] == true) {
            return redirect()->back()->with(['success' => $update['message']]);
        }

        return redirect()->back()->with(['dismiss' => $update['message']]);
    }


    /**
     * removeTradeLimit
     *
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     *
     */
    public function removeTradeLimit(Request $request)
    {
        if ($request->id == 1) {
            return response()->json([
                'status' => false,
                'message' => __('First Limit can not be removed')
            ]);
        }
        $limits = AdminSetting::where('slug', 'like', '%_' . $request->id)->get();
        foreach ($limits as $limit) {
            $limit->forceDelete();
        }

        return response()->json([
            'status' => true,
            'message' => __('Limit is removed successfully')
        ]);
    }

    public function coinPairsChartUpdate($id)
    {
        $adminSettingService = new AdminSettingService();
        $update = $adminSettingService->coinPairsChartUpdate($id);

        if (isset($update) && $update['success'] == true) {
            return redirect()->back()->with(['success' => $update['message']]);
        }

        return redirect()->back()->with(['dismiss' => $update['message']]);
    }

    public function changeFutureTradeStatus(Request $request)
    {
        $adminSettingService = new AdminSettingService();
        $update = $adminSettingService->changeFutureTradeStatus($request);

        return response()->json(['success' => $update['success'], 'message' => $update['message']]);
    }

    public function coinPairFutureSetting($id)
    {
        $data['title'] = __('Coin Pair settings');

        $response = $this->coinPairService->getCoinPairDetails(decrypt($id));

        if($response['success'])
        {
            $data['coin_pair_details']  = $response['data'];

            return view('admin.exchange.coin_pair.settings', $data);
        }

        return back()->with(['dismiss' => $response['message']]);
        
    }

    public function coinPairFutureSettingUpdate(FutureCoinPairRequest $request)
    {
        $response = $this->coinPairService->coinPairFutureSettingUpdate($request);
        if($response['success'])
        {
            return redirect()->route('coinPairs')->with(['success'=>$response['message']]);
        }else{
            return back()->with(['dismiss'=>$response['message']]);
        }
    }

}
