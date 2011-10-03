<?php

class KFilterJson extends KFilterAbstract
{
  public function __construct(KConfig $config) 
  {
    parent::__construct($config);

    $this->_walk = false;
  }

  protected function _validate($value)
  {
  return is_string($value) && !is_null(json_decode($value));
  }

  protected function _sanitize($value)
  {
    $result = null;
   
    if(is_string($value)) $result = json_decode($value);
  
    if(is_null($result)) {
      if($value instanceof KConfig) $value = KConfig::toData($value); 
      $result =  json_encode($value);              
    }
  
    return $result;
  }
}