<?php

class KEvent extends KConfig
{
  const PRIORITY_HIGHEST = 1;
  const PRIORITY_HIGH    = 2;
  const PRIORITY_NORMAL  = 3;
  const PRIORITY_LOW     = 4;
  const PRIORITY_LOWEST  = 5;

  protected $_propagate = true;
  protected $_name;   
  
  public function __construct( $name, $config = array() )
  { 
    parent::__construct($config);
    $this->_name = $name;   
  } 
  
  public function getName()
  {
    return $this->_name;
  }

  public function canPropagate()
  {
    return $this->_propagate;
  }

  public function stopPropagation()
  {
    $this->_propagate = false;
    return $this;       
  }
}