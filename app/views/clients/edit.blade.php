@extends('header')


@section('onReady')
	$('input#name').focus();
@stop

@section('content')
<div class="row">

	{{ Former::open($url)->addClass('col-md-12 warn-on-exit')->method($method)->rules(array(
  		'nit' => 'nit|required|Numeric',  
  		'name' => 'required', 
  		'group_id' => 'required'
	)); }}

	@if ($client)
		{{ Former::populate($client) }}
	@endif

	<div class="row">
		<div class="col-md-6">


			{{ Former::legend('datos Cliente') }}

			{{ Former::text('nit')->label('NIT/CI')->data_bind("value: capitalizeLastName() ? '0' : nit")}}
			{{ Former::text('name')->label('Señor(es)')->data_bind("value: capitalizeLastName() ? 'Sin Nombre' : name") }}

			{{ Former::checkbox('Datos')->label('-')->text('Sin Datos para la factura')->data_bind("checked: capitalizeLastName, valueUpdate: 'afterkeydown'")
		 }}

			{{-- Former::text('website') --}}
			{{ Former::text('work_phone')->label('Teléfono/Celular') }}

			{{ Former::legend('address') }}

			{{ Former::select('zone_id')->addOption('','')->label('zona/Barrio')
				->fromQuery($zones, 'name', 'id') }}
			{{ Former::text('address1')->label('Otro') }}
			{{ Former::text('address2')->label('dirección') }}
			{{-- Former::text('city') --}}
			{{-- Former::text('state') --}}
			{{-- Former::text('postal_code') --}}
			{{ Former::select('country_id')->addOption('','')->label('Ciudad')
				->fromQuery($countries, 'name', 'id') }}


		</div>
		<div class="col-md-6">

			{{ Former::legend('contacts') }}
			<div data-bind='template: { foreach: contacts,
		                            beforeRemove: hideContact,
		                            afterAdd: showContact }'>
				{{ Former::hidden('public_id')->data_bind("value: public_id, valueUpdate: 'afterkeydown'") }}
				{{ Former::text('first_name')->data_bind("value: first_name, valueUpdate: 'afterkeydown'") }}
				{{ Former::text('last_name')->data_bind("value: last_name, valueUpdate: 'afterkeydown'") }}
				{{ Former::text('email')->data_bind('value: email, valueUpdate: \'afterkeydown\', attr: {id:\'email\'+$index()}') }}
				{{ Former::text('phone')->label('Celular')->data_bind("value: phone, valueUpdate: 'afterkeydown'") }}	

				<div class="form-group">
					<div class="col-lg-8 col-lg-offset-4 bold">
						<span class="redlink bold" data-bind="visible: $parent.contacts().length > 1">
							{{ link_to('#', 'Remover contacto -', array('data-bind'=>'click: $parent.removeContact')) }}
						</span>					
						<span data-bind="visible: $index() === ($parent.contacts().length - 1)" class="pull-right greenlink bold">
							{{ link_to('#', 'Añadir contacto +', array('onclick'=>'return addContact()')) }}
						</span>
					</div>
				</div>
			</div>

			{{ Former::legend('additional_info') }}
			{{ Former::select('group_id')->addOption('','')
				->fromQuery($groups, 'name', 'id') }}
			{{ Former::hidden('currency_id')->addOption('','')
				->fromQuery($currencies, 'name', 'id') }}
			{{ Former::select('business_type_id')->addOption('','')
				->fromQuery($business_types, 'name', 'id') }}
			{{ Former::textarea('private_notes') }}
			
					
			{{ Former::checkboxes('custom_value1')->label($customLabel1)
					->checkboxes('Lunes', 'Martes', 'Miércoles', 'Jueves', 'Viernes', 'Sábado', 'Domingo')
					->check(array('custom_value1_0' => $monday, 'custom_value1_1' => $tuesday, 'custom_value1_2' => $wednesday, 'custom_value1_3' => $thursday, 'custom_value1_4' => $friday, 'custom_value1_5' => $saturday, 'custom_value1_6' => $sunday)) }}
			
		</div>
	</div>


	{{ Former::hidden('data')->data_bind("value: ko.toJSON(model)") }}	

	<script type="text/javascript">

	$(function() {
		$('#zone_id').combobox();
		$('#country_id').combobox();
		$('#business_type_id').combobox();
		$('#group_id').combobox();

	});

	function ContactModel(data) {
		var self = this;
		self.public_id = ko.observable('');
		self.first_name = ko.observable('');
		self.last_name = ko.observable('');
		self.email = ko.observable('');
		self.phone = ko.observable('');

		if (data) {
			ko.mapping.fromJS(data, {}, this);			
		}		
	}

	function ContactsModel(data) {
		var self = this;
		self.contacts = ko.observableArray();
		self.capitalizeLastName = ko.observable(false);

		self.nit = ko.observable('');
		self.name = ko.observable('');


		self.mapping = {
		    'contacts': {
		    	create: function(options) {
		    		return new ContactModel(options.data);
		    	}
		    }
		}		

		if (data) {
			ko.mapping.fromJS(data, self.mapping, this);			
		} else {
			self.contacts.push(new ContactModel());
		}

	}

	window.model = new ContactsModel({{ $client }});

	model.showContact = function(elem) { if (elem.nodeType === 1) $(elem).hide().slideDown() }
	model.hideContact = function(elem) { if (elem.nodeType === 1) $(elem).slideUp(function() { $(elem).remove(); }) }


	ko.applyBindings(model);

	function addContact() {
		model.contacts.push(new ContactModel());
		return false;
	}

	model.removeContact = function() {
		model.contacts.remove(this);
	}


	</script>

	<center class="buttons">
		{{ Button::lg_primary_submit_success('Guardar')->append_with_icon('floppy-disk') }}
    {{ Button::lg_default_link('clients/' . ($client ? $client->public_id : ''), 'Cancelar')->append_with_icon('remove-circle'); }}
	</center>

	{{ Former::close() }}
</div>
@stop