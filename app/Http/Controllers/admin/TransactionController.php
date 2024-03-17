<?php

namespace App\Http\Controllers\admin;

use App\User;
use App\Model\Wallet;
use App\Jobs\MailSend;
use function foo\func;
use App\Exports\Transaction;
use Illuminate\Http\Request;
use App\Http\Services\Logger;
use App\Model\WithdrawHistory;
use App\Model\AffiliationHistory;
use App\Model\DepositeTransaction;
use Illuminate\Support\Facades\DB;
use App\Http\Services\TransService;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Maatwebsite\Excel\Facades\Excel;
use App\Model\CurrencyDepositHistory;
use App\Http\Services\CoinPaymentsAPI;
use App\Http\Services\BitCoinApiService;
use App\Model\CurrencyWithdrawalHistory;
use App\Http\Repositories\AffiliateRepository;
use App\Http\Requests\Admin\TransactionExportRequest;
use App\Http\Requests\CurrencyWithdrawalAcceptRequest;

class TransactionController extends Controller
{
    public $affiliateRepo;
    public $coinPayment;
    public $logger;
    public $bitCoinApi;
    public $transService;

    public function __construct()
    {
        $this->affiliateRepo = new AffiliateRepository();
        $this->affiliateRepo = new AffiliateRepository();
        $this->coinPayment = new CoinPaymentsAPI();
        $this->logger = new Logger();
        $this->bitCoinApi =  new BitCoinApiService(settings('coin_api_user'),settings('coin_api_pass'),settings('coin_api_host'),settings('coin_api_port'));
        $this->transService = new TransService();
    }


    // deposit  history
    public function adminTransactionHistory(Request $request)
    {
        $data['title'] = __('Transaction History');
        if ($request->ajax()) {
            $deposit = DepositeTransaction::select('deposite_transactions.address'
                , 'deposite_transactions.amount'
                , 'deposite_transactions.fees'
                , 'deposite_transactions.transaction_id'
                , 'deposite_transactions.confirmations'
                , 'deposite_transactions.address_type as addr_type'
                , 'deposite_transactions.created_at'
                , 'deposite_transactions.sender_wallet_id'
                , 'deposite_transactions.receiver_wallet_id'
                , 'deposite_transactions.status'
                , 'deposite_transactions.coin_type'
                , 'deposite_transactions.network_type'
            )->orderBy('deposite_transactions.id', 'desc');

            return datatables()->of($deposit)
                ->addColumn('address_type', function ($dpst) {
                    if ($dpst->addr_type == 'internal_address') {
                        return __('External');
                    } else {
                        return addressType($dpst->addr_type);
                    }

                })
                ->addColumn('coin_type', function ($dpst) {
                    return $dpst->coin_type;
                })
                ->addColumn('status', function ($dpst) {
                    return deposit_status($dpst->status);
                })
                ->addColumn('sender', function ($dpst) {
                    if (!empty($dpst->senderWallet) && $dpst->senderWallet->type == CO_WALLET) return  'Multi-signature Pocket: '.$dpst->senderWallet->name;
                    else
                        return isset($dpst->senderWallet->user) ? $dpst->senderWallet->user->first_name . ' ' . $dpst->senderWallet->user->last_name : 'N/A';
                })
                ->addColumn('receiver', function ($dpst) {
                    if (!empty($dpst->receiverWallet) && $dpst->receiverWallet->type == CO_WALLET) return  'Multi-signature Pocket: '.$dpst->receiverWallet->name;
                    else
                        return isset($dpst->receiverWallet->user) ? $dpst->receiverWallet->user->first_name . ' ' . $dpst->receiverWallet->user->last_name : 'N/A';
                })
                ->make(true);
        }

        return view('admin.transaction.all-transaction', $data);
    }

    // withdrawal history
    public function adminWithdrawalHistory(Request $request)
    {
        if ($request->ajax()) {
            $withdrawal = WithdrawHistory::select('withdraw_histories.address'
                    , 'withdraw_histories.amount'
                    , 'withdraw_histories.user_id'
                    , 'withdraw_histories.fees'
                    , 'withdraw_histories.transaction_hash'
                    , 'withdraw_histories.confirmations'
                    , 'withdraw_histories.address_type as addr_type'
                    , 'withdraw_histories.created_at'
                    , 'withdraw_histories.wallet_id'
                    , 'withdraw_histories.coin_type'
                    , 'withdraw_histories.network_type'
                    , 'withdraw_histories.receiver_wallet_id'
                    , 'withdraw_histories.status'
                    , 'withdraw_histories.memo'
                )->orderBy('withdraw_histories.id', 'desc');
            return datatables()->of($withdrawal)
                ->addColumn('address_type', function ($wdrl) {
                    return addressType($wdrl->addr_type);
                })
                ->addColumn('coin_type', function ($wdrl) {
                    return find_coin_type($wdrl->coin_type);
                })
                ->addColumn('sender', function ($wdrl) {
                    if(!empty($wdrl->user)) $user = $wdrl->user;
                    else $user = isset($wdrl->senderWallet) ? $wdrl->senderWallet->user : null;
                    return isset($user) ? $user->first_name . ' ' . $user->last_name : 'N/A';
                })
                ->addColumn('receiver', function ($wdrl) {
                    if (!empty($wdrl->receiverWallet) && $wdrl->receiverWallet->type == CO_WALLET) return  'Multi-signature Pocket: '.$wdrl->receiverWallet->name;
                    else
                    return isset($wdrl->receiverWallet->user) ? $wdrl->receiverWallet->user->first_name . ' ' . $wdrl->receiverWallet->user->last_name : 'N/A';
                })
                ->addColumn('status', function ($wdrl) {
                    return deposit_status($wdrl->status);
                })
                ->make(true);
        }

        return view('admin.transaction.all-transaction');
    }



    // pending withdrawal list
    public function adminPendingWithdrawal(Request $request)
    {
        $data['title'] = __('Pending Withdrawal');
        if ($request->ajax()) {
            $withdrawal = WithdrawHistory::select(
                'withdraw_histories.id',
                'withdraw_histories.address'
                , 'withdraw_histories.amount'
                , 'withdraw_histories.user_id'
                , 'withdraw_histories.fees'
                , 'withdraw_histories.transaction_hash'
                , 'withdraw_histories.confirmations'
                , 'withdraw_histories.address_type as addr_type'
                , 'withdraw_histories.updated_at'
                , 'withdraw_histories.wallet_id'
                , 'withdraw_histories.coin_type'
                , 'withdraw_histories.network_type'
                , 'withdraw_histories.receiver_wallet_id'
                , 'withdraw_histories.memo'
            )->where(['withdraw_histories.status' => STATUS_PENDING])
                ->orderBy('withdraw_histories.id', 'desc');

            return datatables()->of($withdrawal)
                ->addColumn('address_type', function ($wdrl) {
                    return addressType($wdrl->addr_type);
                })
                ->addColumn('coin_type', function ($wdrl) {
                    return find_coin_type($wdrl->coin_type);
                })
                ->addColumn('sender', function ($wdrl) {
                    if(!empty($wdrl->user)) $user = $wdrl->user;
                    else $user = isset($wdrl->senderWallet) ? $wdrl->senderWallet->user : null;
                    return isset($user) ? $user->first_name . ' ' . $user->last_name : 'N/A';
                })
                ->addColumn('receiver', function ($wdrl) {
                    if (!empty($wdrl->receiverWallet) && $wdrl->receiverWallet->type == CO_WALLET) return  'Multi-signature Pocket: '.$wdrl->receiverWallet->name;
                    else
                    return isset($wdrl->receiverWallet->user) ? $wdrl->receiverWallet->user->first_name . ' ' . $wdrl->receiverWallet->user->last_name : 'N/A';
                })
                ->addColumn('actions', function ($wdrl) {
                    $action = '<div class="activity-icon"><ul>';
                    $action .= accept_html('adminAcceptPendingWithdrawal',encrypt($wdrl->id));
                    $action .= reject_html('adminRejectPendingWithdrawal',encrypt($wdrl->id));
                    $action .= '</ul> </div>';

                    return $action;
                })
                ->rawColumns(['actions'])
                ->make(true);
        }
        return view('admin.transaction.pending-withdrawal', $data);
    }

    // rejected withdrawal list
    public function adminRejectedWithdrawal(Request $request)
    {
        $data['title'] = __('Rejected Withdrawal');
        if ($request->ajax()) {
            $withdrawal = WithdrawHistory::select(
                'withdraw_histories.address'
                , 'withdraw_histories.amount'
                , 'withdraw_histories.user_id'
                , 'withdraw_histories.fees'
                , 'withdraw_histories.transaction_hash'
                , 'withdraw_histories.confirmations'
                , 'withdraw_histories.address_type as addr_type'
                , 'withdraw_histories.updated_at'
                , 'withdraw_histories.wallet_id'
                , 'withdraw_histories.coin_type'
                , 'withdraw_histories.network_type'
                , 'withdraw_histories.receiver_wallet_id'
                , 'withdraw_histories.memo'
            )->where(['withdraw_histories.status' => STATUS_REJECTED])
                ->orderBy('withdraw_histories.id', 'desc');

            return datatables()->of($withdrawal)
                ->addColumn('address_type', function ($wdrl) {
                    return addressType($wdrl->addr_type);
                })
                ->addColumn('coin_type', function ($wdrl) {
                    return find_coin_type($wdrl->coin_type);
                })
                ->addColumn('sender', function ($wdrl) {
                    if(!empty($wdrl->user)) $user = $wdrl->user;
                    else $user = isset($wdrl->senderWallet) ? $wdrl->senderWallet->user : null;
                    return isset($user) ? $user->first_name . ' ' . $user->last_name : 'N/A';
                })
                ->addColumn('receiver', function ($wdrl) {
                    if (!empty($wdrl->receiverWallet) && $wdrl->receiverWallet->type == CO_WALLET) return  'Multi-signature Pocket: '.$wdrl->receiverWallet->name;
                    else
                    return isset($wdrl->receiverWallet->user) ? $wdrl->receiverWallet->user->first_name . ' ' . $wdrl->receiverWallet->user->last_name : 'N/A';
                })
                ->make(true);
        }

        return view('admin.transaction.pending-withdrawal', $data);
    }

    // active withdrawal list
    public function adminActiveWithdrawal(Request $request)
    {
        $data['title'] = __('Completed Withdrawal');
        if ($request->ajax()) {
            $withdrawal = WithdrawHistory::select(
                'withdraw_histories.address'
                , 'withdraw_histories.amount'
                , 'withdraw_histories.user_id'
                , 'withdraw_histories.fees'
                , 'withdraw_histories.transaction_hash'
                , 'withdraw_histories.confirmations'
                , 'withdraw_histories.address_type as addr_type'
                , 'withdraw_histories.updated_at'
                , 'withdraw_histories.wallet_id'
                , 'withdraw_histories.coin_type'
                , 'withdraw_histories.network_type'
                , 'withdraw_histories.receiver_wallet_id'
                , 'withdraw_histories.memo'
            )->where(['withdraw_histories.status' => STATUS_SUCCESS])
                ->orderBy('withdraw_histories.id', 'desc');

            return datatables()->of($withdrawal)
                ->addColumn('address_type', function ($wdrl) {
                    return addressType($wdrl->addr_type);
                })
                ->addColumn('coin_type', function ($wdrl) {
                    return find_coin_type($wdrl->coin_type);
                })
                ->addColumn('sender', function ($wdrl) {
                    if(!empty($wdrl->user)) $user = $wdrl->user;
                    else $user = isset($wdrl->senderWallet) ? $wdrl->senderWallet->user : null;
                    return isset($user) ? $user->first_name . ' ' . $user->last_name : 'N/A';
                })
                ->addColumn('receiver', function ($wdrl) {
                    if (!empty($wdrl->receiverWallet) && $wdrl->receiverWallet->type == CO_WALLET) return  'Multi-signature Pocket: '.$wdrl->receiverWallet->name;
                    else
                    return isset($wdrl->receiverWallet->user) ? $wdrl->receiverWallet->user->first_name . ' ' . $wdrl->receiverWallet->user->last_name : 'N/A';
                })
                ->make(true);
        }

        return view('admin.transaction.pending-withdrawal', $data);
    }

    // accept process of pending withdrawal
    public function adminAcceptPendingWithdrawal($id)
    {
        if (isset($id)) {
            try {
                $wdrl_id = decrypt($id);
            } catch (\Exception $e) {
                return redirect()->back();
            }
            $transaction = WithdrawHistory::with('wallet')->with('users')->where(['id' => $wdrl_id, 'status' => STATUS_PENDING])->firstOrFail();
            if (!empty($transaction)) {
                if ($transaction->address_type == ADDRESS_TYPE_INTERNAL) {
                    DepositeTransaction::where(['transaction_id' =>$transaction->transaction_hash, 'address' => $transaction->address])
                        ->update(['status' => STATUS_SUCCESS,'updated_by' => Auth::id()]);

                    Wallet::where(['id' => $transaction->receiver_wallet_id])->increment('balance', $transaction->amount);
                    $transaction->status = STATUS_SUCCESS;
                    $transaction->updated_by = Auth::id();
                    $transaction->save();

                    return redirect()->back()->with('success', __('Pending withdrawal accepted successfully.'));
                } elseif ($transaction->address_type == ADDRESS_TYPE_EXTERNAL) {
                    try {
                        $result =  $this->transService->acceptPendingExternalWithdrawal($transaction,Auth::id());
                        if ($result['success'] == true) {
                            return redirect()->back()->with('success', $result['message']);
                        } else {
                            return redirect()->back()->with('dismiss', $result['message']);
                        }
                    } catch(\Exception $e) {
                        $this->logger->log('adminAcceptPendingWithdrawal', $e->getMessage());
                        return redirect()->back()->with('dismiss', __('Something went wrong'));
                    }
                }
            }

            return redirect()->back()->with('dismiss', __('Something went wrong! Please try again!'));
        }
    }

    // pending withdrawal reject process
    public function adminRejectPendingWithdrawal($id)
    {
        DB::beginTransaction();
        try {
            if (isset($id)) {
                try {
                    $wdrl_id = decrypt($id);
                } catch (\Exception $e) {
                    return redirect()->back();
                }
                $data['message'] = __('Something went wrong');
                $transaction = WithdrawHistory::where(['id' => $wdrl_id, 'status' => STATUS_PENDING])->firstOrFail();

                if (!empty($transaction)) {
                    $amount = $transaction->amount + $transaction->fees;
                    if ($transaction->address_type == ADDRESS_TYPE_INTERNAL) {
                        Wallet::where(['id' => $transaction->wallet_id])->increment('balance', $amount);
                        $transaction->status = STATUS_REJECTED;
                        $transaction->updated_by = Auth::id();
                        $transaction->update();

                        $depositTransaction = DepositeTransaction::where(['transaction_id' =>$transaction->transaction_hash, 'address' => $transaction->address])->first();
                        $depositTransaction->update(['status' => STATUS_REJECTED,'updated_by' => Auth::id()]);

                        $data['message'] = __('Pending withdrawal rejected Successfully.');
                    } elseif ($transaction->address_type == ADDRESS_TYPE_EXTERNAL) {
                        Wallet::where(['id' => $transaction->wallet_id])->increment('balance', $amount);
                        $transaction->status = STATUS_REJECTED;
                        $transaction->updated_by = Auth::id();
                        $transaction->update();
                        $data['message'] = __('Pending withdrawal rejected Successfully.');
                    }

                    DB::commit();
                    return redirect()->back()->with('success', $data['message']);
                } else {
                    return redirect()->back()->with('dismiss', __('Transaction not found'));
                }
            }
            return redirect()->back()->with('dismiss', __('Something went wrong!'));
        } catch (\Exception $e) {
            DB::rollBack();
            $this->logger->log('adminRejectPendingWithdrawal', $e->getMessage());
            return redirect()->back()->with('dismiss', __('Something went wrong! Please try again!'));
        }
    }



    // pending deposit list
    public function adminPendingDeposit(Request $request)
    {
        $data['title'] = __('Pending Deposit');
        if ($request->ajax()) {
            $items = DepositeTransaction::where(['status' => STATUS_PENDING, 'address_type' => ADDRESS_TYPE_EXTERNAL])
                ->orderBy('id', 'desc');

            return datatables()->of($items)
                ->addColumn('created_at', function ($item) {
                    return $item->created_at;
                })
                ->addColumn('receiver_wallet_id', function ($item) {
                    return isset($item->receiverWallet->user->email) ? $item->receiverWallet->user->email : 'N/A';
                })
                ->addColumn('actions', function ($item) {
                    $action = '<div class="activity-icon"><ul>';
                    $action .= accept_html('adminPendingDepositAcceptProcess',encrypt($item->id));
                    $action .= '</ul> </div>';

                    return $action;
                })
                ->rawColumns(['actions'])
                ->make(true);
        }
        return view('admin.transaction.pending-deposit', $data);
    }

    // admin pending deposit accept process
    public function adminPendingDepositAcceptProcess($id)
    {
        DB::beginTransaction();
        try {
            if (isset($id)) {
                try {
                    $wdrl_id = decrypt($id);
                } catch (\Exception $e) {
                    storeException('adminPendingDepositAccept',$e->getMessage());
                    return redirect()->back()->with('dismiss', __('Encryption problem'));
                }
                $data['message'] = __('Something went wrong');
                $transaction = DepositeTransaction::where(['id' => $wdrl_id, 'status' => STATUS_PENDING,'address_type' => ADDRESS_TYPE_EXTERNAL])->first();
                if (!empty($transaction)) {
                    Wallet::where(['id' => $transaction->receiver_wallet_id])->increment('balance', $transaction->amount);
                    $transaction->status = STATUS_ACTIVE;
                    $transaction->update();

                    $data['message'] = __('Pending deposit accepted Successfully.');
                    DB::commit();
                    return redirect()->back()->with('success', $data['message']);
                } else {
                    return redirect()->back()->with('dismiss', __('Transaction not found'));
                }
            } else {
                return redirect()->back()->with('dismiss', __('Id is required'));
            }
        } catch (\Exception $e) {
            DB::rollBack();
            storeException('adminPendingDepositAccept',$e->getMessage());
            return redirect()->back()->with('dismiss', __('Something went wrong'));
        }
    }

    public function adminWithdrawalReferralHistory(Request $request)
    {
        $data['title'] = __('Withdrawal Referral Distribution History');
        $data['sub_menu'] = 'withdrawal_referral';

        if ($request->ajax()) {
            $referral_history_list = AffiliationHistory::join('users as reference_user', 'reference_user.id','=','affiliation_histories.user_id')
                                                    ->join('users as referral_user', 'referral_user.id','=','affiliation_histories.child_id')
                                                    ->latest()->select('affiliation_histories.*', 'reference_user.email as reference_user_email',
                                                    'referral_user.email as referral_user_email' );

            return datatables($referral_history_list)
                ->editColumn('created_at', function ($item){
                    return $item->created_at;
                })
                ->make(true);
        }
        return view('admin.transaction.deposit.withdrawal-referral-history', $data);
    }

    public function adminTransactionHistoryExport(TransactionExportRequest $request)
    {
        try{
            $file = $request->type ?? 'deposit';
            return Excel::download(new Transaction($request), $file.($request->export_to ?? '.csv'));
        }catch(\Exception $e){
            storeException('adminTransactionHistoryExport', $e->getMessage());
            return redirect()->back()->with('dismiss', __('Something went wrong'));
        }
    }

    public function adminPendingCurrencyDeposit(Request $request)
    {
        $data['title'] = __('Pending Deposit Currency');
        if ($request->ajax()) {
            $status = $request->status ?? 0;
            $items = CurrencyDepositHistory::with([
                'user:id,email',
                'payment_method:id,title,payment_method',
                'bank',
            ])
            ->where('status' , $status)
            ->orderBy('id', 'desc')->get();

            return datatables()->of($items)
                ->addColumn('user', function ($item) {
                    return $item?->user?->email;
                })
                ->addColumn('payment', function ($item) {
                    if($item?->payment_type == BANK_DEPOSIT)
                        return bankDepositShowHtml($item, $item?->bank, $item->bank_recipt);
                    else
                        return $item?->payment_method?->title;
                })
                ->editColumn('transaction_id', function ($item) {
                    if(empty($item->transaction_id))
                        return "N/A";
                    else
                        return $item?->transaction_id;
                })
                ->addColumn('created_at', function ($item) {
                    return $item->created_at;
                })
                ->editColumn('note', function ($item) {
                    return show_text_html($item->id,$item->note);
                })
                ->addColumn('actions', function ($item) {
                    $action = '<div class="activity-icon"><ul>';
                    $action .= accept_html('adminPendingCurrencyDepositAcceptProcess',encrypt($item->id));
                    $action .= reject_html_get_reject_note('adminPendingCurrencyDepositRejectProcess',encrypt($item->id));
                    $action .= '</ul> </div>';

                    return $action;
                })
                ->rawColumns(['payment','note','actions'])
                ->make(true);
        }
        return view('admin.transaction.pending-deposit-currency', $data);
    }

    public function adminPendingCurrencyDepositAcceptProcess($id)
    {
        DB::beginTransaction();
        try {
            if (isset($id)) {
                try {
                    $wdrl_id = decrypt($id);
                } catch (\Exception $e) {
                    storeException('adminPendingCurrencyDepositAcceptProcess',$e->getMessage());
                    return redirect()->back()->with('dismiss', __('Encryption problem'));
                }
                if($deposit = CurrencyDepositHistory::where(['id' => $wdrl_id, 'status' => STATUS_PENDING])->first()){
                    if($wallet = Wallet::where(['id' => $deposit->wallet_id])->first()){
                        if($wallet->increment('balance', $deposit->amount) && $deposit->update(['status' => STATUS_ACCEPTED])){
                            DB::commit();
                            return redirect()->back()->with('success', __('Pending deposit accepted Successfully.'));
                        }
                        return redirect()->back()->with('dismiss', __('Deposit failed to accept'));
                    }
                    return redirect()->back()->with('dismiss', __('User wallet not found'));
                }
                return redirect()->back()->with('dismiss', __('Deposit not found'));
            } else {
                return redirect()->back()->with('dismiss', __('Id is required'));
            }
        } catch (\Exception $e) {
            DB::rollBack();
            storeException('adminPendingDepositAccept',$e->getMessage());
            return redirect()->back()->with('dismiss', __('Something went wrong'));
        }
    }

    public function adminPendingCurrencyDepositRejectProcess(Request $request)
    {
        DB::beginTransaction();
        try {
            if (isset($request->id)) {
                try {
                    $wdrl_id = decrypt($request->id);
                } catch (\Exception $e) {
                    storeException('adminPendingCurrencyDepositRejectProcess',$e->getMessage());
                    return redirect()->back()->with('dismiss', __('Encryption problem'));
                }
                if(! isset($request->reject_note)) return redirect()->back()->with('dismiss', __('Reject not required'));
                if($deposit = CurrencyDepositHistory::where(['id' => $wdrl_id, 'status' => STATUS_PENDING])->first()){
                    $dataForUpdate = [
                        'status' => STATUS_REJECTED,
                        'note' => $request->reject_note
                    ];
                    if($deposit->update($dataForUpdate)){
                        DB::commit();
                        return redirect()->back()->with('success', __('Pending deposit rejected Successfully.'));
                    }
                    return redirect()->back()->with('dismiss', __('Deposit failed to rejected'));
                }
                return redirect()->back()->with('dismiss', __('Deposit not found'));
            } else {
                return redirect()->back()->with('dismiss', __('Id is required'));
            }
        } catch (\Exception $e) {
            DB::rollBack();
            storeException('adminPendingCurrencyDepositRejectProcess',$e->getMessage());
            return redirect()->back()->with('dismiss', __('Something went wrong'));
        }
    }

    public function downloadCurrencyDeposit($id){
        if($deposit = CurrencyDepositHistory::find($id)){
            if($deposit->payment_type !== BANK_DEPOSIT)
                return redirect()->back()->with('dismiss', __('This deposit payment method is not bank'));

            if(! file_exists(public_path(IMG_SLEEP_PATH.'/'.$deposit->bank_recipt)))
                return redirect()->back()->with('dismiss', __('File not exists'));

            return response()->download(public_path(IMG_SLEEP_PATH.'/'.$deposit->bank_recipt));
        }
        return redirect()->back()->with('dismiss', __('Deposit not found'));
    }

    // pending withdrawal list Currency
    public function adminPendingWithdrawalCurrency(Request $request)
    {
        $data['title'] = __('Pending Withdrawal');
        if ($request->ajax()) {
            $withdrawal = CurrencyWithdrawalHistory::with(
                'user:id,email'
            )->where('status', STATUS_PENDING)->orderBy('id', 'desc');

            return datatables()->of($withdrawal)
                ->addColumn('user', function ($wdrl) {
                    return $wdrl?->user?->email;
                })
                ->addColumn('bank', function ($wdrl) {
                    return bankShowHtml($wdrl->bank);
                })
                ->addColumn('currency', function ($wdrl) {
                    return $wdrl->coin_type;
                })
                ->addColumn('actions', function ($wdrl) {
                    $action = '<div class="activity-icon"><ul>';
                    $action .= upload_image_html('adminAcceptPendingWithdrawalCurrency',encrypt($wdrl->id));
                    $action .= reject_html('adminRejectPendingWithdrawalCurrency',encrypt($wdrl->id));
                    $action .= '</ul> </div>';

                    return $action;
                })
                ->rawColumns(['bank','actions'])
                ->make(true);
        }
        return view('admin.transaction.pending-withdrawal-currency', $data);
    }

        // rejected withdrawal list
        public function adminRejectedWithdrawalCurrency(Request $request)
        {
            $data['title'] = __('Rejected Withdrawal');
            if ($request->ajax()) {
                $withdrawal = CurrencyWithdrawalHistory::with(
                    'user:id,email'
                )->where('status', STATUS_REJECTED)->orderBy('id', 'desc');

                return datatables()->of($withdrawal)
                    ->addColumn('user', function ($wdrl) {
                        return $wdrl?->user?->email;
                    })
                    ->addColumn('bank', function ($wdrl) {
                        return bankShowHtml($wdrl->bank);
                    })
                    ->addColumn('currency', function ($wdrl) {
                        return $wdrl->coin_type;
                    })
                    ->rawColumns(['bank'])
                    ->make(true);
            }

            return view('admin.transaction.pending-withdrawal-currency', $data);
        }

            // active withdrawal list
    public function adminActiveWithdrawalCurrency(Request $request)
    {
        $data['title'] = __('Completed Withdrawal');
        if ($request->ajax()) {
            $withdrawal = CurrencyWithdrawalHistory::with(
                'user:id,email'
            )->where('status', STATUS_ACCEPTED)->orderBy('id', 'desc');

            return datatables()->of($withdrawal)
                ->addColumn('user', function ($wdrl) {
                    return $wdrl?->user?->email;
                })
                ->addColumn('bank', function ($wdrl) {
                    return bankDepositShowHtml($wdrl,$wdrl->bank, $wdrl->receipt);
                })
                ->addColumn('currency', function ($wdrl) {
                    return $wdrl->coin_type;
                })
                ->rawColumns(['bank'])
                ->make(true);
        }

        return view('admin.transaction.pending-withdrawal-currency', $data);
    }

    // accept process of pending withdrawal
    public function adminAcceptPendingWithdrawalCurrency(CurrencyWithdrawalAcceptRequest $request)
    {
       DB::beginTransaction();
       try{
            try {
                $wdrl_id = decrypt($request->id);
            } catch (\Exception $e) {
                return redirect()->back()->with('dismiss', __("Withdrawal id is invalid"));
            }

            if($transaction = CurrencyWithdrawalHistory::where(['id' => $wdrl_id, 'status' => STATUS_PENDING])->first()){
                $image = uploadFile($request->receipt, IMG_SLEEP_PATH, '');
                if($transaction->update(['status' => STATUS_ACCEPTED, 'receipt' => $image])){
                    DB::commit();
                    return redirect()->back()->with('success', __('Pending withdrawal accepted successfully.'));
                }
                return redirect()->back()->with('dismiss', __("Pending withdrawal accepted failed"));
            }
            return redirect()->back()->with('dismiss', __("Pending withdrawal record not nound"));
        } catch (\Exception $e) {
            DB::rollBack();
            storeException('adminAcceptPendingWithdrawalCurrency', $e->getMessage());
            return redirect()->back()->with('dismiss', __("Something went wrong"));
        }
    }

    public function adminRejectPendingWithdrawalCurrency($id)
    {
        DB::beginTransaction();
        try {
            if (isset($id)) {
                try {
                    $wdrl_id = decrypt($id);
                } catch (\Exception $e) {
                    return redirect()->back()->with('dismiss', __('Withrawal id is invalid'));
                }
                if($transaction = CurrencyWithdrawalHistory::with('wallet')->where(['id' => $wdrl_id, 'status' => STATUS_PENDING])->first()){
                    if($wallet = $transaction?->wallet){
                        if($wallet->increment('balance', $transaction->amount) && $transaction->update(["status"=>STATUS_ACCEPTED])){
                            DB::commit();
                            return redirect()->back()->with('success', __('Pending withdrawal accepted successfully.'));
                        }
                        return redirect()->back()->with('dismiss', __("User wallet not found"));
                    }
                    return redirect()->back()->with('dismiss', __("User wallet not found"));
                }
                return redirect()->back()->with('dismiss', __("Pending withdrawal record not nound"));
            }
            return redirect()->back()->with('dismiss', __('Withrawal id is required'));
        } catch (\Exception $e) {
            DB::rollBack();
            $this->logger->log('adminRejectPendingWithdrawalCurrency', $e->getMessage());
            return redirect()->back()->with('dismiss', __('Something went wrong! Please try again!'));
        }
    }

    // deposit  history
    public function adminTransactionHistoryCurrency(Request $request)
    {
        $data['title'] = __('Transaction History');
        if ($request->ajax()) {
            $items = CurrencyDepositHistory::with([
                'user:id,email',
                'payment_method:id,title,payment_method',
                'bank',
            ])
            ->where('status' , STATUS_ACCEPTED)
            ->orderBy('id', 'desc')->get();

            return datatables()->of($items)
                ->addColumn('user', function ($item) {
                    return $item?->user?->email;
                })
                ->addColumn('payment', function ($item) {
                    if($item?->payment_type == BANK_DEPOSIT)
                        return bankDepositShowHtml($item, $item?->bank, $item->bank_recipt);
                    else
                        return $item?->payment_method?->title;
                })
                ->editColumn('transaction_id', function ($item) {
                    if(empty($item->transaction_id))
                        return "N/A";
                    else
                        return $item?->transaction_id;
                })
                ->addColumn('created_at', function ($item) {
                    return $item->created_at;
                })
                ->addColumn('receiver_wallet_id', function ($item) {
                    return isset($item->receiverWallet->user->email) ? $item->receiverWallet->user->email : 'N/A';
                })
                ->rawColumns(['payment'])
                ->make(true);
        }

        return view('admin.transaction.all-transaction-currency', $data);
    }

    // withdrawal history
    public function adminWithdrawalHistoryCurrency(Request $request)
    {
        if ($request->ajax()) {
            $withdrawal = CurrencyWithdrawalHistory::with(
                'user:id,email'
            )->orderBy('id', 'desc');

            return datatables()->of($withdrawal)
                ->addColumn('user', function ($wdrl) {
                    return $wdrl?->user?->email;
                })
                ->addColumn('bank', function ($wdrl) {
                    return bankWithdrawalShowHtml($wdrl,$wdrl->bank);
                })
                ->addColumn('currency', function ($wdrl) {
                    return $wdrl->coin_type;
                })
                ->editColumn('status', function ($wdrl) {
                    return deposit_status($wdrl->status);
                })
                ->rawColumns(['bank'])
                ->make(true);
        }

        return view('admin.transaction.all-transaction-currency');
    }
}
