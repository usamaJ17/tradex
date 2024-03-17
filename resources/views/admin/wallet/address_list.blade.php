@extends('admin.master',['menu'=>'wallet'])
@section('title', isset($title) ? $title : '')
@section('style')
@endsection
@section('content')
    <!-- breadcrumb -->
    <div class="custom-breadcrumb">
        <div class="row">
            <div class="col-12">
                <ul>
                    <li>{{__('Wallet Management')}}</li>
                    <li class="active-item">{{__('User Wallet Address List')}}</li>
                </ul>
            </div>
        </div>
    </div>
    <!-- /breadcrumb -->

    <!-- User Management -->
    <div class="user-management pt-4">
        <div class="row">
            <div class="col-12">
                <div class="table-area">
                    <div class="table-responsive">
                        <table id="table" class="table table-borderless custom-table display text-lg-center" width="100%">
                            <thead>
                            <tr>
                                <th class="all">{{__('Coin Type')}}</th>
                                <th class="all">{{__('Address')}}</th>
                                <th>{{__('Date')}}</th>
                            </tr>
                            </thead>
                            <tbody>
                                @if(isset($address_list[0]))
                                    @foreach ($address_list as $item)
                                        <tr>
                                            <td>{{ $item->coin_type }}</td>
                                            <td>{{ $item->address }}</td>
                                            <td>{{ $item->created_at }}</td>
                                        </tr>
                                    @endforeach
                                @endif

                                @if(isset($address_network_list[0]))
                                    @foreach ($address_network_list as $items)
                                        <tr>
                                            <td>{{ $items->network_type }}</td>
                                            <td>{{ $items->address }}</td>
                                            <td>{{ $items->created_at }}</td>
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
        })(jQuery)
    </script>
@endsection
