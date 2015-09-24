@extends('accounts.nav')

@section('content') 
  @parent

<div class="row">

  {{ Former::open($url)->method($method)->addClass('col-md-8 col-md-offset-2 warn-on-exit') }}


  {{ Former::legend($title) }}

  @if ($product)
    {{ Former::populate($product) }}
    {{ Former::populateField('cost', number_format($product->cost, 2)) }}
  @endif

  <div class="row">
      <div class="col-md-6">

      {{ Former::text('product_key')->label('texts.product_cod') }}
      {{ Former::textarea('notes')->label('texts.notes')->data_bind("value: wrapped_notes, valueUpdate: 'afterkeydown'") }}

      {{ Former::text('units')->label('texts.units') }}
      {{ Former::text('cc')->label('texts.cc') }}

      </div>
    <div class="col-md-6">
      {{ Former::legend('prices') }}

      {{ Former::text('price1')->label('texts.price1') }}
      {{ Former::text('price2')->label('texts.price2') }}
      {{ Former::text('price3')->label('texts.price3') }}

    </div>

  </div>

  {{ Former::radios('pack_types')->label('texts.pack_types')
         ->radios(array(
           'Vidrio' => array('name' => 'pack_types', 'value' => 'V'),
           'Plástico' => array('name' => 'pack_types', 'value' => 'P'),
       )) }}

  {{ Former::checkbox('ice')->label('texts.ice') }}

  {{ Former::actions( 
      Button::lg_success_submit(trans('texts.save'))->append_with_icon('floppy-disk'),
      Button::lg_default_link('company/products', 'Cancelar')->append_with_icon('remove-circle')      
  ) }}

  {{ Former::close() }}

  <script type="text/javascript">

  function ViewModel(data) {
    var self = this;
    @if ($product)
      self.notes = ko.observable(wordWrapText('{{ str_replace(["\r\n","\r","\n"], '\n', addslashes($product->notes)) }}', 300));
    @else
      self.notes = ko.observable('');
    @endif
    
    self.wrapped_notes = ko.computed({
      read: function() {
        return self.notes();
      },
      write: function(value) {
        value = wordWrapText(value, 235);
        self.notes(value);
      },
      owner: this
    });
  }

  window.model = new ViewModel();
  ko.applyBindings(model);  

  </script>

@stop