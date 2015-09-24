<?php

class Product extends EntityModel
{	
	public static function findProductByKey($key)
	{
		return Product::scope()->where('product_key','=',$key)->first();
	}

	public function invoices()
	{
		return $this->hasMany('Invoice');
	}


	public function prices()
	{
		return $this->hasMany('Price');
	}

	public function getEntityType()
	{
		return ENTITY_PRODUCT;
	}

	public function getName()
	{
		return $this->getDisplayName();
	}

	public function getDisplayName()
	{
		if ($this->notes) 
		{
			return $this->notes;
		}
	}
	public function getProductKey()
	{
		if ($this->product_key) 
		{
			return $this->product_key;
		}
	}

	public function getPackTypes()
	{
		if ($this->pack_types) 
		{	
			$var1 = $this->pack_types;
			$var2 = 'V';
			if(strcmp($var1, $var2) == 0)
			{
				return 'Vidrio';
			}
			else
			{
				$var2 = 'P';
				if(strcmp($var1, $var2) == 0)
				{
					return 'Plástico';
				}
			}
		}
	}
	public function getIce()
	{
		if ($this->ice) 
		{		
			return 'con ICE';
		}
		else
		{
			return 'sin ICE';
		}

	}
	public function getCc()
	{
		if ($this->cc) 
		{
			return $this->cc;
		}
	}
}
