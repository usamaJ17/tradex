<?php
/**
 * Created by Masum.
 * User: itech
 * Date: 11/15/18
 * Time: 4:27 PM
 */


namespace App\Http\Validators;

use App\Http\Services\CoinPairService;
use App\Http\Services\CoinService;
use App\Model\CoinPair;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;

class BuyOrderValidator extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */

    public $coin_type;

    public function authorize()
    {
        return Auth::user()->status == 1;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        $coinService = new CoinService();
        $coinSetting = $coinService->getCoin(['id' => $this->trade_coin_id]);
        $minimum_buy_amount = 0;
        if (!empty($coinSetting)) {
            $this->coin_type = $coinSetting[0]['coin_type'];
            $minimum_buy_amount = $coinSetting[0]['minimum_buy_amount'];
        }
        return [
            'price' => 'required_if:is_market,0|numeric|min:0.00000001',
            'amount' => "required|numeric|between:$minimum_buy_amount,99999999999.99999999",
            'trade_coin_id' => 'required|in:' . arrValueOnly(array_column(coin_type_restrict_trade(),'id')),
            'base_coin_id' => 'required|in:' . arrValueOnly(bscointype()),
        ];
    }

    public function messages()
    {
        $message = [
            'price.required_if' => __('Price field can not be Empty'),
            'price.numeric' => __('Invalid value for buy order price!'),
            'price.between' => __('Invalid value for buy order price!'),
            'is_market.integer' => __('Invalid value for order type.'),
            'is_market.in' => __('Invalid value for order type.'),
            'amount.required' => __('Amount field can not be Empty'),
            'amount.numeric' => __('Amount Field Must be Numeric Value'),
            'amount.between' => __('Minimum Buy amount of :ctype should be :min!', ['ctype' => $this->coin_type]),
            'trade_coin_id.required' => __('Coin type field is required.'),
            'trade_coin_id.in' => __('Invalid value for trade coin.'),
            'base_coin_id.required' => __('Base coin field is required.'),
            'base_coin_id.in' => __('Invalid value for base coin.'),
            'category.required' => __('Category is required.')
        ];
        if ($this->amount > 99999999999) {
            $message['amount.between'] = __('Maximum Buy amount of :ctype should be 99999999999!', ['ctype' => $this->coin_type]);
        }
        return $message;
    }

    protected function failedValidation(Validator $validator)
    {
//        if ($this->header('accept') == "application/json") {
        $errors = [];
        if ($validator->fails()) {
            $e = $validator->errors()->all();
            foreach ($e as $error) {
                $errors[] = $error;
            }
        }
        $json = [
            'status'=>false,
            'message' => $errors[0],
        ];

            $response = new JsonResponse($json, 200);

            throw (new ValidationException($validator, $response))->errorBag($this->errorBag)->redirectTo($this->getRedirectUrl());
//        } else {
//            throw (new ValidationException($validator))
//                ->errorBag($this->errorBag)
//                ->redirectTo($this->getRedirectUrl());
//        }
    }
}
