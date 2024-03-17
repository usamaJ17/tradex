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
                    <li>{{__('Coin Pair Settings')}}</li>
                    <li class="active-item">{{ $title }}</li>
                </ul>
            </div>
            <div class="col-md-3 text-right">
                <a class="add-btn theme-btn" href="" data-toggle="modal" data-target="#pairModal"><i class="fa fa-plus"></i>{{__('Add New Pair')}}</a>
            </div>
        </div>
    </div>
    <!-- /breadcrumb -->

    <!-- User Management -->
    <div class="user-management p-4">
        <div class="row">
            <div class="col-12">
                <div class="table-area">
                    <div>
                        <table id="table" class=" table table-borderless custom-table display text-lg-center" width="100%">
                            <thead>
                            <tr>
                                <th scope="col">{{__('Trade Coin')}}</th>
                                <th scope="col" class="all">{{__('Base Coin')}}</th>
                                <th scope="col">{{__('Last Price')}}</th>
                                <th scope="col" class="all">{{__('Active Status')}}</th>
                                <th scope="col" class="all">{{__('Is Default')}}</th>
                                <th scope="col" class="all">{{__('Bot Trading')}}</th>
                                <th scope="col" class="all">{{__('Future Trading')}}</th>
                                <th scope="col" class="">{{__('Price Available From Api')}}</th>
                                <th scope="col">{{__('Created At')}}</th>
                                <th scope="col" class="all">{{__('Actions')}}</th>
                            </tr>
                            </thead>
                            <tbody>
                            @if(isset($items[0]))
                                @foreach($items as $item)
                                    <tr>
                                        <td>{{isset($item->child_coin) ? check_default_coin_type($item->child_coin->coin_type) : ''}}</td>
                                        <td>{{isset($item->parent_coin) ? check_default_coin_type($item->parent_coin->coin_type) : ''}}</td>
                                        <td>{{ $item->price }} {{isset($item->parent_coin) ? check_default_coin_type($item->parent_coin->coin_type) : ''}}</td>
                                        <td>
                                            <div>
                                                <label class="switch">
                                                    <input type="checkbox" onclick="return processForm('{{encrypt($item->id)}}')"
                                                           id="notification" name="" @if($item->status == STATUS_ACTIVE) checked @endif>
                                                    <span class="slider" for="status"></span>
                                                </label>
                                            </div>
                                        </td>
                                        <td>
                                            <div>
                                                <label class="switch">
                                                    <input type="checkbox" onclick="defaultStatus('{{encrypt($item->id)}}')"
                                                        name="" @if($item->is_default == STATUS_ACTIVE) checked @endif>
                                                    <span class="slider" for="status"></span>
                                                </label>
                                            </div>
                                        </td>
                                        <td>
                                            <div>
                                                {{-- @if($item->bot_possible == STATUS_ACTIVE) --}}
                                                <label class="switch">
                                                    <input type="checkbox" onclick="return processMarketBot('{{encrypt($item->id)}}')"
                                                           id="notification" name="" @if($item->bot_trading == STATUS_ACTIVE) checked @endif>
                                                    <span class="slider" for="status"></span>
                                                </label>
                                                {{-- @else
                                                    <button class="btn-sm btn-warning" data-toggle="tooltip" data-placement="top" title="{{__('Bot trading is not possible for this pair')}}">{{__('Bot Disable')}}</button>
                                                @endif --}}
                                            </div>
                                        </td>
                                        <td>
                                            <div>
                                                <label class="switch">
                                                    <input type="checkbox" onclick="return futureTradeStatus('{{encrypt($item->id)}}')"
                                                        id="notification" name=""
                                                        @if($item->enable_future_trade == STATUS_ACTIVE)
                                                            checked
                                                        @endif>
                                                    <span class="slider" for="status"></span>
                                                </label>
                                            </div>
                                        </td>

                                        <td>{!! $item->is_token == STATUS_ACTIVE ? '<span class="text-danger"> No </span>' : '<span class="text-success"> Yes </span>' !!}</td>
                                        <td>{{ $item->created_at }}</td>
                                        <td>
                                            <ul class="d-flex activity-menu">
                                                <li class="viewuser">
                                                    <a data-toggle="modal" data-target="#pair_edit_{{$item->id}}" title="{{__('Edit')}}" class="btn btn-primary btn-sm">
                                                        <i class="fa fa-pencil"></i>
                                                    </a>
                                                </li>
                                                <li class="viewuser">
                                                    <a href="#delete1WV4d6uF6Ytu18v1Pl_{{($item->id)}}" data-toggle="modal" title="{{__("Delete")}}" class="btn btn-danger btn-sm">
                                                        <i class="fa fa-trash"></i>
                                                    </a>
                                                    <div id="delete1WV4d6uF6Ytu18v1Pl_{{($item->id)}}" class="modal fade delete" role="dialog">
                                                        <div class="modal-dialog modal-sm">
                                                            <div class="modal-content">
                                                                <div class="modal-header"><h6 class="modal-title">{{__('Delete')}}</h6><button type="button" class="close" data-dismiss="modal">&times;</button></div>
                                                                <div class="modal-body"><p>{{ __('Do you want to delete ?')}}</p></div>
                                                                <div class="modal-footer"><button type="button" class="btn btn-default" data-dismiss="modal">{{__("Close")}}</button>
                                                                    <a class="btn btn-danger"href="{{route('coinPairsDelete', encrypt($item->id))}}">{{__('Confirm')}} </a>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </li>
                                                @if($item->is_chart_updated == STATUS_PENDING)
                                                    <li class="viewuser">
                                                        <a href="#chart1WV4d6uF6Ytu18v1Pl_{{($item->id)}}" data-toggle="modal" title="{{__("Update Chart Data")}}" class="btn btn-success btn-sm">
                                                            <i class="fa fa-bar-chart"></i>
                                                        </a>
                                                        <div id="chart1WV4d6uF6Ytu18v1Pl_{{($item->id)}}" class="modal fade delete" role="dialog">
                                                            <div class="modal-dialog modal-sm">
                                                                <div class="modal-content">
                                                                    <div class="modal-header"><h6 class="modal-title">{{__('Update Chart Data')}}</h6><button type="button" class="close" data-dismiss="modal">&times;</button></div>
                                                                    <div class="modal-body"><p>{{ __('Do you want to get chart data from api ?')}}</p></div>
                                                                    <div class="modal-footer"><button type="button" class="btn btn-default" data-dismiss="modal">{{__("Close")}}</button>
                                                                        <a class="btn btn-danger"href="{{route('coinPairsChartUpdate', encrypt($item->id))}}">{{__('Confirm')}} </a>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </li>
                                                @endif
                                                <li class="viewuser">
                                                    <a href="{{route('coinPairFutureSetting', encrypt($item->id))}}" title="{{__("Settings")}}" class="btn btn-warning btn-sm">
                                                        <i class="fa fa-cog"></i>
                                                    </a>
                                                </li>
                                            </ul>
                                            <div id="pair_edit_{{$item->id}}" class="modal fade" role="dialog">
                                                <div class="modal-dialog">
                                                    <!-- Modal content-->
                                                    <div class="modal-content">
                                                        <div class="modal-header">
                                                            <button type="button" class="close" data-dismiss="modal">&times;</button>
                                                            <h4 class="modal-title">{{__('Update Coin Pair')}}</h4>
                                                        </div>
                                                        <div class="modal-body">
                                                            {{Form::open(['route' => 'saveCoinPairSettings', 'files' => 'true' ])}}
                                                            <div class="row">
                                                                <div class="col-md-6">
                                                                    <input type="hidden" name="edit_id" value="{{encrypt($item->id)}}">
                                                                    <div class="form-group">
                                                                        <label class="form-label">{{__('Base Coin')}}</label>
                                                                        <select class=" form-control" name="parent_coin_id"  style="width: 100%;">
                                                                            <option value="">{{__('Select')}}</option>
                                                                            @if(isset($coins[0]))
                                                                                @foreach($coins as $coin)
                                                                                    <option @if($item->parent_coin_id == $coin->id) selected @endif value="{{$coin->id}}">{{check_default_coin_type($coin->coin_type)}}</option>
                                                                                @endforeach
                                                                            @endif
                                                                        </select>
                                                                    </div>
                                                                </div>
                                                                <div class="col-md-6">
                                                                    <div class="form-group">
                                                                        <label class="form-label">{{__('Pair Coin')}}</label>
                                                                        <select class=" form-control" name="child_coin_id"  style="width: 100%;">
                                                                            <option value="">{{__('Select')}}</option>
                                                                            @if(isset($coins[0]))
                                                                                @foreach($coins as $coin)
                                                                                    <option @if($item->child_coin_id == $coin->id) selected @endif value="{{$coin->id}}">{{check_default_coin_type($coin->coin_type)}}</option>
                                                                                @endforeach
                                                                            @endif
                                                                        </select>
                                                                    </div>
                                                                </div>
                                                                <div class="col-md-6">
                                                                    <div class="form-group">
                                                                        <label class="form-label">{{__('Is this pair listed in bot api ?')}}</label>
                                                                        <select class=" form-control" name="pair_listed_api"  style="width: 100%;">
                                                                            <option @if($item->is_token == STATUS_ACTIVE) selected @endif value="2">{{__('No')}}</option>
                                                                            <option @if($item->is_token == STATUS_DEACTIVE) selected @endif value="1">{{__('Yes')}}</option>
                                                                        </select>
                                                                    </div>
                                                                </div>
                                                                <div class="col-md-6">
                                                                    <div class="form-group">
                                                                        <label class="form-label">{{__('Digits after Decimal point')}}</label>
                                                                        
                                                                        <select class="form-control" name="pair_decimal"  style="width: 100%;">
                                                                            @foreach (range(2,8) as $v)
                                                                                <option @if($item->pair_decimal == $v) selected @endif value="{{ $v }}">{{ $v }}</option>
                                                                            @endforeach
                                                                        </select>
                                                                        <p class="text-secondary sm-text">{{ __("Select the number of digits after decimal point.") }}</p>
                                                                    </div>
                                                                </div>
                                                                @if(env('APP_MODE') == 'myDemo')
                                                                    <div class="col-md-6">
                                                                        <div class="form-group">
                                                                            <label class="form-label">{{__('Last Price')}}</label>
                                                                            <input type="text" class="form-control" name="price" value="{{$item->price}}">
                                                                        </div>
                                                                    </div>
                                                                @endif
                                                            </div>
                                                            <div class="row">
                                                                <div class="col-md-4">
                                                                    <button class="btn btn-info" type="submit">{{__('Update')}}</button>
                                                                </div>
                                                            </div>
                                                            {{Form::close()}}
                                                        </div>
                                                        <div class="modal-footer">
                                                            <button type="button" class="btn btn-default" data-dismiss="modal">{{__('Close')}}</button>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
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
    <!-- Modal -->
    <div id="pairModal" class="modal fade" role="dialog">
        <div class="modal-dialog modal-dialog-centered">
            <!-- Modal content-->
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal">&times;</button>
                    <h4 class="modal-title">{{__('Add Coin Pair')}}</h4>
                </div>
                <div class="modal-body">
                    {{Form::open(['route' => 'saveCoinPairSettings', 'files' => 'true' ])}}
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label class="form-label">{{__('Base Coin')}}</label>
                                <div class="cp-select-area">
                                    <select class=" form-control" name="parent_coin_id"  style="width: 100%;">
                                        <option value="">{{__('Select')}}</option>
                                        @if(isset($coins[0]))
                                            @foreach($coins as $coin)
                                                <option value="{{$coin->id}}">{{check_default_coin_type($coin->coin_type)}}</option>
                                            @endforeach
                                        @endif
                                    </select>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label class="form-label">{{__('Pair Coin')}}</label>
                                <div class="cp-select-area">
                                    <select class=" form-control" name="child_coin_id"  style="width: 100%;">
                                        <option value="">{{__('Select')}}</option>
                                        @if(isset($coins[0]))
                                            @foreach($coins as $coin)
                                                <option value="{{$coin->id}}">{{check_default_coin_type($coin->coin_type)}}</option>
                                            @endforeach
                                        @endif
                                    </select>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label class="form-label">{{__('Get last price from api ?')}}</label>
                                <div class="cp-select-area">
                                    <select name="get_price_api" id="" class="form-control">
                                        <option value="1">{{__('Yes')}}</option>
                                        <option value="2">{{__('No')}}</option>
                                    </select>
                                </div>
                                <small class="text-warning">{{__('If no , please input the initial price')}}</small>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label class="form-label">{{__('Initial Price')}}</label>
                                <input type="text" class="form-control" name="price" >
                            </div>
                        </div>
                    </div>

                    <div class="modal-footer mt-4">
                        <button class="btn btn-warning text-white" type="submit">{{__('Save')}}</button>
                        <button type="button" class="btn btn-dark" data-dismiss="modal">{{__('Close')}}</button>
                    </div>
                    {{Form::close()}}
                </div>

            </div>
        </div>
    </div>
@endsection

@section('script')
    <script>

            $('#table').DataTable({
                responsive: true,
                paging: true,
                searching: true,
                ordering: true,
                select: false,
                bDestroy: true
            });

            function processForm(active_id) {

                $.ajax({
                    type: "POST",
                    url: "{{ route('changeCoinPairStatus') }}",
                    data: {
                        '_token': "{{ csrf_token() }}",
                        'active_id': active_id
                    },
                    success: function (data) {
                        if(data.success == true) {
                            VanillaToasts.create({
                                text: data.message,
                                backgroundColor: "linear-gradient(135deg, #73a5ff, #5477f5)",
                                type: 'success',
                                timeout: 5000
                            });
                        } else {
                            VanillaToasts.create({
                                text: data.message,
                                type: 'warning',
                                timeout: 5000
                            });
                        }
                    }
                });
            }
            function defaultStatus(active_id) {
                $.ajax({
                    type: "POST",
                    url: "{{ route('changeCoinPairDefaultStatus') }}",
                    data: {
                        '_token': "{{ csrf_token() }}",
                        'active_id': active_id
                    },
                    success: function (data) {
                        if(data.success == true) {
                            VanillaToasts.create({
                                text: data.message,
                                backgroundColor: "linear-gradient(135deg, #73a5ff, #5477f5)",
                                type: 'success',
                                timeout: 5000
                            });
                            setTimeout(function () {
                                location.reload();
                            }, 2000);
                        } else {
                            VanillaToasts.create({
                                text: data.message,
                                type: 'warning',
                                timeout: 5000
                            });
                        }
                    }
                });
            }
            function processMarketBot(active_id) {

                $.ajax({
                    type: "POST",
                    url: "{{ route('changeCoinPairBotStatus') }}",
                    data: {
                        '_token': "{{ csrf_token() }}",
                        'active_id': active_id
                    },
                    success: function (data) {
                        if(data.success == true) {
                            VanillaToasts.create({
                                text: data.message,
                                backgroundColor: "linear-gradient(135deg, #73a5ff, #5477f5)",
                                type: 'success',
                                timeout: 5000
                            });
                        } else {
                            VanillaToasts.create({
                                text: data.message,
                                type: 'warning',
                                timeout: 5000
                            });

                        }
                    }
                });
            }

            function futureTradeStatus(active_id) {

                $.ajax({
                    type: "POST",
                    url: "{{ route('changeFutureTradeStatus') }}",
                    data: {
                        '_token': "{{ csrf_token() }}",
                        'active_id': active_id
                    },
                    success: function (data) {
                        if(data.success == true) {
                            VanillaToasts.create({
                                text: data.message,
                                backgroundColor: "linear-gradient(135deg, #73a5ff, #5477f5)",
                                type: 'success',
                                timeout: 5000
                            });
                        } else {
                            VanillaToasts.create({
                                text: data.message,
                                type: 'warning',
                                timeout: 5000
                            });

                        }
                    }
                });
            }

    </script>
@endsection
