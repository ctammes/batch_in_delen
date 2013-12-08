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
		$iTeller = 0;
		foreach($this->m_aIds as $iId) {
			$sSql = "SELECT timestamp from log where id=" . $iId;
			$results  = $this->m_database->lees($sSql);
			foreach ($results as $result) {
				fprintf($this->m_fp, "id: %-6s timestamp: %s\n",  $iId, $result['timestamp']);
			}
			$iTeller++;
		}
		fprintf($this->m_fp, "%-6s records verwerkt\n",  $iTeller);
	}
	
}

?>
