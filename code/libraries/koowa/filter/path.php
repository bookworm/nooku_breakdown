<?php

class KFilterPath extends KFilterAbstract
{
  const PATTERN = '#^(?:[a-z]:/|~*/)[a-z0-9_\.-\s/~]*$#i';  
  
  protected function _validate($value)
  {
    $value = trim(str_replace('\\', '/', $value));
    return (is_string($value) && (preg_match(self::PATTERN, $value)) == 1);
  }

  protected function _sanitize($value)
  {
    $value = trim(str_replace('\\', '/', $value));
    preg_match(self::PATTERN, $value, $matches);
    $match = isset($matches[0]) ? $matches[0] : '';
         
    return $match;
  } 
}