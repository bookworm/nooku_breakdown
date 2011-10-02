<?php 

class KViewFile extends KViewAbstract
{
  public $path = '';
  public $filename = '';
  public $disposition = 'attachment';
  
  public function __construct(KConfig $config)
  {
    parent::__construct($config);

    $this->set($config->toArray());    
  }
  
  protected function _initialize(KConfig $config)
  {
    $count = count($this->_identifier->path);

    $config->append(array(
      'path'        => '',
      'filename'    => $this->_identifier->path[$count-1].'.'.$this->_identifier->name,
      'disposition' => 'attachment'  
    ));

    parent::_initialize($config); 
  }  
  
  public function display()
  {
    if(ini_get('zlib.output_compression')) 
      ini_set('zlib.output_compression', 'Off');

    if(!ini_get('safe_mode')) @set_time_limit(0);

    if($this->mimetype) header('Content-type: '.$this->mimetype);

    header('Content-Transfer-Encoding: binary');
    header('Accept-Ranges: bytes');

    header("Pragma: no-store,no-cache");
    header("Cache-Control: no-cache, no-store, must-revalidate, max-age=-1");
    header("Cache-Control: post-check=0, pre-check=0", false);
    header("Expires: Mon, 14 Jul 1789 12:30:00 GMT");
    header("Last-Modified: " . gmdate("D, d M Y H:i:s") . " GMT");

    while (@ob_end_clean());

    $this->filename = basename($this->filename);
    if(!empty($this->output))
    {
      if(empty($this->filename))
        throw new KViewException('No filename supplied');   
        
      $this->_setDisposition();
      $filesize = strlen($this->output);
      header('Content-Length: '.$filesize);
      flush();
      echo $this->output;
    }
    elseif(!empty($this->path))
    {
      if(empty($this->filename)) 
        $this->filename = basename($this->path); 
                       
      $filesize = @filesize($this->path);
      header('Content-Length: '.$filesize);
      $this->_setDisposition();
      flush();
      $this->_readChunked($this->path);
    }
    else throw new KViewException('No output or path supplied');

    die; 
  }     
  
  protected function _setDisposition()
  {
    if(isset($this->disposition) && $this->disposition == 'inline')     
      header('Content-Disposition: inline; filename="'.$this->filename.'"'); 
    else 
    {    
      header('Content-Description: File Transfer');
      header('Content-type: application/force-download');
      header('Content-Disposition: attachment; filename="'.$this->filename.'"');
    }
    return $this;  
  }    
  
  protected function _readChunked($path)
  {
    $chunksize  = 1*(1024*1024); // Chunk size
    $buffer     = '';
    $cnt        = 0;

    $handle = fopen($path, 'rb');  
    
    if($handle === false) 
      throw new KViewException('Cannot open file');

    while(!feof($handle)) 
    {
      $buffer = fread($handle, $chunksize);
      echo $buffer;
      @ob_flush();
      flush();
      $cnt += strlen($buffer);
    }

    $status = fclose($handle);
    return $cnt; 
  }                 
}