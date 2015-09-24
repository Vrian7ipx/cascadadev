<?php

class UserGroup extends EntityModel
{	

	public function account()
	{
		return $this->belongsTo('Account');
	}
	public function client()
	{
		return $this->belongsTo('Client');
	}

	public function group()
	{
		return $this->belongsTo('Group');
	}

	public function getEntityType()
	{
		return ENTITY_USERGROUP;
	}
	
}
