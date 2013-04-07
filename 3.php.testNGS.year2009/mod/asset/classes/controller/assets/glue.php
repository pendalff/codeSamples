<?php defined('SYSPATH') or die('No direct script access.');
/**
 * @author Sem
 * @email yapendalff@gmail.com
 * @url http://pendalff.ru
 */
class Controller_Assets_Glue extends Controller_Assets
{

	public $config_group = 'glue';
	public function action_process()
	{
		$files = $this->request->param('file', false);
		
		$files = str_replace(self::$folder_delimiter, "/", $files);

		$files = explode(self::$delimiter, $files);

		$type = $this->request->param('type');
		
		if( $type == null || !(bool)$files ){
			$this->request->status = 404;
			throw new SMVC_Request_Exception('Undefined type OR files to process');			
		}

		switch ($type) {
			case 'js':
				$out = $this->proccess_js( $files );
			break;
			case 'css':
				$out = $this->proccess_css( $files );
			break;			
		}
		$this->request->response = $out;
	}		
	
	protected function proccess_js( array $files ){
		$out = '';

		foreach ($files as $file) {
			$out .=Request::factory( 'assets/js/'.$file )->execute()."\n";
		}

		return $out;
	}
	
	protected function proccess_css( array $files ){
		$out = ''; 
		foreach ($files as $file) {
			$out .= Request::factory( 'assets/css/'.$file )->execute()."\n";
		}
		return $out;		
	}
}
?>