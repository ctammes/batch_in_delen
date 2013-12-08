<?php

class Cron_Batch_Test {

	private $m_aIds = array();
	private $m_database = null;
	private $m_fp = null;
	
	public function Cron_Batch_Test($database, $aIds, $fp) {
		$this->m_aIds = $aIds;
		$this->m_database = $database;
		$this->m_fp = $fp;
	}
	
	public function run() {
		fprintf($this->m_fp, count($this->m_aIds) . " run() gestart\n");
		$iTeller = 0;
		foreach($this->m_aIds as $iId) {
			$results = $this->m_database->fetchAll( 'SELECT timestamp from log where id=' . $iId );
			foreach ($results as $result) {
				fprintf($this->m_fp, "id: %-6s timestamp: %s\n",  $iId, $result['timestamp']);
			}
			$iTeller++;
		}
		fprintf($this->m_fp, "%-6s records verwerkt\n",  $iTeller);
	}
	
}

?>
