<?php

class Group extends EntityModel
{	

	public function account()
	{
		return $this->belongsTo('Account');
	}
	public function client()
	{
		return $this->belongsTo('Client');
	}

	public function user_group()
	{
		return $this->hasMany('UserGroup');
	}

	public function getEntityType()
	{
		return ENTITY_GROUP;
	}

	public function getName()
	{
		if ($this->name) 
		{
			return $this->name;
		}
	}
	
}
