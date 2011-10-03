<?php

class KFilterNumeric extends KFilterAbstract
{            
	protected function _validate($value)
	{
		return (is_string($value) && is_numeric($value));
	}
	
	protected function _sanitize($value)
	{
		return (string) filter_var($value, FILTER_SANITIZE_NUMBER_FLOAT, 
			FILTER_FLAG_ALLOW_FRACTION | FILTER_FLAG_ALLOW_THOUSAND | FILTER_FLAG_ALLOW_SCIENTIFIC);
	}
}