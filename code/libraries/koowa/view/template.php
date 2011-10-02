<?php 

abstract class KViewTemplate extends KViewAbstract
{
  protected $_template;
  protected $_escape;
  protected $_auto_assign;
  protected $_data;
  protected $_scripts = array();
  protected $_styles = array();  
  
  public function __construct(KConfig $config)
  {
    parent::__construct($config);

    $this->_auto_assign = $config->auto_assign;
    $this->setEscape($config->escape);
    $this->_template = $config->template;
 
    if(!empty($config->template_filters))
      $this->getTemplate()->addFilter($config->template_filters);

    $this->assign('baseurl' , $config->base_url)
      ->assign('mediaurl', $config->media_url);

    $this->getTemplate()->getFilter('alias')->append(
      array('media://' => $config->media_url.'/'), KTemplateFilter::MODE_READ | KTemplateFilter::MODE_WRITE
    );

    $this->getTemplate()->getFilter('alias')->append(
      array('base://' => $config->base_url.'/'), KTemplateFilter::MODE_READ | KTemplateFilter::MODE_WRITE
    );    
  }  
  
  protected function _initialize(KConfig $config)
  {
    $identifier = clone $this->_identifier;

    $config->append(array(
      'escape'           => 'htmlspecialchars',
      'template'         => $this->getName(),
      'template_filters' => array('shorttag', 'alias', 'variable', 'script', 'style', 'link', 'template'),
      'auto_assign'      => true,
      'base_url'         => KRequest::base(),
      'media_url'        => KRequest::root().'/media',
    ));

    parent::_initialize($config);  
  }        
  
  public function __set($property, $value)
  {
    $this->_data[$property] = $value;
  }
  
  public function __get($property)
  {
    $result = null;
    if(isset($this->_data[$property]))
      $result = $this->_data[$property];

    return $result;  
  }    
  
  public function assign()
  {
    $arg0 = @func_get_arg(0);
    $arg1 = @func_get_arg(1);

    if (is_object($arg0) || is_array($arg0))
      $this->set($arg0);
    elseif (is_string($arg0) && substr($arg0, 0, 1) != '_' && func_num_args() > 1)
      $this->set($arg0, $arg1);

    return $this;     
  }  
  
  public function escape($var)
  {
    return call_user_func($this->_escape, $var);
  }
  
  public function display()
  {
    if(empty($this->output))
    {
      $this->output = $this->getTemplate()
        ->loadIdentifier($this->_layout, $this->_data)
        ->render();  
    }

    return parent::display(); 
  }      
  
  public function setEscape($spec)
  {
    $this->_escape = $spec;
    return $this; 
  }                   
  
  public function setLayout($layout)
  {
    if(is_string($layout) && strpos($layout, '.') === false ) {
      $identifier = clone $this->_identifier; 
      $identifier->name = $layout;
    }
    else $identifier = KFactory::identify($layout);

    $this->_layout = $identifier;
    return $this;   
  }       
  
  public function getLayout()
  {
    return $this->_layout->name;
  } 
  
  public function getTemplate()
  {
    if(!$this->_template instanceof KTemplateAbstract)
    { 
      if(!($this->_template instanceof KIdentifier))
        $this->setTemplate($this->_template);

      $options = array(
        'view' => $this
      );

      $this->_template = KFactory::tmp($this->_template, $options);  
    }

    return $this->_template; 
  }   
            
  public function setTemplate($template)
  {
    if(!($template instanceof KTemplateAbstract))
    {
      if(is_string($template) && strpos($template, '.') === false ) 
      {
        $identifier = clone $this->_identifier; 
        $identifier->path = array('template');
        $identifier->name = $template;        
      }
      else $identifier = KFactory::identify($template);

      if($identifier->path[0] != 'template')
        throw new KViewException('Identifier: '.$identifier.' is not a template identifier');

      $template = $identifier;
    } 

    $this->_template = $template;

    return $this;        
  }  
  
  public function __toString()
  {
    return $this->display();
  }    
  
  public function __call($method, $args) 
  { 
    if(count($args) == 1) 
    { 
      if(method_exists($this, 'set'.ucfirst($method))) 
        return $this->{'set'.ucfirst($method)}($args[0]); 
      else  
        return $this->set($method, $args[0]);  
    } 

    return parent::__call($method, $args);       
  }
}