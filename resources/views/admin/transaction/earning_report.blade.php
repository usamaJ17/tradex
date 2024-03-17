@extends('admin.master',['menu'=>'transaction', 'sub_menu'=>'transaction_all'])
@section('title', isset($title) ? $title : '')
@section('style')
@endsection
@section('content')
    <!-- breadcrumb -->
    <div class="custom-breadcrumb">
        <div class="row">
            <div class="col-12">
                <ul>
                    <li>{{__('Earning')}}</li>
                    <li class="active-item">{{__('All Report')}}</li>
                </ul>
            </div>
        </div>
    </div>
    <!-- /breadcrumb -->

    <!-- User Management -->
    <div class="user-management pt-4">
        <div class="row no-gutters">
            <div class="col-12 col-lg-2">
                <ul class="nav user-management-nav profile-nav mb-3" id="pills-tab" role="tablist">
                    <li>
                        <a class=" active  nav-link " data-id="profile" data-toggle="pill" role="tab" data-controls="profile" aria-selected="true" href="#profile">
                            <img src="{{asset('assets/admin/images/sidebar-icons/wallet.svg')}}" class="img-fluid" alt="">
                            <span>{{__('Withdrawal')}}</span>
                        </a>
                    </li>
{{--                    <li>--}}
{{--                        <a class=" @if(isset($tab) && $tab=='edit_profile') active @endif nav-link  " data-id="edit_profile" data-toggle="pill" role="tab" data-controls="edit_profile" aria-selected="true" href="#edit_profile">--}}
{{--                            <img src="{{asset('assets/admin/images/sidebar-icons/coin.svg')}}" class="img-fluid" alt="">--}}
{{--                            <span>{{__('Trade')}}</span>--}}
{{--                        </a>--}}
{{--                    </li>--}}
                </ul>
            </div>
            <div class="col-12 col-lg-10">
                <div class="tab-content tab-pt-n" id="tabContent">
                    <div class="tab-pane fade show active " id="profile" role="tabpanel" aria-labelledby="general-setting-tab">
                        <div class="table-area">
                            <div class="table-responsive">
                                <table id="deposit_table" class="table table-borderless custom-table display text-center"
                                        width="100%">
                                    <thead>
                                    <tr>
                                        <th class="all">{{__('Coin')}}</th>
                                        <th class="all">{{__('Type')}}</th>
                                        <th class="all">{{__('Earned Coin')}}</th>
                                    </tr>
                                    </thead>
                                    <tbody>
                                    </tbody>
                                </table>
                            </div>

                        </div>
                    </div>
                    <div class="tab-pane fade @if(isset($tab) && $tab=='edit_profile')show active @endif" id="edit_profile" role="tabpanel" aria-labelledby="apisetting-tab">
                        <div class="table-area">
                            <div class="table-responsive">
                                <table id="withdrawal_table" class="table table-borderless custom-table display text-center"
                                        width="100%">
                                    <thead>
                                    <tr>
                                        <th class="all">{{__('Coin')}}</th>
                                        <th class="all">{{__('Type')}}</th>
                                        <th class="all">{{__('Amount')}}</th>
                                    </tr>
                                    </thead>
                                    <tbody>
                                    </tbody>
                                </table>
                            </div>

                        </div>
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

            $('#deposit_table').DataTable({
                processing: true,
                serverSide: true,
                pageLength: 10,
                responsive: true,
                ajax: '{{route('adminEarningReport')}}?type=withdraw',
                order: [0, 'desc'],
                autoWidth: false,
                language: {
                    paginate: {
                        next: 'Next &#8250;',
                        previous: '&#8249; Previous'
                    }
                },
                columns: [
                    {"data": "name"},
                    {"data": "coin_type"},
                    {"data": "fees"}
                ]
            });

            $('#withdrawal_table').DataTable({
                processing: true,
                serverSide: true,
                pageLength: 25,
                responsive: true,
                ajax: '{{route('adminEarningReport')}}?type=trade',
                order: [0, 'desc'],
                autoWidth: false,
                language: {
                    paginate: {
                        next: 'Next &#8250;',
                        previous: '&#8249; Previous'
                    }
                },
                columns: [
                    {"data": "name"},
                    {"data": "coin_type"},
                    {"data": "fees"}
                ]
            });
        })(jQuery)
    </script>
@endsection
