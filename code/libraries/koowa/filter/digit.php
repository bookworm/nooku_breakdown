<?php

class KFilterDigit extends KFilterAbstract
{
  protected function _validate($value)
  {
    return empty($value) || ctype_digit($value);
  }          
  
  protected function _sanitize($value)
  {
    $value = trim($value);
    $pattern ='/[^0-9]*/';
    return preg_replace($pattern, '', $value);    
  }
}