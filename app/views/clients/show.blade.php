@extends('header')

@section('content') 
	
	
	@if (!$client->trashed())		
	<div class="pull-right">
		{{ Former::open('clients/bulk')->addClass('mainForm') }}
		<div style="display:none">
			{{ Former::text('action') }}
			{{ Former::text('id')->value($client->public_id) }}
		</div>

		{{ DropdownButton::normal(trans('texts.edit_client'),
			  Navigation::links(
			    [
			      [trans('texts.edit_client'), URL::to('clients/' . $client->public_id . '/edit')],
			      [Navigation::DIVIDER],
			      [trans('texts.archive_client'), "javascript:onArchiveClick()"],
			      [trans('texts.delete_client'), "javascript:onDeleteClick()"],
			    ]
			  )
			, ['id'=>'normalDropDown'])->split(); }}
@if (!Utils::isAdmin())
			{{ DropdownButton::primary('Crear Factura', Navigation::links($actionLinks), ['id'=>'primaryDropDown'])->split(); }}
	    {{ Former::close() }}		
@endif
	</div>
	@endif

	<div class="row">

		<div class="col-md-8">
			<table class="table" style="width:100%">
				<tr>
					<td><h3><strong>Razón Social </strong> : {{ $client->getDisplayName() }}</h3></td>				
				</tr>
			</table>

			<h4>&nbsp;&nbsp;&nbsp;&nbsp;<strong>NIT/CI</strong> : {{ $client->getNit() }}
			&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
			<strong>Código Nº </strong> : {{ $client->getCod() }}</h4>
		</div>

	</div>

	<div class="row">

		<div class="col-md-4">
			<h3>{{ trans('texts.details') }}</h3>
			<p>{{ $client->getBusinessType() }}</p>
			<p>{{ $client->getZone() }}</p>
		  	<p>{{ $client->getAddress() }}</p>
		  	<p>{{ $client->getCustomFields() }}</p>
		  	<p>{{ $client->getPhone() }}</p>
		  	<p>{{ $client->getNotes() }}</p>
		  	<p>{{ $client->getIndustry() }}</p>
		  	<p>{{ $client->getWebsite() }}</p>
		</div>

		<div class="col-md-4">
			<h3>{{ trans('texts.contacts') }}</h3>
		  	@foreach ($client->contacts as $contact)		  	
		  		{{ $contact->getDetails() }}		  	
		  	@endforeach			
		</div>

		<div class="col-md-4">
			<h3>{{ trans('texts.standing') }}
			<table class="table" style="width:300px">
				<tr>
					<td><small>{{ trans('texts.paid_to_date') }}</small></td>
					<td style="text-align: right">{{ Utils::formatMoney($client->balance, $client->currency_id); }}</td>
				</tr>
			</table>

			</h3>

		</div>
	</div>

	<p>&nbsp;</p>
	
	<ul class="nav nav-tabs nav-justified">
		{{ HTML::tab_link('#activity', trans('texts.activity'), true) }}
		{{ HTML::tab_link('#invoices', trans('texts.invoices')) }}		
	</ul>

	<div class="tab-content">

        <div class="tab-pane active" id="activity">

			{{ Datatable::table()		
		    	->addColumn(
		    		trans('texts.date'),
		    		trans('texts.message'))
		    	->setUrl(url('api/activities/'. $client->public_id))    	
		    	->setOptions('sPaginationType', 'bootstrap')
		    	->setOptions('bFilter', false)
		    	->setOptions('aaSorting', [['0', 'desc']])
		    	->render('datatable') }}

        </div>

		<div class="tab-pane" id="invoices">

			@if ($hasRecurringInvoices)
				{{ Datatable::table()		
			    	->addColumn(
			    		trans('texts.frequency_id'),
			    		trans('texts.start_date'),
			    		trans('texts.end_date'),
			    		trans('texts.invoice_total'))			    		
			    	->setUrl(url('api/recurring_invoices/' . $client->public_id))    	
			    	->setOptions('sPaginationType', 'bootstrap')
			    	->setOptions('bFilter', false)
			    	->setOptions('aaSorting', [['0', 'asc']])
			    	->render('datatable') }}
			@endif

			{{ Datatable::table()		
		    	->addColumn(
		    			trans('texts.invoice_number'),
		    			trans('texts.invoice_date'),
		    			trans('texts.invoice_total'),
		    			trans('texts.status'))
		    	->setUrl(url('api/invoices/' . $client->public_id))    	
		    	->setOptions('sPaginationType', 'bootstrap')
		    	->setOptions('bFilter', false)
		    	->setOptions('aaSorting', [['0', 'asc']])
		    	->render('datatable') }}
            
        </div>

    </div>
	
	<script type="text/javascript">

	$(function() {
		$('#normalDropDown > button:first').click(function() {
			window.location = '{{ URL::to('clients/' . $client->public_id . '/edit') }}';
		});
		$('#primaryDropDown > button:first').click(function() {
			window.location = '{{ URL::to('invoices/create/' . $client->public_id ) }}';
		});
	});

	function onArchiveClick() {
		$('#action').val('archive');
		$('.mainForm').submit();
	}

	function onDeleteClick() {
		if (confirm("{{ trans('texts.are_you_sure') }}")) {
			$('#action').val('delete');
			$('.mainForm').submit();
		}		
	}

	</script>

@stop