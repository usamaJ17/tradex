@extends('admin.master',['menu'=>'coin', 'sub_menu' => 'coin_list'])
@section('title', isset($title) ? $title : '')
@section('style')
@endsection
@section('content')
    <!-- breadcrumb -->
    <div class="custom-breadcrumb">
        <div class="row">
            <div class="col-md-9">
                <ul>
                    <li>{{__('Coin')}}</li>
                    <li class="active-item">{{ $title }}</li>
                </ul>
            </div>
        </div>
    </div>
    <!-- /breadcrumb -->

    <!-- User Management -->
    <div class="user-management">
        <div class="row">
            <div class="col-12">
                <div class="profile-info-form">
                    <div>
                        {{Form::open(['route'=>'adminCoinSaveProcess', 'files' => true])}}
                        <input type="hidden" class="form-control" name="currency_type" @if(isset($item))value="{{$item->currency_type}}" @else value="{{old('currency_type')}}" @endif>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <div class="controls">
                                        <div class="form-label">{{__('Coin Type')}}</div>
                                        <input type="text" class="form-control" name="coin_type" @if(isset($item))value="{{$item->coin_type}}" @else value="{{old('coin_type')}}" @endif>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <div class="controls">
                                        <div class="form-label">{{__('Coin Full Name')}}</div>
                                        <input type="text" class="form-control" name="name" @if(isset($item))value="{{$item->name}}" @else value="{{old('name')}}" @endif>
                                        <pre class="text-danger">{{$errors->first('name')}}</pre>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            @if($item->currency_type == CURRENCY_TYPE_CRYPTO)
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <div class="controls">
                                            <div class="form-label">{{__('Coin API')}}</div>
                                            <select class="form-control" name="network" id="">
                                                @foreach(api_settings() as $key => $val)
                                                    <option @if(isset($item) && $item->network == $key) selected @endif value="{{$key}}">{{$val}}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <div class="controls">
                                            <div class="form-label">{{__('Coin Price (in USD)')}}</div>
                                            <div class="input-group mb-3 w-85 ">
                                                <input type="text" class="form-control" name="coin_price" value="{{ $item->coin_price }}">
                                                <div class="input-group-append">
                                                    <span class="input-group-text px-4"><span class="currency text-warning">USD</span></span>
                                                </div>
                                            </div>
                                            <small>{{__('Coin price in USD. it will update by currency api regularly')}}</small>
                                        </div>
                                    </div>
                                </div>
                            @endif
                            <div class="col-md-4">
                                <div class="form-group">
                                    <div class="controls">
                                        <div class="form-label">{{__('Minimum Withdrawal')}}</div>
                                        <input type="text" class="form-control" name="minimum_withdrawal"
                                               @if(isset($item))value="{{$item->minimum_withdrawal}}" @else value="0.00000001" @endif >
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <div class="controls">
                                        <div class="form-label">{{__('Maximum Withdrawal')}}</div>
                                        <input type="text" class="form-control" name="maximum_withdrawal"
                                               @if(isset($item))value="{{$item->maximum_withdrawal}}" @else value="99999999" @endif>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <div class="controls">
                                        <div class="form-label">{{__('Minimum Buy Amount')}}</div>
                                        <input type="text" class="form-control" name="minimum_buy_amount"
                                               @if(isset($item))value="{{$item->minimum_buy_amount}}" @else value="0.00000010" @endif >
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <div class="controls">
                                        <div class="form-label">{{__('Minimum Sell Amount')}}</div>
                                        <input type="text" class="form-control" name="minimum_sell_amount"
                                               @if(isset($item))value="{{$item->minimum_sell_amount}}" @else value="0.00000010" @endif>
                                    </div>
                                </div>
                            </div>

                        </div>
                        <div class="row mt-3">
                            <div class="col-md-12">
                                <div class="form-group">
                                    <div class="controls">
                                        <div class="form-label"><b>{{__('Withdrawal Setting')}}</b></div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group">
                                    <div class="controls">
                                        <div class="form-label">{{__('Withdrawal Fees Type')}}</div>
                                        <select name="withdrawal_fees_type" id="" class="form-control">
                                            @foreach(discount_type() as $key => $val)
                                                <option @if(isset($item) && ($item->withdrawal_fees_type == $key)) selected @endif value="{{ $key }}">{{ $val }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group">
                                    <div class="controls">
                                        <div class="form-label">{{__('Withdrawal Fees')}}</div>
                                        <input type="text" class="form-control" name="withdrawal_fees"
                                               @if(isset($item))value="{{$item->withdrawal_fees}}" @else value="0.00000010" @endif>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group">
                                    <div class="controls">
                                        <div class="form-label">{{__('Withdrawal limit for admin approval')}}</div>
                                        <input type="text" class="form-control" name="max_send_limit"
                                               @if(isset($item))value="{{$item->max_send_limit}}" @else value="0" @endif>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label>{{__('Enable admin approval ')}}</label>
                                    <div class="cp-select-area">
                                        <select name="admin_approval" class="form-control">
                                            <option @if($item->admin_approval == STATUS_ACTIVE) selected @endif value="{{STATUS_ACTIVE}}">{{__("Yes")}}</option>
                                            <option @if($item->admin_approval == STATUS_REJECTED) selected @endif value="{{STATUS_REJECTED}}">{{__("No")}}</option>
                                        </select>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="row mt-3">
                            <div class="col-md-2">
                                <div class="form-group">
                                    <div class="controls">
                                        <div class="form-label">{{__('Deposit Status')}}</div>
                                        <label class="switch">
                                            <input type="checkbox" name="is_deposit" @if(isset($item) && $item->is_deposit==1)checked  @endif>
                                            <span class="slider"></span>
                                        </label>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-2">
                                <div class="form-group">
                                    <div class="controls">
                                        <div class="form-label">{{__('Withdrawal Status')}}</div>
                                        <label class="switch">
                                            <input type="checkbox" name="is_withdrawal" @if(isset($item) && $item->is_withdrawal==1)checked  @endif>
                                            <span class="slider"></span>
                                        </label>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-2">
                                <div class="form-group">
                                    <div class="controls">
                                        <div class="form-label">{{__('Trading Status')}}</div>
                                        <label class="switch">
                                            <input type="checkbox" name="trade_status" @if(isset($item) && $item->trade_status==1)checked  @endif>
                                            <span class="slider"></span>
                                        </label>

                                    </div>
                                </div>
                            </div>
                            <div class="col-md-2">
                                <div class="form-group">
                                    <div class="controls">
                                        <div class="form-label">{{__('Active Status')}}</div>
                                        <label class="switch">
                                            <input type="checkbox" name="status" @if(isset($item) && $item->status==1)checked  @endif>
                                            <span class="slider"></span>
                                        </label>
                                    </div>
                                </div>
                            </div>
                            @if(isset($module) && isset($module['DemoTrade']))
                            <div class="col-md-2">
                                <div class="form-group">
                                    <div class="controls">
                                        <div class="form-label">{{__('Demo Trade Status')}}</div>
                                        <label class="switch">
                                            <input type="checkbox" name="is_demo_trade" @if(isset($item) && $item->is_demo_trade==1)checked  @endif>
                                            <span class="slider"></span>
                                        </label>
                                    </div>
                                </div>
                            </div>
                            @endif
                        </div>
                        <div class="row">
                            <div class="col-md-3">
                                <div class="form-group">
                                    <div class="form-label">{{__('Coin Icon')}}</div>
                                    <div class="input-group">
                                        <span class="input-group-btn">
                                            <span class="btn btn-default btn-file">
                                                <input type="file" name="coin_icon">
                                            </span>
                                        </span>
                                        <img width="150px" src="{{empty($item->coin_icon) ? '' : show_image_path($item->coin_icon,'coin/')}}">
                                    </div>
                                    <img id='img-upload'/>
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-2">
                                @if(isset($item))<input type="hidden" name="coin_id" value="{{encrypt($item->id)}}">  @endif
                                <button type="submit" class="btn theme-btn">{{$button_title}}</button>
                            </div>
                        </div>
                        {{Form::close()}}
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- /User Management -->

@endsection

@section('script')
@endsection
