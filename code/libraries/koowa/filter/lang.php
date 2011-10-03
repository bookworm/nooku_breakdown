<?php

class KFilterLang extends KFilterAbstract
{
  protected function _validate($value)
  {
    $value = trim($value);
    $pattern = '/^[a-z]{2}-[A-Z]{2}$/';
    return (empty($value)) 
            || (is_string($value) && preg_match($pattern, $value) == 1);
  }

  protected function _sanitize($value)
  {
    $value = trim($value);

    $parts  = explode('-', $value, 2);
    if(2 != count($parts)) return null;

    $parts[0]   = substr(preg_replace('/[^a-z]*/', '', $parts[0]), 0, 2);
    $parts[1]   = substr(preg_replace('/[^A-Z]*/', '', $parts[1]), 0, 2);
    $result = implode('-', $parts);

    if($this->_validate($result)) return $result;

    return null;
  }  
}