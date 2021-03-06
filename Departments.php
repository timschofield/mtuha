<?php

include('includes/session.inc');

$Title = _('Departments');

include('includes/header.inc');

if (isset($_GET['SelectedDepartmentID']))
	$SelectedDepartmentID = $_GET['SelectedDepartmentID'];
elseif (isset($_POST['SelectedDepartmentID']))
	$SelectedDepartmentID = $_POST['SelectedDepartmentID'];

if (isset($_POST['Submit'])) {

	//initialise no input errors assumed initially before we test

	$InputError = 0;

	/* actions to take once the user has clicked the submit button
	ie the page has called itself with some user input */

	//first off validate inputs sensible

	if (ContainsIllegalCharacters($_POST['DepartmentName'])) {
		$InputError = 1;
		prnMsg(_('The description of the department must not contain the character') . " '&amp;' " . _('or the character') . " '", 'error');
	}
	if (trim($_POST['DepartmentName']) == '') {
		$InputError = 1;
		prnMsg(_('The Name of the Department should not be empty'), 'error');
	}

	if (isset($_POST['SelectedDepartmentID']) and $_POST['SelectedDepartmentID'] != '' and $InputError != 1) {


		/*SelectedDepartmentID could also exist if submit had not been clicked this code would not run in this case cos submit is false of course  see the delete code below*/
		// Check the name does not clash
		$sql = "SELECT count(*) FROM departments
				WHERE departmentid <> '" . $SelectedDepartmentID . "'
				AND description " . LIKE . " '" . $_POST['DepartmentName'] . "'";
		$result = DB_query($sql, $db);
		$myrow = DB_fetch_row($result);
		if ($myrow[0] > 0) {
			$InputError = 1;
			prnMsg(_('This department name already exists.'), 'error');
		} else {
			// Get the old name and check that the record still exist neet to be very careful here

			$sql = "SELECT description
					FROM departments
					WHERE departmentid = '" . $SelectedDepartmentID . "'";
			$result = DB_query($sql, $db);
			if (DB_num_rows($result) != 0) {
				// This is probably the safest way there is
				$myrow = DB_fetch_array($result);
				$OldDepartmentName = $myrow['description'];
				$sql = array();
				$sql[] = "UPDATE departments
							SET description='" . $_POST['DepartmentName'] . "',
								authoriser='" . $_POST['Authoriser'] . "'
							WHERE description " . LIKE . " '" . $OldDepartmentName . "'";
			} else {
				$InputError = 1;
				prnMsg(_('The department does not exist.'), 'error');
			}
		}
		$msg = _('The department has been modified');
	} elseif ($InputError != 1) {
		/*SelectedDepartmentID is null cos no item selected on first time round so must be adding a record*/
		$sql = "SELECT count(*) FROM departments
				WHERE description " . LIKE . " '" . $_POST['DepartmentName'] . "'";
		$result = DB_query($sql, $db);
		$myrow = DB_fetch_row($result);
		if ($myrow[0] > 0) {
			$InputError = 1;
			prnMsg(_('There is already a department with the specified name.'), 'error');
		} else {
			$sql = "INSERT INTO departments (departmentid,
											 description,
											 authoriser )
					VALUES ('" . $_POST['DepartmentID'] . "',
							'" . $_POST['DepartmentName'] . "',
							'" . $_POST['Authoriser'] . "')";
		}
		$msg = _('The new department has been created');
	}

	if ($InputError != 1) {
		//run the SQL from either of the above possibilites
		if (is_array($sql)) {
			$result = DB_Txn_Begin($db);
			$ErrMsg = _('The department could not be inserted');
			$DbgMsg = _('The sql that failed was') . ':';
			foreach ($sql as $SQLStatement) {
				$result = DB_query($SQLStatement, $db, $ErrMsg, $DbgMsg, true);
				if (!$result) {
					$InputError = 1;
					break;
				}
			}
			if ($InputError != 1) {
				$result = DB_Txn_Commit($db);
			} else {
				$result = DB_Txn_Rollback($db);
			}
		} else {
			$result = DB_query($sql, $db);
		}
		prnMsg($msg, 'success');
		echo '<br />';
	}
	unset($SelectedDepartmentID);
	unset($_POST['SelectedDepartmentID']);
	unset($_POST['DepartmentName']);

} elseif (isset($_GET['delete'])) {
	//the link to delete a selected record was clicked instead of the submit button


	$sql = "SELECT description
			FROM departments
			WHERE departmentid = '" . $SelectedDepartmentID . "'";
	$result = DB_query($sql, $db);
	if (DB_num_rows($result) == 0) {
		prnMsg(_('You cannot delete this Department'), 'warn');
	} else {
		$myrow = DB_fetch_row($result);
		$OldDepartmentName = $myrow[0];
		$sql = "SELECT COUNT(*)
				FROM stockrequest INNER JOIN departments
				ON stockrequest.departmentid=departments.departmentid
				WHERE description " . LIKE . " '" . $OldDepartmentName . "'";
		$result = DB_query($sql, $db);
		$myrow = DB_fetch_row($result);
		if ($myrow[0] > 0) {
			prnMsg(_('You cannot delete this Department'), 'warn');
			echo '<br />' . _('There are') . ' ' . $myrow[0] . ' ' . _('There are items related to this department');
		} else {
			$sql = "DELETE FROM departments WHERE description " . LIKE . "'" . $OldDepartmentName . "'";
			$result = DB_query($sql, $db);
			prnMsg($OldDepartmentName . ' ' . _('The department has been removed') . '!', 'success');
		}
	} //end if account group used in GL accounts
	unset($SelectedDepartmentID);
	unset($_GET['SelectedDepartmentID']);
	unset($_GET['delete']);
	unset($_POST['SelectedDepartmentID']);
	unset($_POST['DepartmentID']);
	unset($_POST['DepartmentName']);
}

if (!isset($_GET['delete'])) {

	if (isset($SelectedDepartmentID)) {
		echo '<div class="toplink">
				<a href="' . htmlspecialchars($_SERVER['PHP_SELF'], ENT_QUOTES, 'UTF-8') . '">' . _('View all Departments') . '</a>
			</div>';
	}

	$FormName = 'Department1';
	echo '<form name="' . $FormName . '" onSubmit="return VerifyForm(this);" method="post" class="noPrint standard" action="' . htmlspecialchars($_SERVER['PHP_SELF'], ENT_QUOTES, 'UTF-8') . '">';
	echo '<input type="hidden" name="FormID" value="' . $_SESSION['FormID'] . '" />';
	echo '<p class="page_title_text noPrint" ><img src="' . $RootPath . '/css/' . $Theme . '/images/magnifier.png" title="' . _('Departments') . '" alt="" />' . ' ' . $Title . '</p>';

	if (isset($SelectedDepartmentID)) {
		//editing an existing section

		$sql = "SELECT departmentid,
						description,
						authoriser
				FROM departments
				WHERE departmentid='" . $SelectedDepartmentID . "'";

		$result = DB_query($sql, $db);
		if (DB_num_rows($result) == 0) {
			prnMsg(_('The selected departemnt could not be found.'), 'warn');
			unset($SelectedDepartmentID);
		} else {
			$myrow = DB_fetch_array($result);

			$_POST['DepartmentID'] = $myrow['departmentid'];
			$_POST['DepartmentName'] = $myrow['description'];
			$AuthoriserID = $myrow['authoriser'];

			echo '<input type="hidden" name="SelectedDepartmentID" value="' . $_POST['DepartmentID'] . '" />';
			Text('SelectedDepartmentID', $FormName, _('Department ID'), $_POST['DepartmentID']);
		}

	} else {
		$_POST['DepartmentName'] = '';
		$AuthoriserID = '';
		InputText($FormName, 'DepartmentID', _('Department ID'), _('Identifier to be used for this department.'), 20, 60, True, array());
	}
	InputText($FormName, 'DepartmentName', _('Department Name'), _('Name of the department.'), 50, 100, True, array(), $_POST['DepartmentName']);

	if (!isset($_POST['Authoriser'])) {
		$_POST['Authoriser'] = -1;
	}
	$UserSQL = "SELECT userid, realname FROM www_users";
	$UserResult = DB_query($UserSQL, $db);
	while ($myrow = DB_fetch_array($UserResult)) {
		$Users[$myrow['userid']] = $myrow['realname'];
	}
	Select($FormName, 'Authoriser', _('Authoriser'), _('Responsible person from this department who can authorise stock requests'), False, $Users, array(), $AuthoriserID);
	SubmitButton(_('Enter Information'), 'Submit', 'submitbutton');
	echo '</form>';

} //end if record deleted no point displaying form to add record

if (!isset($SelectedDepartmentID)) {

	$sql = "SELECT departmentid,
					description,
					www_users.realname
			FROM departments
			INNER JOIN www_users
				ON departments.authoriser=www_users.userid
			ORDER BY description";

	$ErrMsg = _('There are no departments created');
	$result = DB_query($sql, $db, $ErrMsg);

	echo '<table class="selection">
			<tr>
				<th>' . _('Department Name') . '</th>
				<th>' . _('Authoriser') . '</th>
			</tr>';

	$k = 0; //row colour counter
	while ($myrow = DB_fetch_array($result)) {

		if ($k == 1) {
			echo '<tr class="EvenTableRows">';
			$k = 0;
		} else {
			echo '<tr class="OddTableRows">';
			$k++;
		}

		echo '<td>' . $myrow['description'] . '</td>
				<td>' . $myrow['realname'] . '</td>
				<td><a href="' . htmlspecialchars($_SERVER['PHP_SELF'], ENT_QUOTES, 'UTF-8') . '?SelectedDepartmentID=' . $myrow['departmentid'] . '">' . _('Edit') . '</a></td>
				<td><a href="' . htmlspecialchars($_SERVER['PHP_SELF'], ENT_QUOTES, 'UTF-8') . '?SelectedDepartmentID=' . $myrow['departmentid'] . '&amp;delete=1" onclick="return MakeConfirm(\'' . _('Are you sure you wish to delete this department?') . '\', \'Confirm Delete\', this);">' . _('Delete') . '</a></td>
			</tr>';

	} //END WHILE LIST LOOP
	echo '</table>';
} //end of ifs and buts!

include('includes/footer.inc');
?>