<?php

class KTemplateHelperBehavior extends KTemplateHelperAbstract
{
  protected static $_loaded = array();
  
  public function mootools($config = array())
  {
    $config = new KConfig($config);
    $html ='';

    if(!isset(self::$_loaded['mootools'])) {
      $html .= '<script src="media://lib_koowa/js/mootools.js" />';
      self::$_loaded['mootools'] = true;    
    }

    return $html;  
  }    
  
  public function modal($config = array())
  {
    $config = new KConfig($config);
    $config->append(array(
      'selector' => 'a.modal',
      'options'  => array('disableFx' => true)
    ));

    $html = '';

    if(!isset(self::$_loaded['modal'])) {
      $html .= '<script src="media://lib_koowa/js/modal.js" />';
      $html .= '<style src="media://lib_koowa/css/modal.css" />';
      self::$_loaded['modal'] = true;
    }

    $signature = md5(serialize(array($config->selector,$config->options)));
    if(!isset(self::$_loaded[$signature]))
    {
      $options = !empty($config->options) ? $config->options->toArray() : array();
      $html .= "
      <script>
        window.addEvent('domready', function() {

        SqueezeBox.initialize(".json_encode($options).");
        SqueezeBox.assign($$('".$config->selector."'), {
              parse: 'rel'
        });
      });
      </script>";

      self::$_loaded[$signature] = true;
    }

    return $html;
  } 
  
  public function tooltip($config = array())
  {
    $config = new KConfig($config);
    $config->append(array(
      'selector' => '.hasTip',
      'options'  => array()
    ));

    $html = '';

    $signature = md5(serialize(array($config->selector,$config->options)));
    if(!isset(self::$_loaded[$signature]))
    {
      $options = $config->options->toArray() ? ', '.$config->options : '';
      $html .= "
      <script>
        window.addEvent('domready', function(){ new Tips($$('".$config->selector."')".$options."); });
      </script>";

      self::$_loaded[$signature] = true;
    }

    return $html;
  }      
  
  public function overlay($config = array())
  {
    $config = new KConfig($config);
    $config->append(array(
      'url'     => '',
      'options'   => array(),
      'attribs' => array()
    ));

    $html = '';     
    
    if(!isset(self::$_loaded['overlay']))
    {
      $html .= '<script src="media://lib_koowa/js/koowa.js" />';
      $html .= '<style src="media://lib_koowa/css/koowa.css" />';

      $options = $config->options->toArray() ? ', '.$config->options : '';
      $html .= "
      <script>
        window.addEvent('domready', function(){ $$('.-koowa-overlay').each(function(overlay){ new Koowa.Overlay(overlay".$options."); }); });
      </script>";

      self::$_loaded['overlay'] = true;
    }

    $url = KFactory::tmp('lib.koowa.http.url', array('url' => $config->url));
    $url->query['tmpl'] = '';

    $attribs = KHelperArray::toString($config->attribs);

    $html .= '<div href="'.$url.'" class="-koowa-overlay" id="'.$url->fragment.'" '.$attribs.'><div class="-koowa-overlay-status">'.JText::_('Loading...').'</div></div>';
    return $html;
  }           
  
  public function keepalive($config = array())
  {
    $config = new KConfig($config);
    $config->append(array(
      'refresh' => 15 * 60000, //15min
       'url'    => KRequest::url()
    ));

    $refresh = (int) $config->refresh;

    if($refresh > 3600000 || $refresh <= 0) $refresh = 3600000;

    $html =
    "<script>
      Koowa.keepalive =  function() {
        var request = new Request({method: 'get', url: '".$config->url."'}).send();
      }

      window.addEvent('domready', function() { Koowa.keepalive.periodical('".$refresh."'); });
    </script>";

    return $html;
  }   
  
  public function validator($config = array())
  {
    $config = new KConfig($config);
    $config->append(array(
      'selector' => '.-koowa-form',
      'options'  => array(
        'scrollToErrorsOnChange' => true,
        'scrollToErrorsOnBlur'   => true,
      )        
    ));

    $html = '';

    if(!isset(self::$_loaded['validator']))
    {
      if(version_compare(JVERSION,'1.6.0','ge')) 
        $html .= '<script src="media://lib_koowa/js/validator-1.3.js" />';
      else 
        $html .= '<script src="media://lib_koowa/js/validator-1.2.js" />';
                                        
      self::$_loaded['validator'] = true;
    }

    $options = $config->options->toArray() ? ', '.$config->options : '';
    $html .= "<script>
    window.addEvent('domready', function(){
        $$('$config->selector').each(function(form){
            new Form.Validator.Inline(form".$options.");
            form.addEvent('validate', form.validate.bind(form));
        });
    });
    </script>";

    return $html;
  }
}