<?php
  
   /************************************************************************
    * REQUIRED
    * 
    * Access Key ID and Secret Acess Key ID, obtained from:
    * http://mws.amazon.com
    ***********************************************************************/
     define('ACCESS_KEY_ID', 'AKIAISU25TYRYXALNFCA');
     define('SECRET_ACCESS_KEY', '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');  

	/************************************************************************
    * REQUIRED
    * 
    * All MWS requests must contain a User-Agent header. The application
    * name and version defined below are used in creating this value.
    ***********************************************************************/
    define('APPLICATION_NAME', 'amazon fullfillment service');
    define('APPLICATION_VERSION', '2.0');

   /************************************************************************
    * REQUIRED
    * 
    * All MWS requests must contain the seller's seller ID.
    ***********************************************************************/
    define ('SELLER_ID', 'A23M9O2MZ80ZFM');
	define ('MARKETPLACE_ID', 'ATVPDKIKX0DER');
   /************************************************************************
    * REQUIRED
    * 
    * All MWS requests must contain the MWS endpoint URL,
    * please set appropiate domain name for the country you wish.
    *
    * US: mws.amazonservices.com
    * UK: mws.amazonservices.co.uk
    * Germany: mws.amazonservices.de
    * France: mws.amazonservices.fr
    * Japan: mws.amazonservices.jp
    * China: mws.amazonservices.com.cn
    * Italy: mws.amazonservices.it
    ***********************************************************************/
    define ('MWS_ENDPOINT_URL', 'https://mws.amazonservices.com/FulfillmentOutboundShipment/2010-10-01/');

   /************************************************************************ 
    * OPTIONAL ON SOME INSTALLATIONS
    *
    * Set include path to root of library, relative to Samples directory.
    * Only needed when running library from local directory.
    * If library is installed in PHP include path, this is not needed
    ***********************************************************************/   
    set_include_path(LIBPATH);    
    
   /************************************************************************ 
    * OPTIONAL ON SOME INSTALLATIONS  
    * 
    * Autoload function is reponsible for loading classes of the library on demand
    * 
    * NOTE: Only one __autoload function is allowed by PHP per each PHP installation,
    * and this function may need to be replaced with individual require_once statements
    * in case where other framework that define an __autoload already loaded.
    * 
    * However, since this library follow common naming convention for PHP classes it
    * may be possible to simply re-use an autoload mechanism defined by other frameworks
    * (provided library is installed in the PHP include path), and so classes may just 
    * be loaded even when this function is removed
    ***********************************************************************/   
     function __autoload($className){
     	$filePath = str_replace('_', DIRECTORY_SEPARATOR, $className) . '.php';
     	$includePath = LIBPATH . DIRECTORY_SEPARATOR . $filePath;
        if(file_exists($includePath)){
            require_once $includePath;
            return;
        }
    }
    
    if (function_exists('__autoload')) {
    	spl_autoload_register('__autoload');
    }