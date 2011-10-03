<?php

class KFilterInt extends KFilterAbstract
{
  protected function _validate($value)
  {
    return empty($value) || (false !== filter_var($value, FILTER_VALIDATE_INT));
  }
  
  protected function _sanitize($value)
  {
    return filter_var($value, FILTER_SANITIZE_NUMBER_INT);
  }
}