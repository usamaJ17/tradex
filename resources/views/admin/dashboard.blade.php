@extends('admin.master',['menu'=>'dashboard'])
@section('title', isset($title) ? $title : '')
@section('style')
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

    <!-- Status -->
    <div class="dashboard-status">
        @include('admin.dashboard.dashboard_status')
    </div>

    <!-- user chart -->
    <div class="user-chart mt-0">
        <div class="row">
            <div class="col-md-6 mb-4 mb-md-0">
                <div class="card">
                    <div class="card-body">
                        <div class="card-top">
                            <h4>{{__('Deposit')}}</h4>
                        </div>
                        <p class="subtitle">{{__('Current Year')}}</p>
                        <canvas id="depositChart"></canvas>
                    </div>
                </div>
            </div>
            <div class="col-md-6">
                <div class="card">
                    <div class="card-body">
                        <div class="card-top">
                            <h4>{{__('Withdrawal')}}</h4>
                        </div>
                        <p class="subtitle">{{__('Current Year')}}</p>
                        <canvas id="withdrawalChart"></canvas>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- /user chart -->
    <div class="user-management user-chart card">
        <div class="card-body">
            <div class="card-top">
                <h4>{{__('Pending Withdrawal')}}</h4>
            </div>
            <div class="table-area">
                <div>
                    <table id="pending_withdrwall" class="table table-borderless custom-table display text-left"
                        >
                        <thead>
                        <tr>
                            <th class="all">{{__('Type')}}</th>
                            <th>{{__('Sender')}}</th>
                            <th>{{__('Address')}}</th>
                            <th>{{__('Receiver')}}</th>
                            <th>{{__('Amount')}}</th>
                            <th class="all">{{__('Coin Type')}}</th>
                            <th>{{__('Fees')}}</th>
                            <th>{{__('Transaction Id')}}</th>
                            <th>{{__('Update Date')}}</th>
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
    <!-- /user chart -->

@endsection

@section('script')
    <script src="{{asset('assets/common/chart/chart.min.js')}}"></script>
    <script>
        (function($) {
            "use strict";
            var ctx = document.getElementById('depositChart').getContext("2d")
            var depositChart = new Chart(ctx, {
                type: 'line',
                yaxisname: "Monthly Deposit",

                data: {
                    labels: ["Jan", "Feb", "Mar", "Apr", "May", "Jun", "Jul", "Aug", "Sep", "Oct", "Nov", "Dec"],
                    datasets: [{
                        label: "Monthly Deposit",
                        borderColor: "#1cf676",
                        pointBorderColor: "#1cf676",
                        pointBackgroundColor: "#1cf676",
                        pointHoverBackgroundColor: "#1cf676",
                        pointHoverBorderColor: "#D1D1D1",
                        pointBorderWidth: 4,
                        pointHoverRadius: 2,
                        pointHoverBorderWidth: 1,
                        pointRadius: 3,
                        fill: false,
                        borderWidth: 3,
                        data: {!! json_encode($monthly_deposit) !!}
                    }]
                },
                options: {
                    legend: {
                        position: "bottom",
                        display: true,
                        labels: {
                            fontColor: '#928F8F'
                        }
                    },
                    scales: {
                        yAxes: [{
                            ticks: {
                                fontColor: "#928F8F",
                                fontStyle: "bold",
                                beginAtZero: true,
                                // maxTicksLimit: 5,
                                padding: 20
                            },
                            gridLines: {
                                drawTicks: false,
                                display: false
                            }
                        }],
                        xAxes: [{
                            gridLines: {
                                zeroLineColor: "transparent",
                                drawTicks: false,
                                display: false
                            },
                            ticks: {
                                padding: 20,
                                fontColor: "#928F8F",
                                fontStyle: "bold"
                            }
                        }]
                    }
                }
            });

            var ctx = document.getElementById('withdrawalChart').getContext("2d");
            var withdrawalChart = new Chart(ctx, {
                type: 'line',
                yaxisname: "Monthly Withdrawal",

                data: {
                    labels: ["Jan", "Feb", "Mar", "Apr", "May", "Jun", "Jul", "Aug", "Sep", "Oct", "Nov", "Dec"],
                    datasets: [{
                        label: "Monthly Withdrawal",
                        borderColor: "#f691be",
                        pointBorderColor: "#f691be",
                        pointBackgroundColor: "#f691be",
                        pointHoverBackgroundColor: "#f691be",
                        pointHoverBorderColor: "#D1D1D1",
                        pointBorderWidth: 4,
                        pointHoverRadius: 2,
                        pointHoverBorderWidth: 1,
                        pointRadius: 3,
                        fill: false,
                        borderWidth: 3,
                        data: {!! json_encode($monthly_withdrawal) !!}
                    }]
                },
                options: {
                    legend: {
                        position: "bottom",
                        display: true,
                        labels: {
                            fontColor: '#928F8F'
                        }
                    },
                    scales: {
                        yAxes: [{
                            ticks: {
                                fontColor: "#928F8F",
                                fontStyle: "bold",
                                beginAtZero: true,
                                // maxTicksLimit: 5,
                                // padding: 20,
                                // max: 1000
                            },
                            gridLines: {
                                drawTicks: false,
                                display: false
                            }
                        }],
                        xAxes: [{
                            gridLines: {
                                zeroLineColor: "transparent",
                                drawTicks: true,
                                display: false
                            },
                            ticks: {
                                // padding: 20,
                                fontColor: "#928F8F",
                                fontStyle: "bold",
                                // max: 10000,
                                autoSkip: false
                            }
                        }]
                    }
                }
            });


            $('#pending_withdrwall').DataTable({
                processing: true,
                serverSide: true,
                pageLength: 25,
                responsive: true,
                ajax: '{{route('adminPendingWithdrawals')}}',
                order: [8, 'desc'],
                autoWidth: false,
                language: {
                    paginate: {
                        next: 'Next &#8250;',
                        previous: '&#8249; Previous'
                    }
                },
                columns: [
                    {"data": "address_type"},
                    {"data": "sender"},
                    {"data": "address"},
                    {"data": "receiver"},
                    {"data": "amount"},
                    {"data": "coin_type"},
                    {"data": "fees"},
                    {"data": "transaction_hash"},
                    {"data": "updated_at"},
                    {"data": "actions"}
                ]
            });
        })(jQuery)
    </script>
@endsection
