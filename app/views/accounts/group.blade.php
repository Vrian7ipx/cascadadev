@extends('accounts.nav')

@section('content') 
  @parent

  {{ Former::open($url)->method($method)->addClass('col-md-12 warn-on-exit') }}


  {{ Former::legend($title) }}

  @if ($group)
    {{ Former::populate($group) }}
  @endif
  <div class="row">
    <div class="col-md-8">  

    {{ Former::legend('Datos del Grupo') }} 
    {{ Former::text('code')->label('Código') }}
    {{ Former::text('name')->label('Nombre') }}
    {{ Former::textarea('text')->label('Información adicional') }}

    </div>

  </div>


  {{ Former::actions( 
      Button::lg_success_submit(trans('texts.save'))->append_with_icon('floppy-disk'),
      Button::lg_default_link('company/groups', 'Cancelar')->append_with_icon('remove-circle')      
  ) }}

  {{ Former::close() }}

    <script type="text/javascript">

    $(function() {
      $('#country_id').combobox();
    });

  </script>

@stop