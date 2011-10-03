<?php

class KFilterDirname extends KFilterAbstract
{
  protected function _validate($value)
	{
		$value = trim($value);
   	return ((string) $value === $this->sanitize($value));
	}                  
	
	protected function _sanitize($value)
 	{
 		$value = trim($value);
   	return dirname($value);
 	}
}