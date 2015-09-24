@extends('accounts.nav')

@section('content') 
  @parent

  {{ Former::legend('Gestión de Agrupación de Clientes') }}

  {{ Button::success_link(URL::to('groups/create'), trans("texts.create_group"), array('class' => 'pull-right'))->append_with_icon('plus-sign') }} 

  {{ Datatable::table()   
      ->addColumn(
        trans('texts.code'),
        trans('texts.name'),
        trans('Datos Adicionales'),
        trans('texts.action'))
      ->setUrl(url('api/groups/'))      
      ->setOptions('sPaginationType', 'bootstrap')
      ->setOptions('bFilter', false)      
      ->setOptions('bAutoWidth', false)      
      ->setOptions('aoColumns', [[ "sWidth"=> "20%" ],[ "sWidth"=> "30%" ], [ "sWidth"=> "30%" ], ["sWidth"=> "20%" ]])      
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