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
 *     subsequent requests.
 *
 * @package    Assets
 * @author     David Evans
 * @copyright  (c) 2009-2010 David Evans
 * @license    MIT-style
 */
class Controller_Assets extends Controller
{
	/**
	 * @var  string  Extension of requested file (automatically set)
	 */
	public $extension;
	
	/**
	 * @var  string  Content-Type header (if empty, set by file extension)
	 */
	public $content_type;

	/**
	 * @var  boolean Enable output caching
	 */
	public $cache = FALSE;
	
	/**
	 * @var  mixed   Cache type to use
	 */
	public $cache_config = Controller_Assets::IN_CACHE;

	public static $delimiter = '...'; //delimiter for filenames
	
	public static $folder_delimiter = "+";	
	
	// Cache types
	const IN_DOCROOT = 0x01;
	const IN_CACHE   = 0x02;
	
	/**
	 * @var  string  Config group to load
	 */
	public $config_group = 'default';
	public $gzip  = false;
	public $gzip_compression = 9;
	
	
	protected $url_path = NULL;	
	/**
	 * Apply config settings to the controller. Any config parameters
	 * which match existing controller properties will be set.
	 * 
	 * Accepts either the name of a config file as a string, or an
	 * array of config values.
	 * 
	 * @param   mixed    Config file or config array
	 * @return  void
	 */
	public function apply_config($config_group)
	{
		$config = (array)SMVC::config('assets')->$config_group;

		foreach($config as $key => $value)
		{
			if(property_exists($this, $key)) $this->$key = $value;
		}
		//$this->cache = $this->request === Request::instance() ? TRUE : FALSE;
	}
	
	/**
	 * Loads and applies config settings, determines the appropriate
	 * extension and content type and, if caching is enabled, attempts
	 * to serve the response from the cache.
	 * 
	 * @return  void
	 */
	public function before()
	{

		$this->apply_config($this->config_group);
		// Get the extension of the current request
		$this->extension = pathinfo($this->request->uri, PATHINFO_EXTENSION);
		
		// Set the content type if not yet set
		if (empty($this->content_type))
		{
			// Get mimetype based on extension
			$mimes = SMVC::config('mimes.'.strtolower($this->extension));

			// Use default if none found
			$this->content_type = (isset($mimes[0])) ? $mimes[0] : 'application/octet-stream';
		}
		
		// Bind Content-Type header to `$content_type` variable
		$this->request->headers['Content-Type'] =& $this->content_type;
		
		// If we're using SMVC's cache, attempt to serve the request
		// from the cache
		if ($this->cache && $this->cache_config === Controller_Assets::IN_CACHE)
		{	;
			$this->cache_load();
		}
	}
	
	/**
	 * Caches the response if caching is enabled and the request was
	 * successful.
	 * 
	 * @return  void
	 */
	public function after()
	{
		if($this->request->status===200){
			//gzip it?
			$this->request->response = $this->gzip($this->request->response);
		}
		if ($this->cache AND $this->request->status === 200)
		{
			switch ($this->cache_config)
			{
				case Controller_Assets::IN_CACHE:
					$this->cache_save();
					break;
					
				case Controller_Assets::IN_DOCROOT:
					$this->cache_in_docroot();
					break;
					
				default:
					throw new SMVC_Exception('Unknown cache_type :type',
						array('type' => $this->cache_config));
			}
		}

		return parent::after();
	}
	
	public function action_index(){
		$this->request->status = 403;
		exit();
	}
	public function action_js(){
		$this->request->status = 403;
		exit();
	}
	public function action_css(){
		$this->request->status = 403;
		exit();
	}

	public function action_glue(){
		$this->request->status = 403;
		exit();
	}	
	protected function gzip( $input ){
		if(!$this->gzip || !function_exists('gzcompress'))   
				return $input;

		$compress = ( strpos( $_SERVER["HTTP_ACCEPT_ENCODING"], "gzip" ) !== false );
		if($compress){	
//echo		headers_sent() ? 1 :2 ;	return $input;
//			$this->request->headers['Content-Encoding'] = 'gzip';
			return gzcompress($input, $this->gzip_compression);
			
		}

	}	
	
	/**
	 * If there is a cached response to the current request, uses
	 * `Request::send_file` to output it, otherwise does nothing.
	 * 
	 * Note that if `Request::send_file` is called, all execution will
	 * be halted.
	 * 
	 * @uses  Request::send_file
	 * @uses  Controller_Assets::cache_location
	 * 
	 * @return  void
	 */
	protected function cache_load()
	{
		$file = join('', $this->cache_location());

		if (file_exists($file))
		{
			if($this->gzip){
				$this->request->headers['Content-Encoding'] = 'gzip';
			}
			$this->request->send_file($file, '-', array
			(
				'inline'    => TRUE,
				'mime_type' => $this->request->headers['Content-Type']
			));
		}
	}
	
	/**
	 * Saves the current response in SMVC's cache directory.
	 * 
	 * We do this manually, rather than use `SMVC::cache` because we
	 * want to save the data as a raw string, rather than serializing
	 * it. This enables us to send the file more efficiently when
	 * serving subsequent requests.
	 * 
	 * @uses  Controller_Assets::cache_location
	 * 
	 * @return  void
	 */
	protected function cache_save()
	{
		list($dir, $file) = $this->cache_location();
		
		if ( ! is_dir($dir))
		{
			// Create the cache directory
			mkdir($dir, 0777, TRUE);

			// Set permissions (must be manually set to fix umask issues)
			chmod($dir, 0777);
		}
		
		file_put_contents($dir.$file, $this->request->response);
	}
	
	/**
	 * Gets the location of the corresponding cache file for the current
	 * request as an array of the form `(<dir>, <filename>)`
	 * 
	 * @return  array
	 */
	protected function cache_location()
	{
		$file = sha1("assets_".$_SERVER['REQUEST_URI'].$this->request->uri).'.txt';

		// Cache directories are split by keys to prevent filesystem overload
		$dir = SMVC::$cache_dir.DIRECTORY_SEPARATOR.$file[0].$file[1].DIRECTORY_SEPARATOR;
		
		return array($dir, $file);
	}
	
	/**
	 * Saves the contents of the current response into the path in
	 * `DOCROOT` which corresponds to the current URL. If the normal
	 * mod_rewrite settings are in use then, on the next request for
	 * this URL, Apache will serve up the cached file without invoking
	 * PHP. This is by far the most efficient means of serving cached
	 * content.
	 * 
	 * @return  void
	 */
	protected function cache_in_docroot()
	{
		// Translate URL into a pathname in DOCROOT
		$path = DOCROOT.DIRECTORY_SEPARATOR.ltrim($this->request->uri, '\\/');
		
		// Check that we're using URL rewriting
		if ( ! empty(SMVC::$index_file))
		{
			throw new SMVC_Exception('DocRoot caching only works with URL rewriting');
		}
		
		// If conditional URL rewriting is working properly then this
		// controller should only be invoked when there is no static
		// file matching the URL in the document root. The following
		// is just a paranoia check to make sure we don't overwrite
		// files in case of a server misconfiguration.
		if (file_exists($path))
		{
			throw new SMVC_Exception('File already exists at :path',
				array(':path' => $path));
		}
		
		if ( ! is_dir($dir = dirname($path)))
		{
			// Attempt to make, recursively, all required directories
			mkdir($dir, 0777, TRUE);
		
			// Set permissions (must be manually set to fix umask issues)
			chmod($dir, 0777);
		}
		
		file_put_contents($path, $this->request->response);
	}


	/**
	 * FIXME! Joomla hardcode for paths! 
	 * @param object $filename
	 * @return 
	 */
	public function get_url( $filename ){

			$this->url_path = URL::site()."/";
	}
	
	/**
	 * @param string $output
	 * @return 
	 */
	public function change_url( $output ){
		if(preg_match("/(url.*\()(.+)(jpg|gif|jpeg|png|svg)\)/i", $output)){

		$pattern     = array("/(url.*\()(.+)(jpg|gif|jpeg|png|svg)\)/i");
		$replacement = array("\${1} ".$this->url_path."\${2}\${3} )");
			return preg_replace($pattern, $replacement, $output);
		}
		
		return ($output);
	}	

}
