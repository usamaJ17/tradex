<div class="header-bar">
    <div class="table-title">
        <h3>{{__('Wallet Overview')}}</h3>
    </div>
</div>
@php
    $string_coins = $settings["wallet_overview_selected_coins"] ?? "[]";
    $coin_array = json_decode($string_coins);
    if(!(json_last_error() === JSON_ERROR_NONE)) $coin_array = [];
@endphp
<div class="profile-info-form">
    <form action="{{route('adminSaveWalletOverviewSettings')}}" method="post"
          enctype="multipart/form-data">
        @csrf
        <div class="row">
            <div class="col-lg-6 col-12  mt-20">
                <div class="form-group">
                    <label for="#">{{__('Wallet Overview Coins')}}</label>
                   
                    <div class="cp-select-area">
                        <select name="wallet_overview_selected_coins[]" class="selectpicker show-tick hide-menu-arrow" data-style="bg-dark" data-width="100%" multiple>
                            @foreach ($coins as $coin)
                                <option value="{{ $coin->coin_type }}" @if(in_array($coin->coin_type, $coin_array)) selected @endif>{{ $coin->coin_type }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
            </div>
        </div>
        <div class="row">
            <div class="col-lg-4">
                <div class="single-uplode">
                    <div class="uplode-catagory">
                        <span>{{__('Wallet Overview Banner')}}</span>
                    </div>
                    <div class="form-group buy_coin_address_input ">
                        <div id="file-upload" class="section-p">
                            <input type="file" placeholder="0.00" name="wallet_overview_banner"
                                   id="file" ref="file" class="dropify"
                                   @if(isset($settings['wallet_overview_banner']) && (!empty($settings['wallet_overview_banner'])))  data-default-file="{{asset(IMG_PATH.$settings['wallet_overview_banner'])}}" @endif />
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="row">
            @if(isset($settings['wallet_overview_banner']) && (!empty($settings['wallet_overview_banner'])))
                <div class="col-lg-2 col-12 mt-20">
                    <button type="submit" name="remove" value="remove" class="btn btn-danger">{{__('Remove Wallet Overview Banner')}}</button>
                </div>
            @endif
            <div class="col-lg-2 col-12 mt-20">
                <button type="submit" class="button-primary theme-btn">{{__('Update')}}</button>
            </div>
        </div>
    </form>
</div>
