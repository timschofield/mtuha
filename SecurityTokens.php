<?php

include('includes/session.inc');
$Title = _('Maintain Security Tokens');

include('includes/header.inc');

if (isset($_GET['SelectedToken'])) {
	if ($_GET['Action'] == 'delete') {
		$Result = DB_query("SELECT script FROM scripts WHERE pagesecurity='" . $_GET['SelectedToken'] . "'", $db);
		if (DB_num_rows($Result) > 0) {
			prnMsg(_('This secuirty token is currently used by the following scripts and cannot be deleted'), 'error');
			echo '<table>
					<tr>';
			$i = 0;
			while ($ScriptRow = DB_fetch_array($Result)) {
				if ($i == 5) {
					$i = 0;
					echo '</tr>
							<tr>';
				}
				$i++;
				echo '<td>' . $ScriptRow['script'] . '</td>';
			}
			echo '</tr></table>';
		} else {
			$Result = DB_query("DELETE FROM securitytokens WHERE tokenid='" . $_GET['SelectedToken'] . "'", $db);
		}
	} else { // it must be an edit
		$sql = "SELECT tokenid,
					tokenname
				FROM securitytokens
				WHERE tokenid='" . $_GET['SelectedToken'] . "'";
		$Result = DB_query($sql, $db);
		$myrow = DB_fetch_array($Result, $db);
		$_POST['TokenID'] = $myrow['tokenid'];
		$_POST['TokenDescription'] = $myrow['tokenname'];
	}
}
if (!isset($_POST['TokenID'])) {
	$_POST['TokenID'] = '';
	$_POST['TokenDescription'] = '';
}

$InputError = 0;

if (isset($_POST['Submit']) or isset($_POST['Update'])) {
	if (!is_numeric($_POST['TokenID'])) {
		prnMsg(_('The token ID is expected to be a number. Please enter a number for the token ID'), 'error');
		$InputError = 1;
	}
	if ($_POST['TokenID'] > 999) {
		prnMsg(_('The token ID must be less than 1000'), 'error');
		$InputError = 1;
	}
	if (mb_strlen($_POST['TokenDescription']) == 0) {
		prnMsg(_('A token description must be entered'), 'error');
		$InputError = 1;
	}
}

if (isset($_POST['Submit'])) {

	$TestSQL = "SELECT tokenid FROM securitytokens WHERE tokenid='" . $_POST['TokenID'] . "'";
	$TestResult = DB_query($TestSQL, $db);
	if (DB_num_rows($TestResult) != 0) {
		prnMsg(_('This token ID has already been used. Please use a new one'), 'warn');
		$InputError = 1;
	}
	if ($InputError == 0) {
		$sql = "INSERT INTO securitytokens values('" . $_POST['TokenID'] . "', '" . $_POST['TokenDescription'] . "')";
		$Result = DB_query($sql, $db);
		$_POST['TokenID'] = '';
		$_POST['TokenDescription'] = '';
	}
}

if (isset($_POST['Update']) and $InputError == 0) {
	$sql = "UPDATE securitytokens
				SET tokenname='" . $_POST['TokenDescription'] . "'
			WHERE tokenid='" . $_POST['TokenID'] . "'";
	$Result = DB_query($sql, $db);
	$_POST['TokenDescription'] = '';
	$_POST['TokenID'] = '';
}

$FormName = 'SecurityToken1';
echo '<form onSubmit="return VerifyForm(this);" method="post" class="noPrint standard" action="' . htmlspecialchars($_SERVER['PHP_SELF'], ENT_QUOTES, 'UTF-8') . '" id="form">';
echo '<p class="page_title_text noPrint" ><img src="' . $RootPath . '/css/' . $Theme . '/images/security.png" title="' . _('Print') . '" alt="" />' . ' ' . $Title . '</p>';
echo '<input type="hidden" name="FormID" value="' . $_SESSION['FormID'] . '" />';

if (isset($_GET['Action']) and $_GET['Action'] == 'edit') {
	echo '<input type="hidden" name="TokenID" value="' . $_GET['SelectedToken'] . '" />';
	InputText($FormName,'TokenDescription', _('Description'), _('Description of the security token. This will also appear on the drop down menu.'), 50, 50, True, array(), _($_POST['TokenDescription']));
	SubmitButton( _('Update'), 'Update', 'submitbutton');

	echo '</form>';
} else {
	InputInteger($FormName,'TokenID', _('Token ID'), _('The ID for this security token.'), True, array(), '');
	InputText($FormName,'TokenDescription', _('Description'), _('Description of the security token. This will also appear on the drop down menu.'), 50, 50, False, array(), _($_POST['TokenDescription']));
	SubmitButton( _('Insert'), 'Submit', 'submitbutton');

	echo '</form>';
	echo '<table class="selection">';
	echo '<tr>
			<th>' . _('Icon') . '</th>
			<th>' . _('Token ID') . '</th>
			<th>' . _('Description') . '</th>
		</tr>';

	$sql = "SELECT tokenid,
					tokenname
				FROM securitytokens
				WHERE tokenid<1000
					AND tokenid>0
				ORDER BY tokenid";
	$Result = DB_query($sql, $db);

	while ($myrow = DB_fetch_array($Result, $db)) {
		echo '<tr>
				<td><img src="' . $RootPath . '/css/' . $Theme . '/images/' . $myrow['tokenid'] . '.png" /></td>
				<td>' . $myrow['tokenid'] . '</td>
				<td>' . htmlspecialchars($myrow['tokenname'], ENT_QUOTES, 'UTF-8') . '</td>
				<td><a href="' . htmlspecialchars($_SERVER['PHP_SELF'], ENT_QUOTES, 'UTF-8') . '?SelectedToken=' . $myrow['tokenid'] . '&amp;Action=edit">' . _('Edit') . '</a></td>
				<td><a href="' . htmlspecialchars($_SERVER['PHP_SELF'], ENT_QUOTES, 'UTF-8') . '?SelectedToken=' . $myrow['tokenid'] . '&amp;Action=delete" onclick="return MakeConfirm(\'' . _('Are you sure you wish to delete this security token?') . '\', \'Confirm Delete\', this);">' . _('Delete') . '</a></td>
			</tr>';
	}

	echo '</table>';
}

include('includes/footer.inc');
?>