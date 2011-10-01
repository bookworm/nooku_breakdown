<?php 

class KCommandContext extends KConfig
{
  protected $_error;

  function setError($error) 
  {
    $this->_error = $error;
    return $this;
  }
  
  function getError() 
  {
    return $this->_error;
  }
}