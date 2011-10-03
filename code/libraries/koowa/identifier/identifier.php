<?php

class KIdentifier implements KIdentifierInterface
{
  protected static $_applications = array();
  protected $_identifier = '';
  protected $_application = '';
  protected $_type = '';
  protected $_package = '';
  protected $_path = array();
  protected $_name = '';
  public $filepath = '';
  public $basepath = '';
  public $classname = '';    

  public function __construct($identifier)
  {
    $identifier = (string) $identifier;

    if(strpos($identifier, '.') === FALSE) 
      throw new KIdentifierException('Wrong identifier format : '.$identifier);

    if(strpos($identifier, '::'))  
      list($this->application, $parts) = explode('::', $identifier);
    else $parts = $identifier;

    $parts = explode('.', $parts);

    $this->_type = array_shift($parts);

    $this->_package = array_shift($parts);

    if(count($parts)) 
      $this->_name = array_pop($parts);

    if(count($parts))
      $this->_path = $parts;

    $this->_identifier = $identifier; 
  }  

  public static function registerApplication($application, $path)
  {
    self::$_applications[$application] = $path;
  }  
  
  public function __set($property, $value)
  {
    if(isset($this->{'_'.$property})) 
    {
      if($property == 'path')
        if(is_scalar($value)) $value = (array) $value;   

      if($property == 'application')
      { 
        if(!isset(self::$_applications[$value]))
          throw new KIdentifierException('Unknow application : '.$value);  
        $this->basepath = self::$_applications[$value];
      }

      $this->{'_'.$property} = $value;
      $this->_identifier = '';     
    }  
  }
  
  public function &__get($property)
  {
    if(isset($this->{'_'.$property})) return $this->{'_'.$property};
  }

  public function __isset($property)
  {
    return isset($this->{'_'.$property});
  }

  public function __toString()
  {
    if($this->_identifier == '')
    {
      if(!empty($this->_application))
        $this->_identifier .= $this->_application.'::';

      if(!empty($this->_type)) 
        $this->_identifier .= $this->_type;

      if(!empty($this->_package))
        $this->_identifier .= '.'.$this->_package;

      if(count($this->_path)) 
        $this->_identifier .= '.'.implode('.',$this->_path);

      if(!empty($this->_name))
        $this->_identifier .= '.'.$this->_name;    
    }

    return $this->_identifier;  
  }
}