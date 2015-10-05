<?php

use ninja\mailers\ContactMailer as Mailer;
use ninja\repositories\InvoiceRepository;
use ninja\repositories\ClientRepository;
use ninja\repositories\TaxRateRepository;

class InvoiceController extends \BaseController {

	protected $mailer;
	protected $invoiceRepo;
	protected $clientRepo;
	protected $taxRateRepo;

	public function __construct(Mailer $mailer, InvoiceRepository $invoiceRepo, ClientRepository $clientRepo, TaxRateRepository $taxRateRepo)
	{
		parent::__construct();

		$this->mailer = $mailer;
		$this->invoiceRepo = $invoiceRepo;
		$this->clientRepo = $clientRepo;
		$this->taxRateRepo = $taxRateRepo;
	}	

	public function index()
	{

		// print_r($this->setDataOffline());
		// return 0;
		$data = [
			'title' => trans('texts.invoices'),
			'entityType'=>ENTITY_INVOICE, 
			'columns'=>Utils::trans(['checkbox', 'invoice_number', 'client', 'invoice_date', 'invoice_total', 'status', 'action'])
		];

		if (Invoice::scope()->where('is_recurring', '=', true)->count() > 0)
		{
			$data['secEntityType'] = ENTITY_RECURRING_INVOICE;
			$data['secColumns'] = Utils::trans(['checkbox', 'frequency', 'client', 'start_date', 'end_date', 'invoice_total', 'action']);
		}

		return View::make('list', $data);
	}

	public function getDatatable($clientPublicId = null)
    {
    	$query = $this->invoiceRepo->getInvoices(Auth::user()->account_id, Auth::user()->branch_id, $clientPublicId, Input::get('sSearch'));
    	$table = Datatable::query($query);			

    	if (!$clientPublicId) {
    		$table->addColumn('checkbox', function($model) { return '<input type="checkbox" name="ids[]" value="' . $model->public_id . '">'; });
    	}
    	
    	$table->addColumn('invoice_number', function($model) { return link_to('invoices/' . $model->public_id . '/edit', $model->invoice_number); });

    	if (!$clientPublicId) {
    		$table->addColumn('client_name', function($model) { return link_to('clients/' . $model->client_public_id, Utils::getClientDisplayName($model)); });
    	}
    	
    	return $table->addColumn('invoice_date', function($model) { return Utils::fromSqlDate($model->invoice_date); })    	    
    		->addColumn('amount', function($model) { return Utils::formatMoney($model->amount, $model->currency_id); })
    	    ->addColumn('invoice_status_name', function($model) { return $model->invoice_status_name; })
    	    ->addColumn('dropdown', function($model) 
    	    { 
    	    	return '<div class="btn-group tr-action" style="visibility:hidden;">
  							<button type="button" class="btn btn-xs btn-default dropdown-toggle" data-toggle="dropdown">
    							'.trans('texts.select').' <span class="caret"></span>
  							</button>
  							<ul class="dropdown-menu" role="menu">
						    <li><a href="' . URL::to('invoices/'.$model->public_id.'/edit') . '">Ver Factura</a></li>
						    <li class="divider"></li>
						    <li><a href="javascript:deleteEntity(' . $model->public_id . ')">'.trans('texts.delete_invoice').'</a></li>						    
						  </ul>
						</div>';
    	    })    	       	    
    	    ->make();    	
    }

	public function getRecurringDatatable($clientPublicId = null)
    {
    	$query = $this->invoiceRepo->getRecurringInvoices(Auth::user()->account_id, $clientPublicId, Input::get('sSearch'));
    	$table = Datatable::query($query);			

    	if (!$clientPublicId) {
    		$table->addColumn('checkbox', function($model) { return '<input type="checkbox" name="ids[]" value="' . $model->public_id . '">'; });
    	}
    	
    	$table->addColumn('frequency', function($model) { return link_to('invoices/' . $model->public_id, $model->frequency); });

    	if (!$clientPublicId) {
    		$table->addColumn('client_name', function($model) { return link_to('clients/' . $model->client_public_id, Utils::getClientDisplayName($model)); });
    	}
    	
    	return $table->addColumn('start_date', function($model) { return Utils::fromSqlDate($model->start_date); })
    	    ->addColumn('end_date', function($model) { return Utils::fromSqlDate($model->end_date); })    	    
    	    ->addColumn('total', function($model) { return Utils::formatMoney($model->amount, $model->currency_id); })
    	    ->addColumn('dropdown', function($model) 
    	    { 
    	    	return '<div class="btn-group tr-action" style="visibility:hidden;">
  							<button type="button" class="btn btn-xs btn-default dropdown-toggle" data-toggle="dropdown">
    						'.trans('texts.select').' <span class="caret"></span>
  							</button>
  							<ul class="dropdown-menu" role="menu">
						    <li><a href="' . URL::to('invoices/'.$model->public_id.'/edit') . '">'.trans('texts.edit_invoice').'</a></li>
						    <li class="divider"></li>
						    <li><a href="javascript:archiveEntity(' . $model->public_id . ')">'.trans('texts.archive_invoice').'</a></li>
						    <li><a href="javascript:deleteEntity(' . $model->public_id . ')">'.trans('texts.delete_invoice').'</a></li>						    
						  </ul>
						</div>';
    	    })    	       	    
    	    ->make();    	
    }


	public function view($invitationKey)
	{
		$invitation = Invitation::withTrashed()->with('user', 'invoice.invoice_items', 'invoice.account.country', 'invoice.client.contacts', 'invoice.client.country')
			->where('invitation_key', '=', $invitationKey)->firstOrFail();

		$invoice = $invitation->invoice;
		
		if (!$invoice || $invoice->is_deleted) 
		{
			return View::make('invoices.deleted');
		}

		$client = $invoice->client;
		
		if (!$client || $client->is_deleted) 
		{
			return View::make('invoices.deleted');
		}

		if (!Auth::check() || Auth::user()->account_id != $invoice->account_id)
		{
			Activity::viewInvoice($invitation);	
			Event::fire('invoice.viewed', $invoice);
		}

		$client->account->loadLocalizationSettings();		

		$invoice->invoice_date = Utils::fromSqlDate($invoice->invoice_date);
		$invoice->due_date = Utils::fromSqlDate($invoice->due_date);
		$invoice->is_pro = $client->account->isPro();

		$data = array(
			'hideHeader' => true,
			'showBreadcrumbs' => false,
			'invoice' => $invoice->hidePrivateFields(),
			'invitation' => $invitation,
			'invoiceLabels' => $client->account->getInvoiceLabels(),
		);

		return View::make('invoices.view', $data);
	}

	public function edit($publicId)
	{
		$invoice = Invoice::scope($publicId)->withTrashed()->with('invitations', 'account.country', 'client.contacts', 'client.country', 'invoice_items')->firstOrFail();
		Utils::trackViewed($invoice->invoice_number . ' - ' . $invoice->client->getDisplayName(), ENTITY_INVOICE);
	
		$invoice->invoice_date = Utils::fromSqlDate($invoice->invoice_date);
		$invoice->due_date = Utils::fromSqlDate($invoice->due_date);
		$invoice->start_date = Utils::fromSqlDate($invoice->start_date);
		$invoice->end_date = Utils::fromSqlDate($invoice->end_date);
		$invoice->is_pro = Auth::user()->isPro();

  	$contactIds = DB::table('invitations')
			->join('contacts', 'contacts.id', '=','invitations.contact_id')
			->where('invitations.invoice_id', '=', $invoice->id)
			->where('invitations.account_id', '=', Auth::user()->account_id)
			->where('invitations.deleted_at', '=', null)
			->select('contacts.public_id')->lists('public_id');
	
		$data = array(
				'showBreadcrumbs' => false,
				'account' => $invoice->account,
				'invoice' => $invoice, 
				'data' => false, 
				'method' => 'PUT', 
				'invitationContactIds' => $contactIds,
				'url' => 'invoices/' . $publicId, 
				'title' => '- ' . $invoice->invoice_number,
				'clients' => Client::scope()->with('contacts', 'country')->orderBy('name')->where('id',$invoice->client->id)->get(),
				'client' => $invoice->client);
		$data = array_merge($data, self::getViewModel());

		// Set the invitation link on the client's contacts
		$clients = $data['clients'];
		foreach ($clients as $client)
		{
			if ($client->id == $invoice->client->id)
			{
				foreach ($invoice->invitations as $invitation)
				{
					foreach ($client->contacts as $contact)
					{
						if ($invitation->contact_id == $contact->id)
						{
							$contact->invitation_link = $invitation->getLink();
						}
					}				
				}
				break;
			}
		}
	
		return View::make('invoices.edit', $data);
	}

	public function create($clientPublicId = 0)
	{		
		$client = null;
		$invoiceNumber = Auth::user()->branch->getNextInvoiceNumber();
		$account = Account::with('country')->findOrFail(Auth::user()->account_id);

		if ($clientPublicId) 
		{
			$client = Client::scope($clientPublicId)->firstOrFail();
  		}

		$data = array(
				'account' => $account,
				'invoice' => null,
				'data' => Input::old('data'), 
				'invoiceNumber' => $invoiceNumber,
				'method' => 'POST', 
				'url' => 'invoices', 
				'title' => '- New Invoice',
				'clients' => Client::scope()->with('contacts', 'country')->orderBy('name')->where('id',$clientPublicId)->get(),
				'client' => $client);
		$data = array_merge($data, self::getViewModel());				
		return View::make('invoices.edit', $data);
	}

	private static function getViewModel()
	{
		return [
			'account' => Auth::user()->account,
			'branch' => Auth::user()->branch,
			'products' => Product::scope()->with('prices')->orderBy('id')->get(),
			'countries' => Country::remember(DEFAULT_QUERY_CACHE)->orderBy('name')->get(),
			'taxRates' => TaxRate::scope()->orderBy('name')->get(),
			'currencies' => Currency::orderBy('name')->get(),
			'industries' => Industry::remember(DEFAULT_QUERY_CACHE)->orderBy('id')->get(),				
			'invoiceDesigns' => InvoiceDesign::remember(DEFAULT_QUERY_CACHE)->orderBy('id')->get(),
			'invoiceLabels' => Auth::user()->account->getInvoiceLabels(),
			'frequencies' => array(
				1 => 'Semanal',
				2 => 'Cada 2 semanas',
				3 => 'Cada 4 semanas',
				4 => 'Mensual',
				5 => 'Trimestral',
				6 => 'Semestral',
				7 => 'Anual'
			)
		];
	}

	/**
	 * Store a newly created resource in storage.
	 *
	 * @return Response
	 */
	public function store()
	{		
		return InvoiceController::save();
	}

	private function save($publicId = null)
	{	
		$action = Input::get('action');
		
		if ($action == 'archive' || $action == 'delete')
		{
			return InvoiceController::bulk();
		}

		$input = json_decode(Input::get('data'));					
		
		$invoice = $input->invoice;

		// if ($errors = $this->invoiceRepo->getErrors($invoice))
		// {					
		// 	Session::flash('error', trans('texts.invoice_error'));

		// 	return Redirect::to('invoices/create')
		// 		->withInput()->withErrors($errors);
		// } 
		// else 
		// {			
			$this->taxRateRepo->save($input->tax_rates);
						
			$clientData = (array) $invoice->client;			
			$client = $this->clientRepo->save($invoice->client->public_id, $clientData);
						
			$invoiceData = (array) $invoice;
			$invoiceData['client_id'] = $client->id;
			$invoiceData['nit'] = $client->nit;
			$invoiceData['name'] = $client->name;
			$invoice = $this->invoiceRepo->save($publicId, $invoiceData);
			
			$account = Auth::user()->account;
			if ($account->invoice_taxes != $input->invoice_taxes 
						|| $account->invoice_item_taxes != $input->invoice_item_taxes
						|| $account->invoice_design_id != $input->invoice->invoice_design_id)
			{
				$account->invoice_taxes = $input->invoice_taxes;
				$account->invoice_item_taxes = $input->invoice_item_taxes;
				$account->invoice_design_id = $input->invoice->invoice_design_id;
				$account->save();
			}

			$client->load('contacts');
			$sendInvoiceIds = [];

			foreach ($client->contacts as $contact)
			{
				if ($contact->send_invoice || count($client->contacts) == 1)
				{	
					$sendInvoiceIds[] = $contact->id;
				}
			}
			
			foreach ($client->contacts as $contact)
			{
				$invitation = Invitation::scope()->whereContactId($contact->id)->whereInvoiceId($invoice->id)->first();
				
				if (in_array($contact->id, $sendInvoiceIds) && !$invitation) 
				{	
					$invitation = Invitation::createNew();
					$invitation->invoice_id = $invoice->id;
					$invitation->contact_id = $contact->id;
					$invitation->invitation_key = str_random(RANDOM_KEY_LENGTH);
					$invitation->save();
				}				
				else if (!in_array($contact->id, $sendInvoiceIds) && $invitation)
				{
					$invitation->delete();
				}
			}

			$branch = Auth::user()->branch;
			$invoice_dateCC = date("Ymd", strtotime($invoice->invoice_date));
			$invoice_date = date("d/m/Y", strtotime($invoice->invoice_date));
			$invoice_date_limitCC = date("d/m/Y", strtotime($branch->deadline));

			require_once(app_path().'/includes/control_code.php');

			
			$cod_control = codigoControl($invoice->invoice_number, $client->nit, $invoice_dateCC, $invoice->amount, $branch->number_autho, $branch->key_dosage);
			$invoice->control_code=$cod_control;
			$invoice->number_autho=$branch->number_autho;
			$invoice->deadline=$branch->deadline;
			$invoice->key_dosage=$branch->key_dosage;

			$invoice->activity_pri=$branch->activity_pri;
			$invoice->activity_sec1=$branch->activity_sec1;
			$invoice->law=$branch->law;

			$invoice->branch=$branch->name;
			$invoice->address1=$branch->address1;
			$invoice->address2=$branch->address2;
			$invoice->work_phone=$branch->postal_code;
			$invoice->city=$branch->city;
			$invoice->state=$branch->state;


			$invoice->save();

			require_once(app_path().'/includes/BarcodeQR.php');

			$ice = $invoice->amount-$invoice->fiscal;
			$desc = $invoice->subtotal-$invoice->amount;

			$amount = number_format($invoice->amount, 2, '.', '');
			$fiscal = number_format($invoice->fiscal, 2, '.', '');

			$icef = number_format($ice, 2, '.', '');
			$descf = number_format($desc, 2, '.', '');

			if($icef=="0.00"){
				$icef = 0;
			}
			if($descf=="0.00"){
				$descf = 0;
			}

			$qr = new BarcodeQR();
			$datosqr = '1006909025|'.$invoice->invoice_number.'|'.$invoice->number_autho.'|'.$invoice_date.'|'.$amount.'|'.$fiscal.'|'.$invoice->control_code.'|'.$invoice->nit.'|'.$icef.'|0|0|'.$descf;
			$qr->text($datosqr); 
			$qr->draw(150, 'qr/' . $account->account_key .'_'. $invoice->invoice_number . '.png');
			$input_file = 'qr/' . $account->account_key .'_'. $invoice->invoice_number . '.png';
			$output_file = 'qr/' . $account->account_key .'_'. $invoice->invoice_number . '.jpg';

			$inputqr = imagecreatefrompng($input_file);
			list($width, $height) = getimagesize($input_file);
			$output = imagecreatetruecolor($width, $height);
			$white = imagecolorallocate($output,  255, 255, 255);
			imagefilledrectangle($output, 0, 0, $width, $height, $white);
			imagecopy($output, $inputqr, 0, 0, 0, 0, $width, $height);
			imagejpeg($output, $output_file);

			$invoice->qr=HTML::image_data('qr/' . $account->account_key .'_'. $invoice->invoice_number . '.jpg');
			$invoice->save();



			$message = trans($publicId ? 'texts.updated_invoice' : 'texts.created_invoice');
			if ($input->invoice->client->public_id == '-1')
			{
				$message = $message . ' ' . trans('texts.and_created_client');

				$url = URL::to('clients/' . $client->public_id);
				Utils::trackViewed($client->getDisplayName(), ENTITY_CLIENT, $url);
			}
			
			if ($action == 'clone')
			{
				return InvoiceController::cloneInvoice($publicId);
			}
			else if ($action == 'email') 
			{	
				if (Auth::user()->confirmed)
				{
					$message = trans('texts.emailed_invoice');
					$this->mailer->sendInvoice($invoice);
					Session::flash('message', $message);
				}
				else
				{
					$errorMessage = trans(Auth::user()->registered ? 'texts.confirmation_required' : 'texts.registration_required');
					Session::flash('error', $errorMessage);
					Session::flash('message', $message);					
				}
			} 
			else 
			{				
				Session::flash('message', $message);
			}

			$url = 'invoices/'. $invoice->public_id . '/edit';

			return Redirect::to($url);
		// }
	}

	/**
	 * Display the specified resource.
	 *
	 * @param  int  $id
	 * @return Response
	 */
	public function show($publicId)
	{
		Session::reflash();
		
		return Redirect::to('invoices/'.$publicId.'/edit');
	}

	/**
	 * Update the specified resource in storage.
	 *
	 * @param  int  $id
	 * @return Response
	 */
	public function update($publicId)
	{
		return InvoiceController::save($publicId);
	}

	/**
	 * Remove the specified resource from storage.
	 *
	 * @param  int  $id
	 * @return Response
	 */
	public function bulk()
	{
		$action = Input::get('action');
		$ids = Input::get('id') ? Input::get('id') : Input::get('ids');
		$count = $this->invoiceRepo->bulk($ids, $action);

 		if ($count > 0)		
 		{
			$message = Utils::pluralize('Successfully '.$action.'d ? invoice', $count);
			Session::flash('message', $message);
		}

		return Redirect::to('invoices');
	}

	//POS
	public function guardarFactura()
    {
    	/* David 
    	 Guardando  factura con el siguiente formato:
    	
			{"invoice_items":[{"qty":"2","id":"2"}],"client_id":"1"}
			//nuevo formato para la cascada XD
			{"invoice_items":[{"qty":"2","id":"2","boni":"1","desc":"3"}],"client_id":"1"}

    	*/
		$input = Input::all();
        
		// $invoice_number = Auth::user()->account->getNextInvoiceNumber();
		$invoice_number = Auth::user()->branch->getNextInvoiceNumber();


		$client_id = $input['client_id'];

		

		$client = DB::table('clients')->select('id','nit','name','public_id')->where('id',$input['client_id'])->first();

		//update for response change client  nit and name for the new entry	
	
		
		//$swap = false ;
		if(strcmp($input['nit'],$client->nit) xor strcmp($input['name'],$client->name))
		{

				DB::table('clients')
				->where('id',$input['client_id'])
				->update(array('nit' => $input['nit'],'name'=>$input['name']));
				//if change nit an name  update client
				$client = DB::table('clients')->select('id','nit','name','public_id')->where('id',$input['client_id'])->first();
		//		$swap = true;
		}

		$user_id = Auth::user()->getAuthIdentifier();
		$user  = DB::table('users')->select('account_id','branch_id','price_type_id')->where('id',$user_id)->first();
		
		//$account_id = $user->account_id;
		// $account = DB::table('accounts')->select('num_auto','llave_dosi','fecha_limite')->where('id',$user->account_id)->first();
		//$branch = DB::table('branches')->select('num_auto','llave_dosi','fecha_limite','address1','address2','country_id','industry_id')->where('id',$user['branch_id'])->first();
		//$branch = DB::table('branches')->select('num_auto','llave_dosi','fecha_limite','address1','address2','country_id','industry_id')->where('id','=',$user->branch_id)->first();	
    	// $branch = DB::table('branches')->select('number_autho','key_dosage','deadline','address1','address2','country_id','industry_id','law','activity_pri','activity_sec1','name')->where('id','=',$user->branch_id)->first();	
    	$branch = DB::table('branches')->where('id','=',$user->branch_id)->first();	
    	$items = $input['invoice_items'];
    
    	$amount = 0;
    	$subtotal=0;
    	$fiscal=0;
    	$icetotal=0;
    	$bonidesc =0;
    	foreach ($items as $item) 
    	{
    		# code...
    		$product_id = $item['id'];
    		 
    		$pr = DB::table('products')
    							->join('prices',"product_id","=",'products.id')
    					
    							->select('products.id','products.notes','prices.cost','products.ice','products.units','products.cc')
    						    ->where('prices.price_type_id','=',$user->price_type_id)
    						    ->where('products.account_id','=',$user->account_id)
    						    ->where('products.id',"=",$product_id)
    							->first();

    		// $pr = DB::table('products')->select('cost')->where('id',$product_id)->first();
    		
    		$qty = (int) $item['qty'];
    		$cost = $pr->cost/$pr->units;
    		$st = ($cost * $qty);
    		$subtotal = $subtotal + $st; 
    		$bd= ($item['boni']*$cost) + $item['desc'];
    		$bonidesc= $bonidesc +$bd;
    		$amount = $amount +$st-$bd;

    		$ice = DB::table('tax_rates')->select('rate')->where('name','=','ice')->first();
    		if($pr->ice == 1)
    		{
    			
    			//caluclo ice bruto 
    			$iceBruto = ($qty *($pr->cc/1000)*$ice->rate);
    			$iceNeto = (((int)$item['boni']) *($pr->cc/1000)*$ice->rate);
    			$icetotal = $icetotal +($iceBruto-$iceNeto) ;
    			// $fiscal = $fiscal + ($amount - ($item['qty'] *($pr->cc/1000)*$ice->rate) );
    		}
    		
    			// $fiscal = $fiscal +$amount;

    			

    	}
    	$fiscal = $amount -$bonidesc-$icetotal;

    	$balance= $amount;
    	/////////////////////////hasta qui esta bien al parecer hacer prueba de que fuciona el join de los productos XD
    	$invoice_dateCC = date("Ymd");
    	$invoice_date = date("Y-m-d");
    
		$invoice_date_limitCC = date("Y-m-d", strtotime($branch->deadline));

		require_once(app_path().'/includes/control_code.php');	
		$cod_control = codigoControl($invoice_number, $client->nit, $invoice_dateCC, $amount, $branch->number_autho, $branch->key_dosage);
	     $ice = DB::table('tax_rates')->select('rate')->where('name','=','ice')->first();
	     //
	     // creando invoice
	     $invoice = Invoice::createNew();
	     $invoice->invoice_number=$invoice_number;
	     $invoice->client_id=$client_id;
	     $invoice->user_id=$user_id;
	     $invoice->account_id = $user->account_id;
	     $invoice->branch_id= $user->branch_id;
	     $invoice->amount =number_format((float)$amount, 2, '.', '');	
	     $invoice->subtotal = $subtotal;
	     $invoice->fiscal =$fiscal;
	     $invoice->law = $branch->law;
	     $invoice->balance=$balance;
	     $invoice->control_code=$cod_control;
	     $invoice->start_date =$invoice_date;
	     $invoice->invoice_date=$invoice_date;

		 $invoice->activity_pri=$branch->activity_pri;
	     $invoice->activity_sec1=$branch->activity_sec1;
	     
	     // $invoice->invoice
	     $invoice->end_date=$invoice_date_limitCC;
	     //datos de la empresa atra vez de una consulta XD
	     /*****************error generado al intentar guardar **/
	   	 // $invoice->branch = $branch->name;
	     $invoice->address1=$branch->address1;
	     $invoice->address2=$branch->address2;
	     $invoice->number_autho=$branch->number_autho;
	     $invoice->work_phone=$branch->postal_code;
			$invoice->city=$branch->city;
			$invoice->state=$branch->state;
	     // $invoice->industry_id=$branch->industry_id;

	     $invoice->country_id= $branch->country_id;
	     $invoice->key_dosage = $branch->key_dosage;
	     $invoice->deadline = $branch->deadline;
	     $invoice->custom_value1 =$icetotal;
	     $invoice->ice = $ice->rate;
	     //cliente
	     $invoice->nit=$client->nit;
	     $invoice->name =$client->name;

	     $invoice->save();
	     
	     $account = Auth::user()->account;
	     require_once(app_path().'/includes/BarcodeQR.php');

			$ice = $invoice->amount-$invoice->fiscal;
			$desc = $invoice->subtotal-$invoice->amount;

			$amount = number_format($invoice->amount, 2, '.', '');
			$fiscal = number_format($invoice->fiscal, 2, '.', '');

			$icef = number_format($ice, 2, '.', '');
			$descf = number_format($desc, 2, '.', '');

			if($icef=="0.00"){
				$icef = 0;
			}
			if($descf=="0.00"){
				$descf = 0;
			}

			$qr = new BarcodeQR();
			$datosqr = '1006909025|'.$invoice->invoice_number.'|'.$invoice->number_autho.'|'.$invoice_date.'|'.$amount.'|'.$fiscal.'|'.$invoice->control_code.'|'.$invoice->nit.'|'.$icef.'|0|0|'.$descf;
			$qr->text($datosqr); 
			$qr->draw(150, 'qr/' . $account->account_key .'_'. $invoice->invoice_number . '.png');
			$input_file = 'qr/' . $account->account_key .'_'. $invoice->invoice_number . '.png';
			$output_file = 'qr/' . $account->account_key .'_'. $invoice->invoice_number . '.jpg';

			$inputqr = imagecreatefrompng($input_file);
			list($width, $height) = getimagesize($input_file);
			$output = imagecreatetruecolor($width, $height);
			$white = imagecolorallocate($output,  255, 255, 255);
			imagefilledrectangle($output, 0, 0, $width, $height, $white);
			imagecopy($output, $inputqr, 0, 0, 0, 0, $width, $height);
			imagejpeg($output, $output_file);

			$invoice->qr=HTML::image_data('qr/' . $account->account_key .'_'. $invoice->invoice_number . '.jpg');

	     	$invoice->save();
	     	 DB::table('invoices')
            ->where('id', $invoice->id)
            ->update(array('branch' => $branch->name));
	     //error verificar

	     // $invoice = DB::table('invoices')->select('id')->where('invoice_number',$invoice_number)->first();

	     //guardadndo los invoice items
	    foreach ($items as $item) 
    	{
    		
    		
    		
    		// $product = DB::table('products')->select('notes')->where('id',$product_id)->first();
    		  $product_id = $item['id'];
	    		 
	    		$product = DB::table('products')
	    							->join('prices',"product_id","=",'products.id')
	    					
	    							->select('products.id','products.notes','prices.cost','products.ice','products.units','products.cc','products.product_key')
	    						    ->where('prices.price_type_id','=',$user->price_type_id)
	    						    ->where('products.account_id','=',$user->account_id)
	    						    ->where('products.id',"=",$product_id)
	    							->first();

	    		// $pr = DB::table('products')->select('cost')->where('id',$product_id)->first();
	    		
	    		
	    		$cost = $product->cost/$product->units;
	    		$line_total= ((int)$item['qty'])*$cost;

    		
    		  $invoiceItem = InvoiceItem::createNew();
    		  $invoiceItem->invoice_id = $invoice->id; 
		      $invoiceItem->product_id = $product_id;
		      $invoiceItem->product_key = $product->product_key;
		      $invoiceItem->notes = $product->notes;
		      $invoiceItem->cost = $cost;
		      $invoiceItem->boni = (int)$item['boni'];
		      $invoiceItem->desc =$item['desc'];
		      $invoiceItem->qty = (int)$item['qty'];
		      $invoiceItem->line_total=$line_total;
		      $invoiceItem->tax_rate = 0;
		      $invoiceItem->save();
		  
    	}
    	

    	$invoiceItems =DB::table('invoice_items')
    				   ->select('notes','cost','qty','boni','desc')
    				   ->where('invoice_id','=',$invoice->id)
    				   ->get(array('notes','cost','qty','boni','desc'));

    	$date = new DateTime($invoice->deadline);
    	$dateEmision = new DateTime($invoice->invoice_date);
    	$account  = array('name' =>$branch->name,'nit'=>'1006909025' );
    	$ice = $invoice->amount-$invoice->fiscal;

    	$factura  = array('invoice_number' => $invoice->invoice_number,
    					'control_code'=>$invoice->control_code,
    					'invoice_date'=>$dateEmision->format('d-m-Y'),
    					'amount'=>number_format((float)$invoice->amount, 2, '.', ''),
    					'subtotal'=>number_format((float)$invoice->subtotal, 2, '.', ''),
    					'fiscal'=>number_format((float)$invoice->fiscal, 2, '.', ''),
    					'client'=>$client,
    					// 'id'=>$invoice->id,

    					'account'=>$account,
    					'law' => $invoice->law,
    					'invoice_items'=>$invoiceItems,
    					'address1'=>str_replace('+', '°', $invoice->address1),
    					// 'address2'=>str_replace('+', '°', $invoice->address2),
    					'address2'=>$invoice->address2,
    					'num_auto'=>$invoice->number_autho,
    					'fecha_limite'=>$date->format('d-m-Y'),
    					//'swap'=>$swap,
    					// 'fecha_emsion'=>,
    					'ice'=>number_format((float)$ice, 2, '.', '')	
    					
    					);

    	// $invoic = Invoice::scope($invoice_number)->withTrashed()->with('client.contacts', 'client.country', 'invoice_items')->firstOrFail();
		// $d  = Input::all();
		//en caso de problemas irracionales me refiero a que se jodio  
		// $input = Input::all();
		// $client_id = $input['client_id'];
		// $client = DB::table('clients')->select('id','nit','name')->where('id',$input['client_id'])->first();

		// $datos = array('hola ' => $input);

		// return Response::json($datos);
		return Response::json($factura);
       
    }

    public function listasCuenta()
    {	
    	$user_id = Auth::user()->getAuthIdentifier();
    	$user = DB::table('users')->select('account_id','price_type_id','branch_id')->where('id',$user_id)->first();

    	// $clients = DB::table('clients')->select('id','name','nit')->where('account_id',$user->account_id)->get(array('id','name','nit'));
    	
    	$products = DB::table('products')
    							->join('prices',"product_id","=",'products.id')
    							->select('products.id','products.product_key','products.notes','prices.cost','products.ice','products.units','products.cc')
    						    ->where('products.account_id','=',$user->account_id)
    							->where('prices.price_type_id','=',$user->price_type_id)
    							->get(array('products.id','product_key','notes','cost','ice','units','cc'));

    	// $ice = DB::table('tax_rates')->select('rate')
    	// 							 // ->where('account_id','=',$user->account_id)
    	// 							 ->where('name','=','ice')
    	// 							 ->first();


    	$mensaje = array(
    			//'clientes' => $clients,
    			//'user'=> $user,
    			'productos' => $products
    			//'ice'=>$ice->rate
    		);
    	return Response::json($mensaje);


    }
    public function factura($numeroFactura)
	{
		$invoice = Invoice::scope($numeroFactura)->withTrashed()->with('account.country', 'client.contacts', 'client.country', 'invoice_items')->firstOrFail();

		return Response::json($invoice);
	}
	//recuperar FActura 
	public function facturas()
	{
		$account_id= Auth::user()->getAuthIdentifier();
		//$name = DB::table('users')->where('name', 'John')->pluck('name');
		//$invoices = DB::table('invoices')->where('account_id',$account_id)->pluck('invoice_number');

		// DB::table('users')
  //           ->join('contacts', 'users.id', '=', 'contacts.user_id')
  //           ->join('orders', 'users.id', '=', 'orders.user_id')
  //           ->select('users.id', 'contacts.phone', 'orders.price');
	//	$invoices = Invoice::withTrashed()->select('invoice_number','amount','client_id')->where('account_id', $account_id)->get();
		$invoices = DB::table('invoices')
					 ->join('clients', 'clients.id', '=', 'invoices.client_id')
					 ->where('invoices.account_id','=',$account_id)
					 ->where('invoices.state','=','0')
					 ->orderBy('invoices.invoice_number')
					 ->get(array('invoices.id','invoices.invoice_number','invoices.amount','clients.nit','clients.name'));
					//->select('invoices.invoice_number','invoices.amount','invoices.client_id')->where('invoices.account_id', $account_id);
					// ->join('clients','invoices.client_id','=','clients.id')
					// ->select('invoices.invoice_numer','invoices.amount','clients.nit');
					// //->select('invoices.invoice_numer','invoices.amount','clients.nit')->where('invoices.account_id', $account_id)->get();
		return Response::json($invoices);
	}

	public function printFactura($numeroFactura)
	{

		$account_id= Auth::user()->getAuthIdentifier();

		//Actualizando el estado de la factura
		    DB::table('invoices')
			
			->where('account_id','=',$account_id)
			->where('invoice_number','=',$numeroFactura)
			->update(array(
				'state' => 1
			));

			
		// devolviendo la lista de facturas actualizada
		$invoices = DB::table('invoices')
					 ->join('clients', 'clients.id', '=', 'invoices.client_id')
					 ->where('invoices.account_id','=',$account_id)
					 ->where('invoices.state','=','0')
					 ->orderBy('invoices.invoice_number')
					 ->get(array('invoices.invoice_number','invoices.amount','clients.nit','clients.name'));
		
		 return Response::json($invoices);
	
	}
	public function mensajeInvoice()
	{
		$input = Input::all();
		$user_id = Auth::user()->getAuthIdentifier();
		$user  = DB::table('users')->select('account_id','branch_id','price_type_id')->where('id',$user_id)->first();
		 DB::table('invoices')
            ->where('id', 71)
            ->update(array('branch' => 'hola '));
		$branch = DB::table('branches')->select('number_autho','key_dosage','deadline','address1','address2','country_id','industry_id','law','activity_pri','activity_sec1','name')->where('id','=',$user->branch_id)->first();
		$datos = array('hola ' => $branch->name );

		return Response::json($datos);
	}

	// modulos offline

		public function listaOffline()
    {
    	$user_id = Auth::user()->getAuthIdentifier();
    	$user = DB::table('users')->select('account_id','price_type_id','branch_id','id','groups')->where('id',$user_id)->first();
    	// $grupo = DB::table('groups')->where('account_id','=',$user->account_id)
    	// 							->where('user_id','=',$user->id)
    	// 							->first();
    	$grupo = explode(',',$user->groups);
    	$array =array();
    	foreach ($grupo as $idgrupo) {

    		$idg = $idgrupo+1;
    		$cliente = DB::table('clients')->select('id','name','nit')
    							->where('account_id',$user->account_id)
    							->where('group_id',$idg)
    							->first();
    		$array[] =$cliente;
    		# code...
    	}

    	$clients = DB::table('clients')->select('id','name','nit')->where('account_id',$user->account_id)->get(array('id','name','nit'));
    
    	$products = DB::table('products')
    							->join('prices',"product_id","=",'products.id')
    							->select('products.id','products.product_key','products.notes','prices.cost','products.ice','products.units','products.cc')
    						    ->where('products.account_id','=',$user->account_id)
    							->where('prices.price_type_id','=',$user->price_type_id)
    							->get(array('products.id','product_key','notes','cost','ice','units','cc'));

    	// $ice = DB::table('tax_rates')->select('rate')
    	// 							 // ->where('account_id','=',$user->account_id)
    	// 							 ->where('name','=','ice')
    	// 							 ->first();


    	$mensaje = array(
    			// 'clientes' => $clients,
    			'clientes' =>$array,
    			'user' =>$user,
    			'user_id' => $user_id,
    			'user'=> $user,
    			'grupo'=> $grupo
    			// 'productos' => $products
    			//'ice'=>$ice->rate
    		);
    	return Response::json($mensaje);

    }
     public function datosOffline()
    {
		$user_id = Auth::user()->getAuthIdentifier();
    	$user = DB::table('users')->select('account_id','price_type_id','branch_id','id','groups')->where('id',$user_id)->first();
    	// $grupo = DB::table('groups')->where('account_id','=',$user->account_id)
    	// 							->where('user_id','=',$user->id)
    	// 							->first();
    	$grupo = explode(',',$user->groups);
    	$array =array();
    	foreach ($grupo as $idgrupo) {

    		$idg = $idgrupo+1;
    		$cliente = DB::table('clients')->select('id','name','nit')
    							->where('account_id',$user->account_id)
    							->where('group_id',$idg)
    							->first();
    		$array[] =$cliente;
    		# code...
    	}

    	// $clients = DB::table('clients')->select('id','name','nit','public_id')->where('account_id',$user->account_id)->get(array('id','name','nit','public_id'));
    	
    	$products = DB::table('products')
    							->join('prices',"product_id","=",'products.id')
    							->select('products.id','products.product_key','products.notes','prices.cost','products.ice','products.units','products.cc')
    						    ->where('products.account_id','=',$user->account_id)
    							->where('prices.price_type_id','=',$user->price_type_id)
    							->get(array('products.id','product_key','notes','cost','ice','units','cc'));

    	$sucursal = DB::table('branches')
    						->select('name','address1','address2','number_autho','deadline','key_dosage','activity_pri','invoice_number_counter','law')
    						->where('id','=',$user->branch_id)
    						->first();

    	$ice = DB::table('tax_rates')->select('rate')
    								 // ->where('account_id','=',$user->account_id)
    								 ->where('name','=','ice')
    								 ->first();


    	$mensaje = array(
    			// 'clientes' => $array,//esta lista estara dentro del menu principal
    			'sucursal'=> $sucursal,
    			'productos' => $products,
    			'ice'=>$ice->rate
    		);
    	return Response::json($mensaje);    	

    }

    private function setDataOffline(){
	    	$data = "[
		   {
		    \"invoice_items\": [
		      {
		        \"boni\": \"3\",
		        \"desc\": \"2\",
		        \"qty\": \"41\",
		        \"id\": \"1\"
		      },
		      {
		        \"boni\": \"5\",
		        \"desc\": \"0\",
		        \"qty\": \"4\",
		        \"id\": \"2\"
		      }
		    ],
		    \"fecha\": \"01-10-2015\",
		    \"name\": \"MOLLISACA1\",
		    \"cod_control\": \"AB-07-3B-27\",
		    \"nit\": \"122038325\",
		    \"invoice_number\": \"9\",
		    \"client_id\": \"21\"
		  },
		  {
		    \"invoice_items\": [
		      {
		        \"boni\": \"3\",
		        \"desc\": \"2\",
		        \"qty\": \"41\",
		        \"id\": \"1\"
		      }
		    ],
		    \"fecha\": \"01-10-2015\",
		    \"name\": \"MOLLISACA2\",
		    \"cod_control\": \"D4-21-5F-0B\",
		    \"nit\": \"122038325\",
		    \"invoice_number\": \"10\",
		    \"client_id\": \"10\"
		  },
		  {
		    \"invoice_items\": [
		      {
		        \"boni\": \"3\",
		        \"desc\": \"2\",
		        \"qty\": \"41\",
		        \"id\": \"1\"
		      }
		    ],
		    \"fecha\": \"01-10-2015\",
		    \"name\": \"MOLLISACA3\",
		    \"cod_control\": \"D4-21-5F-0B\",
		    \"nit\": \"122038325\",
		    \"invoice_number\": \"11\",
		    \"client_id\": \"11\"
		  },
		  {
		    \"invoice_items\": [
		      {
		        \"boni\": \"3\",
		        \"desc\": \"2\",
		        \"qty\": \"41\",
		        \"id\": \"1\"
		      }
		    ],
		    \"fecha\": \"01-10-2015\",
		    \"name\": \"MOLLISACA4\",
		    \"cod_control\": \"D4-21-5F-0B\",
		    \"nit\": \"122038325\",
		    \"invoice_number\": \"12\",
		    \"client_id\": \"10\"
		  }
			]"; 
		$datos = json_decode($data);
		return $datos;
    }

    private function completeFields($factura)
    {
    	$invoice_items = array();

    	$datos = $factura;    	    	
		$user_id = Auth::user()->getAuthIdentifier();
		$user  = DB::table('users')->select('account_id','branch_id','price_type_id')->where('id',$user_id)->first();
    	$ice = DB::table('tax_rates')->select('rate')->where('name','=','ice')->first();
    	$branch = DB::table('branches')->where('id','=',$user->branch_id)->first();	

    	foreach ($factura->invoice_items as $key => $item) {
    		$product = DB::table('products')
    							->join('prices',"product_id","=",'products.id')    					
    							->select('products.id','products.notes','prices.cost','products.ice','products.units','products.cc','products.product_key')
    						    ->where('prices.price_type_id','=',$user->price_type_id)
    						    ->where('products.account_id','=',$user->account_id)
    						    ->where('products.id',"=",$item->id)
    							->first();
			$new_item = [
				'boni'	=>	$item->boni,
				'desc'	=>	$item->desc,
				'qty'	=>	$item->qty,
				'id' 	=>	$item->id,
				'units'		=>	$product->units,
				'cost'		=>	$product->cost,
				'ice'		=>	$product->ice,
				'cc'		=>	$product->cc,
				'product_key'	=>	$product->product_key,
				'notes'		=>	$product->notes,				
			];

			array_push($invoice_items, $new_item) ;
    	}
    	$new = [
    	    'fecha'	=>	$datos->fecha,
    	    'name'	=>	$datos->name,
    	    'cod_control'	=>	$datos->cod_control,
    	    'nit'	=>	$datos->nit,
    	    'invoice_number'	=>	$datos->invoice_number,
    	    'client_id'	=>	$datos->client_id,//until here is sent from POS
    	    'user_id'	=>	$user_id,
    	    'ice'	=>	$ice->rate,
    	    'deadline'	=>	$branch->deadline,
    	    'account_id'	=>	'1',
    	    'branch_id'	=>	$branch->id,
    	    'law'	=>	$branch->law,
    	    'activity_pri'	=>	$branch->activity_pri,
    	    'activity_sec1'	=>	$branch->activity_sec1,
    	    'address1'	=>	$branch->address1,
    	    'address2'	=>	$branch->address2,
    	    'number_autho'	=>	$branch->number_autho,
    	    'postal_code'	=>	$branch->postal_code,
    	    'city'	=>	$branch->city,
    	    'state'	=>	$branch->state,
    	    'country_id'	=>	$branch->country_id,
    	    'key_dosage'	=>	$branch->key_dosage,
    	    'branch'	=>	$branch->name,
    	    'invoice_items'	=> $invoice_items,
    	];

    	return $new;
    }
    
    public function saveOfflineInvoices(){

    	//$this->saveBackUpToMirror();
    	//return 0;    	

    	//$input = Input::all();

    	$respuesta = array();
    	//$input =  $this->setDataOffline();
    	$input = Input::all();

    	$backup = array();
    	$cantidad =  0;
    	foreach ($input as $key => $factura) {    		
			array_push($backup, $this->completeFields($factura));    		
    		array_push($respuesta, $factura->invoice_number);  		
    		$cant++;
    	}
    	$input = $this->saveBackUpToMirror($backup);
		$input = json_decode($input);
    	
    	foreach ($input as $key => $factura) {    				
    		$this->saveOfflineInvoice($factura);	    		
    		//array_push($respuesta, $factura->invoice_number);  		
    	}
    	
    	$datos = array('resultado ' => "0",'respuesta'=>$cantidad);		
    	//print_r($datos);
		return Response::json($datos);
    }
    private function saveOfflineInvoice($factura)
    {

		$input = $factura;
		// $invoice_number = Auth::user()->account->getNextInvoiceNumber();
		$invoice_number = (int)Auth::user()->branch->getNextInvoiceNumber();

		$numero =(int) $input->invoice_number;

		// $numero =(int)  $input['invoice_number'];

		// if($invoice_number!=$numero)
		// {			
		// 	return Response::json( array('resultado' => '1' ,'invoice_number'=>$invoice_number));

		// }
		$client_id = $input->client_id;
		// $client = DB::table('clients')->select('id','nit','name','public_id')->where('id',$input['client_id'])->first();

		$user_id = Auth::user()->getAuthIdentifier();
		$user  = DB::table('users')->select('account_id','branch_id','price_type_id')->where('id',$user_id)->first();
		
		//$account_id = $user->account_id;
		// $account = DB::table('accounts')->select('num_auto','llave_dosi','fecha_limite')->where('id',$user->account_id)->first();
		//$branch = DB::table('branches')->select('num_auto','llave_dosi','fecha_limite','address1','address2','country_id','industry_id')->where('id',$user['branch_id'])->first();
		//$branch = DB::table('branches')->select('num_auto','llave_dosi','fecha_limite','address1','address2','country_id','industry_id')->where('id','=',$user->branch_id)->first();	
    	// $branch = DB::table('branches')->select('number_autho','key_dosage','deadline','address1','address2','country_id','industry_id','law','activity_pri','activity_sec1','name')->where('id','=',$user->branch_id)->first();	
    	$branch = DB::table('branches')->where('id','=',$user->branch_id)->first();	
    	$items = $input->invoice_items;
    	// echo "this is initializioinh";
    	// print_r($input->invoice_items); 
		// return 0;
    	
    	// $linea ="";
    	$amount = 0;
    	$subtotal=0;
    	$fiscal=0;
    	$icetotal=0;
    	$bonidesc =0;
    	foreach ($items as $item) 
    	{
    		# code...
    		$product_id = $item->id;
    		 
    		$pr = DB::table('products')
    							->join('prices',"product_id","=",'products.id')
    					
    							->select('products.id','products.notes','prices.cost','products.ice','products.units','products.cc')
    						    ->where('prices.price_type_id','=',$user->price_type_id)
    						    ->where('products.account_id','=',$user->account_id)
    						    ->where('products.id',"=",$product_id)
    							->first();

    		// $pr = DB::table('products')->select('cost')->where('id',$product_id)->first();
    		
    		$qty = (int) $item->qty;
    		$cost = $pr->cost/$pr->units;
    		$st = ($cost * $qty);
    		$subtotal = $subtotal + $st; 
    		$bd= ($item->boni*$cost) + $item->desc;
    		$bonidesc= $bonidesc +$bd;
    		$amount = $amount +$st-$bd;

    		$ice = DB::table('tax_rates')->select('rate')->where('name','=','ice')->first();
    		if($pr->ice == 1)
    		{
    			
    			//caluclo ice bruto 
    			$iceBruto = ($qty *($pr->cc/1000)*$ice->rate);
    			$iceNeto = (((int)$item->boni) *($pr->cc/1000)*$ice->rate);
    			$icetotal = $icetotal +($iceBruto-$iceNeto) ;
    			// $fiscal = $fiscal + ($amount - ($item['qty'] *($pr->cc/1000)*$ice->rate) );
    		}
    		
    		

    			

    	}
    	$fiscal = $amount -$bonidesc-$icetotal;

    	$balance= $amount;
    	/////////////////////////hasta qui esta bien al parecer hacer prueba de que fuciona el join de los productos XD
    	$invoice_dateCC = date("Ymd");
    	$invoice_date = date("Y-m-d");
    
		$invoice_date_limitCC = date("Y-m-d", strtotime($branch->deadline));

		// require_once(app_path().'/includes/control_code.php');	
		// $cod_control = codigoControl($invoice_number, $input['nit'], $invoice_dateCC, $amount, $branch->number_autho, $branch->key_dosage);
	     $cod_control = $input->cod_control;
	     $ice = DB::table('tax_rates')->select('rate')->where('name','=','ice')->first();
	     //
	     // creando invoice
	     $invoice = Invoice::createNew();
	     $invoice->invoice_number=$invoice_number;
	     $invoice->client_id=$client_id;
	     $invoice->user_id=$user_id;
	     $invoice->account_id = $user->account_id;
	     $invoice->branch_id= $user->branch_id;
	     $invoice->amount =number_format((float)$amount, 2, '.', '');	
	     $invoice->subtotal = $subtotal;
	     $invoice->fiscal =$fiscal;
	     $invoice->law = $branch->law;
	     $invoice->balance=$balance;
	     $invoice->control_code=$cod_control;
	     $invoice->start_date =$invoice_date;
	     $invoice->invoice_date=$invoice_date;

		 $invoice->activity_pri=$branch->activity_pri;
	     $invoice->activity_sec1=$branch->activity_sec1;
	     
	     // $invoice->invoice
	     $invoice->end_date=$invoice_date_limitCC;
	     //datos de la empresa atra vez de una consulta XD
	     /*****************error generado al intentar guardar **/
	   	 // $invoice->branch = $branch->name;
	     $invoice->address1=$branch->address1;
	     $invoice->address2=$branch->address2;
	     $invoice->number_autho=$branch->number_autho;
	     $invoice->work_phone=$branch->postal_code;
			$invoice->city=$branch->city;
			$invoice->state=$branch->state;
	     // $invoice->industry_id=$branch->industry_id;

	     $invoice->country_id= $branch->country_id;
	     $invoice->key_dosage = $branch->key_dosage;
	     $invoice->deadline = $branch->deadline;
	     $invoice->custom_value1 =$icetotal;
	     $invoice->ice = $ice->rate;
	     //cliente
	     $invoice->nit=$input->nit;
	     $invoice->name =$input->name;

	     $invoice->save();
	     
	     $account = Auth::user()->account;
	     require_once(app_path().'/includes/BarcodeQR.php');

			$ice = $invoice->amount-$invoice->fiscal;
			$desc = $invoice->subtotal-$invoice->amount;

			$amount = number_format($invoice->amount, 2, '.', '');
			$fiscal = number_format($invoice->fiscal, 2, '.', '');

			$icef = number_format($ice, 2, '.', '');
			$descf = number_format($desc, 2, '.', '');

			if($icef=="0.00"){
				$icef = 0;
			}
			if($descf=="0.00"){
				$descf = 0;
			}

			// $qr = new BarcodeQR();
			// $datosqr = '1006909025|'.$input->invoice_number.'|'.$invoice->number_autho.'|'.$invoice_date.'|'.$amount.'|'.$fiscal.'|'.$invoice->nit.'|'.$icef.'|0|0|'.$descf;
			// $qr->text($datosqr); 
			// $qr->draw(150, 'qr/' . $account->account_key .'_'. $invoice->invoice_number . '.png');
			// $input_file = 'qr/' . $account->account_key .'_'. $invoice->invoice_number . '.png';
			// $output_file = 'qr/' . $account->account_key .'_'. $invoice->invoice_number . '.jpg';

			// $inputqr = imagecreatefrompng($input_file);
			// list($width, $height) = getimagesize($input_file);
			// $output = imagecreatetruecolor($width, $height);
			// $white = imagecolorallocate($output,  255, 255, 255);
			// imagefilledrectangle($output, 0, 0, $width, $height, $white);
			// imagecopy($output, $inputqr, 0, 0, 0, 0, $width, $height);
			// imagejpeg($output, $output_file);

			$invoice->qr="";//=HTML::image_data('qr/' . $account->account_key .'_'. $input->invoice_number . '.jpg');

	     	$invoice->save();
				$fecha =$input->fecha;
			$f = date("Y-m-d", strtotime($fecha));
	     	 DB::table('invoices')
            ->where('id', $invoice->id)
            ->update(array('branch' => $branch->name,'invoice_date'=>$f));
	     //error verificar

	     // $invoice = DB::table('invoices')->select('id')->where('invoice_number',$invoice_number)->first();

	     //guardadndo los invoice items
	    foreach ($items as $item) 
    	{
    		
    		
    		
    		// $product = DB::table('products')->select('notes')->where('id',$product_id)->first();
    		  $product_id = $item->id;
	    		 
	    		$product = DB::table('products')
	    							->join('prices',"product_id","=",'products.id')
	    					
	    							->select('products.id','products.notes','prices.cost','products.ice','products.units','products.cc','products.product_key')
	    						    ->where('prices.price_type_id','=',$user->price_type_id)
	    						    ->where('products.account_id','=',$user->account_id)
	    						    ->where('products.id',"=",$product_id)
	    							->first();

	    		// $pr = DB::table('products')->select('cost')->where('id',$product_id)->first();
	    		
	    		
	    		$cost = $product->cost/$product->units;
	    		$line_total= ((int)$item->qty)*$cost;

    		
    		  $invoiceItem = InvoiceItem::createNew();
    		  $invoiceItem->invoice_id = $invoice->id; 
		      $invoiceItem->product_id = $product_id;
		      $invoiceItem->product_key = $product->product_key;
		      $invoiceItem->notes = $product->notes;
		      $invoiceItem->cost = $cost;
		      $invoiceItem->boni = (int)$item->boni;
		      $invoiceItem->desc =$item->desc;
		      $invoiceItem->qty = (int)$item->qty;
		      $invoiceItem->line_total=$line_total;
		      $invoiceItem->tax_rate = 0;
		      $invoiceItem->save();
		  
    	}
    	

		$datos = array('resultado ' => "0");
		//colocar una excepcion en caso de error

		// return Response::json($datos);
		//return Response::json($datos);
       
    }

    private function saveBackUpToMirror($backup)
    {
    	
    	extract($_POST);
        $username='firstuser';
		$password='first_password';
		$URL='localhost/cascada_ventas/public/api/v1/ventas';
		//echo json_encode($backup);
		$fields= array(
			'ventas'=>urlencode(json_encode($backup)),			
			'adicional'=>urlencode("nada")
			);
		$fields_string="";
		foreach($fields as $key=>$value) { $fields_string .= $key.'='.$value.'&'; }
		rtrim($fields_string, '&');
		
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL,$URL);
		curl_setopt($ch, CURLOPT_TIMEOUT, 30); //timeout after 30 seconds
		curl_setopt($ch, CURLOPT_RETURNTRANSFER,1);
		curl_setopt($ch,CURLOPT_POST, count($fields));
		curl_setopt($ch,CURLOPT_POSTFIELDS, $fields_string);
		curl_setopt($ch, CURLOPT_HTTPAUTH, CURLAUTH_ANY);
		curl_setopt($ch, CURLOPT_USERPWD, "$username:$password");
		$status_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);   //get status code
		$response = curl_exec($ch);
		if(!$response){
    		die('Error: "' . curl_error($ch) . '" - Code: ' . curl_errno($ch));
		}						
		curl_close ($ch);
		return $response;

    }

    private function isConnected($url=NULL)  
	{  

	    if($url == NULL) return false;  
	    $ch = curl_init($url);  
	    curl_setopt($ch, CURLOPT_TIMEOUT, 5);  
	    curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 5);  
	    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);  
	    $data = curl_exec($ch);  
	    $httpcode = curl_getinfo($ch, CURLINFO_HTTP_CODE);  
	    curl_close($ch);  
	    if($httpcode>=200 && $httpcode<300){  
	        return true;  
	    } else {  
	        return false;  
	    }  
	}

    public function guardarFacturaOffline()
    {
    	/* David 
    	 Guardando  factura con el siguiente formato:
    	
			//Formato Offline
			//nuevo formato para la cascada XD
			{"invoice_items":[{"qty":"2","id":"2","boni":"1","desc":"3"}],"client_id":"1","nit":"6047054","name":"torrez","public_id":"1","invoice_date":"14-10-2014","invoice_number":"0001","cod_control"}

    	*/
		$input = Input::all();
        
		// $invoice_number = Auth::user()->account->getNextInvoiceNumber();
		$invoice_number = (int)Auth::user()->branch->getNextInvoiceNumber();

		$numero =(int) $input['invoice_number'];
		// $numero =(int)  $input['invoice_number'];

		if($invoice_number!=$numero)
		{
			return Response::json( array('resultado' => '1' ,'invoice_number'=>$invoice_number));

		}
		$client_id = $input['client_id'];
		// $client = DB::table('clients')->select('id','nit','name','public_id')->where('id',$input['client_id'])->first();

		$user_id = Auth::user()->getAuthIdentifier();
		$user  = DB::table('users')->select('account_id','branch_id','price_type_id')->where('id',$user_id)->first();
		
		//$account_id = $user->account_id;
		// $account = DB::table('accounts')->select('num_auto','llave_dosi','fecha_limite')->where('id',$user->account_id)->first();
		//$branch = DB::table('branches')->select('num_auto','llave_dosi','fecha_limite','address1','address2','country_id','industry_id')->where('id',$user['branch_id'])->first();
		//$branch = DB::table('branches')->select('num_auto','llave_dosi','fecha_limite','address1','address2','country_id','industry_id')->where('id','=',$user->branch_id)->first();	
    	// $branch = DB::table('branches')->select('number_autho','key_dosage','deadline','address1','address2','country_id','industry_id','law','activity_pri','activity_sec1','name')->where('id','=',$user->branch_id)->first();	
    	$branch = DB::table('branches')->where('id','=',$user->branch_id)->first();	
    	$items = $input['invoice_items'];
    	// $linea ="";
    	$amount = 0;
    	$subtotal=0;
    	$fiscal=0;
    	$icetotal=0;
    	$bonidesc =0;
    	foreach ($items as $item) 
    	{
    		# code...
    		$product_id = $item['id'];
    		 
    		$pr = DB::table('products')
    							->join('prices',"product_id","=",'products.id')
    					
    							->select('products.id','products.notes','prices.cost','products.ice','products.units','products.cc')
    						    ->where('prices.price_type_id','=',$user->price_type_id)
    						    ->where('products.account_id','=',$user->account_id)
    						    ->where('products.id',"=",$product_id)
    							->first();

    		// $pr = DB::table('products')->select('cost')->where('id',$product_id)->first();
    		
    		$qty = (int) $item['qty'];
    		$cost = $pr->cost/$pr->units;
    		$st = ($cost * $qty);
    		$subtotal = $subtotal + $st; 
    		$bd= ($item['boni']*$cost) + $item['desc'];
    		$bonidesc= $bonidesc +$bd;
    		$amount = $amount +$st-$bd;

    		$ice = DB::table('tax_rates')->select('rate')->where('name','=','ice')->first();
    		if($pr->ice == 1)
    		{
    			
    			//caluclo ice bruto 
    			$iceBruto = ($qty *($pr->cc/1000)*$ice->rate);
    			$iceNeto = (((int)$item['boni']) *($pr->cc/1000)*$ice->rate);
    			$icetotal = $icetotal +($iceBruto-$iceNeto) ;
    			// $fiscal = $fiscal + ($amount - ($item['qty'] *($pr->cc/1000)*$ice->rate) );
    		}
    		
    		

    			

    	}
    	$fiscal = $amount -$bonidesc-$icetotal;

    	$balance= $amount;
    	/////////////////////////hasta qui esta bien al parecer hacer prueba de que fuciona el join de los productos XD
    	$invoice_dateCC = date("Ymd");
    	$invoice_date = date("Y-m-d");
    
		$invoice_date_limitCC = date("Y-m-d", strtotime($branch->deadline));

		// require_once(app_path().'/includes/control_code.php');	
		// $cod_control = codigoControl($invoice_number, $input['nit'], $invoice_dateCC, $amount, $branch->number_autho, $branch->key_dosage);
	     $cod_control = $input['cod_control'];
	     $ice = DB::table('tax_rates')->select('rate')->where('name','=','ice')->first();
	     //
	     // creando invoice
	     $invoice = Invoice::createNew();
	     $invoice->invoice_number=$invoice_number;
	     $invoice->client_id=$client_id;
	     $invoice->user_id=$user_id;
	     $invoice->account_id = $user->account_id;
	     $invoice->branch_id= $user->branch_id;
	     $invoice->amount =number_format((float)$amount, 2, '.', '');	
	     $invoice->subtotal = $subtotal;
	     $invoice->fiscal =$fiscal;
	     $invoice->law = $branch->law;
	     $invoice->balance=$balance;
	     $invoice->control_code=$cod_control;
	     $invoice->start_date =$invoice_date;
	     $invoice->invoice_date=$invoice_date;

		 $invoice->activity_pri=$branch->activity_pri;
	     $invoice->activity_sec1=$branch->activity_sec1;
	     
	     // $invoice->invoice
	     $invoice->end_date=$invoice_date_limitCC;
	     //datos de la empresa atra vez de una consulta XD
	     /*****************error generado al intentar guardar **/
	   	 // $invoice->branch = $branch->name;
	     $invoice->address1=$branch->address1;
	     $invoice->address2=$branch->address2;
	     $invoice->number_autho=$branch->number_autho;
	     $invoice->work_phone=$branch->postal_code;
			$invoice->city=$branch->city;
			$invoice->state=$branch->state;
	     // $invoice->industry_id=$branch->industry_id;

	     $invoice->country_id= $branch->country_id;
	     $invoice->key_dosage = $branch->key_dosage;
	     $invoice->deadline = $branch->deadline;
	     $invoice->custom_value1 =$icetotal;
	     $invoice->ice = $ice->rate;
	     //cliente
	     $invoice->nit=$input['nit'];
	     $invoice->name =$input['name'];

	     $invoice->save();
	     
	     $account = Auth::user()->account;
	     require_once(app_path().'/includes/BarcodeQR.php');

			$ice = $invoice->amount-$invoice->fiscal;
			$desc = $invoice->subtotal-$invoice->amount;

			$amount = number_format($invoice->amount, 2, '.', '');
			$fiscal = number_format($invoice->fiscal, 2, '.', '');

			$icef = number_format($ice, 2, '.', '');
			$descf = number_format($desc, 2, '.', '');

			if($icef=="0.00"){
				$icef = 0;
			}
			if($descf=="0.00"){
				$descf = 0;
			}

			$qr = new BarcodeQR();
			$datosqr = '1006909025|'.$input['invoice_number'].'|'.$invoice->number_autho.'|'.$invoice_date.'|'.$amount.'|'.$fiscal.'|'.$invoice->nit.'|'.$icef.'|0|0|'.$descf;
			$qr->text($datosqr); 
			$qr->draw(150, 'qr/' . $account->account_key .'_'. $invoice->invoice_number . '.png');
			$input_file = 'qr/' . $account->account_key .'_'. $invoice->invoice_number . '.png';
			$output_file = 'qr/' . $account->account_key .'_'. $invoice->invoice_number . '.jpg';

			$inputqr = imagecreatefrompng($input_file);
			list($width, $height) = getimagesize($input_file);
			$output = imagecreatetruecolor($width, $height);
			$white = imagecolorallocate($output,  255, 255, 255);
			imagefilledrectangle($output, 0, 0, $width, $height, $white);
			imagecopy($output, $inputqr, 0, 0, 0, 0, $width, $height);
			imagejpeg($output, $output_file);

			$invoice->qr=HTML::image_data('qr/' . $account->account_key .'_'. $input['invoice_number'] . '.jpg');

	     	$invoice->save();
				$fecha =$input['fecha'];
			$f = date("Y-m-d", strtotime($fecha));
	     	 DB::table('invoices')
            ->where('id', $invoice->id)
            ->update(array('branch' => $branch->name,'invoice_date'=>$f));
	     //error verificar

	     // $invoice = DB::table('invoices')->select('id')->where('invoice_number',$invoice_number)->first();

	     //guardadndo los invoice items
	    foreach ($items as $item) 
    	{
    		
    		
    		
    		// $product = DB::table('products')->select('notes')->where('id',$product_id)->first();
    		  $product_id = $item['id'];
	    		 
	    		$product = DB::table('products')
	    							->join('prices',"product_id","=",'products.id')
	    					
	    							->select('products.id','products.notes','prices.cost','products.ice','products.units','products.cc','products.product_key')
	    						    ->where('prices.price_type_id','=',$user->price_type_id)
	    						    ->where('products.account_id','=',$user->account_id)
	    						    ->where('products.id',"=",$product_id)
	    							->first();

	    		// $pr = DB::table('products')->select('cost')->where('id',$product_id)->first();
	    		
	    		
	    		$cost = $product->cost/$product->units;
	    		$line_total= ((int)$item['qty'])*$cost;

    		
    		  $invoiceItem = InvoiceItem::createNew();
    		  $invoiceItem->invoice_id = $invoice->id; 
		      $invoiceItem->product_id = $product_id;
		      $invoiceItem->product_key = $product->product_key;
		      $invoiceItem->notes = $product->notes;
		      $invoiceItem->cost = $cost;
		      $invoiceItem->boni = (int)$item['boni'];
		      $invoiceItem->desc =$item['desc'];
		      $invoiceItem->qty = (int)$item['qty'];
		      $invoiceItem->line_total=$line_total;
		      $invoiceItem->tax_rate = 0;
		      $invoiceItem->save();
		  
    	}
    	

    	// $invoiceItems =DB::table('invoice_items')
    	// 			   ->select('notes','cost','qty','boni','desc')
    	// 			   ->where('invoice_id','=',$invoice->id)
    	// 			   ->get(array('notes','cost','qty','boni','desc'));

    	// $date = new DateTime($invoice->deadline);
    	// $dateEmision = new DateTime($invoice->invoice_date);
    	// $account  = array('name' =>$branch->name,'nit'=>'1006909025' );
    	// $ice = $invoice->amount-$invoice->fiscal;

    	// $factura  = array('invoice_number' => $invoice->invoice_number,
    	// 				'control_code'=>$invoice->control_code,
    	// 				'invoice_date'=>$dateEmision->format('d-m-Y'),
    	// 				'amount'=>number_format((float)$invoice->amount, 2, '.', ''),
    	// 				'subtotal'=>number_format((float)$invoice->subtotal, 2, '.', ''),
    	// 				'fiscal'=>number_format((float)$invoice->fiscal, 2, '.', ''),
    	// 				'client'=>$client,
    	// 				// 'id'=>$invoice->id,

    	// 				'account'=>$account,
    	// 				'law' => $invoice->law,
    	// 				'invoice_items'=>$invoiceItems,
    	// 				'address1'=>str_replace('+', '°', $invoice->address1),
    	// 				// 'address2'=>str_replace('+', '°', $invoice->address2),
    	// 				'address2'=>$invoice->address2,
    	// 				'num_auto'=>$invoice->number_autho,
    	// 				'fecha_limite'=>$date->format('d-m-Y'),
    	// 				// 'fecha_emsion'=>,
    	// 				'ice'=>number_format((float)$ice, 2, '.', '')	
    					
    	// 				);

    	// $invoic = Invoice::scope($invoice_number)->withTrashed()->with('client.contacts', 'client.country', 'invoice_items')->firstOrFail();
		// $d  = Input::all();
		//en caso de problemas irracionales me refiero a que se jodio  
		// $input = Input::all();
		// $client_id = $input['client_id'];
		// $client = DB::table('clients')->select('id','nit','name')->where('id',$input['client_id'])->first();

		$datos = array('resultado ' => "0");
		//colocar una excepcion en caso de error

		// return Response::json($datos);
		return Response::json($datos);
       
    }
}