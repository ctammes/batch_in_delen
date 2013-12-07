<?php

class Verwerk_Task {

	private $m_aIds;
	private $m_database;
	private $m_fp;
	
	public function Verwerk_Task($database, $aIds, $fp) {
		$this->m_aIds = $aIds;
		$this->m_database = $database;
		$this->m_fp = $fp;
	}
	
	public function verwerk() {
		foreach($this->m_aIds as $iId) {
			$sSql = "SELECT timestamp from log where id=" . $iId;
			$results  = $this->m_database->lees($sSql);
			foreach ($results as $result) {
				fprintf($this->m_fp, "timestamp: %s\n", $result['timestamp']);
				// sleep(1);
			}
		}
	}
	
}

?>
