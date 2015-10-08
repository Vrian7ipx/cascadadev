@extends('accounts.nav')

@section('content') 
  @parent

  {{ Former::open($url)->method($method)->addClass('col-md-12 warn-on-exit')->rules(array(
      'email' => 'required|email',
      'first_name' => 'required',
      'last_name' => 'required',
      'username' => 'required'
  )); }}

  {{ Former::legend($title) }}

  @if ($user)
    {{ Former::populate($user) }}    
  @endif
  <div class="row">
    <div class="col-md-6">

      {{ Former::text('first_name') }}
      {{ Former::text('last_name') }}
      {{ Former::text('email') }}

      
      {{ Former::text('phone') }}

    </div>
    <div class="col-md-6">
<?php     
     if($allgroups)
      {
          $array = json_decode($allgroups, true);
          $i=0;
          foreach ($array as $arrays) { 
            $gruposlist[$i]= $arrays['name'];
            $i++;

          }
      }
      else
      {
        $gruposlist = array();

      }

      
        

      if($user)
      {
          $gruposlist2 = explode(",", $user->groups);
      }
      else
      {
        $gruposlist2 = array();
      }
      
      $lista = array();
      ?>
        <script type="text/javascript">
            items =[];
            i = 0;
        </script>              
          <?php
      foreach ($gruposlist2 as  $key => $grup)
      {
          ?>
        <script type="text/javascript">
            items[i] = '{{ $grup }}';
            i = i +1;
        </script>              
          <?php
      }
      


?>
      {{ Former::text('username')->label('carnet de Identidad') }}

      {{ Former::legend('Tipo de Usuario') }}

      {{ Former::checkbox('facturador')->label(' ')->text('facturador')->data_bind("checked: displayAdvancedOptions") }}
     
      {{ Former::legend('Sucursal')->data_bind("fadeVisible: displayAdvancedOptions") }}    

      {{ Former::select('branch_id')->label(' ')->addOption('','')
      ->data_bind("fadeVisible: displayAdvancedOptions")->fromQuery($branches, 'name', 'id') }}

      {{ Former::legend('Tipo de Precio')->data_bind("fadeVisible: displayAdvancedOptions") }}    

      {{ Former::select('price_type_id')->label(' ')->addOption('','')
      ->data_bind("fadeVisible: displayAdvancedOptions")->fromQuery($price_types, 'name', 'id') }}

      {{ Former::legend('Grupos Asignados')->data_bind("fadeVisible: displayAdvancedOptions") }}    

        <div class="row" data-bind="fadeVisible: displayAdvancedOptions">

          <div class="col-md-4">
          </div>
          
          <div class="col-md-8">
            
            <select class="select_multiple" multiple="multiple" style="width:100%" name='groups[]'>                
                @foreach ($allgroups as $gru)		  	
                <option value="{{ $gru->id }}">{{ $gru->name }}</option>
		@endforeach                 
              </select> 
          </div>
        </div>

    </div>
       

  </div>

 

  <script type="text/javascript">

    $(".select_multiple").select2();
   console.log('itesm->'+items);
    $(".select_multiple").val(items).trigger("change");

  var PlanetsModel = function() {
      
      this.displayAdvancedOptions = ko.observable({{ $b ? 'true' : 'false' }});
   
      // Animation callbacks for the planets list
      this.showPlanetElement = function(elem) { if (elem.nodeType === 1) $(elem).hide().slideDown() }
      this.hidePlanetElement = function(elem) { if (elem.nodeType === 1) $(elem).slideUp(function() { $(elem).remove(); }) }
  };
   
  // Here's a custom Knockout binding that makes elements shown/hidden via jQuery's fadeIn()/fadeOut() methods
  // Could be stored in a separate utility library
  ko.bindingHandlers.fadeVisible = {
      init: function(element, valueAccessor) {
          // Initially set the element to be instantly visible/hidden depending on the value
          var value = valueAccessor();
          $(element).toggle(ko.utils.unwrapObservable(value)); // Use "unwrapObservable" so we can handle values that may or may not be observable
      },
      update: function(element, valueAccessor) {
          // Whenever the value subsequently changes, slowly fade the element in or out
          var value = valueAccessor();
          ko.utils.unwrapObservable(value) ? $(element).fadeIn() : $(element).fadeOut();
      }
  };
   
  ko.applyBindings(new PlanetsModel());


  </script>


      {{ Former::actions( 
          Button::lg_success_submit(trans($user && $user->confirmed ? 'texts.save' : 'texts.send_invite'))->append_with_icon($user && $user->confirmed ? 'floppy-disk' : 'send'),
          Button::lg_default_link('company/advanced_settings/user_management', 'Cancel')->append_with_icon('remove-circle')      
      ) }}


 {{ Former::close() }}

@stop