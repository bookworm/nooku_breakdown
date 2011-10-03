<?php

class KFilterHtml extends KFilterAbstract
{
  protected $_tagsArray = array();
  protected $_attrArray = array();
  protected $_tagsMethod = true;
  protected $_attrMethod = true;
  protected $_xssAuto = true;
  protected $_tagBlacklist = array('applet', 'body', 'bgsound', 'base', 'basefont', 'embed', 'frame', 
    'frameset', 'head', 'html', 'id', 'iframe', 'ilayer', 'layer', 'link', 'meta', 
    'name', 'object', 'script', 'style', 'title', 'xml');
  protected $_attrBlacklist = array('action', 'background', 'codebase', 'dynsrc', 'lowsrc');
  
  public function __construct(KConfig $config)
  {
    parent::__construct($config);

    if(isset($config->tag_list)) 
      $this->_tagsArray = array_map('strtolower', (array) $config->tag_list);
    if(isset($config->attribute_list)) 
      $this->_attrArray = array_map('strtolower', (array) $config->attribute_list);

    if(isset($config->tag_method)) 
      $this->_tagsMethod = $config->tag_method;
    if(isset($config->attribute_method)) 
      $this->_attrMethod = $config->attribute_method;
    if(isset($config->xss_auto)) 
      $this->_xssAuto = $config->xss_auto;
  }          
  
  protected function _validate($value)
  {
    return is_string($value);
  }      
  
  protected function _sanitize($value)
  {
    $value = (string) $value;

    if(!empty ($value)) 
      $value = $this->_remove($this->_decode($value));

    return $value; 
  }  
  
  protected function _remove($source)
  {
    $loopCounter = 0;

    while($source != $this->_cleanTags($source)) {
      $source = $this->_cleanTags($source);
      $loopCounter ++;
    }
    return $source;        
  }             
  
  protected function _cleanTags($source)
  {
    $preTag         = null;
    $postTag        = $source;
    $currentSpace   = false;
    $attr           = '';   

    $tagOpen_start  = strpos($source, '<');

    while ($tagOpen_start !== false)
    {
      $preTag     .= substr($postTag, 0, $tagOpen_start);
      $postTag     = substr($postTag, $tagOpen_start);
      $fromTagOpen = substr($postTag, 1);
      $tagOpen_end = strpos($fromTagOpen, '>');

      if($tagOpen_end === false) {
        $postTag        = substr($postTag, $tagOpen_start +1);
        $tagOpen_start  = strpos($postTag, '<');
        continue;   
      }

      $tagOpen_nested = strpos($fromTagOpen, '<');
      $tagOpen_nested_end = strpos(substr($postTag, $tagOpen_end), '>');
      if(($tagOpen_nested !== false) && ($tagOpen_nested < $tagOpen_end)) {
        $preTag         .= substr($postTag, 0, ($tagOpen_nested +1));
        $postTag        = substr($postTag, ($tagOpen_nested +1));
        $tagOpen_start  = strpos($postTag, '<');
        continue;    
      } 

      $tagOpen_nested = (strpos($fromTagOpen, '<') + $tagOpen_start +1);
      $currentTag     = substr($fromTagOpen, 0, $tagOpen_end);
      $tagLength      = strlen($currentTag);
      $tagLeft        = $currentTag;
      $attrSet        = array ();
      $currentSpace   = strpos($tagLeft, ' ');  

      if(substr($currentTag, 0, 1) == '/') {
        $isCloseTag     = true;
        list ($tagName) = explode(' ', $currentTag);
        $tagName        = substr($tagName, 1); 
      }
      else {
        $isCloseTag     = false;
        list ($tagName) = explode(' ', $currentTag);
      }

      if((!preg_match("/^[a-z][a-z0-9]*$/i", $tagName)) || (!$tagName) || ((in_array(strtolower($tagName), $this->_tagBlacklist)) && ($this->_xssAuto))) {
        $postTag        = substr($postTag, ($tagLength +2));
        $tagOpen_start  = strpos($postTag, '<');
        continue;
      }     

      while($currentSpace !== false)
      {
        $attr           = '';
        $fromSpace      = substr($tagLeft, ($currentSpace +1));
        $nextSpace      = strpos($fromSpace, ' ');
        $openQuotes     = strpos($fromSpace, '"');
        $closeQuotes    = strpos(substr($fromSpace, ($openQuotes +1)), '"') + $openQuotes +1;
               
        if(strpos($fromSpace, '=') !== false) 
        {
          if(($openQuotes !== false) && (strpos(substr($fromSpace, ($openQuotes +1)), '"') !== false))
            $attr = substr($fromSpace, 0, ($closeQuotes +1));
          else 
            $attr = substr($fromSpace, 0, $nextSpace);
        } 
        else {
          if($fromSpace != '/') $attr = substr($fromSpace, 0, $nextSpace);
        }

        if(!$attr && $fromSpace != '/') $attr = $fromSpace;

        $attrSet[] = $attr;

        $tagLeft        = substr($fromSpace, strlen($attr));
        $currentSpace   = strpos($tagLeft, ' ');     
      }

      $tagFound = in_array(strtolower($tagName), $this->_tagsArray);

      if((!$tagFound && $this->_tagsMethod) || ($tagFound && !$this->_tagsMethod)) 
      {
        if (!$isCloseTag) 
        {
          $attrSet = $this->_cleanAttributes($attrSet);
          $preTag .= '<'.$tagName;
          for($i = 0; $i < count($attrSet); $i ++) {
            $preTag .= ' '.$attrSet[$i];
          }

          if(strpos($fromTagOpen, '</'.$tagName)) 
            $preTag .= '>';
          else
            $preTag .= ' />';
        } 
        else {
          $preTag .= '</'.$tagName.'>';
        }   
      } 

      $postTag        = substr($postTag, ($tagLength +2));
      $tagOpen_start  = strpos($postTag, '<'); 
    }

    if($postTag != '<')
        $preTag .= $postTag;  
        
    return $preTag;      
  } 
  
  protected function _cleanAttributes($attrSet)
  {
    $newSet = array();

    for ($i = 0; $i < count($attrSet); $i ++)
    {
      if(!$attrSet[$i]) continue;

      $attrSubSet = explode('=', trim($attrSet[$i]), 2);
      list ($attrSubSet[0]) = explode(' ', $attrSubSet[0]);
 
      if((!preg_match('/[a-z]*$/i', $attrSubSet[0])) || (($this->_xssAuto) && ((in_array(strtolower($attrSubSet[0]), $this->_attrBlacklist)) || (substr($attrSubSet[0], 0, 2) == 'on'))))
        continue;

      if($attrSubSet[1]) 
      {
        $attrSubSet[1] = str_replace('&#', '', $attrSubSet[1]);
        $attrSubSet[1] = preg_replace('/[\n\r]/', '', $attrSubSet[1]);
        $attrSubSet[1] = str_replace('"', '', $attrSubSet[1]);    
        
        if((substr($attrSubSet[1], 0, 1) == "'") && (substr($attrSubSet[1], (strlen($attrSubSet[1]) - 1), 1) == "'"))
          $attrSubSet[1] = substr($attrSubSet[1], 1, (strlen($attrSubSet[1]) - 2));

        $attrSubSet[1] = stripslashes($attrSubSet[1]);  
      }

      if($this->_checkAttribute($attrSubSet)) continue;

      $attrFound = in_array(strtolower($attrSubSet[0]), $this->_attrArray);

      if((!$attrFound && $this->_attrMethod) || ($attrFound && !$this->_attrMethod)) 
      {
        if($attrSubSet[1]) 
          $newSet[] = $attrSubSet[0].'="'.$attrSubSet[1].'"';
        elseif ($attrSubSet[1] == "0") 
          $newSet[] = $attrSubSet[0].'="0"';
        else 
          $newSet[] = $attrSubSet[0].'="'.$attrSubSet[0].'"';   
      }
    }
    return $newSet;  
  }  
  
  protected function _checkAttribute($attrSubSet)
  {
    $attrSubSet[0] = strtolower($attrSubSet[0]);
    $attrSubSet[1] = strtolower($attrSubSet[1]);
    return (((strpos($attrSubSet[1], 'expression') !== false) && ($attrSubSet[0]) == 'style') || (strpos($attrSubSet[1], 'javascript:') !== false) || (strpos($attrSubSet[1], 'behaviour:') !== false) || (strpos($attrSubSet[1], 'vbscript:') !== false) || (strpos($attrSubSet[1], 'mocha:') !== false) || (strpos($attrSubSet[1], 'livescript:') !== false));
  }

  protected function _decode($source)
  {
    $trans_tbl = get_html_translation_table(HTML_ENTITIES);
    foreach($trans_tbl as $k => $v) {
      $ttr[$v] = utf8_encode($k);
    }   
    
    $source = strtr($source, $ttr);
    $source = preg_replace('/&#(\d+);/me', "chr(\\1)", $source); 
    $source = preg_replace('/&#x([a-f0-9]+);/mei', "chr(0x\\1)", $source); 
    
    return $source; 
  }
}