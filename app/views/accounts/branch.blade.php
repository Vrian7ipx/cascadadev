@extends('accounts.nav')

@section('content') 
  @parent

  {{ Former::open($url)->method($method)->addClass('col-md-12 warn-on-exit')->rules(array(
      'name' => 'required',
      'address1' => 'required',
      'address2' => 'required',
      'postal_code' => 'required',
      'city' => 'required',
      'state' => 'required',
      'activity_pri' => 'required',
      'number_autho' => 'required',
      'deadline' => 'required',
      'key_dosage' => 'required',
      'law' => 'required'
  )); }}


  {{ Former::legend($title) }}

  @if ($branch)
    {{ Former::populate($branch) }}
  @endif
  <div class="row">
    <div class="col-md-6">  

    {{ Former::text('name')->label('texts.name') }}

    {{ Former::legend('address') }} 
    {{ Former::textarea('address1')->label('Zona/Barrio') }}
    {{ Former::textarea('address2')->label('Dirección') }}
    {{ Former::text('postal_code')->label('teléfonos') }}
    {{ Former::text('city')->label('departamento') }}
    {{ Former::text('state')->label('municipio') }}

    {{-- Former::select('country_id')->addOption('','')->label('Departamento')
          ->fromQuery($countries, 'name', 'id') --}}
    </div>

    <div class="col-md-6">    

      {{ Former::legend('Actividades') }}

      {{ Former::textarea('activity_pri')->label('actividad Principal') }}
      {{ Former::textarea('activity_sec1')->label('actividad Secundaria') }}

      {{ Former::legend('dosificación') }}

      {{ Former::text('number_autho')->label('número de autorización') }}
      {{-- Former::text('deadline')->label('fecha límite')--}}           
      {{--Former::label('fecha límite')--}}      
      <div class="form-group required">
      <div class='control-label col-md-4'>
          <b>Fecha limite </b>
      </div>
      <div class='col-md-8'>
          <input id="deadline" class="form-control" type="text" name="deadline" />
      </div>
      </div>      
      
      
      {{ Former::textarea('key_dosage')->label('llave dosificación')->rows(3)}}

      {{ Former::legend('Leyenda') }}

      {{ Former::textarea('law')->label('leyenda Genérica') }}

    
    </div>
  </div>


  {{ Former::actions( 
      Button::lg_success_submit(trans('texts.save'))->append_with_icon('floppy-disk'),
      Button::lg_default_link('company/branches', 'Cancelar')->append_with_icon('remove-circle')      
  ) }}

  {{ Former::close() }}

    <script type="text/javascript">
        $('#deadline').datepicker();        
        var queryDate = '{{ $branch->deadline}}',
    dateParts = queryDate.match(/(\d+)/g)
    realDate = new Date(dateParts[0], dateParts[1] - 1, dateParts[2]);  
                                    // months are 0-based!

$('#deadline').datepicker({ dateFormat: 'dd-mm-yy' }); // format to show
$('#deadline').datepicker('setDate', realDate);
        
    $(function() {
      $('#country_id').combobox();
    });

  </script>

@stop