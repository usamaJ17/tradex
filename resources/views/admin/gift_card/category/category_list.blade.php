@extends('admin.master')
@section('title', isset($title) ? $title : '')
@section('style')
@endsection
@section('sidebar')
@include('admin.gift_card.sidebar.sidebar',['menu'=>'category'])
@endsection
@section('content')
    <!-- breadcrumb -->
    <div class="custom-breadcrumb">
        <div class="row">
            <div class="col-12">
                <ul>
                    <li class="active-item">{{__('Dashboard')}}</li>
                </ul>
            </div>
        </div>
    </div>
    <!-- /breadcrumb -->

    <!-- User Management -->
    <div class="user-management user-chart card">
        <div class="row">
            <div class="card-body">
            <div class="ml-1 card-top">
                <h4>{{__('Gift Card Category')}}</h4>
                <a href="{{ route("giftCardCategory") }}" class="float-right btn btn-primary">{{ __("Add Category") }}</a>
            </div>
                <div class="table-responsive">
                    <table id="table-coin" class="table table-borderless custom-table display text-center" width="100%">
                        <thead>
                            <tr>
                                <th scope="col" class="all">{{__('Name')}}</th>
                                <th scope="col">{{__('Status')}}</th>
                                <th scope="col" class="all">{{__('Actions')}}</th>
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

            $('#table-coin').DataTable({
                processing: true,
                serverSide: true,
                pageLength: 25,
                responsive: true,
                ajax: "{{ route('giftCardCategoryListPage') }}",
                language: {
                    paginate: {
                        next: 'Next &#8250;',
                        previous: '&#8249; Previous'
                    }
                },
                columns: [
                   {"data": "name"},
                   {"data": "status"},
                   {"data": "actions"},
                ]
            });
    })(jQuery);
</script>


@endsection
