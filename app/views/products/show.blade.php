@extends('header')

@section('content') 
	
	
	@if (!$product->trashed())		
	<div class="pull-right">
		{{ Former::open('products/bulk')->addClass('mainForm') }}
		<div style="display:none">
			{{ Former::text('action') }}
			{{ Former::text('id')->value($product->public_id) }}
		</div>

		{{ DropdownButton::normal(trans('texts.option_product'),
			  Navigation::links(
			    [
			      [trans('texts.edit_product'), URL::to('products/' . $product->public_id . '/edit')],
			      [Navigation::DIVIDER],
			      [trans('texts.archive_product'), "javascript:onArchiveClick()"],
			      [trans('texts.delete_product'), "javascript:onDeleteClick()"],
			    ]
			  )
			, ['id'=>'normalDropDown'])->split(); }}	

	</div>
	@endif

	<div class="row">

		<div class="col-md-8">
			<table class="table" style="width:100%">
				<tr>
					<td><h3><strong>Nombre Producto </strong> : {{ $product->getDisplayName() }}</h3></td>				
				</tr>
			</table>

			
		</div>

	</div>

	<div class="row">

		<div class="col-md-3">
			<h3>{{ trans('texts.details') }}</h3>
			<p><strong>Código Nº </strong> : {{ $product->getProductKey() }}</p>
			<p><strong>Tipo de Envase </strong> : {{ $product->getPackTypes() }}</p>
			<p><strong>ICE </strong> : {{ $product->getIce() }}</p>
			<p><strong>Volumen CC </strong> : {{ $product->getCc() }}</p>

		</div>

		<div class="col-md-3">
			<h3>{{ trans('texts.prices') }}</h3>
			
		  	@foreach ($product->prices as $price)		  	
		  		
		  		{{ $price->getDetails() }}		 

		  	@endforeach			
		</div>


	</div>

	<p>&nbsp;</p>

	
	<script type="text/javascript">


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