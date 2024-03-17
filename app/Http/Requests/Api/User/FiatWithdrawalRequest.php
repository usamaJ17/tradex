<?php

namespace App\Http\Requests\Api\User;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\Validation\ValidationException;

class FiatWithdrawalRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        $rules = [
            'wallet_id' => 'required',
            'currency' => 'required|exists:currency_lists,code',
            'amount' => 'required|numeric|gt:0',
            'payment_method_id' => 'required|exists:currency_deposit_payment_methods,id',
            'payment_method_type' => 'required'
        ];

        if($this->payment_method_type == BANK_DEPOSIT)
        {
            $rules['bank_id'] = 'required';
        }else
        {
            $rules['payment_info'] = 'required';
        }
        
        return $rules;
    }
    public function messages()
    {
        return[
            'wallet_id.required' => __('Wallet is required'),
            'currency.required' => __('Currency is required'),
            'currency.exists' => __('Currency is invalid'),
            'amount.required' => __('Amount is required'),
            'amount.numeric' => __('Amount must be number'),
            'amount.gt' => __('Amount must be greater than 0'),
            'payment_method_id.required' => __('Payment method ID is required!'),
            'payment_method_id.exists' => __('Payment method ID is not exists!'),
            'payment_method_type.required' => __('Payment method Type is required!'),
            'bank_id.required' => __('Bank Id is required!'),
            'payment_info.required' => __('Payment Info is required!'),
        ];
    }

    protected function failedValidation(Validator $validator)
    {
        if ($this->header('accept') == "application/json") {
            $errors = [];
            if ($validator->fails()) {
                $e = $validator->errors()->all();
                foreach ($e as $error) {
                    $errors[] = $error;
                }
            }
            $json = ['success'=>false,
                'data'=>[],
                'message' => $errors[0],
            ];
            $response = new JsonResponse($json, 200);

            throw (new ValidationException($validator, $response))->errorBag($this->errorBag)->redirectTo($this->getRedirectUrl());
        } else {
            throw (new ValidationException($validator))
                ->errorBag($this->errorBag)
                ->redirectTo($this->getRedirectUrl());
        }
    }
}
