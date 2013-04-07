<?php defined('SYSPATH') or die('No direct script access.');
/**
 * 
 */
class Controller_config extends Controller_Site{
	
	public function action_index(){ 

		if($this->request->param('install', null)){
			$ret = true;
		}
		else{
			auth::login_role();
			$ret = auth::has_perm('config', 'edit', auth::userid());	
		}
		if($ret){	
			$i =(array) $this->_configs;
			$errors = array();
			if (Arr::get($_REQUEST, 'hidden') == 'form_sent')
		     {
		     
		     		$keys = array_keys($i);
		            $i = Arr::extract($_REQUEST, $keys, NULL);
		            
		            $valid = Validate::factory($i)
		                        ->filter('title','trim')
		                        ->filter('meta_keywords','trim')
		                        ->filter('meta_description','trim')
		                        ->filter('host','trim')
		                        ->filter('dbname','trim')
		                        ->filter('username','trim')
								->filter('password','trim')
		                        ->filter('title','htmlspecialchars')
		                        ->filter('meta_keywords','htmlspecialchars')
		                        ->filter('meta_description','htmlspecialchars')

		                        ->rules('title',array('not_empty'=>NULL,'max_length'=>array(200)))
		                        ->rules('meta_keywords',array('not_empty'=>NULL))
                  				->rules('meta_description',array('not_empty'=>NULL))
		                        ->rules('host',array('not_empty'=>NULL,'alpha_dash'=>array()))
		                        ->rules('dbname',array('not_empty'=>NULL,'alpha_dash'=>array()))
		                        ->rules('username',array('not_empty'=>NULL,'alpha_dash'=>array()))
		                        ->rules('password',array('not_empty'=>NULL,'alpha_dash'=>array()))
		                        ->rules('topic_per_page',array('not_empty'=>NULL,'digit'=>array()))
		                        ->rules('post_per_page',array('not_empty'=>NULL,'digit'=>array()));

			
		            if ( ! $valid->check())
		            {
		                $errors = $valid->errors();    
		            }
					else{

						$filename = APPPATH.'config'.DIRECTORY_SEPARATOR."forum".SMVC::FILE_EXTENTION;
						$writer = new SMVC_Config_Writer(
												  array('config'   => $i,
				                                  	'filename' => $filename,
												  	'ExclusiveLock'=>true
												  ));
						try{
							$writer->write();	
							$this->write_install();		
							$this->request->redirect("/");
						}
						catch ( SMVC_Config_Exception $e){
							$r = chmod($filename, 0777);
							if (!$r) 
								throw new SMVC_Exception ( $e->getMessage() );
							else{
								$writer->write();
								$this->write_install();
								$this->request->redirect("/");
							}
						}						
					}
		     }
			 
			$view = View::factory('conf')->set('input',$i)->set('errors',$errors);
			
			$this->page->content = $view;

		}
		else{
			throw new SMVC_Exception403('Недостаточно прав.');
		}			
	}
	private function write_install(){
		if(!file_exists(APPPATH.'installed')) {
			$result = file_put_contents(APPPATH."installed",'1');		
	
	        if ($result === false) {
	            throw new SMVC_Config_Exception('Could not write to file "' . $this->_filename . '"');
	        }
		}
	}
}

