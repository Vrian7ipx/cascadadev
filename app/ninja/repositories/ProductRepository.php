<?php namespace ninja\repositories;

use Product;
use Price;

class ProductRepository
{
	public function find($filter = null)
	{
    	$query = \DB::table('products')
			
			->select('products.public_id', 'products.product_key', 'products.pack_types', 'products.ice','products.units', 'products.cc', 'products.notes');


    	if (!\Session::get('show_trash:product'))
    	{
    		$query->where('products.deleted_at', '=', null);
    	}

    	if ($filter)
    	{
    		$query->where(function($query) use ($filter)
            {
            	$query->where('products.product_key', 'like', '%'.$filter.'%');

            });
    	}

    	return $query;
	}

	public function getErrors($data)
	{
		$price = isset($data['prices']) ? (array)$data['prices'][0] : (isset($data['prices']) ? $data['prices'] : []);
		$validator = \Validator::make($price, ['product_key' => 'required']);
		if ($validator->fails()) {
			return $validator->messages();
		}
		
		return false;		
	}

	public function bulk($ids, $action)
	{
		$products = Product::scope($ids)->get();

		foreach ($products as $product) 
		{		
			$product->delete();			
		}

		return count($products);
	}	
}