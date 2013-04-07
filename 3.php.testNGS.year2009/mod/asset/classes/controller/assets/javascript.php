<?php defined('SYSPATH') or die('No direct script access.');
/**
 * Abstract controller class to facilitate the generation and caching
 * of non-HTML content
 * 
 * This controller performs three functions:
 * 
 *   * Sets the `$extension` property of the controller based on the
 *     extension of the request.
 *   * Sets the content-type of the response based on the `$extension`
 *     property.
 *   * Caches the response and serves up the cached content on
 *     subsequest requests.
 *
 * @package    Assets
 * @author     David Evans
 * @copyright  (c) 2009-2010 David Evans
 * @license    MIT-style
 */
class Controller_Assets_Javascript extends Controller_Assets
{
	/**
	 * @var  string  Config group to load
	 */
	public $config_group = 'javascript';
	
	/**
	 * @var  string  Content-Type header
	 */
	public $content_type = 'application/javascript';
	
	/**
	 * @var  string  Directory where JavaScript files are stored
	 */
	public $directory = 'javascript';
	
	/**
	 * @var  boolean Enables "minification" of JavaScript output
	 */
	public $compress = FALSE;
	
	/**
	 * @var  array    Minification library and settings to use
	 */
	public $compress_config = array();
	
	public $use_preprocessor = true;
	
	/**
	 * @var  array    JavaScript include paths 
	 */
	public $include_paths = array();
	
	
	public function action_process($path)
	{
		
		$path = str_replace(self::$folder_delimiter, "/", $path);

		// Search for file using cascading file system
		$filename = SMVC::find_file($this->directory, $path, false);

		if ( !$filename && $this->request===Request::instance())
		{
			$this->request->status = 404;
			throw new SMVC_Request_Exception('Unable to find JavaScript file: <tt>:path</tt>',
				array(':path' => $path));
		}
		else{
			if ( !$filename ) return '';
		}
	    $this->get_url( $filename );
		if($this->use_preprocessor){
			// Add all the 'javascript' directories in SMVC's include path
			// to the include_paths array
			foreach(SMVC::include_paths() as $path)
			{
				$this->include_paths[] = $path.$this->directory;
			}
			// Load file, along with all dependencies
			include_once SMVC::find_file('vendor', 'JavaScriptPreprocessor');
			$preprocessor = new JavaScriptPreprocessor($this->include_paths);		
			$output = $preprocessor->load($filename);
		
		}
		else{
			$output = file_get_contents($filename);
		}

		$output = $this->change_url($output);
		// Compress JavaScript, if desired
		if ($this->compress)
		{
			$output = $this->compress($output, $this->compress_config);
		}
		
		$this->request->response = $output;
	}
	
	
	public function compress($javascript, $config)
	{
		switch($config['type'])
		{
			case 'jsmin':
				include_once SMVC::find_file('vendor', 'JSMin');
				return JSMin::minify($javascript);
			case 'packer':
				include_once SMVC::find_file('vendor', 'JavaScriptPacker');
				$packer = new JavaScriptPacker($javascript, empty($config['level']) ? 'Normal' : $config['level']);
				return $packer->pack();
			default:
				throw new SMVC_Exception('Unknown JavaScript compression type :type',
					array(':type' => $config['type']));
		}
	}
}
