<?php

class Client extends EntityModel
{
	public static $fieldNit = 'Client - Nit';
	public static $fieldName = 'Client - Name';
	public static $fieldPhone = 'Client - Phone';
	public static $fieldAddress1 = 'Client - Street';
	public static $fieldAddress2 = 'Client - Apt/Floor';
	public static $fieldCity = 'Client - City';
	public static $fieldState = 'Client - State';
	public static $fieldPostalCode = 'Client - Postal Code';
	public static $fieldNotes = 'Client - Notes';
	public static $fieldCountry = 'Client - Country';

	public function account()
	{
		return $this->belongsTo('Account');
	}

	// public function branch()
	// {
	// 	return $this->belongsTo('Branch');
	// }

	public function invoices()
	{
		return $this->hasMany('Invoice');
	}

	public function contacts()
	{
		return $this->hasMany('Contact');
	}

	public function country()
	{
		return $this->belongsTo('Country');
	}

	public function currency()
	{
		return $this->belongsTo('Currency');
	}

	public function industry()
	{
		return $this->belongsTo('Industry');
	}
	public function business_type()
	{
		return $this->belongsTo('BusinessType');
	}
	public function zone()
	{
		return $this->belongsTo('Zone');
	}

	public function getName()
	{
		return $this->getDisplayName();
	}

	public function getCod()
	{
		return $this->public_id;
	}
	
	public function getNit()
	{
		if(!$this->nit)
		{
			return '';
		}	
		return $this->nit;
	}

	public function getDisplayName()
	{
		if ($this->name) 
		{
			return $this->name;
		}

		$this->load('contacts');
		$contact = $this->contacts()->first();
		
		return $contact->getDisplayName();
	}

	public function getEntityType()
	{
		return ENTITY_CLIENT;
	}

	public function getAddress()
	{
		$str = '';

		if ($this->address1) {
			$str .= $this->address1 . '<br/>';
		}
		if ($this->address2) {
			$str .= $this->address2 . '<br/>';	
		}
		if ($this->city) {
			$str .= $this->city . ', ';	
		}
		if ($this->state) {
			$str .= $this->state . ' ';	
		}
		if ($this->postal_code) {
			$str .= $this->postal_code;
		}
		// if ($this->country) {
		// 	$str .= '<br/>' . $this->country->name;			
		// }

		if ($str)
		{
			$str = '<p>' . $str . '</p>';
		}

		return $str;
	}

	public function getPhone()
	{
		$str = '';

		if ($this->work_phone)
		{
			$str .= '<i class="fa fa-phone" style="width: 20px"></i>' . Utils::formatPhoneNumber($this->work_phone);
		}

		return $str;
	}

	public function getNotes()
	{
		$str = '';

		if ($this->private_notes)
		{
			$str .= '<i>' . $this->private_notes . '</i>';
		}

		return $str;
	}

	public function getIndustry()
	{
		$str = '';

		if ($this->client_industry)
		{
			$str .= $this->client_industry->name . ' ';
		}

		return $str;
	}

	public function getZone()
	{
		$str = '';

		if ($this->zone_id)
		{
			$zone = Zone::find(1)->select('name')->where('id', '=', $this->zone_id)->first();
			$result = $zone->name;
			$str .= $result . '<br/>';
		}

		return $str;
	}


	public function getBusinessType()
	{
		$str = '';

		if ($this->business_type_id)
		{
			$business = BusinessType::select('name')->where('id', '=', $this->business_type_id)->first();
			$result = $business->name;
			$str .= '<b>' . $result . '</b><br/>';
		}

		return $str;
	}

	public function getCustomFields()
	{
		$str = '';
		$account = $this->account;

		if ($account->custom_client_label1 && $this->custom_value1)
		{
			$str .= "{$account->custom_client_label1}: {$this->custom_value1}<br/>";
		}

		if ($account->custom_client_label2 && $this->custom_value2)
		{
			$str .= "{$account->custom_client_label2}: {$this->custom_value2}<br/>";
		}

		return $str;
	}

	public function getWebsite()
	{
		if (!$this->website)
		{
			return '';
		}

		$link = $this->website;
		$title = $this->website;
		$prefix = 'http://';

		if (strlen($link) > 7 && substr($link, 0, 7) === $prefix) {
			$title = substr($title, 7);
		} else {
			$link = $prefix . $link;
		}

		return link_to($link, $title, array('target'=>'_blank'));
	}

	public function getDateCreated()
	{		
		if ($this->created_at == '0000-00-00 00:00:00') 
		{
			return '---';
		} 
		else 
		{
			return $this->created_at->format('m/d/y h:i a');
		}
	}
}


Client::updating(function($client)
{
	Activity::updateClient($client);
});

Client::deleting(function($client)
{
	Activity::archiveClient($client);
});