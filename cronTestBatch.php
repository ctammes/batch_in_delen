<?php
	// Dit script test het uitvoeren van crons als child process

	// dit vervangt application.ini
	define('DB', 'mediq');
	define('DB_USER', 'root');
	define('DB_PASSWORD', 'super');
	define('DB_HOST', 'localhost');

	try {
		include_once( dirname( __FILE__ ) . '/cliBootstrap.php' );

		error_reporting( E_ALL );
		ini_set( 'display_errors', 'On' );
	
        // Application_Model_Log::setLogname( 'BATCH' );
        echo 'Start ' . basename(__FILE__, 'php') . "\n";

        // Start database connection voor id's
		$database = Zend_Db::factory('Pdo_Mysql', array(
		    'host'     => DB_HOST,
		    'username' => DB_USER,
		    'password' => DB_PASSWORD,
		    'dbname'   => DB
		)  );
		
		$database->setFetchMode( Zend_Db::FETCH_ASSOC );

		// Haal alle id's op en zet ze in array
		$aLogIds = $database->fetchAll( 'select id from log' );
		$aJobIds = array();
		foreach( $aLogIds as $aLogId ) {
			$aJobIds[] = $aLogId[ 'id' ];
		}
		
		// database vanaf hier niet meer nodig
		$database->closeConnection();
		
		// Zijn er resultaten?
		$iAantal = count( $aJobIds );
		if( $iAantal == 0 ) {
			echo "Test batch is aangeroepen, maar er zijn geen records om te verwerken.\n" ;
			exit(2);
		} else {
			echo "Er zijn " . $iAantal . " jobids\n";
		}
			
		// Splitsen van array met id's in array met arrays voor ieder child process
		$aAlleIds = array();
		$iStart = 0;
		$iLimiet = 2000;		// aantal records per child process
		while ($iStart * $iLimiet < $iAantal) {
			$aAlleIds[] = array_slice($aJobIds, $iStart * $iLimiet, $iLimiet);
			$iStart++;
		}
/*
		$max = ceil($iAantal / $iLimiet);		// aantal aan te maken taken
		for ($iTeller = 0; $iTeller < $max; $iTeller++) {
			$aAlleIds[] = array_slice($aJobIds, $iStart * $iLimiet, $iLimiet);
			$iStart++;
		}
		
		// Alternatief
		// Ophalen van totaal aantal records
		$results = $database->fetchAll( 'SELECT count(*) aantal from log' );
		foreach ($results as $result) {
			$iAantal = $result["aantal"];
			echo "Aantal logrecords: " . $iAantal . "\n";
		}

		// Splitsen van array met id's in arrays voor ieder child process
		$aAlleIds = array();
		$iLimiet = 2000;		// aantal records per child process
		$max = ceil($iAantal / $iLimiet);		// aantal aan te maken taken
		for ($iTeller = 0; $iTeller < $max; $iTeller++) {
			$aIds = array();
			$iRecord = $iTeller * $iLimiet;
			$results = $database->fetchAll( 'SELECT id from log limit '.$iRecord.',' . $iLimiet );
			foreach ($results as $result) {
				$aIds[] = $result['id'];
			}
			$aAlleIds[] = $aIds;
		}
*/
		echo "Aantal taken (aIds): " . count($aAlleIds) . "\n";

		// alle taken achtereensvolgens starten
		$i=1;
		foreach($aAlleIds as $aIds) {
			index($i++, $aIds);
			// echo $i++ . ' - ' . count($aIds) ."\n"; 
		}
		
		echo "Parent is gereed; er zijn " . ($i-1) . " taken gestart\n";

		
		// We halen de klant- en medicatiegegevens rechtstreeks van de webservice op
		// $datasource = new Cron_DataSource_Soap();
		// $datasource->setInitialStatus( 'PROFILE_UPDATE' );
		
		// Draai de dagelijkse klant batch, per klant
		// $batch = new Cron_Batch_Test( $database, $aIds );
		// $batch->run();
		
		// Draai ook de postprocessing
		// $batch = new Cron_Batch_DagelijkseKlantPostProcessing( $datasource, $database );
		// $batch->run();

        echo 'Einde ' . basename(__FILE__, 'php') . "\n";

		// Return a statuscode 0
		exit(0);
	} catch( Exception $e ) {
		// If the database has been loaded, log the error. Otherwise, an error has occurred before 
		// the settings have been loaded 
		if( defined( 'DB' ) ) {
			echo "Er is een fout opgetreden in de dagelijkse klant batch: " . $e->getMessage() . "\n";
		}
		file_put_contents('php://stderr', $e->getMessage() . "\n" . $e->getTraceAsString() . "\n");
		
		exit(1);
	}
	
	function index($i, $aIds) 
	{ 
			// Do some initial processing 
	
			echo("Child process " . $i . " start ...\n");
	
			// Switch over to daemon mode. 
	
			if ($pid = pcntl_fork()) 
				return;     // Parent 
	
			// ob_end_clean(); // Discard the output buffer and close
	
			fclose(STDIN);  // Close all of the standard 
			fclose(STDOUT); // file descriptors as we 
			fclose(STDERR); // are running as a daemon. 
	
			register_shutdown_function('shutdown'); 
	
			// Make the current process a session leader
			if (posix_setsid() < 0) 
				return; 
	
			// Waar is dit goed voor??
			//if ($pid = pcntl_fork()) 
			//    return;     // Parent 
	
			// Now running as a daemon. This process will even survive 
			// an apachectl stop. 
	
			// ???
			// sleep(10);
			
			// Start database connection
			$database = Zend_Db::factory('Pdo_Mysql', array(
				'host'     => DB_HOST,
				'username' => DB_USER,
				'password' => DB_PASSWORD,
				'dbname'   => DB
			)  );
			$database->setFetchMode( Zend_Db::FETCH_ASSOC );
	
			// maak en schrijf logfile
			$file = "/tmp/sdf123_" . $i; 
			if (file_exists($file)) {
				unlink($file);
			}
			$fp = fopen($file, "w"); 
			if ($fp === false) {
				exit(1);
			}
			fprintf($fp, "PID = %s - start %s\n", posix_getpid(), date("H:i:s"));
	
			// voer de taak uit
			$batch = new Cron_Batch_Test( $database, $aIds, $fp );
			$batch->run();

			// sluit logfile
			fprintf($fp, "einde %s\n", date("H:i:s"));
			fclose($fp); 
	
			// even wachten, dan kun je nog iets zien met 'ps ux'
			sleep(10);
			
			return; 
	} 
	
	// einde van het childprocess (ipv. exit() )
	// zie 'kill -l' voor de signalen
	function shutdown() { 
		// verwijdert dit proces
		posix_kill(posix_getpid(), SIGHUP);
		// of deze - voorkomt cleanup??
		// posix_kill(posix_getpid(), SIGKILL); 
	} 



