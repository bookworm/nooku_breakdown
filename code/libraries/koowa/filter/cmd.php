<?php

class KFilterCmd extends KFilterAbstract
{ 
  protected function _validate($value)
  {
    $value = trim($value);
    $pattern = '/^[A-Za-z0-9.\-_]*$/';
    return (is_string($value) && (preg_match($pattern, $value)) == 1);
  }  
  
  protected function _sanitize($value)
  {
    $value = trim($value);
    $pattern    = '/[^A-Za-z0-9.\-_]*/';
    return preg_replace($pattern, '', $value);   
  }
}