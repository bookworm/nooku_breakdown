<?php 

class KCommandContext extends KConfig
{
  protected $_error;

  public function setError($error) 
  {
    $this->_error = $error;
    return $this;
  }
  
  public function getError() 
  {
    return $this->_error;
  }
}