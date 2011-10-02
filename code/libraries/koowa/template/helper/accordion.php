<?php

class KTemplateHelperAccordion extends KTemplateHelperBehavior 
{
  public function startPane($config = array())
	{
    $config = new KConfig($config);

    $config->append(array(
      'id'	       => 'accordions',
      'options'	   => array(
      'duration'   => 300,
      'opacity'	   => false,
      'alwaysHide' => true,
      'scroll'	   => false
      ),
      'attribs'	=> array(),
      'events'	=> array()    
    ));

    $html  = ''; 
		
		if(!isset($this->_loaded['accordion'])) 
  		$this->_loaded['accordion'] = true;

		
		$id      = strtolower($config->id);
		$attribs = KHelperArray::toString($config->attribs);
		
		$events			  = '';
		$onActive 		= 'function(e){e.addClass(\'jpane-toggler-down\');e.removeClass(\'jpane-toggler\');}';
		$onBackground	= 'function(e){e.addClass(\'jpane-toggler\');e.removeClass(\'jpane-toggler-down\');}';
		
		if($config->events) { 
			$events = '{onActive:'.$onActive.',onBackground:'.$onBackground.'}';
		}

		$scroll = $config->options->scroll ? ".addEvent('onActive', function(toggler){
			new Fx.Scroll(window, {duration: this.options.duration, transition: this.transition}).toElement(toggler);
		})" : '';

		$html .= '
			<script>
				window.addEvent(\'domready\', function(){ 
					new Accordion($$(\'.panel h3.jpane-toggler\'),$$(\'.panel div.jpane-slider\'),$merge('.$events.','.$config->options.'))'.$scroll.'; 
				});
			</script>';
	
		$html .= '<div id="'.$id.'" class="pane-sliders" '.$attribs.'>';
		return $html;
	}   
	
	public function endPane($config = array())
	{
    return '</div>';
	} 
	
	public function startPanel($config = array())
	{
		$config = new KConfig($config);
		
		$config->append(array(
			'title'		=> 'Slide',
			'attribs'	=> array(),
			'translate'	=> true
		));
		
		$title   = $config->translate ? JText::_($config->title) : $config->title;
		$attribs = KHelperArray::toString($config->attribs);
	
		$html = '<div class="panel"><h3 class="jpane-toggler title" '.$attribs.'><span>'.$title.'</span></h3><div class="jpane-slider content">';
		return $html;
	}
	
	public function endPanel($config = array())
	{
  	return '</div></div>';
	}
}