<?php

class KFilterIP extends KFilterAbstract
{
  protected function _validate($value)
  {
    $value = trim($value);
    return (false !== filter_var($value, FILTER_VALIDATE_IP));
  }
  
  protected function _sanitize($value)
  {
    return preg_replace('#[^a-f0-9:\.]#i', '', $value);
  }
}