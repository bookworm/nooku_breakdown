<?php

class KFilterMd5 extends KFilterAbstract
{
  protected function _validate($value)
  {
    $value = trim($value);
    $pattern = '/^[a-f0-9]{32}$/';
    return (is_string($value) && preg_match($pattern, $value) == 1);
  }

  protected function _sanitize($value)
  {
    $value      = trim(strtolower($value));
    $pattern    = '/[^a-f0-9]*/';
    return substr(preg_replace($pattern, '', $value), 0, 32);
  }  
}