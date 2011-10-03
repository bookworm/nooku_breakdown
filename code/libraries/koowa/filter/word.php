<?php

class KFilterWord extends KFilterAbstract
{
  protected function _validate($value)
  {
    $value = trim($value);
    $pattern = '/^[A-Za-z_]*$/';
    return (is_string($value) && preg_match($pattern, $value) == 1);
  }

  protected function _sanitize($value)
  {
    $value = trim($value);
    $pattern    = '/[^A-Za-z_]*/';
    return preg_replace($pattern, '', $value);
  }  
}