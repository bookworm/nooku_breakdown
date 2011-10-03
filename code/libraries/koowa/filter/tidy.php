<?php

class KFilterTidy extends KFilterAbstract
{
  protected $_tidy = null;
  protected $_encoding = 'utf8';
  protected $_config = array();   
  
  public function __construct(KConfig $config)
  {
    parent::__construct($config);
    $this->_encdoing = $config->encoding;
    $this->_config   = KConfig::toData($config->config);       
  }   
  
  protected function _initialize(KConfig $config)
  {
    $config->append(array(
      'encoding'      => 'utf8',
      'config'        =>  array(
        'clean'                       => true,
        'drop-proprietary-attributes' => true, 
        'output-html'                 => true,
        'show-body-only'              => true,
        'bare'                        => true, 
        'wrap'                        => 0,
        'word-2000'                   => true,      
      )     
    ));

    parent::_initialize($config);   
  }   
  
  protected function _validate($value)
  {
    return (is_string($value));
  }  
  
  protected function _sanitize($value)
  {   
    if($tidy = $this->getTidy($value)) {
      if($tidy->cleanRepair())
         $value = (string) $tidy;     
    }

    return $value; 
  }  
  
  public function getTidy($string)
  {
    if(class_exists('Tidy')) 
    {
      if (!$this->_tidy) {
        $this->_tidy = new Tidy();
      }            

      $this->_tidy->parseString($string, $this->_config, $this->_encoding);
    }

    return $this->_tidy;  
  }
}