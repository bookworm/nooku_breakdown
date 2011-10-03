<?php

class KFilterEmail extends KFilterAbstract
{
  protected function _validate($value)
  {
    $value = trim($value);
    return (false !== filter_var($value, FILTER_VALIDATE_EMAIL));
  }
  
  protected function _sanitize($value)
  {
    $value = trim($value);
    return filter_var($value, FILTER_SANITIZE_EMAIL);
  }
}