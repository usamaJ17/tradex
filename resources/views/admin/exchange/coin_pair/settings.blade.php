@extends('admin.master',['menu'=>'coin', 'sub_menu'=>'coin_pair'])
@section('title', isset($title) ? $title : '')
@section('style')
@endsection
@section('content')
    <!-- breadcrumb -->
    <div class="custom-breadcrumb">
        <div class="row">
            <div class="col-md-9">
                <ul>
                    <li class="active-item">{{ $title }}</li>
                </ul>
            </div>
        </div>
    </div>
    <!-- /breadcrumb -->
    <div class="user-management">
        <div class="row">
            <div class="col-12">
                <div class="profile-info-form">
                    <div>
                        <form action="{{route('coinPairFutureSettingUpdate')}}" method="POST">
                            @csrf
                            <input type="hidden" name="id" value="{{encrypt($coin_pair_details->id)}}">
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <div class="controls">
                                            <div class="form-label">{{__('Trade Coin')}}</div>
                                            <input type="text" class="form-control" 
                                                value="{{isset($coin_pair_details->child_coin) ? check_default_coin_type($coin_pair_details->child_coin->coin_type) : ''}}" 
                                                readonly>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <div class="controls">
                                            <div class="form-label">{{__('Base Coin')}}</div>
                                            <input type="text" class="form-control" 
                                                value="{{isset($coin_pair_details->parent_coin) ? check_default_coin_type($coin_pair_details->parent_coin->coin_type) : ''}}" 
                                                readonly>
                                        </div>
                                    </div>
                                </div>

                                <div class="col-md-6">
                                    <div class="form-group">
                                        <div class="controls">
                                            <div class="form-label">{{__('Minimum Amount For Future Trade')}}</div>
                                            <input type="text" class="form-control" name="minimum_amount_future" 
                                                value="{{$coin_pair_details->minimum_amount_future}}">
                                        </div>
                                    </div>
                                </div>

                                <div class="col-md-6">
                                    <div class="form-group">
                                        <div class="controls">
                                            <div class="form-label">{{__('Maintenance Margin Rate')}} ({{__('Percentage')}} %)</div>
                                            <input type="text" class="form-control" name="maintenance_margin_rate" 
                                                value="{{$coin_pair_details->maintenance_margin_rate}}">
                                        </div>
                                    </div>
                                </div>

                                <div class="col-md-6">
                                    <div class="form-group">
                                        <div class="controls">
                                            <div class="form-label">{{__('Funding Fee')}} ({{__('Percentage')}} %)</div>
                                            <input type="text" class="form-control" name="leverage_fee" 
                                                value="{{$coin_pair_details->leverage_fee}}">
                                        </div>
                                    </div>
                                </div>

                                <div class="col-md-6">
                                    <div class="form-group">
                                        <div class="controls">
                                            <div class="form-label">{{__('Maximum Leverage')}}</div>
                                            <input type="text" class="form-control" name="max_leverage" 
                                                value="{{$coin_pair_details->max_leverage}}">
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-2">
                                    <button type="submit" class="btn theme-btn">{{ __('Submit')}}</button>
                                </div>
                            </div>
                        </form>
                        
                       
                        
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('script')
@endsection
