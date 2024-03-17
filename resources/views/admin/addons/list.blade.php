@extends('admin.master',['menu'=>'addons', 'sub_menu'=>'addons_list'])
@section('title', isset($title) ? $title : '')
@section('style')
@endsection
@section('content')
    <!-- breadcrumb -->
    <div class="custom-breadcrumb">
        <div class="row">
            <div class="col-12">
                <ul>
                    <li>{{__('Addons Lists')}}</li>
                    <li class="active-item">{{__('Addons')}}</li>
                </ul>
            </div>
        </div>
    </div>
    <!-- /breadcrumb -->
@php $update = false; @endphp
    <!-- User Management -->
 <div class="user-management">
    <div class="row">
        <div class="col-md-6">
        <div class="card-body">
            <div class="table-area payment-table-area">
                <div class="table-responsive">
                    <table class="table table-borderless custom-table display text-center" id="table" >
                        <thead>
                            <tr>
                                <th>{{ __('Addons Name') }}</th>
                                <th>{{ __('Action') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                        @if(isset($list))
                            @foreach($list as $item)
                                <tr>
                                    <td>{{ $item['title'] }}</td>
                                    <td>
                                        <a href="{{ route($item['url']) }}" class="btn btn-xl btn-primary">
                                        {{ __('Manage') }}
                                        </a>
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
</div>
    <!-- /User Management -->
@endsection

@section('script')
    <script>



       $('#table').DataTable({
            dom:'',
            processing: true,
            serverSide: false,
            order:false,
            responsive: true,
            autoWidth: false,
            columnDefs: [{
            "orderable": false,
            "targets": '_all'
            }]
        });



    </script>
@endsection
