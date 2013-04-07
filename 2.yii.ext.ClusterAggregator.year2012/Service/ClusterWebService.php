<?php
/**
 * ClusterWebService encapsulates SoapServer and provides a WSDL-based web service.
 */
class ClusterWebService extends CWebService
{
	/**
	 * Generates the WSDL as defined by the provider.
	 * The cached version may be used if the WSDL is found valid in cache.
	 * @return string the generated WSDL
	 * @see wsdlCacheDuration
	 */
	public function generateWsdl()
	{
		$providerClass=is_object($this->provider) ? get_class($this->provider) : Yii::import($this->provider,true);
		if($this->wsdlCacheDuration>0 && $this->cacheID!==false && ($cache=Yii::app()->getComponent($this->cacheID))!==null)
		{
			$key='Yii.CWebService.'.$providerClass.$this->serviceUrl.$this->encoding;
			if(($wsdl=$cache->get($key))!==false)
				return $wsdl;
		}

    $providerClassArray = is_object($this->provider) && $this->provider instanceof IClusterAggregatorProviders
        ?
          $this->provider->getWSDLProviders() : array( $providerClass );

    $generator=new ClusterWsdlGenerator;
		$wsdl=$generator->generateWsdl( $providerClassArray,$this->serviceUrl,$this->encoding);

		if(isset($key))
			$cache->set($key,$wsdl,$this->wsdlCacheDuration);
		return $wsdl;
	}

	/**
	 * Handles the web service request.
	 */
	public function run()
	{
		header('Content-Type: text/xml;charset='.$this->encoding);
		if(YII_DEBUG)
			ini_set("soap.wsdl_cache_enabled",0);
		$server=new SoapServer($this->wsdlUrl,$this->getOptions());
		Yii::app()->attachEventHandler('onError',array($this,'handleError'));
		try
		{
			if($this->persistence!==null)
				$server->setPersistence($this->persistence);
			if(is_string($this->provider))
				$provider=Yii::createComponent($this->provider);
			else
				$provider=$this->provider;

			if(method_exists($server,'setObject'))
				$server->setObject($provider);
			else
				$server->setClass('CSoapObjectWrapper',$provider);

			if($provider instanceof IWebServiceProvider)
			{
				if($provider->beforeWebMethod($this))
				{
					$server->handle();
					$provider->afterWebMethod($this);
				}
			}
			else
				$server->handle();
		}
		catch(Exception $e)
		{
			if($e->getCode()!==self::SOAP_ERROR) // non-PHP error
			{
				// only log for non-PHP-error case because application's error handler already logs it
				// php <5.2 doesn't support string conversion auto-magically
				Yii::log($e->__toString(),CLogger::LEVEL_ERROR,'application');
			}
			$message=$e->getMessage();
			if(YII_DEBUG)
				$message.=' ('.$e->getFile().':'.$e->getLine().")\n".$e->getTraceAsString();

			// We need to end application explicitly because of
			// http://bugs.php.net/bug.php?id=49513
			Yii::app()->onEndRequest(new CEvent($this));
			$server->fault(get_class($e),$message);
			exit(1);
		}
	}
}
