<?php

namespace App\Http\Requests\Api;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\Validation\ValidationException;

class LoginRequest extends FormRequest
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
        $rules =[
            'email'=>'required|email',
            'password'=>'required'
        ];
        $agent = checkUserAgent($this);
        if($agent == 'android' || $agent == 'ios') {
        } else {
            if (isset(allsetting()['select_captcha_type']) && (allsetting()['select_captcha_type'] == CAPTCHA_TYPE_RECAPTCHA)) {
                $rules['recapcha'] = 'required|captcha';
            }

            if (isset(allsetting()['select_captcha_type']) && (allsetting()['select_captcha_type'] == CAPTCHA_TYPE_GEETESTCAPTCHA)) {
                $rules['lot_number'] = 'required';
                $rules['captcha_output'] = 'required';
                $rules['pass_token'] = 'required';
                $rules['gen_time'] = 'required';
            }
        }
        return $rules;
    }

    public function messages()
    {
        $messages=[
            'email.required'=>__('Email can\'t be empty.')
            ,'password.required'=>__('Password can\'t be empty.')
            ,'email.email'=>__('Invalid email format.')
            ,'email.exists'=>__('Email doesn\'t exists.')
            ,'recapcha.required'=>__('Captcha is required')
            ,'recapcha.captcha'=>__('Captcha is invalid')
            ,'g-recaptcha-response.required'=>__('Captcha is required')
            ,'g-recaptcha-response.captcha'=>__('Invalid captcha')
        ];
        return $messages;
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
            $json = [
                'success'=>false,
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
