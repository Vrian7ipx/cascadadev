@extends('header')
@section('content') 
    <div>
        <table>
            <tr>        
                <td>
                <h3><b>C&oacute;digo:</b></h3>
                </td>
                <td>
                  <h3>  {{ $group->code }}</h3>
                </td>           
            </tr>
            <tr>        
                <td>
                <h3><b>Nombre:</b></h3>
                </td>
                <td>
                  <h3> {{ $group->name }}</h3>
                </td>           
            </tr>
            <tr>        
                <td>
                    <h3><b>Datos Adicionales: &nbsp;</b></h3>
                </td>
                <td>
                  <h3>  {{ $group->text }}</h3>
                </td>           
            </tr>
        </table>
    </div>
<br>
<br>
<br>
<br>
<br>
<hr>
<div>
    <table class="table table-striped BmH8vmnb dataTable no-footer">
        <thead>
            <tr>
                <th style="width:20%">
                    C&oacute;digo
                </th>
                <th style="width:30%">
                    NIT/CI
                </th>
                <th style="width:50%">
                    Raz&oacute;n Social
                </th>                
            </tr>
        </thead>
        <tbody>                       
                @foreach ($clients as $client)		  	
                <tr>     
                    <td>{{ $client->public_id }}</td>
                    <td>{{ $client->nit }}</td>
                    <td>{{ $client->name }}</td>
                </tr>
		@endforeach            
            
        </tbody>
    </table>

</div>

@stop