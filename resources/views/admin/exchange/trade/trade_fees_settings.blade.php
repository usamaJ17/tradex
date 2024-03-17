@extends('admin.master',['menu'=>'setting', 'sub_menu'=>'trade_fees_settings'])
@section('title', isset($title) ? $title : __('Trade Fees Settings'))
@section('style')
@endsection
@section('content')
    <!-- breadcrumb -->
    <div class="custom-breadcrumb">
        <div class="row">
            <div class="col-9">
                <ul>
                    <li>{{__('Trade')}}</li>
                    <li class="active-item">{{ __('Trade Fees Settings') }}</li>
                </ul>
            </div>
        </div>
    </div>
    <!-- /breadcrumb -->

    <!-- User Management -->
    <div class="user-management">
        <div class="row">
            <div class="col-12">
                <div class="card trade-card">
                    <div class="card-body">
                        <p class="text-danger"><b>{{__('N.B: Trade limit value must be from smaller to greater')}}</b>
                        </p>
                        {{Form::open(['route'=>'tradeFeesSettingSave'])}}
                        <div id="trade-limit-form" class="profile-info-form">
                            @foreach($settings as $key => $setting)
                                <div class="row">
                                    @foreach($setting as $filed => $value)
                                        @php
                                            $index = explode('_', $filed)[0];
                                        @endphp
                                        <div class="col-md-4">
                                            <div class="form-group">
                                                <label class="form-label">{{ strtoupper(preg_replace('/[0-9]+/', '', str_replace('_', ' ', $filed))) }}</label>
                                                <input type="text"
                                                       class="form-control {{ $index == 'trade' ? 'trade-limits' : '' }}"
                                                       autocomplete="off"
                                                       name="{{$filed}}"  value="{{$value}}" >
                                            </div>
                                        </div>
                                    @endforeach
                                    @if ($filed != 'taker_1')
                                        <div class="col-md-3">
                                            <div class="form-group">
                                                <button type="button" data-id="{{$key}}"
                                                        class="btn btn-danger remove-limit"
                                                        style="margin-top: 35px;"><i class='fa fa-trash'></i></button>
                                            </div>
                                        </div>
                                    @endif
                                </div>
                            @endforeach
                        </div>
                        <div class="row">
                            <div class="col-sm-6 col-6">
                                <button type="submit" class="btn theme-btn" id="submit-form">{{__('Update')}}</button>
                            </div>
                            <div class="col-sm-6 col-6">
                                <button type="button" class="btn theme-btn" id="add-new"
                                        style="float: right">{{__('Add New')}}</button>
                            </div>
                        </div>
                        {{Form::close()}}
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('script')
    <script>
        (function($) {
            "use strict";
            var limits = '{{ !empty($settings) ? max(array_keys($settings)) : 0 }}';
            $("#add-new").on('click', function () {
                limits++;
                $("#trade-limit-form").append(
                    "                                   <div class=\"row\">\n" +
                    "                                        <div class=\"col-md-3\">\n" +
                    "                                            <div class=\"form-group\">\n" +
                    "                                                <label class=\"form-label trade-limits\">{{ strtoupper(__('Trade Limit')) }}</label>\n" +
                    "                                                <input type=\"text\" class=\"form-control\" autocomplete=\"off\" name=\"trade_limit_" + limits + "\"" +
                    "                                            </div>\n" +
                    "                                        </div>\n" +
                    "                                        </div>\n" +
                    "                                        <div class=\"col-md-3\">\n" +
                    "                                            <div class=\"form-group\">\n" +
                    "                                                <label class=\"form-label\">{{ strtoupper(__('Maker')) }}</label>\n" +
                    "                                                <input type=\"text\" class=\"form-control\" autocomplete=\"off\" name=\"maker_" + limits + "\"" +
                    "                                            </div>\n" +
                    "                                        </div>\n" +
                    "                                        </div>\n" +
                    "                                        <div class=\"col-md-3\">\n" +
                    "                                            <div class=\"form-group\">\n" +
                    "                                                <label class=\"form-label\">{{ strtoupper(__('Taker')) }}</label>\n" +
                    "                                                <input type=\"text\" class=\"form-control\" autocomplete=\"off\" name=\"taker_" + limits + "\"" +
                    "                                            </div>\n" +
                    "                                        </div>\n" +
                    "                                        </div>\n" +
                    "                                    <div class=\"col-md-3\">\n" +
                    "                                        <div class=\"form-group\">\n" +
                    "                                            <button type=\"button\" data-id=\"" + limits + "\" class=\"btn btn-danger remove-limit\" style=\"margin-top: 36px;\"><i class='fa fa-trash'></i></button>\n" +
                    "                                        </div>\n" +
                    "                                    </div>\n" +
                    "                                    </div>\n" +
                    "                                </div>");
            });

            $(document).on('click', '.remove-limit', function () {
                var row = $(this).closest('.row');
                $.ajax({
                    url: "{{route('removeTradeLimit')}}",
                    type: 'POST',
                    data: {
                        _token: '{{csrf_token()}}',
                        id: $(this).data('id')
                    },
                    success: function () {
                        row.remove();
                    }
                });
            });

            $(document).on('keyup', '.trade-limits', function () {
                var previousValue = 0;
                var disableButton = false;
                $('.trade-limits').each(function () {
                    if (parseFloat(previousValue) > parseFloat($(this).val())) {
                        $(this).css("border-color", "rgba(255, 0, 0, 0.3)");
                        disableButton = true;
                    } else {
                        $(this).css("border-color", "rgba(185, 173, 232, 0.3)");
                    }
                    previousValue = $(this).val();
                });
                $("#submit-form").prop("disabled", disableButton);
            });
        })(jQuery)
    </script>
@endsection
