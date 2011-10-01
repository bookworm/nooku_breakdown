<?php 

abstract class KLoaderAdapterAbstract implements KLoaderAdapterInterface
{
  protected $_basepath = '';

  public function __construct($basepath)
  {
    $this->_basepath = $basepath; 
  }

  public function getBasepath()
  {
    return $this->_basepath;
  }

  public function getPrefix()
  {
    return $this->_prefix;
  }   
  
  public function path($identifier)
  {
    $path = false;

    if($identifier instanceof KIdentifierInterface) 
      $path = $this->_pathFromIdentifier($identifier);
    else
      $path = $this->_pathFromClassname($identifier);

    return $path; 
  }

  abstract protected function _pathFromIdentifier($identifier);
  abstract protected function _pathFromClassname($classname);
}