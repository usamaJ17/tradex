@extends('admin.staking.layouts.master',['menu'=>'currency_deposit', 'sub_menu'=>'bank_list'])
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

    <!-- User Management -->
    <div class="user-management">
        <div class="row">

            <div class="col-xl-4 col-md-6 col-12 mb-4">
                <div class="card status-card status-card-bg-average">
                    <div class="card-body py-0">
                        <div class="status-card-inner">
                            <div class="content">
                                <p>{{__('Total Investment')}}</p>
                                <h3>{{ $total_investment ?? 0 }}</h3>
                                <a href="" class=" mt-3 btn btn-sm btn-warning">{{__("Show More")}}</a>
                            </div>
                            <div class="icon">
                                <img src="{{asset('assets/admin/images/status-icons/funds.svg')}}" class="img-fluid" alt="">
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-xl-4 col-md-6 col-12 mb-4">
                <div class="card status-card status-card-bg-average">
                    <div class="card-body py-0">
                        <div class="status-card-inner">
                            <div class="content">
                                <p>{{__('Total Disputed Investment')}}</p>
                                <h3>{{ $total_return_investment ?? 0 }}</h3>
                                <a href="" class=" mt-3 btn btn-sm btn-warning">{{__("Show More")}}</a>
                            </div>
                            <div class="icon">
                                <img src="{{asset('assets/admin/images/status-icons/funds.svg')}}" class="img-fluid" alt="">
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-xl-4 col-md-6 col-12 mb-4">
                <div class="card status-card status-card-bg-average">
                    <div class="card-body py-0">
                        <div class="status-card-inner">
                            <div class="content">
                                <p>{{__('Total Disputable Investment')}}</p>
                                <h3>{{ isset($total_investment) && isset($total_return_investment) ?$total_investment - $total_return_investment : 0}}</h3>
                                <a href="" class=" mt-3 btn btn-sm btn-warning">{{__("Show More")}}</a>
                            </div>
                            <div class="icon">
                                <img src="{{asset('assets/admin/images/status-icons/funds.svg')}}" class="img-fluid" alt="">
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-xl-4 col-md-6 col-12 mb-4">
                <div class="card status-card status-card-bg-average">
                    <div class="card-body py-0">
                        <div class="status-card-inner">
                            <div class="content">
                                <p>{{__('Total Investment Bonus')}}</p>
                                <h3>{{ $total_investment_bonus ?? 0 }}</h3>
                                <a href="" class=" mt-3 btn btn-sm btn-warning">{{__("Show More")}}</a>
                            </div>
                            <div class="icon">
                                <img src="{{asset('assets/admin/images/status-icons/funds.svg')}}" class="img-fluid" alt="">
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-xl-4 col-md-6 col-12 mb-4">
                <div class="card status-card status-card-bg-average">
                    <div class="card-body py-0">
                        <div class="status-card-inner">
                            <div class="content">
                                <p>{{__('Total Disputed Bonus')}}</p>
                                <h3>{{ $total_given_bonus ?? 0 }}</h3>
                                <a href="" class=" mt-3 btn btn-sm btn-warning">{{__("Show More")}}</a>
                            </div>
                            <div class="icon">
                                <img src="{{asset('assets/admin/images/status-icons/funds.svg')}}" class="img-fluid" alt="">
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-xl-4 col-md-6 col-12 mb-4">
                <div class="card status-card status-card-bg-average">
                    <div class="card-body py-0">
                        <div class="status-card-inner">
                            <div class="content">
                                <p>{{__('Total Disputable Bonus')}}</p>
                                <h3>{{ isset($total_investment_bonus) && isset($total_given_bonus) ?$total_investment_bonus - $total_given_bonus : 0 }}</h3>
                                <a href="" class=" mt-3 btn btn-sm btn-warning">{{__("Show More")}}</a>
                            </div>
                            <div class="icon">
                                <img src="{{asset('assets/admin/images/status-icons/funds.svg')}}" class="img-fluid" alt="">
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
        </div>
    </div>
    <!-- /User Management -->

@endsection
@section('script')
    
@endsection
