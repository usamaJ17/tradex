<div class="header-bar">
    <div class="table-title">
        <h3>{{__('Future Trade enable/disable')}}</h3>
    </div>
</div>
<div class="profile-info-form">
    <form action="{{route('adminCookieSettingsSave')}}" method="post"
          enctype="multipart/form-data">
        @csrf
        <div class="row">
            <div class="col-lg-6 col-12 mt-20">
                <div class="form-group">
                    <label>{{__('Future Trade')}}</label>
                    <div class="cp-select-area">
                        <select name="enable_future_trade" class="form-control">
                            <option @if(isset($settings['enable_future_trade']) && $settings['enable_future_trade'] == STATUS_PENDING) selected @endif value="{{STATUS_PENDING}}">{{__("Disable")}}</option>
                            <option @if(isset($settings['enable_future_trade']) && $settings['enable_future_trade'] == STATUS_ACTIVE) selected @endif value="{{STATUS_ACTIVE}}">{{__("Enable")}}</option>
                        </select>
                    </div>
                </div>
            </div>
        </div>
        <div class="row">
            <div class="col-lg-2 col-12 mt-20">
                <button class="button-primary theme-btn">{{__('Update')}}</button>
            </div>
        </div>
    </form>
</div>
