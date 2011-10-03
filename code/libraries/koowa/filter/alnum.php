<?php

class KFilterAlnum extends KFilterAbstract
{
  protected function _validate($value)
	{
		$value = trim($value);
		
		return ctype_alnum($value);
	}                         
	
	protected function _sanitize($value)
	{
		$pattern 	= '/[^\w]*/';
  	return preg_replace($pattern, '', $value);
	}
}