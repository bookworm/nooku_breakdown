<?php

class KHttpUrl extends KObject 
{
  const SCHEME   = 1;
  const USER     = 2;
  const PASS     = 4;
  const HOST     = 8;
  const PORT     = 16;
  const PATH     = 32;
  const FORMAT   = 64;
  const QUERY    = 128;
  const FRAGMENT = 256;

  const AUTH     = 6;
  const BASE     = 63;
  const FULL     = 511;         
  
  public $scheme = '';
  public $host = '';
  public $port = '';
  public $user = '';
  public $pass = '';
  public $path = '';
  public $format = '';
  protected $_query = array();
  public $fragment = '';
  
  protected $_encode_path = array (
    ' ' => '+',
    '/' => '%2F',
    '?' => '%3F',
    '&' => '%26',
    '#' => '%23', 
  );    
  
  public function __construct(KConfig $config = null) 
  {
    if(!isset($config)) $config = new KConfig();

    parent::__construct($config);

    $this->set($config->url);     
  } 
  
  protected function _initialize(KConfig $config)
  {
    $config->append(array(
      'url'  => '',
    ));

    parent::_initialize($config);   
  }       
  
  public function __set($key, $val)
  {
    if ($key == 'query') $this->setQuery($val);
    if($key == 'path') $this->setPath($val);   
  }    
  
  public function &__get($key)
  {
    if($key == 'query') 
      return $this->_query;    
  }  
  
  public function get($parts = self::FULL)
  {
    $url = '';
  
    if(($parts & self::SCHEME) && !empty($this->scheme))
      $url .=  urlencode($this->scheme).'://';
  
    if(($parts & self::USER) && !empty($this->user)) 
    {
      $url .= urlencode($this->user);
      if(($parts & self::PASS) && !empty($this->pass))
        $url .= ':' . urlencode($this->pass);

      $url .= '@';  
    }
  
    if(($parts & self::HOST) && !empty($this->host)) 
    {
      $url .=  urlencode($this->host);

      if(($parts & self::PORT) && !empty($this->port))
        $url .=  ':' . (int) $this->port;
    }
  
    if(($parts & self::PATH) && !empty($this->path)) 
    {
      $url .= $this->_pathEncode($this->path);
      if(($parts & self::FORMAT) && trim($this->format) !== '')
        $url .= '.' . urlencode($this->format);   
    }
  
    $query = $this->getQuery();
    if(($parts & self::QUERY) && !empty($query))
      $url .= '?' . $this->getQuery();
  
    if(($parts & self::FRAGMENT) && trim($this->fragment) !== '')
      $url .=  '#' . urlencode($this->fragment);
          
    return $url;    
  }     
  
  public function set($url) 
  {
    if(!empty($url)) 
    {
      $segments = parse_url(urldecode($url));

      foreach ($segments as $key => $value) {
        $this->$key = $value;
      }    

      if($this->format = pathinfo($this->path, PATHINFO_EXTENSION)) 
        $this->path = str_replace('.'.$this->format, '', $this->path);  
    }

    return $this;  
  } 
  
  public function setQuery($query)
  {
    if(!is_array($query)) 
    {
      if(strpos($query, '&amp;') !== false)
        $query = str_replace('&amp;','&',$query);  

      parse_str($query, $this->_query);
    }

    if(is_array($query)) 
      $this->_query = $query;

    return $this; 
  }     
  
  public function getQuery($toArray = false)
  {
  	$result = $toArray ? $this->_query : http_build_query($this->_query, '', '&');
  	return $result;
  } 
  
  public function setPath($path)
  {
    $spec = trim($path, '/');

    $this->path = array();
    if(!empty($path)) $this->path = explode('/', $path);

    foreach ($this->path as $key => $val) {
      $this->path[$key] = urldecode($val);
    }

    if($val = end($this->path)) 
    {
      $pos = strrpos($val, '.');

      if($pos !== false) 
      {
        $key = key($this->path);
        $this->format = substr($val, $pos + 1);
        $this->path[$key] = substr($val, 0, $pos);
      }                    
    }

    return $this; 
  }     
  
  public function __toString()
  {
    return $this->get(self::FULL);
  }  
  
  protected function _pathEncode($spec)
  {
    if(is_string($spec))
      $spec = explode('/', $spec);
    $keys = array_keys($this->_encode_path);
    $vals = array_values($this->_encode_path);

    $out = array();
    foreach ((array) $spec as $elem) {
      $out[] = str_replace($keys, $vals, $elem);
    }

    return implode('/', $out);
  }
}