@extends('admin.future-trade.layouts.master',['menu'=>'future_trade_transaction_history', 'sub_menu'=>  isset($sub_menu) ? $sub_menu : ''])
@section('title', isset($title) ? $title : '')
@section('style')
@endsection
@section('content')
    <!-- breadcrumb -->
    <div class="custom-breadcrumb">
        <div class="row">
            <div class="col-12">
                <ul>
                    <li class="active-item">{{$header ?? __('Future Trade Transaction History')}}</li>
                </ul>
            </div>
        </div>
    </div>
    <!-- /breadcrumb -->
    <!-- User Management -->
    <div class="user-management wallet-transaction-area">
        <div class="tab-pane fade show active" id="all_order_tab" role="tabpanel"
                aria-labelledby=all_order">
            <div class="table-area">
                <div class="table-responsive">
                    <table id="all_table" class="table table-borderless custom-table display text-left"
                            width="100%">
                        <thead>
                        <tr>
                            <th class="all">{{__('User')}}</th>
                            <th class="all">{{__('Amount')}}</th>
                            <th class="all">{{__('Symbol')}}</th>
                        </tr>
                        </thead>
                        <tbody>
                        </tbody>
                    </table>
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

                $("#all_table").DataTable({
                    processing: true,
                    serverSide: true,
                    pageLength: 25,
                    responsive: true,
                    //ajax: url,
                    // order: [7, 'desc'],
                    autoWidth: false,
                    language: {
                        paginate: {
                            next: 'Next &#8250;',
                            previous: '&#8249; Previous'
                        }
                    },
                    columns: [
                        {'data': 'user_id'},
                        {'data': 'amount'},
                        {'data': 'symbol'},
                    ]
                });
        })(jQuery);
    </script>
@endsection
