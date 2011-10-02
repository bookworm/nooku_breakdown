<?php

abstract class KTemplateFilterAbstract extends KObject implements KTemplateFilterInterface
{
  protected $_priority;
  protected $_template;
  
  public function __construct(KConfig $config = null) 
  { 
    parent::__construct($config);

    $this->_priority = $config->priority;   
  }        
  
  protected function _initialize(KConfig $config)
  {
    $config->append(array(
      'priority' => KCommand::PRIORITY_NORMAL,
    ));

    parent::_initialize($config);  
  }  
  
  public function getIdentifier()
  {
    return $this->_identifier;
  }

  public function getPriority()
  {
    return $this->_priority;
  }

  public function getTemplate()
  {
    return $this->_template;
  }   
      
  final public function execute($name, KCommandContext $context) 
  {
    $this->_template = $context->caller;
    $data = $context->data;

    if(($name & KTemplateFilter::MODE_READ) && $this instanceof KTemplateFilterRead) {
      $this->read($data);
    }

    if(($name & KTemplateFilter::MODE_WRITE) && $this instanceof KTemplateFilterWrite) {
      $this->write($data);
    } 

    $context->data = $data;
    $this->_template = null;

    return true;    
  }          
  
  protected function _parseAttributes( $string )
  {
    $result = array(); 

    if(!empty($string))
    {
      $attr   = array();

      preg_match_all( '/([\w:-]+)[\s]?=[\s]?"([^"]*)"/i', $string, $attr );

      if (is_array($attr))
      {
        $numPairs = count($attr[1]);
        for($i = 0; $i < $numPairs; $i++ ) {
           $result[$attr[1][$i]] = $attr[2][$i];
        }   
      } 
    }

    return $result;   
  }
}