@extends('header')


@section('onReady')
	$('input#notes').focus();
@stop

@section('content')
<div class="row">

	{{ Former::open($url)->addClass('col-md-12 warn-on-exit')->method($method)->rules(array(
  		'cost' => 'cost|required|Numeric', 
  	)); }}

	@if ($product)
    	{{ Former::populate($product) }}
	@endif

	<div class="row">
		<div class="col-md-6">

		{{ Former::legend('datos Producto') }}
      	{{ Former::text('product_key')->label('texts.product_cod') }}
      	{{ Former::textarea('notes')->label('texts.notes') }}

      	{{ Former::text('units')->label('texts.units') }}
      	
      	{{ Former::text('cc')->label('texts.cc') }}

      	{{ Former::radios('pack_types')->label('texts.pack_types')
         	->radios(array(
            'Vidrio' => array('name' => 'pack_types', 'value' => 'V'),
            'Plástico' => array('name' => 'pack_types', 'value' => 'P'),
     	  	)) }}

     	{{ Former::radios('ice')->label('Impuesto')
         	->radios(array(
            'Con ICE' => array('name' => 'ice', 'value' => '1'),
            'Sin ICE' => array('name' => 'ice', 'value' => '0'),
     	  	)) }}

  		{{-- Former::checkbox('ice')->label('texts.ice') --}}


		</div>
		<div class="col-md-6">

			{{ Former::legend('prices') }}
			<div data-bind='template: { foreach: prices,
		                            afterAdd: showPrice }'>

				{{ Former::hidden('public_id')->data_bind("value: public_id, valueUpdate: 'afterkeydown'") }}
				<span data-bind="text: $index() == 0 ? 'ciudad':''"> </span>
				<span data-bind="text: $index() == 1 ? 'Viajero':''"> </span>
				<span data-bind="text: $index() == 2 ? 'Otro':''"> </span>
				{{ Former::text('cost')->data_bind("value: cost, valueUpdate: 'afterkeydown'")->label(' ') }}

				<div class="form-group">
					<div class="col-lg-8 col-lg-offset-4 bold">				
						<span data-bind="visible: $parent.prices().length < 3" class="pull-right greenlink bold">
							{{ link_to('#', 'Añadir precio +', array('onclick'=>'return addPrice()')) }}
						</span>
					</div>
				</div>
			</div>


		</div>
	</div>
	
	{{ Former::hidden('data')->data_bind("value: ko.toJSON(model)") }}	

	<script type="text/javascript">

	function PriceModel(data) {
		var self = this;
		self.public_id = ko.observable('');
		self.cost = ko.observable('');


		if (data) {
			ko.mapping.fromJS(data, {}, this);			
		}		
	}

	function PricesModel(data) {
		var self = this;
		self.prices = ko.observableArray();

		self.mapping = {
		    'prices': {
		    	create: function(options) {
		    		return new PriceModel(options.data);
		    	}
		    }
		}
	

		if (data) {
			ko.mapping.fromJS(data, self.mapping, this);			
		} else {
			self.prices.push(new PriceModel());
		}

	}

	 window.model = new PricesModel({{ $product }});

	model.showPrice = function(elem) { if (elem.nodeType === 1) $(elem).hide().slideDown() }

	 ko.applyBindings(model);

	function addPrice() {
		model.prices.push(new PriceModel());
		return false;
	}

	</script>

	<center class="buttons">
		{{ Button::lg_primary_submit_success('Guardar')->append_with_icon('floppy-disk') }}
    {{ Button::lg_default_link('products/' . ($product ? $product->public_id : ''), 'Cancelar')->append_with_icon('remove-circle'); }}
	</center>

	{{ Former::close() }}
</div>
@stop