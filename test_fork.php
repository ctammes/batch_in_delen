<?php 

// zie ook: http://php.net/manual/en/function.pcntl-fork.php

ini_set('display_errors',1); 
ini_set('error_reporting', E_ALL ^ E_NOTICE);

require_once "test_mysql.php";
require_once "test_verwerk_task.php";

$mysql = new Mysql();
$sSql = "SELECT count(*) aantal from log";
$results = $mysql->lees($sSql);
foreach ($results as $result) {
	$iAantal = $result["aantal"];
	echo $iAantal . "\n";
}

$sSql = "SELECT id from log limit 0,1";
$results  = $mysql->lees($sSql);
foreach ($results as $result) {
	echo $result['id'] . "\n";
}

$sSql = "SELECT timestamp from log where id=9544";
$results  = $mysql->lees($sSql);
foreach ($results as $result) {
	printf("timestamp: %s\n", $result['timestamp']);
}

$iLimiet = 60;
for ($iTeller = 0; $iTeller < 10; $iTeller++) {
	$iRecord = $iTeller * $iLimiet;
	$sSql = 'SELECT id from log limit '.$iRecord.',1';
	$results  = $mysql->lees($sSql);
	foreach ($results as $result) {
		echo $iRecord + 1 . ': ' . $result['id'] . "\n";
	}
}

$aAlleIds = array();
for ($iTeller = 0; $iTeller < 10; $iTeller++) {
	$aIds = array();
	$iRecord = $iTeller * $iLimiet;
	$sSql = 'SELECT id from log limit '.$iRecord.',' . $iLimiet;
	$results  = $mysql->lees($sSql);
	foreach ($results as $result) {
		$aIds[] = $result['id'];
	}
	$aAlleIds[] = $aIds;
}

echo "aantal aIds: " . count($aAlleIds) . "\n";

index(1, $aAlleIds[0]);
index(2, $aAlleIds[1]);
index(3, $aAlleIds[2]);

function index($i, $aIds) 
{ 
        // Do some initial processing 

        echo("Hello World: " . $i . "\n"); 

        // Switch over to daemon mode. 

        if ($pid = pcntl_fork()) 
            return;     // Parent 

        ob_end_clean(); // Discard the output buffer and close 

        fclose(STDIN);  // Close all of the standard 
        fclose(STDOUT); // file descriptors as we 
        fclose(STDERR); // are running as a daemon. 

        register_shutdown_function('shutdown'); 

        if (posix_setsid() < 0) 
            return; 

        if ($pid = pcntl_fork()) 
            return;     // Parent 

        // Now running as a daemon. This process will even survive 
        // an apachectl stop. 

        // vertraging tussen iedere taak?
        // lijkt niet te werken
        sleep(60);
        
        // open database connectie
        $mysql = new Mysql();

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
        $task = new Verwerk_Task($mysql, $aIds, $fp);
        $task->verwerk();

        // sluit logfile
        fprintf($fp, "einde %s\n", date("H:i:s"));
        fclose($fp); 

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


