<?php

class KFilterFloat extends KFilterAbstract
{
  protected function _validate($value)
  {
    return (false !== filter_var($value, FILTER_VALIDATE_FLOAT));
  }           
  
  protected function _sanitize($value)
  {
    return (float) filter_var($value, FILTER_SANITIZE_NUMBER_FLOAT,
      FILTER_FLAG_ALLOW_FRACTION | FILTER_FLAG_ALLOW_THOUSAND | FILTER_FLAG_ALLOW_SCIENTIFIC);
  }
}