<?php

include('includes/session.inc');

$SearchSQL = "SELECT pid,
					name_first,
					name_middle,
					name_last,
					date_birth,
					sex,
					phone_1_nr
				FROM person
				WHERE CONCAT(CONCAT(name_first,name_middle),name_last)  LIKE '%" . $_POST['NameKeywords'] . "%'
					AND (phone_1_nr LIKE '%" . $_POST['PhoneKeywords'] . "%' OR phone_1_nr is NULL)
					AND pid LIKE '%" . $_POST['PIDKeywords'] . "%'
				ORDER BY pid
				LIMIT " . $_SESSION['DisplayRecordsMax'];
$SearchResult = DB_query($SearchSQL, $db);

while ($SearchRow = DB_fetch_array($SearchResult)) {
	if ($SearchRow['sex'] == 'm') {
		$Sex = '<img src="css/aguapop/images/man.png" title="Male patient" />';
	} else {
		$Sex = '<img src="css/aguapop/images/female.png" title="Female patient" />';
	}
	echo '<tr>
			<td>' . $SearchRow['pid'] . '</td>
			<td>' . $Sex . '</td>
			<td>' . $SearchRow['name_first'] . ' ' . $SearchRow['name_middle'] . ' ' . $SearchRow['name_last'] . '</td>
			<td>' . ConvertSQLDate($SearchRow['date_birth']) . '</td>
			<td>' . $SearchRow['phone_1_nr'] . '</td>
			<td><img src="css/aguapop/images/action.png" class="ClickImage" onclick="ShowPersonMenu(\'' . $SearchRow['pid'] . '\', \'' . $SearchRow['name_first'] . ' ' . $SearchRow['name_middle'] . ' ' . $SearchRow['name_last'] . '\', event)" title="Click for actions relating to this person" /></td>
		</tr>';
}

?>