<?php

class Table {

	private $Headers;
	private $Name;
	private $SQL;
	private $NumberOfRows;
	private $ShowEditRecord;
	private $ShowDeleteRecord;

	function __construct($Name, $Class, $ShowEditRecord = True, $ShowDeleteRecord = True) {
		$this->Name = $Name;
		$this->ShowEditRecord = $ShowEditRecord;
		$this->ShowDeleteRecord = $ShowDeleteRecord;
		echo '<table class="' . $Class . '">';
	}

	private function DrawHeaders() {
		echo '<thead>
				<tr>';
		foreach($this->Headers as $Header) {
			echo '<th>' . $Header . '</th>';
		}
		echo '<th colspan="2">&nbsp;</th>';
		echo '</tr>
			</thead>';
	}

	private function DrawBody() {
		global $db;
		$Result = DB_Query($this->SQL, $db);
		$this->NumberOfRows = DB_num_rows($Result);
		echo '<tbody id="' . $this->Name . '">';
		while ($MyRow = DB_fetch_row($Result)) {
			echo '<tr>';
			for ($i=0; $i<sizeof($MyRow); $i++) {
				echo '<td>' . $MyRow[$i] . '</td>';
			}
			if ($this->ShowEditRecord) {
				echo '<td><a class="editlink" href="' . htmlspecialchars($_SERVER['PHP_SELF'], ENT_QUOTES, 'UTF-8') . '?Edit=' . $MyRow[0] . '">' . _('Edit') . '</a></td>';
			}
			if ($this->ShowDeleteRecord) {
				echo '<td><a class="deletelink" href="' . htmlspecialchars($_SERVER['PHP_SELF'], ENT_QUOTES, 'UTF-8') . '?Delete=' . $MyRow[0] . '">' . _('Delete') . '</a></td>';
			}
			echo '</tr>';
		}
		echo '</tbody>';
	}

	private function DrawFooter() {
		echo '</table>';
	}

	public function Draw() {
		$this->DrawHeaders();
		$this->DrawBody();
		$this->DrawFooter();
	}

	public function SetHeaders($Headers) {
		$this->Headers = $Headers;
	}

	public function SetSQL($SQL) {
		$this->SQL = $SQL;
	}

}

?>