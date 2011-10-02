<?php

class KTemplateFilterTemplate extends KTemplateFilterAbstract implements KTemplateFilterRead
{
  protected function _initialize(KConfig $config)
  {
    $config->append(array(
      'priority'   => KCommand::PRIORITY_HIGH,
    ));

    parent::_initialize($config);   
  }      
  
  public function read(&$text) 
  {
    if(preg_match_all('#@template\(\'(.*)\'#siU', $text, $matches))
    {
      foreach($matches[1] as $key => $match) 
      {
        if(is_string($match) && strpos($match, '.') === false) {
          $path =  dirname($this->getTemplate()->getPath()).DS.$match.'.php';
          $text = str_replace($matches[0][$key], '$this->loadFile('."'".$path."'", $text);
        }
      }  
    }

    return $this;  
  }
}