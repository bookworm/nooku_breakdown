<?php

class KFilterTime extends KFilterTimestamp
{
  protected function _validate($value)
  {
    if(is_array($value)) $value = $this->_arrayToTime($value);
    $expr = '/^(([0-1][0-9])|(2[0-3])):[0-5][0-9]:[0-5][0-9]$/D';

    return (bool) preg_match($expr, $value) || ($value == '24:00:00');  
  }

  protected function _sanitize($value)
  {
    if(is_array($value)) $value = $this->_arrayToTime($value);
    
    $format = 'H:i:s';
    if(is_int($value)) return date($format, $value);

    return date($format, strtotime($value));
  }                                             
}