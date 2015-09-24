@extends('accounts.nav')

@section('content')	
	@parent
	
	<style type="text/css">

	#logo {
		padding-top: 6px;
	}

	</style>

	{{ Former::legend('Datos de la Empresa') }}

	{{ Former::open_for_files()->addClass('col-md-12 warn-on-exit')->rules(array(
  		'name' => 'required',
  		'email' => 'email|required',
  		'nit' => 'nit|required|Numeric',
	)) }}

	{{ Former::populate($account) }}
	{{ Former::populateField('first_name', $account->users()->first()->first_name) }}
	{{ Former::populateField('last_name', $account->users()->first()->last_name) }}
	{{ Former::populateField('email', $account->users()->first()->email) }}	
	{{ Former::populateField('phone', $account->users()->first()->phone) }}
	{{ Former::populateField('tax', $account->tax_rates()->first()->rate) }}


	<div class="row">
		<div class="col-md-5">

			{{ Former::legend('details') }}
			{{ Former::text('nit') }}
			{{ Former::text('name') }}
			{{-- Former::text('work_email') --}}
			{{-- Former::text('work_phone') --}}


			{{-- Former::file('logo')->max(2, 'MB')->accept('image')->inlineHelp(trans('texts.logo_help')) --}}

			@if (file_exists($account->getLogoPath()))
				<center>
					{{ HTML::image($account->getLogoPath(), "Logo") }} &nbsp;
					<a href="#" onclick="deleteLogo()">{{ trans('texts.remove_logo') }}</a>
				</center><br/>
			@endif

			{{-- Former::legend('address') --}}	
			{{-- Former::textarea('address1')->label('Zona/Barrio') --}}
			{{-- Former::textarea('address2')->label('Dirección') --}}
			{{-- Former::text('city') --}}
			{{-- Former::text('state') --}}
			{{-- Former::textarea('postal_code') --}}
			{{-- Former::select('country_id')->addOption('','')->label('Departamento')
				->fromQuery($countries, 'name', 'id') --}}

			{{ Former::legend('Impuesto ICE') }}
			{{ Former::text('tax') }}

		</div>
	
		<div class="col-md-5 col-md-offset-2">		

			{{ Former::legend('Administrador') }}
			{{ Former::text('first_name') }}
			{{ Former::text('last_name') }}
			{{ Former::text('email') }}
			{{ Former::text('phone') }}


		</div>
	</div>






	<center>
		{{ Button::lg_success_submit(trans('texts.save'))->append_with_icon('floppy-disk') }}
	</center>

	{{ Former::close() }}

	{{ Form::open(['url' => 'remove_logo', 'class' => 'removeLogoForm']) }}	
	{{ Form::close() }}


	<script type="text/javascript">

		$(function() {
			$('#country_id').combobox();
		});
		
		function deleteLogo() {
			if (confirm("{{ trans('texts.are_you_sure') }}")) {
				$('.removeLogoForm').submit();
			}
		}

	</script>

@stop