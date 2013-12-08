<?php
	// Script script can be used to bootstrap zend for command line scripts
	// It loads Zend autoloader and reads the proper configuration file
	 
	// Zorg dat de environment variabelen juist worden ingesteld, op basis van 
	// de apache2 configuratie files.
	
	
	//include_once( dirname( __FILE__ ) . '/../setenv_apache.php' ); 
	putenv("APPLICATION_NAME=herhaal");
	putenv("APPLICATION_ENV=chris");
	putenv("APPLICATION_GROUP=mediq");
		
	// Define path to application directory
	if( defined( 'GENERIC_APPLICATION_PATH' ) ) {
	    echo 'Al eerder gezet!!!!';
	}
    
	defined('GENERIC_APPLICATION_PATH')
	    || define('GENERIC_APPLICATION_PATH', realpath(dirname(__FILE__)) . '/');
	    
	// Define path to application directory
	defined('APPLICATION_PATH')
	    || define('APPLICATION_PATH', realpath(dirname(__FILE__)) . '/');

	// Define application environment
	if( !defined('APPLICATION_ENV') ) {
	    if( !getenv( 'APPLICATION_ENV' ) ) {
	    	throw new Exception( "Geen APPLICATION_ENV ingesteld" );
		}
	    define('APPLICATION_ENV', getenv('APPLICATION_ENV') );
	}

	echo "GENERIC_APPLICATION_PATH" . GENERIC_APPLICATION_PATH . "\n";
	echo "APPLICATION_PATH" . APPLICATION_PATH . "\n";
	echo "APPLICATION_ENV" . getenv('APPLICATION_ENV') . "\n";
	echo "realpath: " . realpath(dirname(__FILE__)) . "\n";
	
	// Ensure library/ is on include_path
	set_include_path(implode(PATH_SEPARATOR, array(
	    realpath(APPLICATION_PATH . '/../library'),
		realpath(APPLICATION_PATH . '/../library/model'),
		realpath(APPLICATION_PATH . '/../library/collections'),
		realpath(APPLICATION_PATH . '/../library/errorhandling'),
	    get_include_path(),
	)));

	// Retrieve the hostname in order to set the directories properly
	define( 'HHG_HOSTNAME', gethostname() );

	require_once 'Zend/Loader/Autoloader.php';
	$autoloader = Zend_Loader_Autoloader::getInstance();
	$autoloader->setFallbackAutoloader(true);
	
	// Make sure models can be loaded properly
	$modelAutoloader = new Zend_Loader_Autoloader_Resource( array(
		'basePath' =>APPLICATION_PATH,
		'namespace' => 'Application'
	));
	$modelAutoloader->addResourceType( 'models', 'models/', 'Model' );
	
	define('CLASSES_ROOT', APPLICATION_PATH.'/../library/' );
	
    
    /** Zend_Application */
    require_once 'Zend/Application.php';
    
    // Setup configuration files; by default only the application.ini
    $configFiles = array(
        'application_1.ini',
        'application_2.ini'
    );
    
    $configFiles = array_filter( $configFiles, 'file_exists' );
    
    // Create application, bootstrap, and run. Always use the autotest configuration options
    // See http://akrabat.com/zend-framework/local-config-files-and-zend_application/ for
    // information about loading multiple configuration files
    /*
    $application = new Zend_Application(
        APPLICATION_ENV,
        array( 'config' => $configFiles )
    );
    
    $application->bootstrap();
    */
	
	// Zorg dat de cron jobs een poos kunnen draaien
	ini_set('memory_limit', '250M') ;
	set_time_limit(0) ;                                         // No time limit
	  
?>
