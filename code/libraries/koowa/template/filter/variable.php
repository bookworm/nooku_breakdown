<?php

class KTemplateFilterVariable extends KTemplateFilterAbstract implements KTemplateFilterRead
{
  public function read(&$text) 
  {		 
    $text = str_replace('\@', '\$', $text);
    $text = str_replace(array('@$'), '$', $text);
    $text = str_replace('\$', '@', $text);

    return $this;  
  }                       
}