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
                    <li class="active-item">{{__('Admin Send Coin History')}}</li>
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
                                <th class="all">{{__('User Email')}}</th>
                                <th>{{__('Request Coin')}}</th>
                                <th>{{__('Converted Coin')}}</th>
                                <th>{{__('Rate')}}</th>
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
                ajax: '{{route('adminSwapCoinHistory')}}',
                order: [0, 'desc'],
                autoWidth: false,
                language: {
                    paginate: {
                        next: 'Next &#8250;',
                        previous: '&#8249; Previous'
                    }
                },
                columns: [
                    {"data": "user.email"},
                    {"data": "request"},
                    {"data": "convert"},
                    {"data": "rate"}
                ]
            });
        })(jQuery)
    </script>
@endsection
