@extends('header')

@section('content')

<div class="row">
  <div class="col-md-4">  
    <div class="panel panel-default">
      <div class="panel-body">
        <img src="{{ asset('images/totalincome.png') }}" class="in-image"/>  
        <div class="in-bold">
          {{ $totalIncome }}
        </div>
        <div class="in-thin">
          {{ trans('texts.in_total_revenue') }}
        </div>
      </div>
    </div>
  </div>
  <div class="col-md-4">
    <div class="panel panel-default">
      <div class="panel-body">
        <img src="{{ asset('images/clients.png') }}" class="in-image"/>  
        <div class="in-bold">
          {{ $billedClients }}
        </div>
        <div class="in-thin">
          {{ Utils::pluralize('billed_client', $billedClients) }}
        </div>
      </div>
    </div>
  </div>
  <div class="col-md-4">
    <div class="panel panel-default">
      <div class="panel-body">
        <img src="{{ asset('images/totalinvoices.png') }}" class="in-image"/>  
        <div class="in-bold">
          {{ $invoicesSent }}
        </div>
        <div class="in-thin">
          {{ Utils::pluralize('invoice', $invoicesSent) }}<br>emitidas          
        </div>
      </div>
    </div>
  </div>
</div>


<p>&nbsp;</p>

<div class="row">
  <div class="col-md-6">  
    <div class="panel panel-default dashboard" style="min-height:320px">
      <div class="panel-heading" style="background-color:#0b4d78">
        <h3 class="panel-title in-bold-white">
          <i class="glyphicon glyphicon-exclamation-sign"></i> {{ trans('texts.notifications') }}
        </h3>
      </div>
      <ul class="panel-body list-group">
      @foreach ($activities as $activity)
        <li class="list-group-item">
          <span style="color:#888;font-style:italic">{{ Utils::timestampToDateString(strtotime($activity->created_at)) }}:</span>
          {{ Utils::decodeActivity($activity->message) }}
        </li>
      @endforeach
      </ul>
    </div>  
  </div>
  <div class="col-md-3">
    <div class="active-clients">      
      <div class="in-bold in-white" style="font-size:42px">{{ $activeClients }}</div>
      <div class="in-thin in-white">{{ Utils::pluralize('active_client', $activeClients) }}</div>
    </div>
      </div>
    <div class="col-md-3">
    <div class="average-invoice">  
      <div><b>{{ trans('texts.average_invoice') }}</b></div>
      <div class="in-bold in-white" style="font-size:42px">{{ $invoiceAvg }}</div>
    </div>
      
  </div> 
</div>
@stop