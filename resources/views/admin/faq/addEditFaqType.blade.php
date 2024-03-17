@extends('admin.master',['menu'=>'faq', 'sub_menu'=>'faq'])
@section('title', isset($title) ? $title : '')
@section('style')
@endsection
@section('content')
    <!-- breadcrumb -->
    <div class="custom-breadcrumb">
        <div class="row">
            <div class="col-12">
                <ul>
                    <li>{{__('FAQs')}}</li>
                    <li class="active-item">{{$title}}</li>
                </ul>
            </div>
        </div>
    </div>
    <!-- /breadcrumb -->

    <!-- User Management -->
    <div class="user-management p-4">
        <div class="row">
            <div class="col-12">
                <div class="header-bar">
                    <div class="table-title">
                        <h3>{{$title}}</h3>
                    </div>
                </div>
                <div class="profile-info-form">
                    <form action="{{route('adminFaqTypeSave')}}" method="post">
                        @csrf
                        <div class="row">
                            <div class="col-6 mb-xl-0 mb-4">
                                <div class="form-group">
                                    <label>{{__('Name')}}</label>
                                    <input type="text" name="name" class="form-control" @if(isset($item)) value="{{$item->name}}" @else value="{{old('question')}}" @endif>
                                </div>
                                
                            </div>
                            <div class="col-6 mb-xl-0 mb-4">
                                <div class="form-group">
                                    <label>{{__('Activation Status')}}</label>
                                    <select name="status" class="form-control wide" >
                                        @foreach(status() as $key => $value)
                                            <option @if(isset($item) && ($item->status == $key)) selected
                                                    @elseif((old('status') != null) && (old('status') == $key)) @endif value="{{ $key }}">{!! $value !!}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                            <div class="col-12">
                                <div class="form-group">
                                    @if(isset($item))
                                        <input type="hidden" name="edit_id" value="{{$item->id}}">
                                    @endif
                                    <button type="submit" class="button-primary theme-btn">{{__('Save')}}</button>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
    <!-- /User Management -->
    <!-- FAQ type list -->
    <div class="user-management padding-30">
        <div class="row">
            <div class="col-12">
                <div class="header-bar">
                    <div class="table-title">
                        <h3>{{__('FAQs Type List')}}</h3>
                    </div>
                    
                </div>
                <div class="table-area">
                    <div>
                        <table id="table" class="table table-borderless custom-table display" width="100%">
                            <thead>
                            <tr>
                                <th class="all">{{__('Name')}}</th>
                                <th>{{__('Status')}}</th>
                                <th>{{__('Updated At')}}</th>
                                <th class="text-lg-center all">{{__('Actions')}}</th>
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
    <!-- /FAQ type list -->
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
            ajax: '{{route('adminFaqTypeAdd')}}',
            order: [2, 'desc'],
            autoWidth: false,
            language: {
                paginate: {
                    next: 'Next &#8250;',
                    previous: '&#8249; Previous'
                }
            },
            columns: [
                {"data": "name", "orderable": false},
                {"data": "status", "orderable": false},
                {"data": "updated_at", "orderable": false},
                {"data": "actions", "orderable": false}
            ],
        });
    })(jQuery);
</script>
@endsection
