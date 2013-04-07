<?php
/**
 * ClusterWebServiceAction implements an  custom  action that provides Web services.
 *
 */
class ClusterWebServiceAction extends CWebServiceAction
{
	/**
	 * Creates a {@link ClusterWebService} instance.
	 * You may override this method to customize the created instance.
	 * @param mixed $provider the web service provider class name or object
	 * @param string $wsdlUrl the URL for WSDL.
	 * @param string $serviceUrl the URL for the Web service.
	 * @return ClusterWebService the Web service instance
	 */
	protected function createWebService($provider,$wsdlUrl,$serviceUrl)
	{
		return new ClusterWebService($provider,$wsdlUrl,$serviceUrl);
	}
}