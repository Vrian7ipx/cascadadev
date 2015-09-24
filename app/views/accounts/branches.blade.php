@extends('accounts.nav')

@section('content') 
  @parent

  {{ Former::legend('Gestión de Sucursales') }}

  {{ Button::success_link(URL::to('branches/create'), trans("texts.create_branch"), array('class' => 'pull-right'))->append_with_icon('plus-sign') }} 

  {{ Datatable::table()   
      ->addColumn(
        trans('texts.name'),
        trans('texts.zona_barrio'),
        trans('texts.direccion'),
        trans('texts.action'))
      ->setUrl(url('api/branches/'))      
      ->setOptions('sPaginationType', 'bootstrap')
      ->setOptions('bFilter', false)      
      ->setOptions('bAutoWidth', false)      
      ->setOptions('aoColumns', [[ "sWidth"=> "30%" ], [ "sWidth"=> "25%" ], ["sWidth"=> "30%"], ["sWidth"=> "15%" ]])      
      ->setOptions('aoColumnDefs', [['bSortable'=>false, 'aTargets'=>[3]]])
      ->render('datatable') }}

  <script>
  window.onDatatableReady = function() {        
    $('tbody tr').mouseover(function() {
      $(this).closest('tr').find('.tr-action').css('visibility','visible');
    }).mouseout(function() {
      $dropdown = $(this).closest('tr').find('.tr-action');
      if (!$dropdown.hasClass('open')) {
        $dropdown.css('visibility','hidden');
      }     
    });
  } 
  </script>  


@stop