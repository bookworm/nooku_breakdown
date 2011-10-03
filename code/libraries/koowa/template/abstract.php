<?php 

abstract class KTemplateAbstract extends KObject implements KObjectIdentifiable
{
  protected $_path;
  protected $_data = array();
  protected $_contents = '';
  protected $_filters = array();
  protected $_view;
  protected $_stack; 
  
  private static $_errors = array(
    1     => 'Fatal Error',
    2     => 'Warning',
    4     => 'Parse Error',
    8     => 'Notice',
    64    => 'Compile Error',
    256   => 'User Error',
    512   => 'User Warning',
    2048  => 'Strict',
    4096  => 'Recoverable Error'    
  ); 
  
  public function __construct(KConfig $config)
  {
    parent::__construct($config);

    $this->_view = $config->view;
    $this->_stack = $config->stack;

    KTemplateStream::register();

    register_shutdown_function(array($this, '__destroy')); 
    $this->mixin(new KMixinCommandchain($config->append(array('mixer' => $this))));
  }   
  
  public function __destroy()
  {
    if(!$this->getStack()->isEmpty())
    {
      if($error = error_get_last()) 
      {
        if($error['type'] === E_ERROR || $error['type'] === E_PARSE || $error['type'] === E_COMPILE_ERROR) {  
          while(@ob_get_clean());
          $this->sandboxError($error['type'], $error['message'], $error['file'], $error['line']);
        } 
      } 
    }
  }  
  
  protected function _initialize(KConfig $config)
  {
    $config->append(array(
      'stack'            => KFactory::get('lib.koowa.template.stack'),
      'view'             => null,
      'command_chain'    => new KCommandChain(),
      'dispatch_events'  => false,
      'enable_callbacks' => false,      
    ));

    parent::_initialize($config);   
  }
  
  public function getIdentifier()
  {
    return $this->_identifier;
  }
  
  public function getPath()
  {
    return $this->_path;
  }
  
  public function getStack()
  {
    return $this->_stack;
  } 
  
  public function getView()
  {
    if(!$this->_view instanceof KViewAbstract)
    {    
      if(!($this->_view instanceof KIdentifier))
        $this->setView($this->_view);

      $this->_view = KFactory::tmp($this->_view, $config);    
    }

    return $this->_view; 
  }     
  
  public function setView($view)
  {
    if(!($view instanceof KViewAbstract))
    {
      if(is_string($view) && strpos($view, '.') === false) 
      {
        $identifier       = clone $this->_identifier;
        $identifier->path = array('view', $view);
        $identifier->name = KRequest::format() ? KRequest::format() : 'html';  
      }
      else $identifier = KFactory::identify($view);

      if($identifier->path[0] != 'view')
        throw new KTemplateException('Identifier: '.$identifier.' is not a view identifier');

      $view = $identifier; 
    }

    $this->_view = $view;

    return $this;     
  }    
  
  public function loadIdentifier($template, $data = array(), $process = true)
  {
    $identifier = KFactory::identify($template);

    if($identifier->filepath)
      $path = dirname($identifier->filepath);
    else 
      $path = dirname(KLoader::path($identifier));

    $file = $this->findFile($path.'/'.$identifier->name.'.php');

    if($file === false) 
      throw new KTemplateException('Template "'.$identifier->name.'" not found');

    $this->loadFile($file, $data, $process);

    return $this;     
  } 
  
  public function loadFile($file, $data = array(), $process = true)
  {
    $this->_path  = $file;
    $contents = file_get_contents($file);
    $this->loadString($contents, $data, $process);

    return $this;  
  }        
  
  public function loadString($string, $data = array(), $process = true)
  {
    $this->_contents = $string;

    $this->_data = array_merge((array) $this->_data, $data);
    if($process == true) $this->__sandbox();

    return $this;   
  }         
  
  public function render()
  { 
    $context = $this->getCommandContext();
    $context->data = $this->_contents;

    $result = $this->getCommandChain()->run(KTemplateFilter::MODE_WRITE, $context);

    return $context->data;        
  }
  
  public function parse()
  { 
    $context = $this->getCommandContext();
    $context->data = $this->_contents;

    $result = $this->getCommandChain()->run(KTemplateFilter::MODE_READ, $context);

    return $context->data;   
  }             
  
  public function addFilter($filters)
  {
    $filters =  (array) KConfig::toData($filters);

    foreach($filters as $filter)
    {
      if(!($filter instanceof KTemplateFilterInterface)) {
        $identifier = (string) $filter;
        $filter     = KTemplateFilter::factory($filter);   
      }
      else $identifier = (string) $filter->getIdentifier();

      $this->getCommandChain()->enqueue($filter);
      $this->_filters[$identifier] = $filter; 
    }

    return $this;    
  }   
  
  public function getFilters()
  {
    return $this->_filters;
  }

  public function getFilter($identifier)
  {
    return isset($this->_filters[$identifier]) ? $this->_filters[$identifier] : null;
  } 
  
  public function findFile($file)
  {    
    $result = false;
    $path   = dirname($file);

    if(strpos($path, '://') === false) {
      $path = realpath($path); 
      $file = realpath($file);     
    }

    if(file_exists($file) && substr($file, 0, strlen($path)) == $path)
      $result = $file;

    return $result;
  }
  
  public function renderHelper($identifier, $params = array())
  {
    $parts    = explode('.', $identifier);
    $function = array_pop($parts);
    
    $helper = $this->getHelper(implode('.', $parts));
    
    if(!is_callable( array( $helper, $function ) )) {
      throw new KTemplateHelperException( get_class($helper).'::'.$function.' not supported.' );
    } 
    
    return $helper->$function($params);
  }     
  
  public function getHelper($helper)
  { 
    if(is_string($helper) && strpos($helper, '.') === false ) 
    {
      $identifier = clone $this->getIdentifier();
      $identifier->path = array('template','helper');
      $identifier->name = $helper;      
    }
    else $identifier = KFactory::identify($helper);
   
    $helper = KTemplateHelper::factory($identifier, array('template' => $this));
    
    return $helper;
  }    
  
  private function __sandbox()
  { 
    set_error_handler(array($this, 'sandboxError'), E_WARNING | E_NOTICE);
    $this->getStack()->push(clone $this);
    extract($this->_data, EXTR_SKIP); 

    ob_start();
    include 'tmpl://'.$this->getStack()->getIdentifier();
    $this->_contents = ob_get_clean();

    $this->getStack()->pop();
    restore_error_handler();

    return $this;   
  }  
  
  public function sandboxError($code, $message, $file = '', $line = 0, $context = array())
  {
    if($file == 'tmpl://lib.koowa.template.stack') 
    {
      if(ini_get('display_errors')) 
        echo '<strong>'.self::$_errors[$code].'</strong>: '.$message.' in <strong>'.$this->_path.'</strong> on line <strong>'.$line.'</strong>';

      if(ini_get('log_errors'))
        error_log(sprintf('PHP %s:  %s in %s on line %d', self::$_errors[$code], $message, $this->_path, $line));

      return true;
    }

    return false;   
  }     

  public function __toString()
  {
    try {
      $result = $this->_contents;
    } 
    catch (Exception $e) {
      $result = $e->getMessage();
    }   
      
    return $result;
  }
}