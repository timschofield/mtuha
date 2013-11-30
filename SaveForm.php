<?php
//$PageSecurity = $SystemAdministrator;
$PageSecurity = 15;
include('includes/session.inc');
foreach ($_POST as $Field=>$State) {
	if ($State == 'on') {
		$State = 1;
	} else {
		$State = 0;
	}
	if (substr($Field, 0, 8) == 'required') {
		$sql = "UPDATE forms SET required='" . $State . "'
				WHERE fieldname='" . substr($Field, 8) . "'
					AND formname='" . $_POST['FormName'] . "'";
		$result = DB_query($sql, $db);
	} elseif (substr($Field, 0, 4) == 'view') {
		$sql = "UPDATE forms SET visible='" . $State . "'
				WHERE fieldname='" . substr($Field, 4) . "'
					AND formname='" . $_POST['FormName'] . "'";
		$result = DB_query($sql, $db);
	}
}

?>