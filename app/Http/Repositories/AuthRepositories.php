<?php
namespace App\Http\Repositories;
use App\Http\Services\Logger;
use App\Model\Coin;
use App\Model\UserVerificationCode;
use App\Model\Wallet;
use App\User;

class AuthRepositories
{
    public $logger;
    public function __construct()
    {
        $this->logger = new Logger;
    }
    public function generate_email_verification_key()
    {
        $key = randomNumber(6);
        return $key;
    }

    public function create($userData)
    {
        try {
            return User::create($userData);
        } catch (\Exception $e) {
            $this->logger->log('user create', $e->getMessage());
            return false;
        }
    }

    public function createUserVerification($data)
    {
        try {
            return UserVerificationCode::create($data);
        } catch (\Exception $e) {
            $this->logger->log('user verification create', $e->getMessage());
            return false;
        }
    }

    public function createUserWallet($user_id)
    {
        try {
            $coins = Coin::where('status','<>', STATUS_DELETED)->get();
            if (isset($coins[0])) {
                foreach ($coins as $coin) {
                    $data = [
                        'user_id' => $user_id,
                        'name' => $coin->coin_type. ' '. 'Wallet',
                        'coin_type' => $coin->coin_type,
                        'coin_id' => $coin->id,
                    ];
                    $this->createWallet($data);
                }
            }
        } catch (\Exception $e) {
            $this->logger->log('createUserWallet', $e->getMessage());
            return false;
        }
    }

    public function createWallet($data)
    {
        return Wallet::create($data);
    }
}
