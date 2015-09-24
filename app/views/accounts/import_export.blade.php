@extends('accounts.nav')
	<script src="{{ asset('js/compatibility.js') }}" type="text/javascript"></script>
@section('content')
	@parent



<div class="col-md-8" id="col_2">

  {{ Former::open('company/export')->addClass('col-md-9 col-md-offset-1') }}
  {{ Former::legend('Descargar libro de Ventas') }}
  {{ Former::text('invoice_date')->data_bind("datePicker: invoice_date, valueUpdate: 'afterkeydown',")
							->data_date_format('yyyy-mm')->append('<i class="glyphicon glyphicon-calendar" onclick="toggleDatePicker(\'invoice_date\')"></i>') }}
  {{ Former::actions( Button::lg_primary_submit(trans('texts.download'))->append_with_icon('download-alt') ) }}
  {{ Former::close() }}

</div>

<div class="col-md-8" id="col_2">

	{{ Former::open_for_files('company/import_map')->addClass('col-md-9 col-md-offset-1') }}
	{{ Former::legend('import_clients') }}
	{{ Former::file('file')->label(trans('texts.csv_file')) }}
	{{ Former::actions( Button::lg_info_submit(trans('texts.upload'))->append_with_icon('open') ) }}
	{{ Former::close() }}
</div>

<script type="text/javascript">

	$('#invoice_date').datepicker({
	    minViewMode: 1,
	    language: "es"
	});

</script>

@stop