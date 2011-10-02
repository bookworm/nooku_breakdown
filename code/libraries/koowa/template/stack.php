<?php

class KTemplateStack extends KObject implements KObjectIdentifiable
{
  protected $_object_stack = null;
    
  public function __construct(KConfig $config) 
  { 
    parent::__construct($config);

    $this->_object_stack = array(); 
  }
  
  final private function __clone() { }
  
  public static function instantiate($config = array())
  {
    static $instance;

    if ($instance === NULL) 
    {
      if(!$config instanceof KConfig) 
        $config = new KConfig($config);

      $instance = new self($config);  
    }

    return $instance;  
  }         
  
  public function getIdentifier()
  {
    return $this->_identifier;
  }   
  
  public function push(KTemplateAbstract $template)
  {
    $this->_object_stack[] = $template;
    return $this;
  }                   
  
  public function top()
  {
    return end($this->_object_stack);
  }   
  
  public function pop()
  {
    return array_pop($this->_object_stack);
  } 

  public function count()
  {
    return count($this->_object_stack);
  }

  public function isEmpty()
  {
    return empty($this->_object_stack);
  } 
}