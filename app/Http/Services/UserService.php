<?php

namespace App\Http\Services;

use App\Http\Repositories\UserRepository;
use App\Model\ThirdPartyKycDetails;
use App\Model\UserVerificationCode;
use App\Model\VerificationDetails;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Session;
use PragmaRX\Google2FA\Google2FA;

class UserService
{
    private $logger;
    private $repository;
    private $smsService;
    private $serviceKYC;

    public function __construct()
    {
        $this->logger = new Logger();
        $this->repository = new UserRepository();
        $this->smsService = new SmsService();
        $this->serviceKYC = new KycService();
    }

    // user profile
    public function userProfile($userId)
    {
        $response = $this->repository->userProfile($userId);
        return $response;
    }

    // user profile update
    public function userProfileUpdate($request,$userId)
    {
        $response = $this->repository->profileUpdate($request,$userId);
        return $response;
    }
    // user change password
    public function userChangePassword($request,$userId)
    {
        $response = $this->repository->passwordChange($request,$userId);
        return $response;
    }

    // send phone verification sms
    public function sendPhoneVerificationSms($user)
    {
        if (env('APP_MODE') == 'demo') {
                return ['success' => false, 'message' => __('Currently disable only for demo')];
            }
        $response['success'] = false;
        $response['message'] = __('Invalid Request');
        DB::beginTransaction();
        try {
            if (!empty($user->phone)) {
                $key = randomNumber(6);
                $code = UserVerificationCode::create([
                    'user_id' => $user->id,
                    'code' => $key,
                    'expired_at' => date('Y-m-d', strtotime('+1 days')),
                    'status' => STATUS_PENDING,
                    'type' => CODE_TYPE_PHONE
                ]);

                $text = __('Your verification code id ') . ' ' . $code->code;
                $number = $user->phone;
                $sendSms =$this->smsService->sendSMS($number, $text);
                // $sendSms = $this->smsService->send("+".$number, $text);
                $response = [
                    'success' => true,
                    'message' => __('We sent a verification code in your phone please input this code in this box')
                ];
            } else {
                $response = [
                    'success' => false,
                    'message' => __('Before verify please add your mobile number first')
                ];
            }
        } catch (\Exception $e) {
            DB::rollBack();
            $this->logger->log('sendPhoneVerificationSms', $e->getMessage());
            $response = [
                'success' => false,
                'message' => __('Something went wrong')
            ];
        }

        DB::commit();
        return $response;
    }

    // send phone verification sms
    public function phoneVerifyProcess($request, $user)
    {
        $response['success'] = false;
        $response['message'] = __('Invalid Request');
        DB::beginTransaction();
        try {
            if(isset($request->verify_code)) {
                $verify = UserVerificationCode::where(['user_id' => $user->id])
                    ->where('code', $request->verify_code)
                    ->where(['status' => STATUS_PENDING, 'type' => CODE_TYPE_PHONE])
                    ->whereDate('expired_at', '>', Carbon::now()->format('Y-m-d'))
                    ->first();
                if ($verify) {
                    $user->phone_verified = 1;
                    $user->save();
                    UserVerificationCode::where(['id' => $verify->id])->delete();
                    $response = [
                        'success' => true,
                        'message' => __('Phone verified successful')
                    ];
                } else {
                    $response = [
                        'success' => false,
                        'message' => __('Verify code expired or not found')
                    ];
                }
            } else {
                $response = [
                    'success' => false,
                    'message' => __('Verify code can not be empty')
                ];
            }
        } catch (\Exception $e) {
            DB::rollBack();
            $this->logger->log('sendPhoneVerificationSms', $e->getMessage());
            $response = [
                'success' => false,
                'message' => __('Something went wrong')
            ];
        }

        DB::commit();
        return $response;
    }

    // upload nid process
    public function nidUploadProcess($request,$user)
    {
        if (env('APP_MODE') == 'demo') {
                return ['success' => false, 'message' => __('Currently disable only for demo')];
            }
        $response['success'] = false;
        $response['message'] = __('Invalid Request');
        DB::beginTransaction();
        try {
            $img =  $request->file('file_two');
            $img2 =  $request->file('file_three');
            $selfie =  $request->file('file_selfie');
            if ($img !== null) {
                $details = VerificationDetails::where('user_id', Auth::id())->where('field_name', 'nid_front')->first();
                if (empty($details)) {
                    $details = new VerificationDetails();
                    $details->field_name = 'nid_front';
                    $details->user_id = Auth::id();
                    $details->status = STATUS_PENDING;
                    $photo = uploadFile($img, IMG_USER_PATH, !empty($details->photo) ? $details->photo : '');
                    $details->photo = $photo;
                    $details->save();
                } else {
                    if ($details->status == STATUS_REJECTED) {
                        $details->field_name = 'nid_front';
                        $details->user_id = Auth::id();
                        $details->status = STATUS_PENDING;
                        $photo = uploadFile($img, IMG_USER_PATH, !empty($details->photo) ? $details->photo : '');
                        $details->photo = $photo;
                        $details->save();
                    }
                }
            }
            if ($img2 !== null) {
                $details = VerificationDetails::where('user_id', Auth::id())->where('field_name', 'nid_back')->first();
                if (empty($details)) {
                    $details = new VerificationDetails();
                    $details->field_name = 'nid_back';
                    $details->user_id = Auth::id();
                    $details->status = STATUS_PENDING;
                    $photo = uploadFile($img2, IMG_USER_PATH, !empty($details->photo) ? $details->photo : '');
                    $details->photo = $photo;
                    $details->save();
                } else {
                    if ($details->status == STATUS_REJECTED) {
                        $details->field_name = 'nid_back';
                        $details->user_id = Auth::id();
                        $details->status = STATUS_PENDING;
                        $photo = uploadFile($img2, IMG_USER_PATH, !empty($details->photo) ? $details->photo : '');
                        $details->photo = $photo;
                        $details->save();
                    }
                }
            }
            if ($selfie !== null) {
                $details= VerificationDetails::where('user_id',Auth::id())->where('field_name','nid_selfie')->first();
                if (empty($details)){
                    $details = new VerificationDetails();
                    $details->field_name = 'nid_selfie';
                    $details->user_id = Auth::id();
                    $details->status = STATUS_PENDING;
                    $photo = uploadFile($selfie, IMG_USER_PATH, !empty($details->photo) ? $details->photo : '');
                    $details->photo = $photo;
                    $details->save();
                } else {
                    if ($details->status == STATUS_REJECTED) {
                        $details->field_name = 'nid_selfie';
                        $details->user_id = Auth::id();
                        $details->status = STATUS_PENDING;
                        $photo = uploadFile($selfie, IMG_USER_PATH, !empty($details->photo) ? $details->photo : '');
                        $details->photo = $photo;
                        $details->save();
                    }
                }
            }
            $response = [
                'success' => true,
                'message' => __('NID photo uploaded successfully')
            ];
        } catch (\Exception $e) {
            DB::rollBack();
            $this->logger->log('nidUploadProcess', $e->getMessage());
            $response = [
                'success' => false,
                'message' => __('Something went wrong')
            ];
        }

        DB::commit();
        return $response;
    }

    // upload passport process
    public function passportUploadProcess($request,$user)
    {
        if (env('APP_MODE') == 'demo') {
                return ['success' => false, 'message' => __('Currently disable only for demo')];
            }
        $response['success'] = false;
        $response['message'] = __('Invalid Request');
        DB::beginTransaction();
        try {
            $img =  $request->file('file_two');
            $img2 =  $request->file('file_three');
            $selfie =  $request->file('file_selfie');
            if ($img !== null) {
                $details= VerificationDetails::where('user_id',Auth::id())->where('field_name','pass_front')->first();
                if (empty($details)) {
                    $details = new VerificationDetails();
                    $details->field_name = 'pass_front';
                    $details->user_id = Auth::id();
                    $details->status = STATUS_PENDING;
                    $photo = uploadFile($img, IMG_USER_PATH, !empty($details->photo) ? $details->photo : '');
                    $details->photo = $photo;
                    $details->save();
                } else {
                    if ($details->status == STATUS_REJECTED) {
                        $details->field_name = 'pass_front';
                        $details->user_id = Auth::id();
                        $details->status = STATUS_PENDING;
                        $photo = uploadFile($img, IMG_USER_PATH, !empty($details->photo) ? $details->photo : '');
                        $details->photo = $photo;
                        $details->save();
                    }
                }
            }
            if ($img2 !== null) {
                $details= VerificationDetails::where('user_id',Auth::id())->where('field_name','pass_back')->first();
                if (empty($details)){
                    $details = new VerificationDetails();
                    $details->field_name = 'pass_back';
                    $details->user_id = Auth::id();
                    $details->status = STATUS_PENDING;
                    $photo = uploadFile($img2, IMG_USER_PATH, !empty($details->photo) ? $details->photo : '');
                    $details->photo = $photo;
                    $details->save();
                } else {
                    if ($details->status == STATUS_REJECTED) {
                        $details->field_name = 'pass_back';
                        $details->user_id = Auth::id();
                        $details->status = STATUS_PENDING;
                        $photo = uploadFile($img2, IMG_USER_PATH, !empty($details->photo) ? $details->photo : '');
                        $details->photo = $photo;
                        $details->save();
                    }
                }
            }
            if ($selfie !== null) {
                $details= VerificationDetails::where('user_id',Auth::id())->where('field_name','pass_selfie')->first();
                if (empty($details)){
                    $details = new VerificationDetails();
                    $details->field_name = 'pass_selfie';
                    $details->user_id = Auth::id();
                    $details->status = STATUS_PENDING;
                    $photo = uploadFile($selfie, IMG_USER_PATH, !empty($details->photo) ? $details->photo : '');
                    $details->photo = $photo;
                    $details->save();
                } else {
                    if ($details->status == STATUS_REJECTED) {
                        $details->field_name = 'pass_selfie';
                        $details->user_id = Auth::id();
                        $details->status = STATUS_PENDING;
                        $photo = uploadFile($selfie, IMG_USER_PATH, !empty($details->photo) ? $details->photo : '');
                        $details->photo = $photo;
                        $details->save();
                    }
                }
            }
            $response = [
                'success' => true,
                'message' => __('Passport photo uploaded successfully')
            ];
        } catch (\Exception $e) {
            DB::rollBack();
            $this->logger->log('passportUploadProcess', $e->getMessage());
            $response = [
                'success' => false,
                'message' => __('Something went wrong')
            ];
        }

        DB::commit();
        return $response;
    }

    // upload driving licence process
    public function drivingUploadProcess($request,$user)
    {
        if (env('APP_MODE') == 'demo') {
                return ['success' => false, 'message' => __('Currently disable only for demo')];
            }
        $response['success'] = false;
        $response['message'] = __('Invalid Request');
        DB::beginTransaction();
        try {
            $img =  $request->file('file_two');
            $img2 =  $request->file('file_three');
            $selfie =  $request->file('file_selfie');
            if ($img !== null) {
                $details= VerificationDetails::where('user_id',Auth::id())->where('field_name','drive_front')->first();
                if (empty($details)){
                    $details = new VerificationDetails();
                    $details->field_name = 'drive_front';
                    $details->user_id = Auth::id();
                    $details->status = STATUS_PENDING;
                    $photo = uploadFile($img, IMG_USER_PATH, !empty($details->photo) ? $details->photo : '');
                    $details->photo = $photo;
                    $details->save();
                } else {
                    if ($details->status == STATUS_REJECTED) {
                        $details->field_name = 'drive_front';
                        $details->user_id = Auth::id();
                        $details->status = STATUS_PENDING;
                        $photo = uploadFile($img, IMG_USER_PATH, !empty($details->photo) ? $details->photo : '');
                        $details->photo = $photo;
                        $details->save();
                    }
                }
            }
            if ($img2 !== null) {
                $details= VerificationDetails::where('user_id',Auth::id())->where('field_name','drive_back')->first();
                if (empty($details)) {
                    $details = new VerificationDetails();
                    $details->field_name = 'drive_back';
                    $details->user_id = Auth::id();
                    $details->status = STATUS_PENDING;
                    $photo = uploadFile($img2, IMG_USER_PATH, !empty($details->photo) ? $details->photo : '');
                    $details->photo = $photo;
                    $details->save();
                } else {
                    if ($details->status == STATUS_REJECTED) {
                        $details->field_name = 'drive_back';
                        $details->user_id = Auth::id();
                        $details->status = STATUS_PENDING;
                        $photo = uploadFile($img2, IMG_USER_PATH, !empty($details->photo) ? $details->photo : '');
                        $details->photo = $photo;
                        $details->save();
                    }
                }
            }
            if ($selfie !== null) {
                $details= VerificationDetails::where('user_id',Auth::id())->where('field_name','drive_selfie')->first();
                if (empty($details)){
                    $details = new VerificationDetails();
                    $details->field_name = 'drive_selfie';
                    $details->user_id = Auth::id();
                    $details->status = STATUS_PENDING;
                    $photo = uploadFile($selfie, IMG_USER_PATH, !empty($details->photo) ? $details->photo : '');
                    $details->photo = $photo;
                    $details->save();
                } else {
                    if ($details->status == STATUS_REJECTED) {
                        $details->field_name = 'drive_selfie';
                        $details->user_id = Auth::id();
                        $details->status = STATUS_PENDING;
                        $photo = uploadFile($selfie, IMG_USER_PATH, !empty($details->photo) ? $details->photo : '');
                        $details->photo = $photo;
                        $details->save();
                    }
                }
            }
            $response = [
                'success' => true,
                'message' => __('Driving licence uploaded successfully')
            ];
        } catch (\Exception $e) {
            DB::rollBack();
            $this->logger->log('drivingUploadProcess', $e->getMessage());
            $response = [
                'success' => false,
                'message' => __('Something went wrong')
            ];
        }

        DB::commit();
        return $response;
    }
    //voter card upload process
    public function voterCardUploadProcess($request,$user)
    {
        if (env('APP_MODE') == 'demo') {
            return ['success' => false, 'message' => __('Currently disable only for demo')];
        }
        $response['success'] = false;
        $response['message'] = __('Invalid Request');
        DB::beginTransaction();
        try {
            $img =  $request->file('file_two');
            $img2 =  $request->file('file_three');
            $selfie =  $request->file('file_selfie');
            if ($img !== null) {
                $details= VerificationDetails::where('user_id',Auth::id())->where('field_name','voter_front')->first();
                if (empty($details)){
                    $details = new VerificationDetails();
                    $details->field_name = 'voter_front';
                    $details->user_id = Auth::id();
                    $details->status = STATUS_PENDING;
                    $photo = uploadFile($img, IMG_USER_PATH, !empty($details->photo) ? $details->photo : '');
                    $details->photo = $photo;
                    $details->save();
                } else {
                    if ($details->status == STATUS_REJECTED) {
                        $details->field_name = 'voter_front';
                        $details->user_id = Auth::id();
                        $details->status = STATUS_PENDING;
                        $photo = uploadFile($img, IMG_USER_PATH, !empty($details->photo) ? $details->photo : '');
                        $details->photo = $photo;
                        $details->save();
                    }
                }
            }
            if ($img2 !== null) {
                $details= VerificationDetails::where('user_id',Auth::id())->where('field_name','voter_back')->first();
                if (empty($details)) {
                    $details = new VerificationDetails();
                    $details->field_name = 'voter_back';
                    $details->user_id = Auth::id();
                    $details->status = STATUS_PENDING;
                    $photo = uploadFile($img2, IMG_USER_PATH, !empty($details->photo) ? $details->photo : '');
                    $details->photo = $photo;
                    $details->save();
                } else {
                    if ($details->status == STATUS_REJECTED) {
                        $details->field_name = 'voter_back';
                        $details->user_id = Auth::id();
                        $details->status = STATUS_PENDING;
                        $photo = uploadFile($img2, IMG_USER_PATH, !empty($details->photo) ? $details->photo : '');
                        $details->photo = $photo;
                        $details->save();
                    }
                }
            }
            if ($selfie !== null) {
                $details= VerificationDetails::where('user_id',Auth::id())->where('field_name','voter_selfie')->first();
                if (empty($details)){
                    $details = new VerificationDetails();
                    $details->field_name = 'voter_selfie';
                    $details->user_id = Auth::id();
                    $details->status = STATUS_PENDING;
                    $photo = uploadFile($selfie, IMG_USER_PATH, !empty($details->photo) ? $details->photo : '');
                    $details->photo = $photo;
                    $details->save();
                } else {
                    if ($details->status == STATUS_REJECTED) {
                        $details->field_name = 'voter_selfie';
                        $details->user_id = Auth::id();
                        $details->status = STATUS_PENDING;
                        $photo = uploadFile($selfie, IMG_USER_PATH, !empty($details->photo) ? $details->photo : '');
                        $details->photo = $photo;
                        $details->save();
                    }
                }
            }
            $response = [
                'success' => true,
                'message' => __('Voter card uploaded successfully')
            ];
        } catch (\Exception $e) {
            DB::rollBack();
            $this->logger->log('voterCardUploadProcess', $e->getMessage());
            $response = [
                'success' => false,
                'message' => __('Something went wrong')
            ];
        }

        DB::commit();
        return $response;
    }

    // kyc verification details
    public function kycStatusDetails($user)
    {
        $response['success'] = false;
        $response['data'] = [];
        $response['message'] = __('Invalid Request');
        try {
            
            $response = $this->manualKYCUserDetails($user);

        } catch (\Exception $e) {
            $this->logger->log('kycStatusDetails', $e->getMessage());
            $response = [
                'success' => false,
                'data' => [],
                'message' => __('Something went wrong')
            ];
        }

        return $response;
    }

    // user setting
    public function userSettingDetails($user)
    {
        $response['success'] = false;
        $response['data'] = '';
        $response['message'] = __('Invalid Request');
        try {
            $fiat_currency_list = [];
            $fiat_currency = fiatCurrency();
            if($fiat_currency) {
                foreach ($fiat_currency as $key => $value) {
                    $fiat_currency_list[] = [
                        'code' => $key,
                        'name' => $value,
                    ];
                }
            }
            $data['fiat_currency'] = $fiat_currency_list;
            if(empty($user->google2fa_secret)) {
                $google2fa = new Google2FA();
                $google2fa->setAllowInsecureCallToGoogleApis(true);
                $data['google2fa_secret'] = $google2fa->generateSecretKey();

                $google2fa_url = $google2fa->getQRCodeGoogleUrl(
                    !empty(allsetting('app_title')) ? allsetting('app_title') : 'TradexPro Exchange',
                    isset($user->email) && !empty($user->email) ? $user->email : 'tradexpro@email.com',
                    $data['google2fa_secret']
                );
                $data['qrcode'] = $google2fa_url;
                $user->google2fa = 0;
            } else {
                $user->google2fa = 1;
            }
            $data['user'] = $user;
            $response = [
                'success' => true,
                'data' => $data,
                'message' => __('Success')
            ];
        } catch (\Exception $e) {
            $this->logger->log('userSettingDetails', $e->getMessage());
            $response = [
                'success' => false,
                'data' => '',
                'message' => __('Something went wrong')
            ];
        }

        return $response;
    }

    // google 2fa setup process
    public function setupGoogle2fa($request)
    {
        if (env('APP_MODE') == 'demo') {
            return ['success' => false, 'message' => __('Currently disable only for demo')];
        }
        $response['success'] = false;
        $response['data'] = '';
        $response['message'] = __('Invalid Request');
        try {
            if(empty($request->code)) {
                $response = [
                    'success' => false,
                    'data' => '',
                    'message' => __('Google authentication code can not be empty')
                ];
                return $response;
            }

            $user = Auth::user();
            if($request->setup == 'remove') {
                if(empty($user->google2fa_secret)) {
                    $response = [
                        'success' => false,
                        'data' => '',
                        'message' => __('Your gAuth is not setup yet, so before remove you must setup gauth first')
                    ];
                } else {
                    $valid = $this->checkGoogle2fa($user->google2fa_secret,$request->code);
                    if ($valid['success'] == false) {
                        $response = [
                            'success' => false,
                            'data' => '',
                            'message' => $valid['message']
                        ];
                    } else {
                        $user->google2fa_secret = null;
                        $user->g2f_enabled = '0';
                        $user->save();
                        $response = [
                            'success' => false,
                            'data' => $user,
                            'message' => __('Google authentication code removed successfully')
                        ];
                    }
                }
            } else {
                if(!empty($user->google2fa_secret)) {
                    $response = [
                        'success' => false,
                        'data' => '',
                        'message' => __('Your gAuth is already setup')
                    ];
                    return $response;
                } else {
                    $valid = $this->checkGoogle2fa($request->google2fa_secret,$request->code);
                    if ($valid['success'] == false) {
                        $response = [
                            'success' => false,
                            'data' => '',
                            'message' => $valid['message']
                        ];
                    } else {
                        $user->google2fa_secret = $request->google2fa_secret;
                        $user->save();
                        $response = [
                            'success' => true,
                            'data' => $user,
                            'message' => __('Google authentication code added successfully')
                        ];
                    }
                }
            }
        } catch (\Exception $e) {
            $this->logger->log('setupGoogle2fa', $e->getMessage());
            $response = [
                'success' => false,
                'data' => '',
                'message' => __('Something went wrong')
            ];
        }

        return $response;
    }

    // check google 2fa
    public function checkGoogle2fa($google2fa_secret,$code)
    {
        $google2fa = new Google2FA();
        $valid = $google2fa->verifyKey($google2fa_secret, $code);
        if ($valid) {
            $data['success'] = true;
            $data['message'] = __('Success');
        } else {
            $data['success'] = false;
            $data['message'] = __('Google authentication code is invalid');
        }
        return $data;
    }

    // language list
    public function languageList()
    {
        $response['success'] = true;
        $response['message'] = __('Success');
        $list = [];
        foreach (language() as $val) {
            $list[] = [
                'key' => $val,
                'lang' => langName($val)
            ];
        }
        $response['data'] = $list;

        return $response;
    }

    // language save
    public function languageSetup($request)
    {
        try {
            $user =  Auth::user();
            if ($request->language) {
                $user->language = $request->language;
                $user->save();
                $response = [
                    'success' => true,
                    'data' => $user,
                    'message' => __('Language changed successfully')
                ];
            } else {
                $response = [
                    'success' => false,
                    'data' => '',
                    'message' => __('Please select a language')
                ];
            }
        } catch (\Exception $e) {
            $this->logger->log('languageSetup', $e->getMessage());
            $response = [
                'success' => false,
                'data' => '',
                'message' => __('Something went wrong')
            ];
        }

        return $response;
    }

    // setup Google2fa Login
    public function setupGoogle2faLogin($user)
    {
        try {
            if (!empty($user->google2fa_secret)) {
                if ($user->g2f_enabled == 0) {
                    $user->g2f_enabled = '1';
                    Session::put('g2f_checked', true);
                    $message = __('Google two factor authentication is enabled');
                } else {
                    $user->g2f_enabled = '0';
                    Session::forget('g2f_checked');
                    $message = __('Google two factor authentication is enabled');
                }
                $user->update();
                $response = [
                    'success' => true,
                    'data' => $user,
                    'message' => $message
                ];
            } else {
                $response = [
                    'success' => false,
                    'data' => '',
                    'message' => __('For using google two factor authentication,please setup your authentication')
                ];
            }
        } catch (\Exception $e) {
            $this->logger->log('setupGoogle2faLogin', $e->getMessage());
            $response = [
                'success' => false,
                'data' => '',
                'message' => __('Something went wrong')
            ];
        }

        return $response;
    }

    // update fiat currency
    public function updateFiatCurrency($request)
    {
        try {
            $user = Auth::user();
            $user->update(['currency' => $request->code]);
            $response = [
                'success' => true,
                'data' => '',
                'message' => __('Currency updated successfully')
            ];
        } catch (\Exception $e) {
            storeException('updateFiatCurrency', $e->getMessage());
            $response = [
                'success' => false,
                'data' => '',
                'message' => __('Something went wrong')
            ];
        }

        return $response;
    }
    public function manualKYCUserDetails($user)
    {
        $kycActiveList = $this->serviceKYC->getKycActiveList();

        if(count($kycActiveList['data'])> 0)
        {
            foreach($kycActiveList['data'] as $item)
            {
                if($item->type == KYC_NID_VERIFICATION)
                {
                    $nid_front = VerificationDetails::where('user_id',$user->id)->where('field_name','nid_front')->first();
                    $nid_back = VerificationDetails::where('user_id',$user->id)->where('field_name','nid_back')->first();
                    $nid_selfie = VerificationDetails::where('user_id',$user->id)->where('field_name','nid_selfie')->first();

                    $data['nid'] = [];

                    if(isset($nid_front) && isset($nid_back) && isset($nid_selfie)) {
                        $data['nid'] = [
                            'front_image' => !empty($nid_front->photo) ? imageSrcUser($nid_front->photo,IMG_USER_VIEW_PATH) : '',
                            'back_image' => !empty($nid_back->photo) ? imageSrcUser($nid_back->photo,IMG_USER_VIEW_PATH) : '',
                            'selfie' => !empty($nid_selfie->photo) ? imageSrcUser($nid_selfie->photo,IMG_USER_VIEW_PATH) : '',
                            'status' => kycStatus($nid_front->status)
                        ];
                    } else {
                        $data['nid'] = [
                            'front_image' => '',
                            'back_image' => '',
                            'selfie' => '',
                            'status' => __('Not Submitted')
                        ];
                    }
                }
                if($item->type == KYC_PASSPORT_VERIFICATION)
                {
                    $pass_front = VerificationDetails::where('user_id',$user->id)->where('field_name','pass_front')->first();
                    $pass_back = VerificationDetails::where('user_id',$user->id)->where('field_name','pass_back')->first();
                    $pass_selfie = VerificationDetails::where('user_id',$user->id)->where('field_name','pass_selfie')->first();

                    $data['passport'] = [];

                    if(isset($pass_front) && isset($pass_back) && isset($pass_selfie)) {
                        $data['passport'] = [
                            'front_image' => !empty($pass_front->photo) ? imageSrcUser($pass_front->photo,IMG_USER_VIEW_PATH) : '',
                            'back_image' => !empty($pass_back->photo) ? imageSrcUser($pass_back->photo,IMG_USER_VIEW_PATH) : '',
                            'selfie' => !empty($pass_selfie->photo) ? imageSrcUser($pass_selfie->photo,IMG_USER_VIEW_PATH) : '',
                            'status' => kycStatus($pass_front->status)
                        ];
                    } else {
                        $data['passport'] = [
                            'front_image' => '',
                            'back_image' => '',
                            'selfie' => '',
                            'status' => __('Not Submitted')
                        ];
                    }
                }
                if($item->type == KYC_DRIVING_VERIFICATION)
                {
                    $drive_front = VerificationDetails::where('user_id',$user->id)->where('field_name','drive_front')->first();
                    $drive_back = VerificationDetails::where('user_id',$user->id)->where('field_name','drive_back')->first();
                    $drive_selfie = VerificationDetails::where('user_id',$user->id)->where('field_name','drive_selfie')->first();

                    $data['driving'] = [];

                    if(isset($drive_front) && isset($drive_back) && isset($drive_selfie)) {
                        $data['driving'] = [
                            'front_image' => !empty($drive_front->photo) ? imageSrcUser($drive_front->photo,IMG_USER_VIEW_PATH) : '',
                            'back_image' => !empty($drive_back->photo) ? imageSrcUser($drive_back->photo,IMG_USER_VIEW_PATH) : '',
                            'selfie' => !empty($drive_selfie->photo) ? imageSrcUser($drive_selfie->photo,IMG_USER_VIEW_PATH) : '',
                            'status' => kycStatus($drive_front->status)
                        ];
                    } else {
                        $data['driving'] = [
                            'front_image' => '',
                            'back_image' => '',
                            'selfie' => '',
                            'status' => __('Not Submitted')
                        ];
                    }
                }
                if($item->type == KYC_VOTERS_CARD_VERIFICATION)
                {
                    $voter_front = VerificationDetails::where('user_id',$user->id)->where('field_name','voter_front')->first();
                    $voter_back = VerificationDetails::where('user_id',$user->id)->where('field_name','voter_back')->first();
                    $voter_selfie = VerificationDetails::where('user_id',$user->id)->where('field_name','voter_selfie')->first();

                    $data['voter'] = [];

                    if(isset($voter_front) && isset($voter_back) && isset($voter_selfie)) {
                        $data['voter'] = [
                            'front_image' => !empty($voter_front->photo) ? imageSrcUser($voter_front->photo,IMG_USER_VIEW_PATH) : '',
                            'back_image' => !empty($voter_back->photo) ? imageSrcUser($voter_back->photo,IMG_USER_VIEW_PATH) : '',
                            'selfie' => !empty($voter_selfie->photo) ? imageSrcUser($voter_selfie->photo,IMG_USER_VIEW_PATH) : '',
                            'status' => kycStatus($voter_front->status)
                        ];
                    } else {
                        $data['voter'] = [
                            'front_image' => '',
                            'back_image' => '',
                            'selfie' => '',
                            'status' => __('Not Submitted')
                        ];
                    }
                }
            }

            $response = [
                'success' => true,
                'data' => $data ?? [],
                'message' => __('Success')
            ];
        }else{
            $response = [
                'success' => false,
                'data' => [],
                'message' => __('Failed')
            ];
        }

        return $response;
    }
    public function userKycSettingsDetails()
    {
        $kyc_type_is = allsetting('kyc_type_is')??0;
        $user = auth()->user();

        if($kyc_type_is == KYC_TYPE_DISABLE)
        {
            $response = ['success'=>false, 'messaage'=> __('KYC is Disabled')];
        }elseif($kyc_type_is == KYC_TYPE_MANUAL){
            $data['enabled_kyc_type'] = KYC_TYPE_MANUAL;
            
            $manual_kyc_response = $this->manualKYCUserDetails($user);
            if($manual_kyc_response['success'])
            {
                $manual_kyc_details = $manual_kyc_response['data'];
                
                $data['enabled_kyc_user_details'] = $manual_kyc_details;
            }
            $response = ['success'=>true, 'message'=>__('KYC details for manual KYC enabled'), 'data'=>$data];
        }elseif($kyc_type_is == KYC_TYPE_PERSONA)
        {
            $data['enabled_kyc_type'] = KYC_TYPE_PERSONA;
            $settings = allsetting(['PERSONA_KYC_API_KEY', 'PERSONA_KYC_TEMPLATED_ID','PERSONA_KYC_MODE','PERSONA_KYC_VERSION']);

            $data['perona_credentials_details'] = $settings;
            $user_third_party_kyc_details = ThirdPartyKycDetails::where('user_id', $user->id)->where('kyc_type',KYC_TYPE_PERSONA)->first();
            $data['enabled_kyc_user_details']['persona'] = [
                'is_verified' => isset($user_third_party_kyc_details)? $user_third_party_kyc_details->is_verified:0
            ];
            $response = ['success'=>true, 'message'=>__('KYC details for Persona KYC enabled'), 'data'=>$data];
        }
        return $response;
    }

    public function profileDeleteRequest($request)
    {
        $response = $this->repository->profileDeleteRequest($request);

        return $response;
    }
}
