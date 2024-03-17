@extends('auth.master',['menu'=>'dashboard'])
@section('title', isset($title) ? $title : __('Phone two factor'))

@section('content')
    <div class="user-content-wrapper" style="background-image: @if(!empty(settings('login_logo')))  url('{{asset(path_image().settings()['login_logo'])}}') @else url('{{asset('assets/user/images/user-content-wrapper-bg.jpg')}}') @endif">
        <div class="user-content-inner-wrap">
            <div class="row align-items-center">
                <div class="col-md-6">
                    <div class="user-form">
                        <div class="user-form-inner">
                            <div class="form-top">
                                <h2>{{__('Two Factor Authentication')}}</h2>
                                <p>{{__('Check otp sms and enter the code for')}} {{settings('app_title')}}</p>
                            </div>
                            {{Form::open(['route' => 'twoFactorVerify', 'files' => true])}}
                            <div class="form-group">
                                <label>{{__('OTP Code')}}</label>
                                <input type="text" value="{{old('code')}}" id="exampleInputEmail1" name="code"
                                    class="form-control" placeholder="{{__('code')}}">
                                @error('code')
                                <p class="invalid-feedback">{{ $message }} </p>
                                @enderror
                                <input type="hidden" name="code_type" value="{{ PHONE_AUTH }}">
                            </div>


                            <button type="submit" class="btn btn-primary nimmu-user-sibmit-button">{{__('Verify')}}</button>
                            <hr>
                            <a href="#" class="btn btn-primary nimmu-user-sibmit-button" style="background-color: #333; margin-bottom: 5px;">{{__('Resend OTP')}}</a>
                            @if(in_array(GOOGLE_AUTH,$two_factor) && Auth::user()->g2f_enabled == ENABLE)
                                <a href="{{ route('g2fChecked') }}" class="btn btn-primary nimmu-user-sibmit-button" style="background-color: #333; margin-bottom: 5px;">{{__('Verify By Google Auth')}}</a>
                            @endif
                            @if(in_array(EMAIL_AUTH,$two_factor) && Auth::user()->email_enabled == ENABLE)
                                <a href="{{ route('verifyEmail') }}" class="btn btn-primary nimmu-user-sibmit-button" style="background-color: #333;">{{__('Verify By Email')}}</a>
                            @endif
                            {{Form::close()}}
                        </div>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="user-content-text text-center">
                        <h3>{{__('Welcome To')}} {{ settings('app_title') }}</h3>
                        <a class="auth-logo" href="javascript:;">
                            <img src="{{show_image(1,'logo')}}" class="img-fluid" alt="">
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('script')
    <script>
        (function($) {
            "use strict";

            $(".toggle-password").on('click', function () {
                $(this).toggleClass("fa-eye-slash fa-eye");
            });

            $(".eye").on('click', function () {
                var $pwd = $(".look-pass-a");
                if ($pwd.attr('type') === 'password') {
                    $pwd.attr('type', 'text');
                } else {
                    $pwd.attr('type', 'password');
                }
            });
        })(jQuery)
    </script>
@endsection
