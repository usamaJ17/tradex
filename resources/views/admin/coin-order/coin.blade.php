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
    @php 
        $demoTrade = (isset($module) && isset($module['DemoTrade'])) ? true : false ;   
    @endphp
    <!-- User Management -->
    <div class="user-management pt-4">
        <div class="row">
            <div class="col-12">
                <div class="header-bar">
                    <div class="table-title">
                        <!-- <h3>{{ $title }}</h3> -->
                    </div>
                    <div class="right d-flex align-items-center">
                        <div class="add-btn-new mb-2 mr-1">
                            <a href="{{route('adminCoinRate')}}">{{__('Update Coin Rate')}}</a>
                        </div>
                        <div class="add-btn-new mb-2 ml-2">
                            <a href="{{route('adminAddCoin')}}">{{__('Add New Coin')}}</a>
                        </div>
                    </div>
                </div>
                <div class="table-area">
                    <div class="table-responsive">
                        <table id="table" class=" table table-borderless custom-table display text-lg-center" width="100%">
                            <thead>
                            <tr>
                                <th scope="col" >{{__('Coin Name')}}</th>
                                <th scope="col" class="all">{{__('Currency Type')}}</th>
                                <th scope="col">{{__('Coin Type')}}</th>
                                <th scope="col">{{__('Coin API')}}</th>
                                <th scope="col">{{__('Coin Price')}}</th>
                                <th scope="col" class="all">{{__('Status')}}</th>
                                @if($demoTrade)
                                    <th scope="col" class="all">{{__('Demo Trade')}}</th>
                                @endif
                                <th scope="col">{{__('Updated At')}}</th>
                                <th scope="col" class="all">{{__('Actions')}}</th>
                            </tr>
                            </thead>
                            <tbody>
                            @if(isset($coins))
                            @foreach($coins as $coin)
                                <tr>
                                    <td> {{$coin->name}} </td>
                                    <td> {{getTradeCurrencyType($coin->currency_type)}} </td>
                                    <td> {{find_coin_type($coin->coin_type)}} </td>
                                    <td> {{ $coin->currency_type == CURRENCY_TYPE_CRYPTO ? api_settings($coin->network) : __("Fiat Currency")}} </td>
                                    <td> {{number_format($coin->coin_price,2).' USD/ '.find_coin_type($coin->coin_type)}} </td>
                                    <td>
                                        @if ($coin->ico_id == 0 || $coin->is_listed == 1)
                                            <div>
                                                <label class="switch">
                                                    <input type="checkbox" onclick="return processForm('{{$coin->id}}')"
                                                        id="notification" name="security" @if($coin->status == STATUS_ACTIVE) checked @endif>
                                                    <span class="slider" for="status"></span>
                                                </label>
                                            </div>
                                        @else
                                            <button class="btn-sm btn-warning" data-toggle="tooltip" data-placement="top" title="{{__('You can  not change the status of this coin')}}">{{__('Disable')}}</button>
                                        @endif
                                    </td>
                                    @if($demoTrade)
                                        <td>
                                            <div>
                                                <label class="switch">
                                                    <input type="checkbox" onclick="changeDemoTradeStatus('{{$coin->coin_type}}')"
                                                        id="notification" name="security" @if($coin->is_demo_trade == STATUS_ACTIVE) checked @endif>
                                                    <span class="slider" for="status"></span>
                                                </label>
                                            </div>
                                        </td>
                                    @endif
                                    <td> {{$coin->updated_at}}</td>
                                    <td>
                                        @if ($coin->ico_id == 0 || $coin->is_listed == 1)
                                            <ul class="d-flex activity-menu">
                                                <li class="viewuser">
                                                    <a href="{{route('adminCoinEdit', encrypt($coin->id))}}" title="{{__("Update")}}" class="btn btn-primary btn-sm">
                                                        <i class="fa fa-pencil"></i>
                                                    </a>
                                                </li>
                                                @if($coin->currency_type == CURRENCY_TYPE_CRYPTO)
                                                    <li class="viewuser">
                                                        <a href="{{route('adminCoinSettings', encrypt($coin->id))}}" title="{{__("Settings")}}" class="btn btn-warning btn-sm">
                                                            <i class="fa fa-cog"></i>
                                                        </a>
                                                    </li>
                                                @endif
                                                <li class="viewuser">
                                                    <a href="#delete1WV4d6uF6Ytu8v1Pl_{{($coin->id)}}" data-toggle="modal" title="{{__("Delete")}}" class="btn btn-danger btn-sm">
                                                        <i class="fa fa-trash"></i>
                                                    </a>
                                                    <div id="delete1WV4d6uF6Ytu8v1Pl_{{($coin->id)}}" class="modal fade delete" role="dialog">
                                                        <div class="modal-dialog modal-sm">
                                                            <div class="modal-content">
                                                                <div class="modal-header"><h6 class="modal-title">{{__('Delete')}}</h6><button type="button" class="close" data-dismiss="modal">&times;</button></div>
                                                                <div class="modal-body"><p>{{ __('Do you want to delete ?')}}</p></div>
                                                                <div class="modal-footer"><button type="button" class="btn btn-default" data-dismiss="modal">{{__("Close")}}</button>
                                                                    <a class="btn btn-danger"href="{{route('adminCoinDelete', encrypt($coin->id))}}">{{__('Confirm')}} </a>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </li>
                                            </ul>
                                        @else
                                            
                                            <ul class="d-flex activity-menu">
                                                <li class="viewuser">
                                                    <a href="#make_listed_coin_{{($coin->id)}}" data-toggle="modal" title="{{__("Make Listed")}}" class="btn btn-danger btn-sm">
                                                        {{__('Make Listed')}}
                                                    </a>
                                                    <div id="make_listed_coin_{{($coin->id)}}" class="modal fade delete" role="dialog">
                                                        <div class="modal-dialog modal-md">
                                                            <div class="modal-content">
                                                                <div class="modal-header">
                                                                    <h6 class="modal-title">{{__('Make Listed')}}</h6>
                                                                    <button type="button" class="close" data-dismiss="modal">&times;</button>
                                                                </div>
                                                                <div class="modal-body">
                                                                    <ul>
                                                                        <li>{{__('1')}}.{{__('Check your Token Buy history for this token. If any request is pending for this token then make it rejected or accepted. Otherwise this token is not listed.')}}</li>
                                                                        <li>{{__('2')}}.{{__('Check your Phases for this token. If any phase is running make it deactive or it will automatically deactive. ')}}</li>
                                                                        <li>{{__('3')}}.{{__('After make it listed you can not create new phase or run any phase for this token.')}}</li>
                                                                    </ul>
                                                                </div>
                                                                <div class="modal-footer"><button type="button" class="btn btn-default" data-dismiss="modal">{{__("Close")}}</button>
                                                                    <a class="btn btn-danger"href="{{route('coinMakeListed', encrypt($coin->id))}}">{{__('Confirm')}} </a>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </li>
                                            <ul>
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                            @endif
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- /User Management -->

@endsection
@section('script')
    <script>
        (function($) {
            "use strict";
            $('#table').DataTable({
                responsive: true,
                paging: true,
                searching: true,
                ordering:  true,
                select: false,
                bDestroy: true
            });
        })(jQuery);
        function processForm(active_id) {
            $.ajax({
                type: "POST",
                url: "{{ route('adminCoinStatus') }}",
                data: {
                    '_token': "{{ csrf_token() }}",
                    'active_id': active_id
                },
                success: function (data) {
                    console.log(data);
                }
            });
        }
        @if($demoTrade)
            function changeDemoTradeStatus($coin){
                let url = '{{ route("demoTradeCoinStatus") }}'+$coin;
                $.get(url,(data)=>{
                    if(data?.success) {
                        VanillaToasts.create({
                            text: data?.message,
                            type: 'success',
                            timeout: 40000
                        }); return;
                    }
                    VanillaToasts.create({
                        text: data?.message,
                        type: 'warning',
                        timeout: 40000
                    });
                });
            }
        @endif
    </script>
@endsection
