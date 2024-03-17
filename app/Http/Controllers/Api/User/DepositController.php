<?php

namespace App\Http\Controllers\Api\User;

use App\Model\CurrencyList;
use Illuminate\Http\Request;
use App\Http\Services\BankService;
use App\Http\Controllers\Controller;
use App\Http\Services\WalletService;
use Illuminate\Support\Facades\Auth;
use App\Http\Services\User2FAService;
use App\Http\Services\CurrencyService;
use App\Http\Services\PaymentMethodService;
use App\Model\CurrencyDepositPaymentMethod;
use App\Http\Services\CurrencyDepositService;
use App\Http\Requests\Api\User\CurrencyDepositRequest;
use App\Http\Requests\Api\User\CurrencyDepositRateRequest;
use App\Http\Requests\Api\User\CurrencyWalletDepositRequest;

class DepositController extends Controller
{
    public $service;
    private $bankService;
    private $paymentMethodService;
    private $walletService;
    private $currencyService;

    function __construct()
    {
        $this->service = new CurrencyDepositService();
        $this->bankService = new BankService();
        $this->paymentMethodService = new PaymentMethodService();
        $this->walletService = new WalletService();
        $this->currencyService = new CurrencyService();
    }
    /**
     * currencyDepositProcess
     *
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function currencyDepositProcess(CurrencyDepositRequest $request)
    {
        $currency_deposit_2fa_status = allsetting()['currency_deposit_2fa_status'] ?? 1;
        if ($currency_deposit_2fa_status == STATUS_ACTIVE && get_fiat_currency_method($request->payment_method_id) != PAYPAL) {
            $google2faService = new User2FAService();
            $valid = $google2faService->userGoogle2faValidation(Auth::user(),$request);
            if ($valid['success']) {
                $response = $this->service->sendCurrencyDepositRequest($request,Auth::user());
            } else {
                $response = responseData(false,$valid['message']);
            }
        } else {
            $response = $this->service->sendCurrencyDepositRequest($request,Auth::user());
        }

        return response()->json($response);
    }

    public function currencyDepositInfo(Request $request)
    {
        $data['banks'] = $this->bankService->getBanks();
        $data['payment_methods'] = CurrencyDepositPaymentMethod::whereNotIn('payment_method',[ CRYPTO ])
            ->where(['status'=> STATUS_ACTIVE,'type' => 'fiat-deposit'])
            ->get();
        $data['wallet_list'] =$this->walletService->getUserWalletList(Auth::id());
        $data['currency_list'] =$this->currencyService->getActiveCurrencyList();
        $data['deposit_fees_type'] = isset(settings()['fiat_deposit_fees_type']) ? settings()['fiat_deposit_fees_type'] : 1;
        $data['fiat_deposit_fees_value'] = isset(settings()['fiat_deposit_fees_value']) ? settings()['fiat_deposit_fees_value'] : 0;

        return response()->json(responseData(true,__('Bank and Payment Method List'),$data));
    }

    public function depositBankDetails($id)
    {
        $data = $this->bankService->getBank($id)['item'];

        return response()->json(responseData(true,__('Bank details'),$data));
    }

    // get currency deposit rate
    public function currencyDepositRate(CurrencyDepositRateRequest $request)
    {
        $response = $this->service->getCurrencyDepositRate($request,Auth::user());
        return response()->json($response);
    }

    public function currencyDepositHistory(Request $request)
    {
        $response = $this->service->getDepositHistory(Auth::id(),$request);
        return response()->json(responseData(true,__('Currency Deposit History'),$response));
    }

    public function getCurrencyDepositPageData(Request $request)
    {
        $data['banks'] = $this->bankService->getBanks();
        $data['payment_methods'] = CurrencyDepositPaymentMethod::whereType('fiat-deposit')->where('status', STATUS_ACTIVE)->whereNotIn('payment_method', [WALLET_DEPOSIT, CRYPTO])->get();

        return response()->json(responseData(true,__('Bank and Payment Method List'),$data));
    }

    public function currencyWalletDepositProcess(CurrencyWalletDepositRequest $request)
    {
        return response()->json(
            $this->service->sendWalletCurrencyDepositRequest($request,Auth::user())
        );
    }

    public function currencyWalletDepositHistory(Request $request){
        return response()->json(
            $this->service->currencyWalletDepositHistory($request)
        );
    }

    public function getCurrencyRate(Request $request)
    {
        $fromCoinType = $request->from_coin_type;
        $toCoinType = $request->to_coin_type;
        $amount = $request->amount??0;

        $data['converted_amount'] = convert_currency($amount, $toCoinType, $fromCoinType);
        $data['fees'] = getCalculatedFiatDepositFees($data['converted_amount']);
        $data['net_amount'] = bcsub($data['converted_amount'],$data['fees'],8);

        $data['coin_type'] = $toCoinType;

        return responseData(true, __('Convert amount!'), $data);

    }
}
