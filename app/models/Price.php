<?php

class Price extends EntityModel
{

	public function Product()
	{
		return $this->belongsTo('Product');
	}

	public function price_type()
	{
		return $this->belongsTo('PriceType');
	}

	public function getEntityType()
	{
		return ENTITY_PRICE;
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

		public function getDetails()
	{
		$str = '';
		
		if ($this->cost)
		{
			$str .= '<b>' . $this->cost . '</b><br/>';
		}

		return $str;
	}
}