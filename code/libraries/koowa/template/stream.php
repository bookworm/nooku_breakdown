<?php

class KTemplateStream
{  
  private $_pos = 0;
  private $_data;
  private $_stat;
  private $_path;  
    
  public static function register()
  {       
    if(!in_array('tmpl', stream_get_wrappers()))
      stream_wrapper_register('tmpl', __CLASS__);
  }  
     
  public function stream_open($path) 
  {        
    $identifier = str_replace('tmpl://', '', $path);
    $template = KFactory::get($identifier)->top();
    $this->_path = $template->getPath();
    $this->_data = $template->parse();
    $this->_stat = array('mode' => 0100777, 'size' => strlen($this->_data));

    return true;   
  } 

  public function stream_read($count) 
  {
    $ret = substr($this->_data, $this->_pos, $count);
    $this->_pos += strlen($ret);
    return $ret;
  }        

  public function stream_tell() 
  {
    return $this->_pos;
  }

  public function stream_eof() 
  {
    return $this->_pos >= strlen($this->_data);
  }

  public function stream_stat() 
  {
    return $this->_stat;
  }

  public function stream_flush()
  {
    return false;
  }

  public function stream_close() { }

  public function stream_cast($cast_as)
  {
    return false; 
  }     

  public function stream_seek($offset, $whence) 
  {
    switch($whence) 
    {
      case SEEK_SET:
        if ($offset < strlen($this->_data) && $offset >= 0) {
          $this->_pos = $offset;
          return true;  
        } 
        else return false;
        break;    
      case SEEK_CUR:
        if($offset >= 0) {
          $this->_pos += $offset;
          return true;   
        } 
        else return false;
        break;   

      case SEEK_END:
        if(strlen($this->_data) + $offset >= 0) {
          $this->_pos = strlen($this->_data) + $offset;
          return true;
        }    
        else return false;
        break;  

      default:
        return false;
    }  
  }   
     
  public function url_stat($path, $flags) 
  {
    return $this->_stat;
  }
}