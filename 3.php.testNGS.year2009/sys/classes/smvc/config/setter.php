<?php defined('SYSPATH') or die('No direct script access.');
/**
 * @author Sem
 * @email yapendalff@gmail.com
 * @url http://pendalff.ru
 */
class SMVC_Config_setter {
	
	protected $_config  = null;
	protected $_section = null;	
	
	public function __construct(SMVC_Config_File $config, $section ) {
		$this->_config = $config; 
		
		$this->_section = $section;
	}
	
	public function __get($var){
		$var = trim($var, Arr::$delimiter."* ");
		if(strpos($var,".")){
			return Arr::path($this->_config, $var);
		}
		else{
			return $this->_config[$var];
		}	
	}
	
	public function set($var,$value){
		$var = trim($var, Arr::$delimiter."* ");
		if(strpos($var,".")){
			$array = $this->_config;
			$this->_config = $this->set_array($var, $value, $array);
		}
		else{
		   $this->_config[$var] = $value;
		}	
	}
	public function set_array($var, $value, $array){
			if(strpos($var,Arr::$delimiter)){
				$keys = explode(Arr::$delimiter, $var);	
				$key = array_shift($keys);
				foreach ($array as $k=>$v) {
			        if (!isset($array[$key]) || !is_array($array[$key])) {
			            $array[$key] = array();
			        }
					if(is_array($array[$key])){
						$new_var  = implode(Arr::$delimiter, $keys);
						$array[$key] = $this->set_array($new_var, $value, $array[$key]);
					} 
		    	}
			}else{
					$array[$var] = $value;
			}
			return $array;
	}

	protected function is_exist($key){
		$key = trim($key, Arr::$delimiter."* ");
		if(strpos($key,".")){
			return Arr::path($this->_config, $key, 'NO_VALUE');
		}
		else{
			return isset($this->_config[$key]) ? $this->_config[$key] : 'NO_VALUE';
		}			
	}
	
	public function array_extend ( array $a , array $b ) {
     foreach ($b as $k=>$v) {
         if (is_array($v)) {
             if (!isset($a[$k])) {
                 $a[$k] = $v;
             } else {
                 $a[$k] = self::array_extend($a[$k], $v);
             }
         } else {
             $a[$k] = $v;
         }
     }

     return $a;
  }
}
?>