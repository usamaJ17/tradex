<?php

namespace App\Http\Controllers\Api\User;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\User\NetworkAddressRequest;
use App\Http\Requests\Api\User\WalletRateRequest;
use App\Http\Requests\Api\User\WithdrawalRequest;
use App\Http\Requests\CoinSwapRequest;
use App\Http\Services\Logger;
use App\Http\Services\TransService;
use App\Http\Services\WalletService;
use App\Http\Services\ProgressStatusService;
use App\Model\Coin;
use App\Model\Wallet;
use App\Model\WalletSwapHistory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;


class WalletController extends Controller
{
    public $service;
    public $logger;
    public $transService;
    public $progressService;

    public function __construct()
    {
        $this->service = new WalletService();
        $this->logger = new Logger();
        $this->transService = new TransService();
        $this->progressService = new ProgressStatusService();
    }

    /**
     * wallet list
     * @return \Illuminate\Http\JsonResponse
     */
    public function walletList(Request $request)
    {
        try {
            $response = $this->service->userWalletList(Auth::id(),$request);
        } catch (\Exception $e) {
            $this->logger->log('walletList', $e->getMessage());
            $response = ['success' => false, 'message' => __('Something went wrong'), 'data' => []];
        }
        return response()->json($response);
    }

    /**
     * wallet deposit
     * @param $walletId
     * @return \Illuminate\Http\JsonResponse
     */
    public function walletDeposit($walletId)
    {
        try {
            $response = $this->service->userWalletDeposit(Auth::id(),$walletId);
        } catch (\Exception $e) {
            $this->logger->log('walletDeposit', $e->getMessage());
            $response = ['success' => false, 'message' => __('Something went wrong'), 'data' => []];
        }
        return response()->json($response);
    }

    /**
     * wallet withdrawal
     * @param $walletId
     * @return \Illuminate\Http\JsonResponse
     */
    public function walletWithdrawal($walletId)
    {
        try {
            $response = $this->service->userWalletWithdrawal(Auth::id(),$walletId);
        } catch (\Exception $e) {
            $this->logger->log('walletWithdrawal', $e->getMessage());
            $response = ['success' => false, 'message' => __('Something went wrong'), 'data' => []];
        }
        return response()->json($response);
    }

    /**
     * wallet withdrawal
     * @param WithdrawalRequest $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function walletWithdrawalProcess(WithdrawalRequest $request)
    {
        try {
            $response = $this->transService->withdrawalProcess($request);
        } catch (\Exception $e) {
            $this->logger->log('walletWithdrawalProcess', $e->getMessage());
            $response = ['success' => false, 'message' => __('Something went wrong'), 'data' => []];
        }
        return response()->json($response);
    }

    /**
     * wallet history
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function walletHistoryApp(Request $request){
        $limit = $request->per_page ?? 5;
        $order_data['column_name'] = $request->column_name ?? '';
        $order_data['order_by'] = $request->order_by ?? '';
        $limit = $request->per_page ?? 5;
        $response = [];
        $data = [];
        if(isset($request->type) && ($request->type == 'deposit' || $request->type == 'withdraw')) {
            $data['type'] = $request->type;
            $data['sub_menu'] = $request->type;
            if ($request->type == 'deposit') {
                $data['title'] = __('Deposit History');
            } else {
                $data['title'] = __("Withdrawal History");
            }
            if ($request->type == 'deposit') {
                $data['histories'] = $this->transService->depositTransactionHistories(Auth::id(),null,null,null,null,$order_data)->paginate($limit);
            } else {
                $data['histories'] = $this->transService->withdrawTransactionHistories(Auth::id(),null,null,null,null,$order_data)->paginate($limit);
            }

            $data['progress_status_for_deposit'] = allsetting('progress_status_for_deposit');
            $data['progress_status_for_withdrawal'] = allsetting('progress_status_for_withdrawal');

            if($request->type == 'deposit' && allsetting('progress_status_for_deposit') == true)
            {
                $data['progress_status_list'] = $this->progressService->getProgressStatusActiveListBytype(PROGRESS_STATUS_TYPE_DEPOSIT)['data'];

            }else if($request->type == 'withdraw' && allsetting('progress_status_for_withdrawal') == STATUS_ACTIVE)
            {
                $data['progress_status_list'] = $this->progressService->getProgressStatusActiveListBytype(PROGRESS_STATUS_TYPE_WITHDRAWN)['data'];
            }

            $data['status'] = deposit_status();
            $response['success'] = true;
            $response['data'] = $data;
            $response['message'] = $data['title'];

        }else{
            $response['success'] = false;
            $response['data'] = [];
            $response['message'] = 'Something Went Wrong!';
        }
        return response()->json($response);
    }

    /**
     * coin swap history
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function coinSwapHistoryApp(Request $request){
        $limit = $request->per_page ?? 5;
        $order_data['column_name'] = $request->column_name ?? 'id';
        $order_data['order_by'] = $request->order_by ?? 'desc';
        $data['title'] = __('Coin swap history');
        $data['sub_menu'] = 'swap_history';
        $data['list'] = WalletSwapHistory::where(['user_id' => Auth::id()])
                                            ->when(isset($request->search), function($query) use($request){
                                                $query->where('requested_amount', 'LIKE', '%'.$request->search.'%')
                                                        ->orWhere('converted_amount', 'LIKE', '%'.$request->search.'%')
                                                        ->orWhere('from_coin_type', 'LIKE', '%'.$request->search.'%')
                                                        ->orWhere('to_coin_type', 'LIKE', '%'.$request->search.'%');
                                            })
                                            ->orderBy($order_data['column_name'], $order_data['order_by'])->paginate($limit);
        foreach($data['list'] as &$item){
            $item->fromWallet=$item->fromWallet->name;
            $item->toWallet=$item->toWallet->name;
        }
        $response['success'] = true;
        $response['data'] = $data;
        $response['message'] = $data['title'];
        return response()->json($response);
    }

    /**
     * @return \Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\View\Factory|\Illuminate\Contracts\View\View
     */
    public function coinSwapApp(){
        $data['title'] = __('Coin Swap');
        $data['wallets'] = Wallet::join('coins','coins.id', '=', 'wallets.coin_id')
            ->where(['wallets.user_id'=> Auth::id(), 'wallets.type'=> PERSONAL_WALLET, 'coins.status' => STATUS_ACTIVE])
            ->orderBy('wallets.id', 'ASC')
            ->select('wallets.*')
            ->get();
        return response()->json(['success'=>true,'data'=>$data,'message'=>__('Coin Swap Data')]);
    }

    /**
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     * get rate of coin
     */
    public function getRateApp(WalletRateRequest $request)
    {
        $data = $this->service->get_wallet_rate($request);
        return response()->json($data);
    }

    /**
     * @param CoinSwapRequest $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function swapCoinApp(CoinSwapRequest $request){
        try{
            $data['success']=false;
            $data['message']=__('Something went wrong');
            $fromWallet = Wallet::where(['id'=>$request->from_coin_id])->first();
            // if(isset($request->code)){
            //     $response = checkTwoFactor("two_factor_swap",$request);
            //     if(!$response["success"]){
            //         return response()->json($response);
            //     }
            // }
            if (!empty($fromWallet) && $fromWallet->type == CO_WALLET) {
                return response()->json($data);
            }
            $response = $this->service->get_wallet_rate($request);
            if ($response['success'] == false) {
                return response()->json($data);
            }
            $swap_coin = $this->service->coinSwap($response['from_wallet'], $response['to_wallet'], $response['convert_rate'], $response['amount'], $response['rate']);
            if ($swap_coin['success'] == true) {
                $data['success']=true;
                $data['message']=$swap_coin['message'];
            } else {
                $data['success']=false;
                $data['message']=$swap_coin['message'];
            }
            return response()->json($data);
        }catch(\Exception $e){
            storeException('swapCoinApp ', $e->getMessage());
            return response()->json(responseData(false,__("Something went wrong")));
        }
    }

    /**
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function getCoinSwapDetailsApp(Request $request)
    {
        $wallet = Wallet::find($request->id);
        $data['wallets'] = Coin::select('coins.*', 'wallets.name as wallet_name', 'wallets.id as wallet_id', 'wallets.balance')
            ->join('wallets', 'wallets.coin_type', '=', 'coins.coin_type')
            ->where('coins.status', STATUS_ACTIVE)
            ->where('wallets.user_id', Auth::id())
            ->where('coins.coin_type', '!=', $wallet->coin_type)
            ->get();

        return response()->json($data);
    }

    /**
     * wallet network address
     * @param $walletId
     * @return \Illuminate\Http\JsonResponse
     */
    public function getWalletNetworkAddress(NetworkAddressRequest $request)
    {
        try {
            $response = $this->service->getWalletNetworkAddress($request,Auth::id());
        } catch (\Exception $e) {
            storeException('getWalletNetworkAddress', $e->getMessage());
            $response = responseData(false);
        }
        return response()->json($response);
    }

    public function preWithdrawalProcess(Request $request)
    {
        try {
            $response = $this->transService->preWithdrawalProcess($request);
        } catch (\Exception $e) {
            $this->logger->log('walletWithdrawalProcess', $e->getMessage());
            $response = ['success' => false, 'message' => __('Something went wrong'), 'data' => []];
        }
        return response()->json($response);
    }

    public function getWalletBalanceDetails(Request $request)
    {
        return response()->json(
            $this->service->getWalletBalanceDetails($request)
        );
    }
}
