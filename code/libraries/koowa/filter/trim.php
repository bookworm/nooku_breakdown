<?php

class KFilterTrim extends KFilterAbstract
{
  protected $_charList = null;


  public function __construct(KConfig $config)
  {
    parent::__construct($config);
  
    if(isset($config->char_list)) $this->_charList = $config->char_list;
  }


  public function getCharList()
  {
    return $this->_charList;
  }

  public function setCharList($charList)
  {
    $this->_charList = $charList;
    return $this;
  }

  protected function _validate($value)
  {
    return (is_string($value));
  }

  protected function _sanitize($value)
  {
    if(null === $this->_charList)
      return trim((string) $value);
    else
      return trim((string) $value, $this->_charList);
  }   
}