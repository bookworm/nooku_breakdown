<?php

class KTemplateHelperTabs extends KTemplateHelperBehavior
{
  public function startPane( $config = array() )
  {
    $config = new KConfig($config);
    $config->append(array(
      'id'      => 'pane',
      'attribs' => array(),
      'options' => array()  
    ));
  
    $html  = '';
  
    if(!isset($this->_loaded['tabs'])) {
      $html .= '<script src="media://lib_koowa/js/tabs.js" />';     
      $this->_loaded['tabs'] = true; 
    }
  
    $id      = strtolower($config->id);
    $attribs = KHelperArray::toString($config->attribs);
    $options = $config->options->toArray() ? ', '.$config->options : '';

    $html .= "
        <script>
            window.addEvent('domready', function(){ new Koowa.Tabs('tabs-".$id."'".$options."); });
        </script>";

    $html .= '<dl class="tabs" id="tabs-'.$id.'" '.$attribs.'>';
    return $html;  
  }      
  
  public function endPane($config = array())
  {
    return '</dl>';
  }     
  
  public function startPanel( $config = array())
  {
    $config = new KConfig($config);
    $config->append(array(
      'title'     => '',
      'attribs'   => array(),
      'options'   => array(),
      'translate' => true
    ));

    $title   = $config->translate ? JText::_($config->title) : $config->title;
    $attribs = KHelperArray::toString($config->attribs);

    return '<dt '.$attribs.'><span>'.$title.'</span></dt><dd>'; 
  }      
  
  public function endPanel($config = array())
  {
    return '</dd>';
  }
}