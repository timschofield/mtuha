<?php

include('includes/session.inc');
include('includes/DefineFormClass.php');
$Title = _('Maintain StockTypes');

include('includes/header.inc');

if (isset($_POST['Submit'])) {
	$EditSQL = "SELECT COUNT(name) as total FROM stocktypes WHERE type='" . $_POST['StockType'] . "'";
	$EditResult = DB_query($EditSQL, $db);
	$EditRow = DB_fetch_array($EditResult);
	if ($EditRow['total'] == 0) {
		$SQL = "INSERT INTO stocktypes VALUES ( '" . $_POST['StockType'] . "',
												'" . $_POST['StockTypeName'] . "',
												'" . $_POST['IsPhysicalItem'] . "')";
	} else {
		$SQL = "UPDATE stocktypes SET name='" . $_POST['StockTypeName'] . "',
									physicalitem='" . $_POST['IsPhysicalItem'] . "'
								WHERE type='" . $_POST['StockType'] . "'";
	}
	$Result = DB_query($SQL, $db);
	unset($_POST['StockType']);
	unset($_POST['StockTypeName']);
	unset($_POST['IsPhysicalItem']);
}

if (isset($_GET['Delete'])) {
	$CheckSQL = "SELECT COUNT(categoryid) as total FROM stockcategory WHERE stocktype='" . $_GET['Delete'] . "'";
	$CheckResult = DB_query($CheckSQL, $db);
	$CheckRow = DB_fetch_array($CheckResult);
	if ($CheckRow['total'] == 0) {
		$DeleteSQL = "DELETE FROM stocktypes WHERE type='" . $_GET['Delete'] . "'";
		$DeleteResult = DB_query($DeleteSQL, $db);
	} else {
		prnMsg(_('There are stock categories that use this stock type, so it cannot be deleted'), 'warn');
	}
}

$Form = new Form('StockTypes1', _('Stock Type Maintenance'), 'maintenance');
if (isset($_GET['Edit'])) {
	$SQL = "SELECT name,
					physicalitem
				FROM stocktypes
				WHERE type='" . $_GET['Edit'] . "'";
	$Result = DB_query($SQL, $db);
	$MyRow = DB_fetch_array($Result);
	$_POST['StockType'] = $_GET['Edit'];
	$_POST['StockTypeName'] = $MyRow['name'];
	$_POST['IsPhysicalItem'] = $MyRow['physicalitem'];
	$Form->AddInputText('StockType', _('Stock Type Code'), _('Enter one character as an identifier for the stock type'), 2, 1, False, array(), True);
} else {
	$Form->AddInputText('StockType', _('Stock Type Code'), _('Enter one character as an identifier for the stock type'), 2, 1, True, array(), False);
}

$YesNo[0] = _('No');
$YesNo[1] = _('Yes');

$Form->AddInputText('StockTypeName', _('Stock Type Name'), _('A description for this stock type'), 30, 30, False, array(), False);
$Form->AddInputSelect('IsPhysicalItem', _('Are these items physical items?'), _('Are these items physical items?'), False, $YesNo, array());
$Form->AddInputSubmit( _('Enter Information'), 'Submit', 'submitbutton');
$Form->CloseForm();

$SQL = "SELECT type,
				name,
				physicalitem
			FROM stocktypes";
$Result = DB_query($SQL, $db);

echo '<table class="selection">
		<tr>
			<th>' . _('Type') . '</th>
			<th>' . _('Name') . '</th>
			<th>' . _('Physical Items') . '</th>
			<th colspan="2">&nbsp;</th>
		</tr>';

while ($MyRow = DB_fetch_array($Result)) {
	if ($MyRow['physicalitem'] == 1) {
		$PhysicalItem = _('Yes');
	} else {
		$PhysicalItem = _('No');
	}
	echo '<tr>
			<td>' . $MyRow['type'] . '</td>
			<td>' . $MyRow['name'] . '</td>
			<td style="text-align:center;">' . $PhysicalItem . '</td>
			<td><a class="editlink" href="' . htmlspecialchars($_SERVER['PHP_SELF'], ENT_QUOTES, 'UTF-8') . '?Edit=' . $MyRow['type'] . '">' . _('Edit') . '</a></td>
			<td><a class="deletelink" href="' . htmlspecialchars($_SERVER['PHP_SELF'], ENT_QUOTES, 'UTF-8') . '?Delete=' . $MyRow['type'] . '">' . _('Delete') . '</a></td>
		</tr>';
}

echo '</table>';
include('includes/footer.inc');
?>