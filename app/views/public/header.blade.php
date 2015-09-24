@extends('master')

@section('head')    
<meta name="csrf-token" content="<?= csrf_token() ?>">
<link href="{{ asset('built.public.css') }}" rel="stylesheet" type="text/css"/>

<script src="//ajax.googleapis.com/ajax/libs/jquery/1.11.0/jquery.min.js"></script>

<script src="{{ asset('js/simpleexpand.js') }}" type="text/javascript"></script>
<script src="{{ asset('js/valign.js') }}" type="text/javascript"></script>
<script src="{{ asset('js/bootstrap.min.js') }}" type="text/javascript"></script>

<style>
  .hero {
    background-image: url({{ asset('/images/hero-bg-1.jpg') }});
  }
  .hero-about {
    background-image: url({{ asset('/images/hero-bg-3.jpg') }});
  }
  .hero-plans {
    background-image: url({{ asset('/images/hero-bg-plans.jpg') }});
  }
  .hero-contact {
    background-image: url({{ asset('/images/hero-bg-contact.jpg') }});
  }
  .hero-features {
    background-image: url({{ asset('/images/hero-bg-3.jpg') }});
  }
  .hero-secure {
    background-image: url({{ asset('/images/hero-bg-secure-pay.jpg') }});
  }
 .hero-faq {
    background-image: url({{ asset('/images/hero-bg-faq.jpg') }});
  }   
  .hero-testi {
    background-image: url({{ asset('/images/hero-bg-testi.jpg') }});
  }   


</style>

@stop

@section('body')


{{ Form::open(array('url' => 'get_started', 'id' => 'startForm')) }}
{{ Form::hidden('guest_key') }}
{{ Form::close() }}

<script>
  if (isStorageSupported()) {
    $('[name="guest_key"]').val(localStorage.getItem('guest_key'));          
  }

  @if (isset($invoiceNow) && $invoiceNow)
  getStarted();
  @endif

  function isStorageSupported() {
    if ('localStorage' in window && window['localStorage'] !== null) {
      var storage = window.localStorage;
    } else {
      return false;
    }
    var testKey = 'test';
    try {
      storage.setItem(testKey, '1');
      storage.removeItem(testKey);
      return true;
    } catch (error) {
      return false;
    }    
  }

  function getStarted() {
    $('#startForm').submit();
    return false;
  }
</script>

<div style="background-color:#211f1f; width:100%">
<div class="container">   
  @if (Session::has('warning'))
    <div class="alert alert-warning">{{ Session::get('warning') }}</div>
  @endif

  @if (Session::has('message'))
    <div class="alert alert-info">{{ Session::get('message') }}</div>
  @endif

  @if (Session::has('error'))
    <div class="alert alert-danger">{{ Session::get('error') }}</div>
  @endif
</div>
</div>

@yield('content')   


<script type="text/javascript">
  jQuery(document).ready(function($) {   
   $('.valign').vAlign();  
 });
</script>


@stop