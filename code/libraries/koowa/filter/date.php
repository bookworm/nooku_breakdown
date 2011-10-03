<?php 

class KFilterDate extends KFilterTimestamp
{
  protected function _validate($value)
  {
    if(is_array($value)) $value = $this->_arrayToDate($value);
    $expr = '/^([0-9]{4})-([0-9]{2})-([0-9]{2})$/D';
  
    return (preg_match($expr, $value, $match) && checkdate($match[2], $match[3], $match[1]));
  }         
  
  protected function _sanitize($value)
  {
    if(is_array($value)) $value = $this->_arrayToDate($value);
  
    $result = '0000-00-00';
    if(!(empty($value) || $value == $result))
    {
      $format = 'Y-m-d';

      if(is_numeric($value))
        $result = date($format, $value); 
      else
        $result = date($format, strtotime($value)); 
    } 
  
    return $result; 
  }
}