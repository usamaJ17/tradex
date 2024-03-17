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
                    <li class="active-item">{{__('Wallet List')}}</li>
                </ul>
            </div>
        </div>
    </div>
    <!-- /breadcrumb -->

    <!-- User Management -->
    <div class="user-management">
        <div class="row">
            <div class="col-12">
                <div class="card-body">
                    <div class="header-bar">
                        <div class="table-title">
                            {{__("Multi Signature Wallet List")}}
                        </div>
                    </div>
                    <div class="table-area">
                        <div class="table-responsive">
                            <table id="table" class="table table-borderless custom-table display text-center" width="100%">
                                <thead>
                                <tr>
                                    <th class="all">{{__('Wallet Name')}}</th>
                                    <th class="all">{{__('Coin Type')}}</th>
                                    <th class="desktop">{{__('Balance')}}</th>
                                    <th class="desktop">{{__('Referral Balance')}}</th>
                                    <th class="all">{{__('Update Date')}}</th>
                                    <th class="all">{{__('Actions')}}</th>
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
    <!-- /User Management -->
@endsection

@section('script')
    <script>
        (function($) {
            "use strict";
            $('#table').DataTable({
                processing: true,
                serverSide: true,
                pageLength: 10,
                bLengthChange: true,
                responsive: true,
                ajax: '{{route('adminCoWallets')}}',
                //order:[6,'desc'],
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
                    {"data": "balance"},
                    {"data": "referral_balance"},
                    {"data": "updated_at"},
                    {"data": "actions"}
                ]
            });
        })(jQuery)
    </script>
@endsection
