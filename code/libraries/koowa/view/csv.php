<?php 

class KViewCsv extends KViewFile
{
	public $quote = '"';
	public $separator = ',';
	public $eol = "\n";     
	
	protected function _initialize(KConfig $config)
	{
    $config->append(array(
      'mimetype'	  => 'text/csv',
      'disposition' => 'inline',
      'quote'		    => '"',
      'separator'   => ',',
      'eol'		      => "\n" 
    ));            

    parent::_initialize($config);    
  } 
  
  public function display()
	{
    $rows    = '';
    $columns = array();
    $rowset  = $this->getModel()->getList();

    foreach($rowset as $row) {
      $data    = $row->toArray();
      $columns = array_merge($columns + array_flip(array_keys($data)));
    }

    foreach($columns as $key => $value) {
      $columns[$key] = '';
    }

    foreach($rowset as $row) 
    {
      $data = $row->toArray();
      $data = array_merge($columns, $data);
      $rows .= $this->_arrayToString(array_values($data)).$this->eol;
    }

    $header = $this->_arrayToString(array_keys($columns)).$this->eol;
    $this->output = $header.$rows;

    return parent::display();       
	}   
       
  protected function _arrayToString($data)
  {
    $fields = array();
    foreach($data as $value)
    {          
      if(is_array($value))
        $value = implode(',', $value);

      if($this->_quoteValue($value)) { 
        $quoted_value = str_replace($this->quote, $this->quote.$this->quote, $value);
        $fields[] 	  = $this->quote . $quoted_value . $this->quote;    
      } 
      else $fields[] = $value; 
    }

    return  implode($this->separator, $fields);  
  }  
    
  protected function _quoteValue($value)
  {
    if(is_numeric($value)) return false;

    if(strpos($value, $this->separator) !== false) return true;

    if(strpos($value, $this->quote) !== false) return true;

    if (strpos($value, "\n") !== false || strpos($value, "\r") !== false ) 
    	return true;

    if(substr($value, 0, 1) == " " || substr($value, -1) == " ") return true;

    return false;       
  }   
}