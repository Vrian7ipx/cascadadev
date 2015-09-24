<?php

class BookSale extends Eloquent
{
	public $timestamps = true;
	protected $softDelete = false;	

	public function scopeScope($query)
	{
		return $query->whereAccountId(Auth::user()->account_id);
	}
	private static function getBlank($entity = false)
	{
		$BookSale = new BookSale;

		if ($entity) 
		{
			$BookSale->user_id = $entity->user_id;
			$BookSale->account_id = $entity->account_id;
		} 
		else if (Auth::check())
		{
			$BookSale->user_id = Auth::user()->id;
			$BookSale->account_id = Auth::user()->account_id;	
		} 
		else 
		{
			Utils::fatalError();
		}

		return $BookSale;
	}

	public static function createIva($invoice)
	{

		$BookSale = BookSale::getBlank($invoice);

		$client = $invoice->client;

		$BookSale->invoice_id = $invoice->id;
		$BookSale->nit_client = $client->nit;
		$BookSale->rz_client = $client->name;
		$BookSale->number_invoice = $invoice->invoice_number;
		$BookSale->na_account = $invoice->account->num_auto;
		$BookSale->date_invoice = $invoice->invoice_date;
		$BookSale->amount = $invoice->amount;
		$BookSale->ice = 0;
		$BookSale->exempt = 0;
		$BookSale->net_amount = $invoice->amount;
		$aux = $invoice->amount;
		$aux = $aux*13;
		$aux = $aux/100;
		$BookSale->iva = $aux;
		$BookSale->status = "V";
		$BookSale->cc_invoice = $invoice->control_code;
		$BookSale->save();
	}	

	public static function deleteIva($invoice)
	{

		if ($invoice->is_deleted)
		{
			$BookSale = BookSale::scope()->whereInvoiceId($invoice->id)->first();
			$BookSale->status = "A";
			$BookSale->save();
		}

	}
}