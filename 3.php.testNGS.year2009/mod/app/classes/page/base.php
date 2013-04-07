<?php defined('SYSPATH') or die('No direct script access.');
/**
 * @author Sem
 * @email yapendalff@gmail.com
 * @url http://pendalff.ru
 */
abstract class Page_Base{
	public static $instance;	
	
	public static $global_css = array( 
										'all'    => array(),
										'single' => array(),
										'code'   => array(),
									  );
	public static $global_js = array( 
										'all'    => array(),
										'single' => array(),
										'code'   => array(),
									  );	
									  
	public $css     		 = array( 
										'all'    => array(),
										'single' => array(),
										'code'   => array(),
									  );	
	public $js     		 = array( 
										'all'    => array(),
										'single' => array(),
										'code'   => array(),
									  );	

	public   $title         	= "";
	public   $meta_keywords 	= "";
	public   $meta_description  = "";
	public   $content 			= "";
	
	
	protected $request 			= NULL;
	protected $base_url			= NULL;
	
	protected function __construct ( Request $req ){ 
		$this->request = $req;
		$this->base_url = URL::site();
		
		$config = SMVC_Config::instance()->load('forum');
	    $this->title         	= $config['title'];
		$this->meta_keywords 	= $config['meta_keywords'];
		$this->meta_description = $config['meta_description'];
		self::$instance = $this;
	}
	
    /**
     * Add global JS
     * @param string $source          path or string code
     * @param string $type [optional] all|single|code
     * @return 
     */
	public static function add_global_JS( $source , $type = 'all' ){

		if( key_exists( $type, self::$global_js)){
			$source32 = substr($source,0,32);
			$key = base64_encode($source32);
			self::$global_js[$type][$key]=$source;

		}
			return self::$instance;
	}
	
	/**
	 * Add JS from object. Если это фоновый запрос - добавляются в глобальные.
	 * @param object $source
	 * @param object $type [optional]
	 * @return 
	 */
	public function add_JS( $source , $type = 'all' ){

			return self::add_global_JS($source, $type);

/*		if( key_exists( $type, $this->js)){
			$source32 = substr($source,0,32);
			$key = base64_encode($source32);
			$this->js[$type][$key]=$source;
			return true;
		}	*/	
	}
		
    /**
     * Add global CSS
     * @param string $source          path or string code
     * @param string $type [optional] all|single|code
     * @return 
     */
	public static function add_global_CSS( $source , $type = 'all' ){
		if( key_exists( $type, self::$global_css)){
			$source32 = substr($source,0,32);
			$key = base64_encode($source32);
			self::$global_css[$type][$key]=$source;
		}			
			return self::$instance;
	}
	
	/**
	 * Add CSS from object
	 * @param object $source
	 * @param object $type [optional]
	 * @return 
	 */
	public function add_CSS( $source , $type = 'all' ){
//		if( $this->request !== Request::instance()){
		   return self::add_global_CSS($source, $type);
//		}
/*		if( key_exists( $type, $this->css)){
			$source32 = substr($source,0,32);
			$key = base64_encode($source32);
			$this->css[$type][$key]=$source;
			return true;
		}		*/
	}
	
	public function get_CSS(){
		$css = Arr::merge( self::$global_css, $this->css);
		return self::$global_css;
	}
	
	public function get_JS(){
		//$js = Arr::merge( self::$global_js, $this->js );

		return self::$global_js;
	}

	public function get_paths( $type = 'js' ){
		$answer = array();
		
		$name = 'get_'.strtoupper($type);
		if( method_exists( $this, $name) ){
			$arr = $this->$name();
		} 
		else{
			throw new SMVC_Exception( $name.' - unknown type on class Page' );
			return NULL;
		}
		$answer['single'] = array();
		foreach( $arr['single'] AS $source ){
			$answer['single'][] = $this->base_url."assets/{$type}/".$source;
		}

		$answer['all'] = array();
		foreach( $arr['all']   AS $source ){
			$answer['all'][]    = $source;
		}		
		$answer['code']     =  implode("\n" , array_values( $arr['code'] ));
	
		return $answer;
	}	
	
	public function render_css(){
		$arr = $this->get_paths('css');

		$view=View::factory('page/elements/css')->bind('src',$path);
		$out = "";
		foreach( $arr AS $key => $type ){
			if( is_array($type)){
				if($key=='all')	{
					$path = $this->base_url."assets/cssglue/".implode(Controller_Assets::$delimiter, $type);
					$out .= $view->render();
				}
				else{
					foreach ( $type AS $path ){
						$out .= $view->render();
					}					
				}
			}
		}
		
		$view=View::factory('page/elements/css')->set('code',$arr['code']);
		$out .= $view->render();
		return $out;
	}
	
	public function render_js(){

		$arr  = $this->get_paths();
		$view = View::factory('page/elements/script')->bind('src',$path);
			
		$out = "";		
		foreach( $arr AS $key => $type ){

			if( is_array($type) && count($type) > 0){
			
				if($key=='all')	{
					$path = $this->base_url."assets/jsglue/".implode(Controller_Assets::$delimiter, $type);
					$out .= $view->render();
				}
				else{
					foreach ( $type AS $path ){
						$out .= $view->render();
					}					
				}
			}
		}
		$view=View::factory('page/elements/script')->set('code',$arr['code']);
		$out .= $view->render();
		
		return $out;		
	}
}
?>