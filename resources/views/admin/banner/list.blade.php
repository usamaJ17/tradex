@extends('admin.master',['menu'=>'landing_setting', 'sub_menu'=>'banner'])
@section('title', isset($title) ? $title : '')
@section('style')
@endsection
@section('content')
    <!-- breadcrumb -->
    <div class="custom-breadcrumb">
        <div class="row">
            <div class="col-12">
                <ul>
                    <li>{{__('Settings')}}</li>
                    <li class="active-item">{{__('Landing Banner')}}</li>
                </ul>
            </div>
        </div>
    </div>
    <!-- /breadcrumb -->

    <!-- User Management -->
    <div class="user-management padding-30">
        <div class="row">
            <div class="col-12">
                <div class="header-bar">
                    <div class="table-title">
                        <h3>{{__('Landing Banner')}}</h3>
                    </div>
                    <div class="right d-flex align-items-center">
                        <div class="add-btn">
                            <a href="{{route('adminBannerAdd')}}">{{__('+ Add')}}</a>
                        </div>
                    </div>
                </div>
                <div class="table-area">
                    <div>
                        <table id="table" class="table table-borderless custom-table display" width="100%">
                            <thead>
                            <tr>
                                <th>{{__('Title')}}</th>
                                <th>{{__('Status')}}</th>
                                <th>{{__('Created At')}}</th>
                                <th class="text-center">{{__('Actions')}}</th>
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
                retrieve: true,
                bLengthChange: true,
                responsive: true,
                ajax: '{{route('adminBannerList')}}',
                order: [2, 'desc'],
                autoWidth: false,
                language: {
                    paginate: {
                        next: 'Next &#8250;',
                        previous: '&#8249; Previous'
                    }
                },
                columns: [
                    {"data": "title", "orderable": false},
                    {"data": "status", "orderable": false},
                    {"data": "created_at", "orderable": false},
                    {"data": "actions", "orderable": false}
                ],
            });
        })(jQuery);
    </script>
@endsection
