<?php namespace ninja\repositories;

use Branch;
use Client;
use Contact;
use Account;
use Request;
use Session;
use Language;
use User;
use Auth;
use Invitation;
use Invoice;
use InvoiceItem;
use AccountGateway;

class AccountRepository
{
	public function create()
	{
		$account = new Account;
		$account->ip = Request::getClientIp();
		$account->account_key = str_random(RANDOM_KEY_LENGTH);

		if (Session::has(SESSION_LOCALE))
		{
			$locale = Session::get(SESSION_LOCALE);
			if ($language = Language::whereLocale($locale)->first())
			{
				$account->language_id = $language->id;
			}
		}

		$account->save();

		$branch = new Branch;
		$account->branches()->save($branch);
		
		$random = str_random(RANDOM_KEY_LENGTH);

		$user = new User;
		$user->password = $random;
		$user->password_confirmation = $random;			
		$user->username = $random;
		$user->price_type_id = 1;
		$user->branch_id = $branch->getId();

		$account->users()->save($user);			
		
		return $account;
	}

	public function getSearchData()
	{

    	$users = \DB::table('users')
			->where('users.deleted_at', '=', null)
			->where('users.account_id', '=', \Auth::user()->account_id)			
			->select(\DB::raw("'Users' as type, users.public_id, CONCAT(users.first_name, ' ', users.last_name) as name, '' as token"));
    	
    	$groups = \DB::table('groups')
			->where('groups.deleted_at', '=', null)
			->where('groups.account_id', '=', \Auth::user()->account_id)			
			->select(\DB::raw("'Groups' as type, groups.public_id, groups.name, '' as token"));

    	$branches = \DB::table('branches')
			->where('branches.deleted_at', '=', null)
			->where('branches.account_id', '=', \Auth::user()->account_id)			
			->select(\DB::raw("'Branches' as type, branches.public_id, CONCAT(branches.name, ' ', branches.address1) as name, '' as token"));

		$data = [];
		
		// foreach ($clients->union($contacts)->union($invoices)->get() as $row)
		foreach ($users->union($groups)->union($branches)->get() as $row)
		{
			$type = $row->type;

			if (!isset($data[$type]))
			{
				$data[$type] = [];	
			}			

			$tokens = explode(' ', $row->name);
			$tokens[] = $type;

			$data[$type][] = [
				'value' => $row->name,
				'public_id' => $row->public_id,
				'tokens' => $tokens
			];
		}
		
    	return $data;
	}


	public function enableProPlan()
	{		
		if (Auth::user()->isPro())
		{
			return false;
		}

		$ninjaAccount = $this->getNinjaAccount();		
		$lastInvoice = Invoice::withTrashed()->whereAccountId($ninjaAccount->id)->orderBy('public_id', 'DESC')->first();
		$publicId = $lastInvoice ? ($lastInvoice->public_id + 1) : 1;

		$ninjaClient = $this->getNinjaClient($ninjaAccount);
		$invoice = $this->createNinjaInvoice($publicId, $ninjaAccount, $ninjaClient);

		return $invoice;
	}

	private function createNinjaInvoice($publicId, $account, $client)
	{
		$invoice = new Invoice();
		$invoice->account_id = $account->id;
		$invoice->user_id = $account->users()->first()->id;
		$invoice->public_id = $publicId;
		$invoice->client_id = $client->id;
		$invoice->invoice_number = $account->getNextInvoiceNumber();
		$invoice->invoice_date = date_create()->format('Y-m-d');
		$invoice->amount = PRO_PLAN_PRICE;
		$invoice->balance = PRO_PLAN_PRICE;
		$invoice->save();

		$item = new InvoiceItem();
		$item->account_id = $account->id;
		$item->user_id = $account->users()->first()->id;
		$item->public_id = $publicId;
		$item->qty = 1;
		$item->cost = PRO_PLAN_PRICE;
		$item->notes = trans('texts.pro_plan_description');
		$item->product_key = trans('texts.pro_plan_product');				
		$invoice->invoice_items()->save($item);

		$invitation = new Invitation();
		$invitation->account_id = $account->id;
		$invitation->user_id = $account->users()->first()->id;
		$invitation->public_id = $publicId;
		$invitation->invoice_id = $invoice->id;
		$invitation->contact_id = $client->contacts()->first()->id;
		$invitation->invitation_key = str_random(RANDOM_KEY_LENGTH);
		$invitation->save();

		return $invoice;
	}

	public function getNinjaAccount()
	{
		$account = Account::whereAccountKey(NINJA_ACCOUNT_KEY)->first();

		if ($account)
		{
			return $account;	
		}
		else
		{
			$account = new Account();
			$account->name = 'FRANKLIN LLANOS SILVA';
			$account->work_email = 'franklin.llanos@ipxserver.com';
			$account->nit = '3457229010';
			$account->work_phone = '2315725';
			$account->address1 = 'Central';
			$account->address2 = 'Av. 16 de Julio 1456 Edif. Caracas Piso: 2';
			$account->country_id = '2';
			$account->num_auto = '2004003826637';
			$account->fecha_limite = '2014-06-10';
			$account->llave_dosi = '6S_4HR6S6fHgCF+aT(){aR7@fPYJTMAy#ai\A@\x(3g+Az8XdDKR5SCdG=J$WX4H';
			$account->account_key = NINJA_ACCOUNT_KEY;
			$account->save();

			$random = str_random(RANDOM_KEY_LENGTH);
			$user = new User();
			$user->registered = true;
			$user->confirmed = true;
			$user->email = 'franklin.llanos@ipxserver.com';
			$user->password = $random;
			$user->password_confirmation = $random;			
			$user->username = $random;
			$user->first_name = 'FRANKLIN';
			$user->last_name = 'LLANOS SILVA';
			$user->notify_sent = true;
			$user->notify_paid = true;
			$account->users()->save($user);			

			$accountGateway = new AccountGateway();
			$accountGateway->user_id = $user->id;
			$accountGateway->gateway_id = NINJA_GATEWAY_ID;
			$accountGateway->public_id = 1;
			$accountGateway->config = NINJA_GATEWAY_CONFIG;
			$account->account_gateways()->save($accountGateway);
		}

		return $account;
	}

	private function getNinjaClient($ninjaAccount)
	{
		$client = Client::whereAccountId($ninjaAccount->id)->wherePublicId(Auth::user()->account_id)->first();

		if (!$client)
		{
			$client = new Client;		
			$client->public_id = Auth::user()->account_id;
			$client->user_id = $ninjaAccount->users()->first()->id;
			$client->currency_id = 1;			
			foreach (['name', 'nit', 'address1', 'address2', 'city', 'state', 'postal_code', 'country_id', 'work_phone'] as $field) 
			{
				$client->$field = Auth::user()->account->$field;
			}		
			$ninjaAccount->clients()->save($client);

			$contact = new Contact;
			$contact->user_id = $ninjaAccount->users()->first()->id;
			$contact->account_id = $ninjaAccount->id;
			$contact->public_id = Auth::user()->account_id;
			$contact->is_primary = true;
			foreach (['first_name', 'last_name', 'email', 'phone'] as $field) 
			{
				$contact->$field = Auth::user()->$field;	
			}		
			$client->contacts()->save($contact);			
		}

		return $client;
	}

}