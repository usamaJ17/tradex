<?php
namespace App\Http\Controllers\Api;
use App\Http\Controllers\Controller;
use App\Http\Services\BitCoinApiService;
use App\Http\Services\Logger;
use App\Http\Services\WalletService;
use App\Model\BuyCoinHistory;
use App\Model\Coin;
use App\Model\DepositeTransaction;
use App\Model\Wallet;
use App\Model\WalletAddressHistory;
use App\Model\WalletNetwork;
use App\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Pusher\Pusher;
use Pusher\PusherException;

class WalletNotifier extends Controller
{

    private $logger;
    private $service;
    function __construct()
    {
        $this->logger = new Logger();
        $this->service = new WalletService();
    }
    // Wallet notifier for checking and confirming order process
    public function coinPaymentNotifier(Request $request)
    {
        storeException('coinPaymentNotifier','payment notifier called');
        $raw_request = $request->all();
        storeException('coinPaymentNotifier request',json_encode($raw_request));
        $merchant_id = settings('ipn_merchant_id');
        $secret = settings('ipn_secret');

        if (env('APP_ENV') != "local"){
            if (!isset($_SERVER['HTTP_HMAC']) || empty($_SERVER['HTTP_HMAC'])) {
                Log::info('No HMAC signature sent');

                die("No HMAC signature sent");
            }

            $merchant = isset($_POST['merchant']) ? $_POST['merchant']:'';
            if (empty($merchant)) {
                Log::info('No Merchant ID passed');

                die("No Merchant ID passed");
            }

            if ($merchant != $merchant_id) {
                Log::info('Invalid Merchant ID');

                die("Invalid Merchant ID");
            }

            $request = file_get_contents('php://input');
            if ($request === FALSE || empty($request)) {
                Log::info('Error reading POST data');

                die("Error reading POST data");
            }

            $hmac = hash_hmac("sha512", $request, $secret);

            if ($hmac != $_SERVER['HTTP_HMAC']) {
                Log::info('HMAC signature does not match');

                die("HMAC signature does not match");
            }
        }

        return $this->depositeWallet($raw_request);
    }

    public function depositeWallet($request)
    {
        Log::info('call deposit wallet');
        $data = ['success'=>false,'message'=>'something went wrong'];

        DB::beginTransaction();
        try {
            $request = (object)$request;
            storeException('$request =>',json_encode($request));
            if(isset($request->dest_tag) && !empty($request->dest_tag)) {
                $walletAddress = WalletAddressHistory::where(['address'=> $request->address, 'memo' => $request->dest_tag])->with('wallet')->first();
            } else {
                $walletAddress = WalletAddressHistory::where(['address'=> $request->address])->with('wallet')->first();
            }

            if (isset($walletAddress)) {
                if (($request->ipn_type == "deposit") && ($request->status >= 100)) {
                    $wallet =  $walletAddress->wallet;
                    $coin_type = strtok($request->currency,".");
                    $data['user_id'] = $wallet->user_id;
                    if (!empty($wallet)){
                        if ($wallet->coin_type != $coin_type && $wallet->coin_type != $request->currency) {
                            $data = ['success'=>false,'message'=>'Coin type not matched'];
                            Log::info('Coin type not matched');
                            return $data;
                        }
                        $checkDeposit = DepositeTransaction::where('transaction_id', $request->txn_id)->first();
                        if (isset($checkDeposit)) {
                            $data = ['success'=>false,'message'=>'Transaction id already exists in deposit'];
                            Log::info('Transaction id already exists in deposit');
                            return $data;
                        }

                        $depositData = [
                            'address' => $request->address,
                            'address_type' => ADDRESS_TYPE_EXTERNAL,
                            'amount' => $request->amount,
                            'fees' => 0,
                            'coin_type' => $walletAddress->coin_type,
                            'transaction_id' => $request->txn_id,
                            'confirmations' => $request->confirms,
                            'status' => STATUS_SUCCESS,
                            'receiver_wallet_id' => $wallet->id
                        ];

                        $depositCreate = DepositeTransaction::create($depositData);
                        Log::info(json_encode($depositCreate));

                        if (($depositCreate)) {
                            Log::info('Balance before deposit '.$wallet->balance);
                            $wallet->increment('balance', $depositCreate->amount);
                            Log::info('Balance after deposit '.$wallet->balance);
                            $data['message'] = 'Deposit successfully';
                            $data['success'] = true;
                        } else {
                            Log::info('Deposit not created ');
                            $data['message'] = 'Deposit not created';
                            $data['success'] = false;
                        }

                    } else {
                        $data = ['success'=>false,'message'=>'No wallet found'];
                        Log::info('No wallet found');
                    }
                }
            } else {
                $checkNetworkAddress = WalletNetwork::where(['address' => $request->address])->first();
                if (!empty($checkNetworkAddress)) {
                    storeException('network type', $checkNetworkAddress->network_type);

                    if (($request->ipn_type == "deposit") && ($request->status >= 100)) {
                        storeException('depositeWallet', 'deposit found');
                        $wallet =  Wallet::find($checkNetworkAddress->wallet_id);
                        $data['user_id'] = $wallet->user_id;
                        $coin_type = strtok($request->currency,".");
                        storeException('depositeWallet wallet ', $wallet);
                        if (!empty($wallet)){
                            if ($wallet->coin_type != $coin_type && $wallet->coin_type != $request->currency) {
                                $data = ['success'=>false,'message'=>'Coin type not matched'];
                                Log::info('Coin type not matched');
                                return $data;
                            }
                            $checkDeposit = DepositeTransaction::where('transaction_id', $request->txn_id)->first();
                            if (isset($checkDeposit)) {
                                $data = ['success'=>false,'message'=>'Transaction id already exists in deposit'];
                                Log::info('Transaction id already exists in deposit');
                                return $data;
                            }

                            $depositData = [
                                'address' => $request->address,
                                'address_type' => ADDRESS_TYPE_EXTERNAL,
                                'amount' => $request->amount,
                                'fees' => 0,
                                'coin_type' => $wallet->coin_type,
                                'transaction_id' => $request->txn_id,
                                'confirmations' => $request->confirms,
                                'status' => STATUS_SUCCESS,
                                'receiver_wallet_id' => $wallet->id,
                                'network_type' => $checkNetworkAddress->network_type
                            ];
                            $depositCreate = DepositeTransaction::create($depositData);
                            storeException('$depositCreate',json_encode($depositCreate));

                            if ($depositCreate) {
                                Log::info('Balance before deposit '.$wallet->balance);
                                $wallet->increment('balance', $depositCreate->amount);
                                Log::info('Balance after deposit '.$wallet->balance);
                                $data['message'] = 'Deposit successfully';
                                $data['success'] = true;
                            } else {
                                Log::info('Deposit not created ');
                                $data['message'] = 'Deposit not created';
                                $data['success'] = false;
                            }

                        } else {
                            $data = ['success'=>false,'message'=>'No wallet found'];
                            Log::info('No wallet found');
                        }
                    } else {
                        storeException('$request->ipn_type', $request->ipn_type);
                    }
                } else {
                    $data = ['success'=>false,'message'=>'Wallet address not found'];
                    Log::info('Wallet address not found id db');
                }
            }

            DB::commit();
            return $data;
        } catch (\Exception $e) {
            $data['message'] = $e->getMessage().' '.$e->getLine();
            Log::info($data['message']);
            DB::rollback();

            return $data;
        }
    }

 // wallet notifier for personal node

    public function walletNotify(Request $request)
    {
        storeException('notify called', date('Y-m-d H:i:s'));
        storeException('notify request',$request);
        return response()->json([
                    'message' => __('Notified successful.'),
                ]);
        try {
            Log::info(json_encode($request->all()));
        $coinType = strtoupper($request->coin_type);

        $transactionId = $request->transaction_id;
        Log::info('transactionId : '. $transactionId);
        $coin = Coin::join('coin_settings','coin_settings.coin_id', '=', 'coins.id')
            ->where(['coins.coin_type' => $coinType])
            ->select('coins.*', 'coin_settings.*')
            ->first();
        $coinservice =  new BitCoinApiService($coin->coin_api_user,decryptId($coin->coin_api_pass),$coin->coin_api_host,$coin->coin_api_port);
        $transaction = $coinservice->getTranscation($transactionId);
        storeException('walletNotify $transaction', json_encode($transaction));
        return response()->json([
                    'message' => __('Notified successful.'),
                ]);

                // next process done by wallet confirm process
        if($transaction) {
            $details = $transaction['details'];
            storeException('walletNotify $transaction details', json_encode($details));
            foreach ($details as $data) {
                storeException('walletNotify data', json_encode($data));
                if ($data['category'] = 'receive') {
                    $address[] = $data['address'];
                    $amount[] = $data['amount'];
                }
            }
            if (empty($address) || empty($amount)) {
                Log::info('transaction : This is a withdraw transaction hash ');
                return response()->json(['message' => __('This is a withdraw transaction hash')]);
            }
            DB::beginTransaction();
            try {
                $wallets = WalletAddressHistory::whereIn('address', $address)->get();

                if ($wallets->isEmpty()) {
                    Log::info('transaction address : Notify Unsuccessful. Address not found ');
                    return response()->json(['message' => __('Notify Unsuccessful. Address not found!')]);
                }
                if (!$wallets->isEmpty()) {
                    foreach ($wallets as $wallet) {
                        foreach ($address as $key => $val) {
                            if ($wallet->address == $val) {
                                $currentAmount = $amount[$key];
                            }
                        }
                        $inserts [] = [
                            'address' => $wallet->address,
                            'receiver_wallet_id' => $wallet->wallet_id,
                            'address_type' => 1,
                            'amount' => $currentAmount,
                            'coin_type' => $coinType,
//                            'type' => 'receive',
                            'status' => STATUS_PENDING,
                            'transaction_id' => $transactionId,
                            'confirmations' => $transaction['confirmations'],
                            'created_at' => Carbon::now(),
                            'updated_at' => Carbon::now()
                        ];
                    }
                }

                $response = [];
                if (!empty($inserts)) {
                    foreach ($inserts as $insert) {
                        $has_transaction = DepositeTransaction::where(['transaction_id' => $insert['transaction_id'], 'address' => $insert['address']])->count();
                        if (!$has_transaction) {
                            try {
                                $deposit = DepositeTransaction::insert($insert);
                                storeException('bitcoin deposit', json_encode($deposit));
                            } catch (\Exception $e) {
                                return response()->json([
                                    'message' => __('Transaction Hash is already in DB .'.$e->getMessage()),
                                ]);
                            }
                            $response[] = [
                                'transaction_id' => $insert['transaction_id'],
                                'address' => $insert['address'],
                                'success' => true
                            ];
                        } else {
                            $response [] = [
                                'transaction_id' => $insert['transaction_id'],
                                'address' => $insert['address'],
                                'success' => false
                            ];
                        }
                    }
                }
                Log::info('notyfy- ');
                Log::info(json_encode($response));
                DB::commit();

            } catch (\Exception $e) {
                DB::rollback();
                $response [] = [
                    'transaction_id' => '',
                    'address' => '',
                    'success' => false
                ];
            }

            if (empty($response)) {
                return response()->json([
                    'message' => __('Notified Unsuccessful.'),
                ]);
            }

            return response()->json([
                'response' => $response,
            ]);
        }
        } catch(\Exception $e) {
            storeException('walletNotify ex', $e->getMessage());
        }

        return response()->json(['message' => __('Not a valid transaction.')]);
    }

    public function notifyConfirm(Request $request)
    {
        // Log::info('notify confirmed called');
        $response=[];
        DB::beginTransaction();
        try {
            storeException('notify confirmed',json_encode($request->all()));
            $number_of_confirmation = settings('number_of_confirmation');
            // $transactions = $request->transactions['transactions'];
            $coinType = $request->coin_type;
            $transactions = $request->transactions;


            if(!empty($transactions))
            {
                foreach ($transactions as $transaction)
                {
                    if($transaction['category'] == 'receive')
                    {
                        $is_confirmed = false;
                        $transactionId = $transaction['txid'];
                        $address = $transaction['address'];
                        $amount = $transaction['amount'];
                        $confirmation = $transaction['confirmations'];
                        $pendingTransaction = DepositeTransaction::where(['transaction_id' => $transactionId, 'address' => $address])->first();
                        if(empty($pendingTransaction))
                        {
                            $checkAddress = WalletAddressHistory::where(['address'=> $address, 'coin_type' => $coinType])->first();
                            if ($checkAddress){
                                storeException('confirmation-> ',$confirmation);
                                if($confirmation >= $number_of_confirmation){

                                    try {
                                        $insert= [
                                            'address' => $address,
                                            'receiver_wallet_id' => $checkAddress->wallet_id,
                                            'address_type' => 1,
                                            'amount' => $amount,
                                            'coin_type' => $coinType,
                                            'status' => STATUS_SUCCESS,
                                            'transaction_id' => $transactionId,
                                            'confirmations' => $transaction['confirmations'],
                                            'created_at' => Carbon::now(),
                                            'updated_at' => Carbon::now()
                                        ];
                                        $deposit = DepositeTransaction::create($insert);
                                        storeException('deposit','found');
                                        storeException('deposit ',json_encode($deposit));
                                        $amount = $deposit->amount;
                                        Log::info('Wallet-confirm balance');
                                        Log::info('Received Amount: '. $amount);
                                        Log::info('Balance Before Update: '. $deposit->receiverWallet->balance);
                                        $deposit->receiverWallet->increment('balance', $amount);
                                        Log::info('Balance After Update: '. $deposit->receiverWallet->balance);
                                        Log::info('Wallet-Notify executed');
                                        $response[] = [
                                            'txid' => $transactionId,
                                            'is_confirmed' => true,
                                            'message' => __('success')
                                        ];
                                    } catch (\Exception $e) {
                                        DB::rollback();
                                        $response[] = [
                                            'txid' => $transactionId,
                                            'is_confirmed' => false,
                                            'message' => __('Already deposited.')
                                        ];

                                        $logText = [
                                            'walletID' => $deposit->receiverWallet->id,
                                            'transactionID' => $transactionId,
                                            'amount' => $amount,
                                        ];
                                        Log::info('Wallet-Notify-Failed');
                                        Log::info(json_encode($logText));
                                        Log::info($e->getMessage());
                                    }
                                    //
                                }
                            }
                        }

                    }
                }
            } else {
                    // Log::info('No Transaction Found');
                    $response [] = [
                        'message' => __('No Transaction Found')
                    ];
                }
        } catch(\Exception $e) {
            DB::rollback();
            storeException('notifier confirm ex', $e->getMessage());
        }
        DB::commit();
        return response()->json($response);
    }


    /**
     * For broadcast data
     * @param $data
     */
    public function broadCast($data)
    {
        $channelName = 'depositConfirmation.' . customEncrypt($data['userId']);
        $fields = json_encode([
            'channel_name' => $channelName,
            'event_name' => 'confirm',
            'broadcast_data' => $data['broadcastData'],
        ]);
        $curl = curl_init();

        curl_setopt_array($curl, array(
            CURLOPT_URL => 'https://' . env('BROADCAST_HOST') . '/api/broadcast',
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_CUSTOMREQUEST => "POST",
            CURLOPT_POSTFIELDS => $fields,
            CURLOPT_HTTPHEADER => array(
                "Content-Type: application/json",
                'broadcast-secret: an9$md_eoUqmNpa@bm34Jd'
            ),
        ));

        $response = curl_exec($curl);
        $err = curl_error($curl);

        curl_close($curl);
    }

    // bitgo wallet webhook
    public function bitgoWalletWebhook(Request $request)
    {
        Log::info('bitgoWalletWebhook start');
        try {
            $this->logger->log('bitgoWalletWebhook',' bitgoWalletWebhook called');
            $this->logger->log('bitgoWalletWebhook $request',json_encode($request->all()));

            if (isset($request->hash)) {
                $txId = $request->hash;
                $type = $request->type;
                $coinType = $request->coin;
//                $state = $request->state;
                $walletId = $request->wallet;
                $this->logger->log('bitgoWalletWebhook hash', $txId);
                if ($type == 'transfer' || $type == 'transaction') {
                    $checkHashInDB = DepositeTransaction::where(['transaction_id' => $txId, 'coin_type' => $coinType])->first();
                    if (isset($checkHashInDB)) {
                        $this->logger->log('bitgoWalletWebhook, already deposited hash -> ',$txId);
                    } else {
                        $this->logger->log('bitgoWalletCoinDeposit', 'called -> ');
                        $this->service->bitgoWalletCoinDeposit($coinType,$walletId,$txId);
                    }
                }
            }
        } catch (\Exception $e) {
            $this->logger->log('bitgoWalletWebhook', $e->getMessage());
        }

    }
}
