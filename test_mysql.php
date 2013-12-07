<?php

class Mysql {

	private $m_mysqli;

	public function Mysql() {
		$this->open();
	}
		
	private function open() {
		$this->m_mysqli = mysqli_connect("localhost", "root", "super", "mediq");
		if ($m_mysqli->connect_error) {
			die('Connect Error (' . $m_mysqli->connect_errno . ') '
			. $m_mysqli->connect_error);
		} else {
			$this->mysqli = $mysqli;
		}

	}
	
	public function lees($p_query) {
		$res = mysqli_query($this->m_mysqli, $p_query);
		$rows = array();
		while ($row = mysqli_fetch_assoc($res)) {
			$rows[] = $row;
		}
		return $rows;
	}

}

?>
