@extends('admin.master',['menu'=>'coin', 'sub_menu' => 'coin_list'])
@section('title', isset($title) ? $title : '')
@section('style')
@endsection
@section('content')
    <!-- breadcrumb -->
    <div class="custom-breadcrumb">
        <div class="row">
            <div class="col-md-9">
                <ul>
                    <li>{{__('Coin')}}</li>
                    <li class="active-item">{{ $title }}</li>
                </ul>
            </div>
        </div>
    </div>
    <!-- /breadcrumb -->

    <!-- User Management -->
    <div class="user-management pt-4">
        <div class="row">
            <div class="col-12">
                <div class="header-bar">
                    <div class="table-title">
                        <!-- <h3>{{ $title }}</h3> -->
                    </div>
                </div>
                <div class="table-area">
                    <div class="table-responsive">
                        <table id="table" class=" table table-borderless custom-table display text-lg-center" width="100%">
                            <thead>
                            <tr>
                                <th scope="col" class="all">{{__('Coin Name')}}</th>
                                <th scope="col" class="all">{{__('Coin Name')}}</th>
                            </tr>
                            </thead>
                            <tbody>
                            @if(isset($items[0]))
                                @foreach($items as $coin)
                                    <tr>
                                        <td> {{$coin->name}} </td>
                                        <td> {{number_format($coin->total_balance,8).' '. $coin->coin_type}} </td>
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
        })(jQuery);
        function processForm(active_id) {
            $.ajax({
                type: "POST",
                url: "{{ route('adminCoinStatus') }}",
                data: {
                    '_token': "{{ csrf_token() }}",
                    'active_id': active_id
                },
                success: function (data) {
                    console.log(data);
                }
            });
        }
    </script>
@endsection
