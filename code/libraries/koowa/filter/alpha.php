<?php

class KFilterAlpha extends KFilterAbstract
{  
  protected function _validate($value)
	{
		$value = trim($value);
		
		return ctype_alpha($value);
	}           
	
	protected function _sanitize($value)
	{
		$pattern 	= '/[^[a-zA-Z]*/';
  	return preg_replace($pattern, '', $value);
	}
}