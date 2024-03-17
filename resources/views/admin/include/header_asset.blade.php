<!-- Required meta tags -->
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
<meta http-equiv="x-ua-compatible" content="ie=edge">
<meta name="description" content="@if(isset(settings()['landing_title'])) {{settings()['landing_title'] }} @else {{__('TradexPro exchange trade Coin Easily')}} @endif">
<meta name="viewport" content="width=device-width, initial-scale=1">
<meta property="og:type" content="article" />
<meta property="og:title" content="{{allsetting('app_title')}}"/>
<meta property="og:image" content="{{landingPageImage('logo','images/logo.svg')}}">
<meta property="og:site_name" content="{{settings('app_title')}}"/>
<meta property="og:url" content="{{url()->current()}}"/>
<meta itemprop="image" content="{{landingPageImage('logo','images/logo.svg')}}" />
<!-- Bootstrap CSS -->
<link rel="stylesheet" href="{{asset('assets/common/css/bootstrap.min.css')}}">
<!-- metismenu CSS -->
<link rel="stylesheet" href="{{asset('assets/common/css/metisMenu.min.css')}}">
<!-- fontawesome CSS -->
<link rel="stylesheet" href="{{asset('assets/common/css/font-awesome.min.css')}}">
{{--for toast message--}}
<link href="{{asset('assets/common/toast/vanillatoasts.css')}}" rel="stylesheet" >
<!-- Datatable CSS -->
<link rel="stylesheet" href="{{asset('assets/common/css/datatable/datatables.min.css')}}">
<link rel="stylesheet" href="{{asset('assets/common/css/datatable/dataTables.bootstrap.min.css')}}">
<link rel="stylesheet" href="{{asset('assets/common/css/datatable/dataTables.jqueryui.min.css')}}">
<link rel="stylesheet" href="{{asset('assets/common/css/datatable/dataTables.responsive.css')}}">
<link rel="stylesheet" href="{{asset('assets/common/css/datatable/jquery.dataTables.min.css')}}">
<link rel="stylesheet" href="{{asset('assets/common/css/css-circular-prog-bar.css')}}">
{{-- datepicker --}}
<link rel="stylesheet" href="{{asset('assets/common/datepicker/css/bootstrap-datepicker.min.css')}}">
{{--    for search with tag--}}
<link href="{{asset('assets/common/multiselect/tokenize2.css')}}" rel="stylesheet">
<!-- select -->
<link rel="stylesheet" href="{{asset('assets/common/multiselect/bootstrap-select.min.css')}}">

{{--    dropify css  --}}
<link rel="stylesheet" href="{{asset('assets/common/dropify/dropify.css')}}">
{{-- summernote --}}
<link rel="stylesheet" href="{{asset('assets/common/summernote/summernote.min.css')}}">
<!-- Style CSS -->
<link rel="stylesheet" href="{{asset('assets/admin/style.css')}}">
<!-- Responsive CSS -->
<link rel="stylesheet" href="{{asset('assets/admin/css/responsive.css')}}">
@yield('style')
<title>@yield('title')</title>
<!-- Favicon and Touch Icons -->
<link rel="shortcut icon" href="{{landingPageImage('favicon','images/fav.png')}}">

<link rel="stylesheet" href="{{asset('assets/common/tags-input/bootstrap-tagsinput.css')}}" />




