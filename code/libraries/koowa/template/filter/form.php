<?php

class KTemplateFilterForm extends KTemplateFilterAbstract implements KTemplateFilterWrite
{  
  protected $_token_value;
  protected $_token_name; 
   
  public function __construct(KConfig $config = null) 
  { 
    parent::__construct($config);

    $this->_token_value = $config->token_value;
    $this->_token_name  = $config->token_name;        
  }        
  
  protected function _initialize(KConfig $config)
  {
    $config->append(array(
      'token_value' => '',
      'token_name'  => '_token',
    ));

    parent::_initialize($config);
  }        
  
  public function write(&$text)
  {   
    if(!empty($this->_token_value)) 
    {
      $text = preg_replace('/(<form.*method="post".*>)/i', 
        '\1'.PHP_EOL.'<input type="hidden" name="'.$this->_token_name.'" value="'.$this->_token_value.'" />', 
        $text     
      );  
    }

    if(!empty($this->_token_value)) 
    {
      $text = preg_replace('/(<form.*method="get".*class=".*-koowa-grid.*".*)>/i', 
        '\1 data-token-name="'.$this->_token_name.'" data-token-value="'.$this->_token_value.'">', 
        $text  
      );  
    }

    $matches = array();
    if(preg_match_all('#<form.*action=".*\?(.*)".*method="get".*>#iU', $text, $matches))
    {
      foreach($matches[1] as $key => $query)
      {
       parse_str(str_replace('&amp;', '&', $query), $query);
 
       $input = '';
       foreach($query as $name => $value) {
         $input .= PHP_EOL.'<input type="hidden" name="'.$name.'" value="'.$value.'" />';
       }
 
       $text = str_replace($matches[0][$key], $matches[0][$key].$input, $text);
      }   
    }  
  }
}