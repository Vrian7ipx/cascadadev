@extends('header')

@section('content')

	<ul class="nav nav-tabs nav nav-justified">
  	{{ HTML::nav_link('company/details', 'company_details') }}
  	{{ HTML::nav_link('company/branches', 'branch_details') }}
  	{{ HTML::nav_link('company/groups', 'group_details') }}
  	{{ HTML::nav_link('company/user_management', 'user_management') }}
    {{ HTML::nav_link('company/import_export', 'import_export', 'company/import_map') }}
	</ul>

	<br/>

@stop