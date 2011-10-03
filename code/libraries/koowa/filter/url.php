<?php

class KFilterUrl extends KFilterAbstract
{
	protected function _validate($value)
	{
		$value = trim($value);
		return (false !== filter_var($value, FILTER_VALIDATE_URL));
	}
	
	protected function _sanitize($value)
	{
		return filter_var($value, FILTER_SANITIZE_URL);
	}
}