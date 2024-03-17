@extends('admin.master',['menu'=>'currency_deposit', 'sub_menu'=>'payment_method_list'])
@section('title', isset($title) ? $title : '')
@section('style')
@endsection
@section('content')
    <!-- breadcrumb -->
    <div class="custom-breadcrumb">
        <div class="row">
            <div class="col-5">
                <ul>
                    <li>{{__('Fiat Deposit')}}</li>
                    <li class="active-item">{{ $title }}</li>
                </ul>
            </div>
            <div class="col-sm-7 text-right">
                <a class="add-btn theme-btn" href="{{route('currencyPaymentMethodAdd')}}">{{__('Add New')}}</a>
            </div>
        </div>
    </div>
    <!-- /breadcrumb -->

    <!-- User Management -->
    <div class="user-management">
        <div class="row">
            <div class="col-12">
                <div class="header-bar p-4">
                    <div class="table-title">
                        <h3>{{ $title }}</h3>
                    </div>
                </div>
                <div class="card-body">
                    <div class="table-area payment-table-area">
                        <div class="table-responsive">
                            <table id="table" class="table table-borderless custom-table display text-center" width="100%">
                                <thead>
                                <tr>
                                    <th scope="col">{{__('Title')}}</th>
                                    <th scope="col">{{__('Payment Method')}}</th>
                                    <th scope="col">{{__('Status')}}</th>
                                    <th scope="col">{{__('Action')}}</th>
                                </tr>
                                </thead>
                                <tbody>
                                @if(isset($currency_deposit_payment_methods))
                                    @foreach($currency_deposit_payment_methods as $value)
                                        <tr>
                                            <td> {{ $value->title}} </td>
                                            <td> {{currencyDepositPaymentMethod($value->payment_method)}} </td>
                                            <td>
                                                <div>
                                                    <label class="switch">
                                                        <input type="checkbox" onclick="statusChange('{{$value->id}}')"
                                                               id="notification" name="security" @if($value->status == STATUS_ACTIVE) checked @endif>
                                                        <span class="slider" for="status"></span>
                                                    </label>
                                                </div>
                                            </td>
                                            <td>
                                                <ul class=" d-flex activity-menu">
                                                    <li>
                                                        <a title="{{__('Edit')}}" href="{{ route("currencyPaymentMethodEdit",["id" => $value->id]) }}">
                                                            <i class="fa fa-pencil"></i>
                                                        </a>
                                                    </li>
                                                    <li>
                                                        <a title="{{__('Delete')}}" href="{{ route("currencyPaymentMethodDelete",["id" => $value->id]) }}">
                                                            <i class="fa fa-trash"></i>
                                                        </a>
                                                    </li>
                                                </ul>
                                            </td>
                                        </tr>
                                    @endforeach
                                @else
                                    <tr>
                                        <td colspan="6">{{__('No data found')}}</td>
                                    </tr>
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

        function statusChange(currency_payment_method_id) {
            $.ajax({
                type: "POST",
                url: "{{ route('currencyPaymentMethodStatus') }}",
                data: {
                    '_token': "{{ csrf_token() }}",
                    'id': currency_payment_method_id
                },
                success: function (data) {
                    console.log(data);
                }
            });
        }


        $('#table').DataTable({
            processing: true,
            serverSide: false,
            paging: true,
            searching: true,
            ordering:  true,
            select: false,
            bDestroy: true,
            order: [0, 'asc'],
            responsive: true,
            autoWidth: false,
            language: {
                "decimal":        "",
                "emptyTable":     "{{__('No data available in table')}}",
                "info":           "{{__('Showing')}} _START_ to _END_ of _TOTAL_ {{__('entries')}}",
                "infoEmpty":      "{{__('Showing')}} 0 to 0 of 0 {{__('entries')}}",
                "infoFiltered":   "({{__('filtered from')}} _MAX_ {{__('total entries')}})",
                "infoPostFix":    "",
                "thousands":      ",",
                "lengthMenu":     "{{__('Show')}} _MENU_ {{__('entries')}}",
                "loadingRecords": "{{__('Loading...')}}",
                "processing":     "",
                "search":         "{{__('Search')}}:",
                "zeroRecords":    "{{__('No matching records found')}}",
                "paginate": {
                    "first":      "{{__('First')}}",
                    "last":       "{{__('Last')}}",
                    "next":       '{{__('Next')}} &#8250;',
                    "previous":   '&#8249; {{__('Previous')}}'
                },
                "aria": {
                    "sortAscending":  ": activate to sort column ascending",
                    "sortDescending": ": activate to sort column descending"
                }
            },
        });
    </script>
@endsection
