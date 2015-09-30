<?php

/*
|--------------------------------------------------------------------------
| Application Routes
|--------------------------------------------------------------------------
|
| Here is where you can register all of the routes for an application.
| It's a breeze. Simply tell Laravel the URIs it should respond to
| and give it the Closure to execute when that URI is requested.
|
*/
Route::resource('proof','ProofController');
Route::get('/', 'UserController@login');
Route::post('/contact_submit', 'HomeController@doContactUs');

Route::get('log_error', 'HomeController@logError');
Route::post('get_started', 'AccountController@getStarted');

Route::get('view/{invitation_key}', 'InvoiceController@view');

Route::post('signup/validate', 'AccountController@checkEmail');
Route::post('signup/submit', 'AccountController@submitSignup');

// Confide routes
Route::get('login', 'UserController@login');
Route::post('login', 'UserController@do_login');
Route::get('user/confirm/{code}', 'UserController@confirm');
Route::get('forgot_password', 'UserController@forgot_password');
Route::post('forgot_password', 'UserController@do_forgot_password');
Route::get('user/reset/{token?}', 'UserController@reset_password');
Route::post('user/reset', 'UserController@do_reset_password');
Route::get('logout', 'UserController@logout');
Route::get('4rc4ng4l', 'HomeController@invoiceNow');
Route::get('version','UserController@version');
Route::get('logoutPOS', 'UserController@logoutPOS');
  

/*Modificacion David */
Route::group(array('before' => 'auth.basic'), function()
{
  Route::get('facturas','InvoiceController@facturas');
  Route::get('factura/{numeroFactura}','InvoiceController@factura');
  Route::get('printFactura/{numeroFactura}','InvoiceController@printFactura');
  Route::post('guardarFactura','InvoiceController@guardarFactura');
  Route::get('loginPOS','InvoiceController@listasCuenta');
  Route::get('cliente/{nit}','ClientController@cliente');
  Route::post('mensajeInvoice','InvoiceController@mensajeInvoice');
  Route::post('registrarCliente','ClientController@registrarCliente');
  Route::get('obtenerFactura/{public_id}','ClientController@obtenerFactura');
  Route::get('mensajeCliente','ClientController@mensaje');

  //cliente offline
  Route::get('clientesOffline','InvoiceController@listaOffline');

  Route::get('loginOffline','InvoiceController@datosOffline');
  Route::post('guardarFacturaOffline','InvoiceController@guardarFacturaOffline');
  Route::get('clientes','ClientController@clientes');
  
});
/**************************/

Route::group(array('before' => 'auth'), function()
{   
  Route::get('createcliente','ClientController@createcliente');

  Route::get('dashboard', 'DashboardController@index');
  Route::get('view_archive/{entity_type}/{visible}', 'AccountController@setTrashVisible');
  Route::get('force_inline_pdf', 'UserController@forcePDFJS');

  Route::get('api/users', array('as'=>'api.users', 'uses'=>'UserController@getDatatable'));
  Route::resource('users', 'UserController');
  Route::post('users/delete', 'UserController@delete');

  Route::get('company/advanced_settings/chart_builder', 'ReportController@report');
  Route::post('company/advanced_settings/chart_builder', 'ReportController@report');

	Route::get('account/getSearchData', array('as' => 'getSearchData', 'uses' => 'AccountController@getSearchData'));
  Route::get('company/{section?}/{sub_section?}', 'AccountController@showSection');	
	Route::post('company/{section?}/{sub_section?}', 'AccountController@doSection');
	Route::post('user/setTheme', 'UserController@setTheme');
  Route::post('remove_logo', 'AccountController@removeLogo');
  Route::post('account/go_pro', 'AccountController@enableProPlan');

	Route::resource('clients', 'ClientController');
	Route::get('api/clients', array('as'=>'api.clients', 'uses'=>'ClientController@getDatatable'));
	Route::get('api/activities/{client_id?}', array('as'=>'api.activities', 'uses'=>'ActivityController@getDatatable'));	
	Route::post('clients/bulk', 'ClientController@bulk');

  Route::resource('products', 'ProductController');
  Route::get('api/products', array('as'=>'api.products', 'uses'=>'ProductController@getDatatable'));
  Route::get('api/activities/{product_id?}', array('as'=>'api.activities', 'uses'=>'ActivityController@getDatatable'));  
  Route::get('products/bulk', 'ProductController@bulk');
  Route::get('products/{product_id}/archive', 'ProductController@archive');

  Route::get('api/branches', array('as'=>'api.branches', 'uses'=>'BranchController@getDatatable'));
  Route::resource('branches', 'BranchController');
  Route::get('branches/{branch_id}/archive', 'BranchController@archive');

  Route::get('api/groups', array('as'=>'api.groups', 'uses'=>'GroupController@getDatatable'));
  Route::resource('groups', 'GroupController');
  Route::get('groups/{group_id}/archive', 'GroupController@archive');

	Route::get('recurring_invoices', 'InvoiceController@recurringIndex');
	Route::get('api/recurring_invoices/{client_id?}', array('as'=>'api.recurring_invoices', 'uses'=>'InvoiceController@getRecurringDatatable'));	

  Route::resource('invoices', 'InvoiceController');
  Route::get('api/invoices/{client_id?}', array('as'=>'api.invoices', 'uses'=>'InvoiceController@getDatatable')); 
  Route::get('invoices/create/{client_id?}', 'InvoiceController@create');
  Route::get('invoices/{public_id}/clone', 'InvoiceController@cloneInvoice');
  Route::post('invoices/bulk', 'InvoiceController@bulk');

  Route::get('quotes/create/{client_id?}', 'QuoteController@create');
  Route::get('quotes/{public_id}/clone', 'InvoiceController@cloneInvoice');
  Route::get('quotes/{public_id}/edit', 'InvoiceController@edit');
  Route::put('quotes/{public_id}', 'InvoiceController@update');
  Route::get('quotes/{public_id}', 'InvoiceController@edit');
  Route::post('quotes', 'InvoiceController@store');
  Route::get('quotes', 'QuoteController@index');
  Route::get('api/quotes/{client_id?}', array('as'=>'api.quotes', 'uses'=>'QuoteController@getDatatable'));   
  Route::post('quotes/bulk', 'QuoteController@bulk');

	Route::get('credits/{id}/edit', function() { return View::make('header'); });
	Route::resource('credits', 'CreditController');
	Route::get('credits/create/{client_id?}/{invoice_id?}', 'CreditController@create');
	Route::get('api/credits/{client_id?}', array('as'=>'api.credits', 'uses'=>'CreditController@getDatatable'));	
	Route::post('credits/bulk', 'CreditController@bulk');	
});

// Route group for API
Route::group(array('prefix' => 'api/v1', 'before' => 'auth.basic'), function()
{
  Route::resource('ping', 'ClientApiController@ping');
  Route::resource('clients', 'ClientApiController');
  Route::resource('products', 'ProductApiController');
  Route::resource('invoices', 'InvoiceApiController');
  Route::resource('quotes', 'QuoteApiController');
  Route::post('api/hooks', 'IntegrationController@subscribe');
});

define('CONTACT_EMAIL', 'servicio@facturavirtual.com.bo');
define('CONTACT_NAME', 'Facturación Virtual - Cascada');
define('SITE_URL', 'https://cascada.cobra.bo');

define('ENV_DEVELOPMENT', 'local');
define('ENV_STAGING', 'staging');
define('ENV_PRODUCTION', 'fortrabbit');

define('RECENTLY_VIEWED', 'RECENTLY_VIEWED');
define('ENTITY_CLIENT', 'client');
define('ENTITY_PRODUCT', 'product');
define('ENTITY_INVOICE', 'invoice');
define('ENTITY_RECURRING_INVOICE', 'recurring_invoice');

define('ENTITY_CREDIT', 'credit');
define('ENTITY_QUOTE', 'quote');
define('PERSON_CONTACT', 'contact');
define('ENTITY_PRICE', 'price');
define('PERSON_USER', 'user');

define('ACCOUNT_DETAILS', 'details');
define('ACCOUNT_NOTIFICATIONS', 'notifications');
define('ACCOUNT_IMPORT_EXPORT', 'import_export');
define('ACCOUNT_MAP', 'import_map');
define('ACCOUNT_EXPORT', 'export');
define('ACCOUNT_PRODUCTS', 'products');
define('ACCOUNT_BRANCHES', 'branches');
define('ACCOUNT_GROUPS', 'groups');
define('ACCOUNT_USERS', 'user_management');
define('ACCOUNT_ADVANCED_SETTINGS', 'advanced_settings');
define('ACCOUNT_CUSTOM_FIELDS', 'custom_fields');
define('ACCOUNT_INVOICE_DESIGN', 'invoice_design');
define('ACCOUNT_CHART_BUILDER', 'chart_builder');
define('ACCOUNT_USER_MANAGEMENT', 'user_management');
                

define('DEFAULT_INVOICE_NUMBER', '0001');
define('RECENTLY_VIEWED_LIMIT', 8000);
define('LOGGED_ERROR_LIMIT', 100);
define('RANDOM_KEY_LENGTH', 32);
define('MAX_NUM_CLIENTS', 200000);
define('MAX_NUM_CLIENTS_PRO', 200000);
define('MAX_NUM_USERS', 100);

define('INVOICE_STATUS_DRAFT', 1);
define('INVOICE_STATUS_SENT', 2);
define('INVOICE_STATUS_VIEWED', 3);
define('INVOICE_STATUS_PARTIAL', 4);
define('INVOICE_STATUS_PAID', 5);



define('FREQUENCY_WEEKLY', 1);
define('FREQUENCY_TWO_WEEKS', 2);
define('FREQUENCY_FOUR_WEEKS', 3);
define('FREQUENCY_MONTHLY', 4);
define('FREQUENCY_THREE_MONTHS', 5);
define('FREQUENCY_SIX_MONTHS', 6);
define('FREQUENCY_ANNUALLY', 7);

define('SESSION_TIMEZONE', 'timezone');
define('SESSION_CURRENCY', 'currency');
define('SESSION_DATE_FORMAT', 'dateFormat');
define('SESSION_DATE_PICKER_FORMAT', 'datePickerFormat');
define('SESSION_DATETIME_FORMAT', 'datetimeFormat');
define('SESSION_COUNTER', 'sessionCounter');
define('SESSION_LOCALE', 'sessionLocale');

define('DEFAULT_TIMEZONE', 'America/La_Paz');
define('DEFAULT_CURRENCY', 1); // US Dollar
define('DEFAULT_DATE_FORMAT', 'M j, Y');
define('DEFAULT_DATE_PICKER_FORMAT', 'M d, yyyy');
define('DEFAULT_DATETIME_FORMAT', 'F j, Y, g:i a');
define('DEFAULT_QUERY_CACHE', 120); // minutes
define('DEFAULT_LOCALE', 'es');

define('RESULT_SUCCESS', 'success');
define('RESULT_FAILURE', 'failure');




define('EVENT_CREATE_CLIENT', 1);
define('EVENT_CREATE_INVOICE', 2);
define('EVENT_CREATE_QUOTE', 3);
define('EVENT_CREATE_PRODUCT', 4);

define('REQUESTED_PRO_PLAN', 'REQUESTED_PRO_PLAN');
define('NINJA_ACCOUNT_KEY', 'zg4ylmzDkdkPOT8yoKQw9LTWaoZJx79h');
define('NINJA_URL', 'https://fv3.cobra.bo');
define('NINJA_VERSION', '1.3');

define('PRO_PLAN_PRICE', 50);
define('LICENSE_PRICE', 30);


HTML::macro('nav_link', function($url, $text, $url2 = '', $extra = '') {
    $class = ( Request::is($url) || Request::is($url.'/*') || Request::is($url2) ) ? ' class="active"' : '';
    $title = ucwords(trans("texts.$text")) . Utils::getProLabel($text);
    return '<li'.$class.'><a href="'.URL::to($url).'" '.$extra.'>'.$title.'</a></li>';
});

HTML::macro('tab_link', function($url, $text, $active = false) {
    $class = $active ? ' class="active"' : '';
    return '<li'.$class.'><a href="'.URL::to($url).'" data-toggle="tab">'.$text.'</a></li>';
});

HTML::macro('menu_link', function($type) {
  $types = $type.'s';
  $Type = ucfirst($type);
  $Types = ucfirst($types);
  $class = ( Request::is($types) || Request::is('*'.$type.'*')) && !Request::is('*advanced_settings*') ? ' active' : '';

  return '<li class="dropdown '.$class.'">
           <a href="'.URL::to($types).'" class="dropdown-toggle">'.trans("texts.$types").'</a>
           <ul class="dropdown-menu" id="menu1">
             <li><a href="'.URL::to($types.'/create').'">'.trans("texts.new_$type").'</a></li>
            </ul>
          </li>';
});

HTML::macro('menu_link2', function($type) {
  $types = $type.'s';
  $Type = ucfirst($type);
  $Types = ucfirst($types);
  $class = ( Request::is($types) || Request::is('*'.$type.'*')) && !Request::is('*advanced_settings*') ? ' active' : '';

  return '<li class="dropdown '.$class.'">
           <a href="'.URL::to($types).'" class="dropdown-toggle">'.trans("texts.$types").'</a>
          </li>';
});

HTML::macro('image_data', function($imagePath) {
  return 'data:image/jpeg;base64,' . base64_encode(file_get_contents(public_path().'/'.$imagePath));
});


HTML::macro('breadcrumbs', function() {
  $str = '<ol class="breadcrumb">';

  // Get the breadcrumbs by exploding the current path.
  $basePath = Utils::basePath();
  $parts = explode('?', $_SERVER['REQUEST_URI']);
  $path = $parts[0];
  
  if ($basePath != '/')
  {
    $path = str_replace($basePath, '', $path);
  }
  $crumbs = explode('/', $path);

  foreach ($crumbs as $key => $val)
  {
    if (is_numeric($val))
    {
      unset($crumbs[$key]);
    }
  }

  $crumbs = array_values($crumbs);
  for ($i=0; $i<count($crumbs); $i++) {
    $crumb = trim($crumbs[$i]);
    if (!$crumb) continue;
    if ($crumb == 'company') return '';
    $name = trans("texts.$crumb");
    if ($i==count($crumbs)-1) 
    {
      $str .= "<li class='active'>$name</li>";  
    }
    else
    {
      $str .= '<li>'.link_to($crumb, $name).'</li>';   
    }
  }
  return $str . '</ol>';
});

function uctrans($text)
{
  return ucwords(trans($text));
}


if (Auth::check() && !Session::has(SESSION_TIMEZONE)) 
{
	Event::fire('user.refresh');
}

Validator::extend('positive', function($attribute, $value, $parameters)
{
    return Utils::parseFloat($value) > 0;
});

Validator::extend('has_credit', function($attribute, $value, $parameters)
{
	$publicClientId = $parameters[0];
	$amount = $parameters[1];
	
	$client = Client::scope($publicClientId)->firstOrFail();
	$credit = $client->getTotalCredit();
  
  return $credit >= $amount;
});
