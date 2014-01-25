<?php

global $db;
$PathPrefix = '../';
include('../config.php');

if (isset($SessionSavePath)) {
	session_save_path($SessionSavePath);
} //isset($SessionSavePath)

if (!isset($SysAdminEmail)) {
	$SysAdminEmail = '';
}

ini_set('session.gc_maxlifetime', $SessionLifeTime);

if (!ini_get('safe_mode')) {
	set_time_limit($MaximumExecutionTime);
	ini_set('max_execution_time', $MaximumExecutionTime);
} //!ini_get('safe_mode')
session_write_close(); //in case a previous session is not closed
session_start();

include($PathPrefix . 'includes/ConnectDB.inc');
include($PathPrefix . 'includes/DateFunctions.inc');

/* iterate through all elements of the $_POST array and DB_escape_string them
to limit possibility for SQL injection attacks and cross scripting attacks
*/

if (isset($_SESSION['DatabaseName'])) {
	foreach ($_POST as $PostVariableName => $PostVariableValue) {
		if (gettype($PostVariableValue) != 'array') {
			if (get_magic_quotes_gpc()) {
				$_POST['name'] = stripslashes($_POST['name']);
			} //get_magic_quotes_gpc()
			$_POST[$PostVariableName] = DB_escape_string($PostVariableValue);
		} else {
			foreach ($PostVariableValue as $PostArrayKey => $PostArrayValue) {
				if (get_magic_quotes_gpc()) {
					$PostVariableValue[$PostArrayKey] = stripslashes($value[$PostArrayKey]);
				}
				$PostVariableValue[$PostArrayKey] = DB_escape_string($PostArrayValue);
			} //$value as $key1 => $value1
		}
	} //$_POST as $key => $value

	/* iterate through all elements of the $_GET array and DB_escape_string them
	to limit possibility for SQL injection attacks and cross scripting attacks
	*/
	foreach ($_GET as $GetKey => $GetValue) {
		if (gettype($GetValue) != 'array') {
			$_GET[$GetKey] = DB_escape_string($GetValue);
		} //gettype($value) != 'array'
	} //$_GET as $key => $value
} else { //set SESSION['FormID'] before the a user has even logged in
	$_SESSION['FormID'] = sha1(uniqid(mt_rand(), true));
}

if ($_POST['mode'] == 'Insert') {
	foreach ($_POST as $field => $value) {
		if ($field != 'FormID' and $field != 'table' and $field != 'Submit' and $field != 'mode') {
			$Fields[] = $field;
			$Values[] = $value;
		}
	}
	$SQL = "INSERT INTO " . $_POST['table'] . " (" . implode(',', $Fields) . ") VALUES ('" . implode("','", $Values) . "')";
} else {
	$SQL = "UPDATE " . $_POST['table'] . "";
}

$Result = DB_query($SQL, $db);

?>