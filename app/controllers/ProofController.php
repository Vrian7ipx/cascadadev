<?php 

class ProofController extends \BaseController {

        public function index(){
                                    
            $publicId = 1;
            
            $invoice = Invoice::scope($publicId)->with('account.country', 'client.contacts', 'client.country', 'invoice_items')->firstOrFail();
		Utils::trackViewed($invoice->invoice_number . ' - ' . $invoice->client->getDisplayName(), ENTITY_INVOICE);
                //$productos = InvoiceItem::scope(1)->get();
		$invoice->invoice_date = Utils::fromSqlDate($invoice->invoice_date);
		$invoice->due_date = Utils::fromSqlDate($invoice->due_date);
		$invoice->start_date = Utils::fromSqlDate($invoice->start_date);
		$invoice->end_date = Utils::fromSqlDate($invoice->end_date);
		$invoice->is_pro = Auth::user()->isPro();
                
                //print_r($invoice->invoice_items);
             //return Response::json($invoice);
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
//                print_r($data['invoice']);
//                echo "<br><br><br><br><br>";
                
                                        
                
		//return View::make('invoices.edit', $data);
            
            
            return View::make('factura',$data);
        }
        
        private static function getViewModel()
	{
		return [
			'account' => Auth::user()->account,
			'branch' => Auth::user()->branch,
                        'matriz' => Branch::scope(1)->firstOrFail(),
			///'products' => Product::scope()->with('prices')->orderBy('id')->get(),
                        
                        'products' => DB::table('invoice_items')->where('invoice_id','=',1)->get(),
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
	public function index2(){		        
    	extract($_POST);
        $username='firstuser';
		$password='first_password';
		$URL='localhost/cascada_ventas/public/api/v1/url';
		$fields= array(
			'url'=>urlencode('https://faccturavirtual.com'),
			'description'=> urlencode('HELLO'),
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
		if(!curl_exec($ch)){
    		die('Error: "' . curl_error($ch) . '" - Code: ' . curl_errno($ch));
		}
		//$result=curl_exec ($ch);


		curl_close ($ch);
	}	        
}

?>