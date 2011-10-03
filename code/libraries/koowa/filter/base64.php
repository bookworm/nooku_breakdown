<?php

class KFilterBase64 extends KFilterAbstract
{                                          
  protected function _validate($value)
  {
    $pattern = '#^[a-zA-Z0-9/+]*={0,2}$#';
    return (is_string($value) && preg_match($pattern, $value) == 1); 
  }
  
  protected function _sanitize($value)
  {
    $value = trim($value);
    $pattern = '#[^a-zA-Z0-9/+=]#';
    return preg_replace($pattern, '', $value);       
  }
}