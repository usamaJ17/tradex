@extends('admin.master')
@section('title', isset($title) ? $title : '')
@section('style')
@endsection
@section('sidebar')
@include('admin.gift_card.sidebar.sidebar',['menu'=>'banner'])
@endsection
@section('content')
    <!-- breadcrumb -->
    <div class="custom-breadcrumb">
        <div class="row">
            <div class="col-12">
                <ul>
                    <li class="active-item">{{__('Gift Card Banner')}}</li>
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
                <h4></h4>
                <a href="{{ route("giftCardBanner") }}" class="float-right btn btn-primary">{{ __("Add Banner") }}</a>
            </div>
                <div class="row">
                    <div class="col-md-6 col-sm-12">
                        <div class="form-group">
                            <label for="category">{{__('Categorys')}}</label>
                            <select id="category"  class="selectpicker" data-width="100%" data-style="btn-dark">
                               <option value="all">{{ __("all") }}</option>
                               @if (isset($categorys))
                                   @foreach ($categorys as $category)
                                        <option value="{{ $category->uid }}">{{ $category->name }}</option>
                                   @endforeach
                               @endif
                            </select>  
                        </div>
                    </div>
                    <div class="col-md-6 col-sm-12">
                        <div class="form-group">
                            <label for="filter">&nbsp;</label><br>
                            <input id="gift_card_filter" class="btn btn-primary" type="button" value="{{ __("Filter") }}" />
                        </div>
                    </div>
                </div>

                <div class="table-responsive">
                    <table id="table-coin" class="table table-borderless custom-table display text-center" width="100%">
                        <thead>
                            <tr>
                                <th scope="col" class="all">{{__('Title')}}</th>
                                <th scope="col">{{__('Sub Title')}}</th>
                                <th scope="col">{{__('Category')}}</th>
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
            function loadDataTable(value = 'all'){
                $('#table-coin').DataTable({
                    processing: true,
                    serverSide: true,
                    destroy   : true,
                    pageLength: 25,
                    responsive: true,
                    ajax: "{{ route('giftCardBannerListPage') }}?type="+value,
                    language: {
                        paginate: {
                            next: 'Next &#8250;',
                            previous: '&#8249; Previous'
                        }
                    },
                    columns: [
                    {"data": "title"},
                    {"data": "sub_title"},
                    {"data": "category_id"},
                    {"data": "status"},
                    {"data": "actions"},
                    ]
                });
            }
            $("#gift_card_filter").on('click', function(){
                let value = $('select[id="category"]').val();
                loadDataTable(value); 
            });
            loadDataTable();
    })(jQuery);
</script>


@endsection
