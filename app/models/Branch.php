<?php

class Branch extends EntityModelB
{

	protected $softDelete = true;	

	public function account()
	{
		return $this->belongsTo('Account');
	}
	
	public function users()
	{
		return $this->hasMany('User');
	}

	public function invoices()
	{
		return $this->hasMany('Invoice');
	}

	public function country()
	{
		return $this->belongsTo('Country');
	}

	public function industry()
	{
		return $this->belongsTo('Industry');
	}
	public function getId()
	{
		return $this->id;
	}
	public function name()
	{
		return $this->name;
	}

	public function getNextInvoiceNumber($isQuote = false)
	{
		$counter = $isQuote && !$this->share_counter ? $this->quote_number_counter : $this->invoice_number_counter;
		$prefix = $isQuote ? $this->quote_number_prefix : $this->invoice_number_prefix;

		return $prefix . str_pad($counter, 4, "0", STR_PAD_LEFT);
	}

	public function incrementCounter($isQuote = false) 
	{
		if ($isQuote && !$this->share_counter) {
			$this->quote_number_counter += 1;
		} else {
			$this->invoice_number_counter += 1;
		}

		$this->save();
	}


}
