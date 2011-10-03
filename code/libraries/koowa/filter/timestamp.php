<?php

class KFilterTimestamp extends KFilterAbstract
{
  protected function _validate($value)
  {
    if(is_array($value)) 
      $value = $this->_arrayToTimestamp($value);
  
    if(strlen($value) != 19)
      return false;
  
    $date = substr($value, 0, 10);
    if(!$this->_filter->validateIsoDate($date))
      return false;
  
    $sep = substr($value, 10, 1);
    if($sep != 'T' && $sep != ' ')
      return false;
  
    $time = substr($value, 11, 8);
    if(!$this->_filter->validateIsoTime($time))
      return false;
  
    return true;
  }   
  
  protected function _sanitize($value)
  {
    if(is_array($value))
      $value = $this->_arrayToTimestamp($value);

    $result = '0000-00-00 00:00:00';
    if(!(empty($value) || $value == $result))
    {
      $format = 'Y-m-d H:i:s';
      if(is_int($value))
        $result = date($format, $value);
      else
        $result = date($format, strtotime($value));
    } 

    return $result; 
  }  
  
  protected function _arrayToTimestamp($array)
  {
    $value = $this->_arrayToDate($array)
      . ' '
      . $this->_arrayToTime($array);  

    return trim($value);  
  }  
  
  protected function _arrayToDate($array)
  {
    $date = array_key_exists('Y', $array) &&
      trim($array['Y']) != '' &&
      array_key_exists('m', $array) &&
      trim($array['m']) != '' &&
      array_key_exists('d', $array) &&
      trim($array['d']) != '';

    if(!$date) return;

    return $array['Y'] . '-'
      . $array['m'] . '-'
      . $array['d'];    
  }    
  
  protected function _arrayToTime($array)
  {
    $time = array_key_exists('H', $array) &&
      trim($array['H']) != '' &&
      array_key_exists('i', $array) &&
      trim($array['i']) != '';

    if(!$time) return;

    $s = array_key_exists('s', $array) && trim($array['s']) != ''
      ? $array['s']
      : '00';  

    return $array['H'] . ':'
      . $array['i'] . ':'
      . $s;  
  }
}