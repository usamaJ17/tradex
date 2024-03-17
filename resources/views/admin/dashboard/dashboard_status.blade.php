<div class="row">
    <div class="col-xl-4 col-md-6 col-12 mb-4">
        <div class="card status-card status-card-bg-read">
            <div class="card-body py-0">
                <div class="status-card-inner">
                    <div class="content">
                        <p>{{__('Total User')}}</p>
                        <h3>{{$total_user}}</h3>
                        <a href="{{ route('adminUsers') }}" class=" mt-3 btn btn-sm btn-warning">{{__("Show More")}}</a>
                    </div>
                    <div class="icon">
                        <img src="{{asset('assets/admin/images/status-icons/team.svg')}}" class="img-fluid" alt="">
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
                        <p>{{__('Total User Coin')}}</p>
                        <h3>{{number_format($total_coin,8)}} BTC</h3>
                        <a href="{{ route('adminUserCoinList') }}" class=" mt-3 btn btn-sm btn-warning">{{__("Show More")}}</a>
                    </div>
                    <div class="icon">
                        <img src="{{asset('assets/admin/images/status-icons/money.svg')}}" class="img-fluid" alt="">
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
                        <p>{{__('Total Earning')}}</p>
                        <h3>{{number_format($total_earning,8)}}</h3>
                        <a href="{{ route('adminEarningReport') }}" class=" mt-3 btn btn-sm btn-warning">{{__("Show More")}}</a>
                    </div>
                    <div class="icon">
                        <img src="{{asset('assets/admin/images/status-icons/funds.svg')}}" class="img-fluid" alt="">
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-xl-4 col-md-6 col-12 mb-4">
        <div class="card status-card status-card-bg-read">
            <div class="card-body py-0">
                <div class="status-card-inner">
                    <div class="content">
                        <p>{{__('Active Buy Order')}}</p>
                        <h3>{{$active_buy}}</h3>
                        <a href="{{ route('adminAllOrdersHistoryBuy') }}" class=" mt-3 btn btn-sm btn-warning">{{__("Show More")}}</a>
                    </div>
                    <div class="icon">
                        <img src="{{asset('assets/admin/images/status-icons/money.svg')}}" class="img-fluid" alt="">
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-xl-4 col-md-6 col-12 mb-4">
        <div class="card status-card status-card-bg-orange">
            <div class="card-body py-0">
                <div class="status-card-inner">
                    <div class="content">
                        <p>{{__('Active Sell Order')}}</p>
                        <h3>{{$active_sell}}</h3>
                        <a href="{{ route('adminAllOrdersHistorySell') }}" class=" mt-3 btn btn-sm btn-warning">{{__("Show More")}}</a>
                    </div>
                    <div class="icon">
                        <img src="{{asset('assets/admin/images/status-icons/money.svg')}}" class="img-fluid" alt="">
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-xl-4 col-md-6 col-12 mb-4">
        <div class="card status-card status-card-bg-orange">
            <div class="card-body py-0">
                <div class="status-card-inner">
                    <div class="content">
                        <p>{{__('Total Transaction')}}</p>
                        <h3>{{number_format($total_transaction,2)}}</h3>
                        <a href="{{ route('adminAllTransactionHistory') }}" class=" mt-3 btn btn-sm btn-warning">{{__("Show More")}}</a>
                    </div>
                    <div class="icon">
                        <img src="{{asset('assets/admin/images/status-icons/funds.svg')}}" class="img-fluid" alt="">
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
