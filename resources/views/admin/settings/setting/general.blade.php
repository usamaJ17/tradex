<div class="header-bar">
    <div class="table-title">
        <h3>{{__('General Settings')}}</h3>
    </div>
</div>
<div class="profile-info-form">
    <form action="{{route('adminCommonSettings')}}" method="post" enctype="multipart/form-data">
        @csrf
        <div class="row">
            <div class="col-lg-6 col-12  mt-20">
                <div class="form-group">
                    <label for="#">{{__('Application Frontend URL')}} (Example: https://exchange.com)</label>
                    <input class="form-control" type="text" name="exchange_url"
                           placeholder="{{__('Url where user show the exchange page')}}"
                           value="{{$settings['exchange_url'] ?? ''}}">
                </div>
            </div>
            <div class="col-lg-6 col-12 mt-20">
                <div class="form-group">
                    <label>{{__('Language')}}</label>
                    <div class="cp-select-area">
                        <select name="lang" class="form-control">
                            @if(isset($languages[0]))
                                @foreach($languages as $val)
                                    <option
                                        @if(isset($settings['lang']) && $settings['lang']==$val->key) selected
                                        @endif value="{{$val->key}}">{{$val->name}}</option>
                                @endforeach
                            @endif
                        </select>
                    </div>
                </div>
            </div>
            <div class="col-lg-6 col-12  mt-20">
                <div class="form-group">
                    <label for="#">{{__('Company Name')}}</label>
                    <input class="form-control" type="text" name="company_name"
                           placeholder="{{__('Company Name')}}"
                           value="{{$settings['app_title']}}">
                </div>
            </div>
            <div class="col-lg-6 col-12  mt-20">
                <div class="form-group">
                    <label for="#">{{__(' Base Coin Type')}}</label>
                    <input class="form-control" type="text" name="base_coin_type"
                           placeholder="{{__('Coin Type eg. BTC')}}"
                           value="{{isset($settings['base_coin_type']) ? $settings['base_coin_type'] : ''}}">
                </div>
            </div>
            <div class="col-lg-6 col-12  mt-20">
                <div class="form-group">
                    <label for="#">{{__('Copyright Text')}}</label>
                    <input class="form-control" type="text" name="copyright_text"
                           placeholder="{{__('Copyright Text')}}"
                           value="{{$settings['copyright_text']}}">
                </div>
            </div>

            <div class="col-lg-6 col-12 mt-20">
                <div class="form-group">
                    <label
                        for="#">{{__('Number of confirmation for Notifier deposit')}} </label>
                    <input class="form-control number_only" type="text"
                           name="number_of_confirmation" placeholder=""
                           value="{{$settings['number_of_confirmation']}}">
                </div>
            </div>
            <div class="col-lg-6 col-12 mt-20">
                <div class="form-group">
                    <label
                        for="#">{{__('Trading price tolerance')}} </label>
                    <input class="form-control number_only" type="text"
                           name="trading_price_tolerance" placeholder=""
                           value="{{$settings['trading_price_tolerance'] ?? 10}}">
                </div>
            </div>
            <div class="col-lg-6 col-12 mt-20">
                <div class="form-group">
                    <label>{{ __("Loading Animation") }}</label>
                    <div class="cp-select-area">
                        <select name="loading_animation" class="form-control">
                            <option value="{{ DEFAULT_LOADING_ANNIMATIOM }}" @if(isset($settings['loading_animation']) && $settings['loading_animation'] == DEFAULT_LOADING_ANNIMATIOM) selected @endif>{{ __("Default") }}</option>
                            <option value="{{ LOGO_LOADING_ANNIMATIOM }}" @if(isset($settings['loading_animation']) && $settings['loading_animation'] == LOGO_LOADING_ANNIMATIOM) selected @endif>{{ __("Logo") }}</option>
                        </select>
                    </div>
                </div>
            </div>
            <div class="col-lg-6 col-12 mt-20">
                <div class="form-group">
                    <label>{{ __("Upload Max Size (MB)") }}</label>
                    <input class="form-control number_only" type="number"
                        name="upload_max_size" placeholder="" 
                        value="{{$settings['upload_max_size'] ?? 10}}">
                </div>
            </div>
        </div>
        <div class="uplode-img-list">
            <div class="row">
                <div class="col-lg-4 mt-20">
                    <div class="single-uplode">
                        <div class="uplode-catagory">
                            <span>{{__('Logo')}}</span>
                        </div>
                        <div class="form-group buy_coin_address_input ">
                            <div id="file-upload" class="section-p">
                                <input type="file" placeholder="0.00" name="logo" value=""
                                       id="file" ref="file" class="dropify"
                                       @if(isset($settings['logo']) && (!empty($settings['logo'])))  data-default-file="{{asset(path_image().$settings['logo'])}}" @endif />
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-lg-4 mt-20">
                    <div class="single-uplode">
                        <div class="uplode-catagory">
                            <span>{{__('Login Background')}}</span>
                        </div>
                        <div class="form-group buy_coin_address_input ">
                            <div id="file-upload" class="section-p">
                                <input type="file" placeholder="0.00" name="login_logo" value=""
                                       id="file" ref="file" class="dropify"
                                       @if(isset($settings['login_logo']) && (!empty($settings['login_logo'])))  data-default-file="{{asset(path_image().$settings['login_logo'])}}" @endif />
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-lg-4 mt-20">
                    <div class="single-uplode">
                        <div class="uplode-catagory">
                            <span>{{__('Favicon')}}</span>
                        </div>
                        <div class="form-group buy_coin_address_input ">
                            <div id="file-upload" class="section-p">
                                <input type="file" placeholder="0.00" name="favicon" value=""
                                       id="file" ref="file" class="dropify"
                                       @if(isset($settings['favicon']) && (!empty($settings['favicon'])))  data-default-file="{{asset(path_image().$settings['favicon'])}}" @endif />
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="row">
            @if(isset($itech))
                <input type="hidden" name="itech" value="{{$itech}}">
            @endif
            <div class="col-lg-2 col-12 mt-20">
                <button class="button-primary theme-btn">{{__('Update')}}</button>
            </div>
        </div>
    </form>
</div>
