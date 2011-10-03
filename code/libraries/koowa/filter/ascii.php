<?php

class KFilterAscii extends KFilterAbstract
{
  protected function _validate($value)
	{
		return (preg_match('/(?:[^\x00-\x7F])/', $value) !== 1);
	}
	
	protected function _sanitize($value)
	{
    $string = htmlentities(utf8_decode($value));
    $string = preg_replace(
      array('/&szlig;/','/&(..)lig;/', '/&([aouAOU])uml;/','/&(.)[^;]*;/'),
      array('ss',"$1","$1".'e',"$1"),
      $string);  

    return $string;    
	}
}