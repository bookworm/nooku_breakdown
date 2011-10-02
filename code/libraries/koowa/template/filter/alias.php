<?php

class KTemplateFilterAlias extends KTemplateFilterAbstract implements KTemplateFilterRead, KTemplateFilterWrite
{
  protected $_alias_read = array(
    '@helper('   => '$this->renderHelper(',
    '@date('     => '$this->renderHelper(\'date.format\',',
    '@overlay('  => '$this->renderHelper(\'behavior.overlay\', ',
    '@text('     => 'JText::_(',
    '@template(' => '$this->loadIdentifier(',
    '@route('    => '$this->getView()->createRoute(',
    '@escape('   => '$this->getView()->escape(',       
  );          
  
  protected $_alias_write = array();
  
  public function append(array $alias, $mode = KTemplateFilter::MODE_READ)
  {
    if($mode & KTemplateFilter::MODE_READ)
      $this->_alias_read = array_merge($this->_alias_read, $alias); 

    if($mode & KTemplateFilter::MODE_WRITE)
      $this->_alias_write = array_merge($this->_alias_write, $alias); 

    return $this;   
  }      
  
  public function read(&$text) 
  {
    $text = str_replace(
      array_keys($this->_alias_read), 
      array_values($this->_alias_read), 
      $text);     

    return $this;    
  }        
  
  public function write(&$text) 
  {
    $text = str_replace(
      array_keys($this->_alias_write), 
      array_values($this->_alias_write), 
      $text);
      
    return $this;   
  }
}