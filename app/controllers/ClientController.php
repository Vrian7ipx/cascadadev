<?php

use ninja\repositories\ClientRepository;

class ClientController extends \BaseController {

	protected $clientRepo;

	public function __construct(ClientRepository $clientRepo)
	{
		parent::__construct();

		$this->clientRepo = $clientRepo;
	}	

	/**
	 * Display a listing of the resource.
	 *
	 * @return Response
	 */
	public function index()
	{  
		return View::make('list', array(
			'entityType'=>ENTITY_CLIENT, 
			'title' => trans('texts.clients'),
			'columns'=>Utils::trans(['checkbox', 'code', 'nit_ci', 'name_client', 'contact', 'date_created', 'action'])
		));		
	}


	public function getDatatable()
    {    	
    	if (Utils::isAdmin())
		{
    	
    	$clients = $this->clientRepo->find(Input::get('sSearch'));

        return Datatable::query($clients)
    	    ->addColumn('checkbox', function($model) { return '<input type="checkbox" name="ids[]" value="' . $model->public_id . '">'; })
    	    ->addColumn('public_id', function($model) { return link_to('clients/' . $model->public_id, $model->public_id); })
    	    ->addColumn('nit', function($model) { return link_to('clients/' . $model->public_id, $model->nit); })
    	    ->addColumn('name', function($model) { return link_to('clients/' . $model->public_id, $model->name); })
    	    ->addColumn('first_name', function($model) { return link_to('clients/' . $model->public_id, $model->first_name . ' ' . $model->last_name); })
    	    ->addColumn('created_at', function($model) { return Utils::timestampToDateString(strtotime($model->created_at)); })
    	    ->addColumn('dropdown', function($model) 
    	    { 
    	    	return '<div class="btn-group tr-action" style="visibility:hidden;">
  							<button type="button" class="btn btn-xs btn-default dropdown-toggle" data-toggle="dropdown">
    							'.trans('texts.select').' <span class="caret"></span>
  							</button>
  							<ul class="dropdown-menu" role="menu">
						    <li><a href="' . URL::to('clients/'.$model->public_id.'/edit') . '">'.trans('texts.edit_client').'</a></li>
						    <li class="divider"></li>
						    <li><a href="javascript:archiveEntity(' . $model->public_id. ')">'.trans('texts.archive_client').'</a></li>
						  </ul>
						</div>';
    	    })    	   
    	    ->make(); 

    	    }   	
    	if (!Utils::isAdmin())
		{
    	
    	$clients = $this->clientRepo->find(Input::get('sSearch'));
        global $uss;
        $branch = Branch::scope()->firstOrFail();
        $uss = $branch->deadline;
        
        if((time()-(60*60*24)) < strtotime($branch->deadline))
            $uss=true;
        else 
            $uss=false;
        
        
        global $enfecha;
        $enfecha = $uss;
        
        return Datatable::query($clients)
    	    ->addColumn('checkbox', function($model) { return '<input type="checkbox" name="ids[]" value="' . $model->public_id . '">'; })
    	    ->addColumn('public_id', function($model) { return link_to('clients/' . $model->public_id, $model->public_id); })
    	    ->addColumn('nit', function($model) { return link_to('clients/' . $model->public_id, $model->nit); })
    	    ->addColumn('name', function($model) { return link_to('clients/' . $model->public_id, $model->name); })
    	    ->addColumn('first_name', function($model) { return link_to('clients/' . $model->public_id, $model->first_name . ' ' . $model->last_name); })
    	    ->addColumn('created_at', function($model) { return Utils::timestampToDateString(strtotime($model->created_at)); })
    	    ->addColumn('dropdown', function($model) 
    	    { 
    	    	$return1 = '<div class="btn-group tr-action" style="visibility:hidden;">
  							<button type="button" class="btn btn-xs btn-default dropdown-toggle" data-toggle="dropdown">
    							'.trans('texts.select').' <span class="caret"></span>
  							</button>
  							<ul class="dropdown-menu" role="menu">
  							<li><a  class="enviar_class" ';
                //$return2=($enfecha?'href="' . URL::to('invoices/create/'.$model->public_id) . '">':' onclick="cancel()" href="#"'. '">');
                global $enfecha;
                global $uss;
                if($enfecha)
                    $return2='href="' . URL::to('invoices/create/'.$model->public_id). '">';
                else
                    $return2 = 'data-toggle="modal" data-target="#myModal" href="#">';
                
                    
                $return3= trans('texts.new_invoice').'</a></li>
						    <li class="divider"></li>
						    <li><a href="' . URL::to('clients/'.$model->public_id.'/edit') . '">'.trans('texts.edit_client').'</a></li>
						    <li class="divider"></li>
						    <li><a href="javascript:archiveEntity(' . $model->public_id. ')">'.trans('texts.archive_client').'</a></li>
						  </ul>
						</div>';
                
                return $return1.$return2.$return3;
    	    })    	   
    	    ->make(); 

    	    }     
    }



	/**
	 * Store a newly created resource in storage.
	 *
	 * @return Response
	 */
	public function store()
	{
		return $this->save();
	}

	/**
	 * Display the specified resource.
	 *
	 * @param  int  $id
	 * @return Response
	 */
	public function show($publicId)
	{
		$client = Client::withTrashed()->scope($publicId)->with('contacts', 'industry')->firstOrFail();
		Utils::trackViewed($client->getDisplayName(), ENTITY_CLIENT);

		$monday ='';
		$tuesday ='';
		$wednesday ='';
		$thursday ='';
		$friday ='';
		$saturday ='';
		$sunday ='';

		$frecuency = strstr($client->custom_value1, '1');
		if ($frecuency != false) {$monday ='Lun, ';} 
		$frecuency = strstr($client->custom_value1, '2');
		if ($frecuency != false) {$tuesday ='Mar, ';} 
		$frecuency = strstr($client->custom_value1, '3');
		if ($frecuency != false) {$wednesday ='Mié, ';} 
		$frecuency = strstr($client->custom_value1, '4');
		if ($frecuency != false) {$thursday ='Jue, ';} 
		$frecuency = strstr($client->custom_value1, '5');
		if ($frecuency != false) {$friday ='Vie, ';} 
		$frecuency = strstr($client->custom_value1, '6');
		if ($frecuency != false) {$saturday ='Sáb, ';} 
		$frecuency = strstr($client->custom_value1, '7');
		if ($frecuency != false) {$sunday ='Dom, ';} 

		$client->custom_value1 = $monday . $tuesday. $wednesday. $thursday. $friday. $saturday. $sunday;


	
		$actionLinks = [
			[trans('texts.create_invoice'), URL::to('invoices/create/' . $client->public_id )]
    ];

		$data = array(
			'actionLinks' => $actionLinks,
			'showBreadcrumbs' => false,
			'client' => $client,
			'title' => trans('texts.view_client'),
			'hasRecurringInvoices' => Invoice::scope()->where('is_recurring', '=', true)->whereClientId($client->id)->count() > 0
		);

		return View::make('clients.show', $data);
	}

	/**
	 * Show the form for creating a new resource.
	 *
	 * @return Response
	 */
	public function create()
	{		
		if (Client::scope()->count() > Auth::user()->getMaxNumClients())
		{
			return View::make('error', ['hideHeader' => true, 'error' => "Lo sentimos, se ha superado el límite de " . Auth::user()->getMaxNumClients() . " clientes"]);
		}

		$monday ='';
		$tuesday ='';
		$wednesday ='';
		$thursday ='';
		$friday ='';
		$saturday ='';
		$sunday ='';

		$data = [
			'client' => null, 
			'method' => 'POST', 
			'monday' => $monday,
			'tuesday' => $tuesday,
			'wednesday' => $wednesday,
			'thursday' => $thursday,
			'friday' => $friday,
			'saturday' => $saturday,
			'sunday' => $sunday,
			'url' => 'clients', 
			'title' => trans('texts.new_client')
		];

		$data = array_merge($data, self::getViewModel());	
		return View::make('clients.edit', $data);

	}	

	/**
	 * Show the form for editing the specified resource.
	 *
	 * @param  int  $id
	 * @return Response
	 */
	public function edit($publicId)
	{
		$client = Client::scope($publicId)->with('contacts')->firstOrFail();

		$monday ='';
		$tuesday ='';
		$wednesday ='';
		$thursday ='';
		$friday ='';
		$saturday ='';
		$sunday ='';

		$frecuency = strstr($client->custom_value1, '1');
		if ($frecuency != false) {$monday ='1';} 
		$frecuency = strstr($client->custom_value1, '2');
		if ($frecuency != false) {$tuesday ='1';} 
		$frecuency = strstr($client->custom_value1, '3');
		if ($frecuency != false) {$wednesday ='1';} 
		$frecuency = strstr($client->custom_value1, '4');
		if ($frecuency != false) {$thursday ='1';} 
		$frecuency = strstr($client->custom_value1, '5');
		if ($frecuency != false) {$friday ='1';} 
		$frecuency = strstr($client->custom_value1, '6');
		if ($frecuency != false) {$saturday ='1';} 
		$frecuency = strstr($client->custom_value1, '7');
		if ($frecuency != false) {$sunday ='1';} 

		$data = [
			'client' => $client, 
			'method' => 'PUT',
			'monday' => $monday,
			'tuesday' => $tuesday,
			'wednesday' => $wednesday,
			'thursday' => $thursday,
			'friday' => $friday,
			'saturday' => $saturday,
			'sunday' => $sunday,
			'url' => 'clients/' . $publicId, 
			'title' => trans('texts.edit_client')
		];

		$data = array_merge($data, self::getViewModel());			
		return View::make('clients.edit', $data);
	}

	private static function getViewModel()
	{
		return [		
			'industries' => Industry::remember(DEFAULT_QUERY_CACHE)->orderBy('name')->get(),
			'business_types' => BusinessType::remember(DEFAULT_QUERY_CACHE)->orderBy('name')->get(),
			'groups' => Group::orderBy('name')->get(),
			'zones' => Zone::remember(DEFAULT_QUERY_CACHE)->orderBy('name')->get(),
			'currencies' => Currency::remember(DEFAULT_QUERY_CACHE)->orderBy('name')->get(),
			'countries' => Country::remember(DEFAULT_QUERY_CACHE)->orderBy('name')->get(),
			'customLabel1' => Auth::user()->account->custom_client_label1,
			'customLabel2' => Auth::user()->account->custom_client_label2,
		];
	}	

	/**
	 * Update the specified resource in storage.
	 *
	 * @param  int  $id
	 * @return Response
	 */
	public function update($publicId)
	{
		return $this->save($publicId);
	}

	private function save($publicId = null)
	{	

 	    $rules['nit'] = 'required';

        $validator = Validator::make(Input::all(), $rules);

			if ($publicId) 
			{
				$client = Client::scope($publicId)->firstOrFail();
			} 
			else 
			{
				$client = Client::createNew();
				if ($validator->fails())
		        {
		           	$url = $publicId ? 'clients/' . $publicId . '/edit' : 'clients/create';
					return Redirect::to($url)
						->withErrors($validator)
						->withInput(Input::except('password'));
		        }  
			}
			$client->nit = trim(Input::get('nit'));
			$client->name = trim(Input::get('name'));
			$client->work_phone = trim(Input::get('work_phone'));

			$frecuency = "";
			if(Input::get('custom_value1_0')){
			$frecuency .= "1,";
			}
			if(Input::get('custom_value1_1')){
			$frecuency .= "2,";
			}
			if(Input::get('custom_value1_2')){
			$frecuency .= "3,";
			}
			if(Input::get('custom_value1_3')){
			$frecuency .= "4,";
			}
			if(Input::get('custom_value1_4')){
			$frecuency .= "5,";
			}
			if(Input::get('custom_value1_5')){
			$frecuency .= "6,";
			}
			if(Input::get('custom_value1_6')){
			$frecuency .= "7,";
			}

			$client->custom_value1 = $frecuency;

			$client->address1 = trim(Input::get('address1'));
			$client->address2 = trim(Input::get('address2'));
			$client->city = trim(Input::get('city'));
			$client->state = trim(Input::get('state'));
			$client->postal_code = trim(Input::get('postal_code'));			
			$client->country_id = Input::get('country_id') ? Input::get('country_id') : null;
			$client->private_notes = trim(Input::get('private_notes'));
			$client->business_type_id = Input::get('business_type_id') ? Input::get('business_type_id') : null;
			$client->group_id = Input::get('group_id') ? Input::get('group_id') : null;
			$client->zone_id = Input::get('zone_id') ? Input::get('zone_id') : null;
			$client->currency_id = Input::get('currency_id') ? Input::get('currency_id') : 1;
			$client->website = trim(Input::get('website'));

			$client->save();

			$data = json_decode(Input::get('data'));
			$contactIds = [];
			$isPrimary = true;
			
			foreach ($data->contacts as $contact)
			{
				if (isset($contact->public_id) && $contact->public_id)
				{
					$record = Contact::scope($contact->public_id)->firstOrFail();
				}
				else
				{
					$record = Contact::createNew();
				}

				$record->email = trim(strtolower($contact->email));
				$record->first_name = trim($contact->first_name);
				$record->last_name = trim($contact->last_name);
				$record->phone = trim($contact->phone);
				$record->is_primary = $isPrimary;
				$isPrimary = false;

				$client->contacts()->save($record);
				$contactIds[] = $record->public_id;					
			}

			foreach ($client->contacts as $contact)
			{
				if (!in_array($contact->public_id, $contactIds))
				{	
					$contact->delete();
				}
			}
						
			if ($publicId) 
			{
				Session::flash('message', trans('texts.updated_client'));
			} 
			else 
			{
				Activity::createClient($client);
				Session::flash('message', trans('texts.created_client'));
			}

			return Redirect::to('clients/' . $client->public_id);

	}

	public function bulk()
	{
		$action = Input::get('action');
		$ids = Input::get('id') ? Input::get('id') : Input::get('ids');		
		$count = $this->clientRepo->bulk($ids, $action);

		$message = Utils::pluralize($action.'d_client', $count);
		Session::flash('message', $message);

		return Redirect::to('clients');
	}



	public function createcliente()
    {
     	$user_id = Auth::user()->getAuthIdentifier();
    	$user = DB::table('users')->select('account_id')->where('id',$user_id)->first();
    	
    	$i=1;
    	$aux='no';
    	//40360
    	for ($i=1;$i<=2000;$i++)
    	{
    		$client1 =  DB::table('clients')->select('id','name','public_id')->where('account_id',$user->account_id)->where('id',$i)->where('deleted_at',NULL)->first();
			if($client1<>null)
    		{
				$client = Client::scope($client1->public_id)->firstOrFail();
				if($client<>null)
	    		{
					$contact =  DB::table('contacts')->select('id','first_name')->where('account_id',$user->account_id)->where('client_id',$client->id)->where('is_primary',1)->first();

					if($contact==null)
	    			{ 				
	    				$contact = Contact::createNew();
						$contact->is_primary = true;
						$client->contacts()->save($contact);
						$aux='si';
	    			}

	    		} 

      		}

    	} 


		return Response::json($aux);	
    	
    }

	/*Modificaciones David */
	public function registrarCliente()
	{
		$data = Input::all();
		// $data  = array('hola' => 'mundo' );
		$client = Client::createNew();
		$contact = Contact::createNew();
		$contact->is_primary = true;

		$client->name = trim($data['name']);

		$client->nit = trim($data['nit']);
	
		
		$client->save();
		
		$email = $data['email'];

		if($email =='sinemail')
		{
			// $email = "david@david.corp";
			$user_id = Auth::user()->getAuthIdentifier();
			$user  = DB::table('users')->select('account_id')->where('id',$user_id)->first();
			$account_id = $user->account_id;
			$account = DB::table('accounts')->select('work_email')->where('id',$account_id)->first();
			$email = $account->work_email;
		}
		$isPrimary = true;

			$contact->email = trim(strtolower($email));
			$contact->phone = trim($data['phone']);
			$contact->is_primary = $isPrimary;
			
			

			$client->contacts()->save($contact);
			$cliente =  DB::table('clients')->select('id','name','nit')->where('account_id',$client->account_id)->where('nit',$client->nit)->first();
    	
    

    		$datos = array(
    			'resultado' => 0,
    			'cliente' => $cliente

    		);
    		return Response::json($datos);	
    	

		// return Response::json($client);

	}
	 public function cliente($public_id)
    {
     	$user_id = Auth::user()->getAuthIdentifier();
    	$user = DB::table('users')->select('account_id')->where('id',$user_id)->first();
    	$client =  DB::table('clients')->select('id','name','nit','public_id')->where('account_id',$user->account_id)->where('public_id',$public_id)->first();
    	
    	if($client!=null)
    	{

    		$datos = array(
    			'resultado' => 0,
    			'cliente' => $client

    		);
    		return Response::json($datos);	
    	}
    	$datos = array(
    			'resultado' => 1,
    			'mensaje' => 'cliente no encontrado'

    		);
    		return Response::json($datos);	
    	
    }
     public function obtenerFactura($public_id)
    {
    	$user_id = Auth::user()->getAuthIdentifier();
    	// $client = Client::where('public_id','=',$public_id);

    
    	$user = DB::table('users')->select('account_id','branch_id')->where('id',$user_id)->first();
    	$client =  DB::table('clients')->select('id','name','nit','public_id')->where('account_id',$user->account_id)->where('public_id',$public_id)->first();
    		// return Response::json($client);
    	if($client==null)
    	{
				$datos = array(
    			'resultado' => 1,
    			'mensaje' => 'cliente no encontrado'

    		);
    		return Response::json($datos);	
    	}
    	

    	//caso contrario tratar al cliente
    
    	$invoices = DB::table('invoices')
					 // ->join('clients', 'clients.id', '=', 'invoices.client_id')
					 // ->where('account_id','=',$user->account_id)
					 ->where('branch_id','=',$user->branch_id)
					 ->where('client_id','=',$client->id)
					 // -where('')
					 ->orderBy('id')
					 // ->first();
					 // ->get();
					 ->get(array('id','invoice_number'));
		// return Response::json($invoices);

		if($invoices==null)
    	{
				$datos = array(
    			'resultado' => 2,
    			'mensaje' => 'Cliente no emitio ninguna factura'

    		);
    		return Response::json($datos);	
    	}

		$inv=""; 
		foreach ($invoices as $invo) 
    	{
    		$inv = $invo;
		}	
		$invoice = DB::table('invoices')
				   ->where('id','=',$inv->id)
				   ->first(); 
		

		$invoiceItems =DB::table('invoice_items')
    				   ->select('notes','cost','qty','boni','desc')
    				   ->where('invoice_id','=',$invoice->id)
    				   ->get(array('notes','cost','qty','boni','desc'));
    			   


    	// $date = date_create($invoice->deadline);
    				   $date = new DateTime($invoice->deadline);
    				   $dateEmision = new DateTime($invoice->invoice_date);
    	// $account = DB::table('accounts')->select('name','nit')->where('id',$invoice->account_id)->first();
    	$account  = array('name' =>$invoice->branch,'nit'=>'1006909025' );

    	// dando formato a las salidas numericas
    	// $monto = (float)$invoice->amount;
    	// $amount= number_format((float)$invoice->amount, 2, '.', '');


		$ice = $invoice->amount-$invoice->fiscal;
		$cliente  = array('name' => $invoice->name ,'nit'=>$invoice->nit);
		$factura  = array(
						'resultado' => 0,
						'invoice_number' => $invoice->invoice_number,
    					'control_code'=>$invoice->control_code,
    					'invoice_date'=>$dateEmision->format('d-m-Y'),
    					// 'amount'=>$invoice->amount,
    					'amount'=>number_format((float)$invoice->amount, 2, '.', ''),
    					'subtotal'=>number_format((float)$invoice->subtotal, 2, '.', ''),
    					'fiscal'=>number_format((float)$invoice->fiscal, 2, '.', ''),
    					'client'=>$client,

    					'account'=>$account,
    					'law' => $invoice->law,

    					'invoice_items'=>$invoiceItems,
    					// 'invoice_items'=>$productos,
    					'address1'=>$invoice->address1,
    					'address2'=>$invoice->address2,
    					'num_auto'=>$invoice->number_autho,
    					'fecha_limite'=>$date->format('d-m-Y'),
    					'ice'=>number_format((float)$ice, 2, '.', '')	
    					);
		//its work ok go  get the money
		return Response::json($factura);			 
					 


    }
    public function mensaje()
    {
    	$user_id = Auth::user()->getAuthIdentifier();
    	$user = DB::table('users')->select('account_id','branch_id')->where('id',$user_id)->first();
    	$invoices = DB::table('invoices')
					 // ->join('clients', 'clients.id', '=', 'invoices.client_id')
					 // ->where('account_id','=',$user->account_id)
					 ->where('branch_id','=',$user->branch_id)
					 ->where('client_id','=','2')
					 // -where('')
					 ->orderBy('invoice_number')
					 // ->first();
					 // ->get();
					 ->get(array('id','invoice_number'));
		$datos  = array('mensaje' => 'hola mundo' );
		return Response::json($invoices);

    }
    //modulos offline
    public function clientes()
    {
    	// $user_id = Auth::user()->getAuthIdentifier();
    	// $user = DB::table('users')->select('account_id','price_type_id','branch_id')->where('id',$user_id)->first();
    	// $clients = DB::table('clients')->select('id','name','nit','public_id')->where('account_id',$user->account_id)->get(array('id','name','nit','public_id'));
    	$user_id = Auth::user()->getAuthIdentifier();
    	$user = DB::table('users')->select('account_id','price_type_id','branch_id','id','groups')->where('id',$user_id)->first();
    	$grupo = explode(',',$user->groups);
    	
    	$array =array();
    	foreach ($grupo as $idgrupo) {

    		$idg = $idgrupo+1;
    		$cliente = DB::table('clients')->select('id','name','nit','public_id')
    							->where('account_id',$user->account_id)
    							->where('group_id',$idg)
    							->get(array('id','name','nit','public_id'));
    							// ->first();
    		foreach ($cliente as $cli) {
    			# code...
    			$array[] =$cli;
    		}
    		
    		# code...
    	}


   		return Response::json($array); 	
    }

}