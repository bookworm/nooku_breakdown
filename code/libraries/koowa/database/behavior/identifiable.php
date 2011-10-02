<?php 

class KDatabaseBehaviorIdentifiable extends KDatabaseBehaviorAbstract
{
  protected $_urand;
	
	public function getMixableMethods(KObject $mixer = null)
	{
    $methods = array();

    if(isset($mixer->uuid))
      $methods = parent::getMixableMethods($mixer);

    $this->_urand = @fopen ( '/dev/urandom', 'rb' );

    return $methods;    
	}        
	
	protected function _beforeTableInsert(KCommandContext $context)
	{
		if(isset($this->uuid)) {
			$hex = $this->getTable()->getColumn('uuid')->type == 'char' ? false : true;
			$this->uuid  = $this->_uuid($hex);
		}
	}  
	
	protected function _uuid($hex = false) 
  {
    $pr_bits = false;
    if(is_resource ( $this->_urand ))
      $pr_bits .= @fread ( $this->_urand, 16 );

    if(!$pr_bits) 
    {
      $fp = @fopen ( '/dev/urandom', 'rb' );
      if($fp !== false) {
        $pr_bits .= @fread ( $fp, 16 );
        @fclose ( $fp );   
      } 
      else 
      {
        $pr_bits = "";
        for($cnt = 0; $cnt < 16; $cnt ++) {
          $pr_bits .= chr ( mt_rand ( 0, 255 ) );
        } 
      }
    }

    $time_low = bin2hex ( substr ( $pr_bits, 0, 4 ) );
    $time_mid = bin2hex ( substr ( $pr_bits, 4, 2 ) );
    $time_hi_and_version = bin2hex ( substr ( $pr_bits, 6, 2 ) );
    $clock_seq_hi_and_reserved = bin2hex ( substr ( $pr_bits, 8, 2 ) );
    $node = bin2hex ( substr ( $pr_bits, 10, 6 ) );
    
    $time_hi_and_version = hexdec ( $time_hi_and_version );
    $time_hi_and_version = $time_hi_and_version >> 4;
    $time_hi_and_version = $time_hi_and_version | 0x4000;

    $clock_seq_hi_and_reserved = hexdec ( $clock_seq_hi_and_reserved );
    $clock_seq_hi_and_reserved = $clock_seq_hi_and_reserved >> 2;
    $clock_seq_hi_and_reserved = $clock_seq_hi_and_reserved | 0x8000;

    $format = $hex ? '%08s%04s%04x%04x%012s' : '%08s-%04s-%04x-%04x-%012s';

    return sprintf ( $format, $time_low, $time_mid, $time_hi_and_version, $clock_seq_hi_and_reserved, $node ); 
  }
}