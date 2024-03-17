<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateWalletKeyRequest extends FormRequest
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
        return [
           'id'=>'required',
           'wallet_key'=>'required'
        ];
    }

    public function messages()
    {
        return  [
            'id.required' => __('Invalid Request'),
            'wallet_key.required' => __('Enter Wallet Key!')
        ];
    }
}
