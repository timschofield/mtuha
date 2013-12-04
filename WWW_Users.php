<?php

if (isset($_POST['UserID']) and isset($_POST['ID'])) {
	if ($_POST['UserID'] == $_POST['ID']) {
		$_POST['Language'] = $_POST['UserLanguage'];
	}
}
include('includes/session.inc');

include('includes/MainMenuLinksArray.php');

$PDFLanguages = array(
	_('Latin Western Languages'),
	_('Eastern European Russian Japanese Korean Vietnamese Hebrew Arabic Thai'),
	_('Chinese'),
	_('Free Serif')
);

$YesNo[0] = _('No');
$YesNo[1] = _('Yes');

$Title = _('User Maintenance');
/* KwaMoja manual links before header.inc */
$ViewTopic = 'GettingStarted';
$BookMark = 'UserMaintenance';
include('includes/header.inc');
include('includes/SQL_CommonFunctions.inc');

// Make an array of the security roles
$sql = "SELECT secroleid,
				secrolename
		FROM securityroles
		ORDER BY secrolename";

$Sec_Result = DB_query($sql, $db);
$SecurityRoles = array();
// Now load it into an a ray using Key/Value pairs
while ($Sec_row = DB_fetch_row($Sec_Result)) {
	$SecurityRoles[$Sec_row[0]] = $Sec_row[1];
}
DB_free_result($Sec_Result);

if (isset($_GET['SelectedUser'])) {
	$SelectedUser = $_GET['SelectedUser'];
} elseif (isset($_POST['SelectedUser'])) {
	$SelectedUser = $_POST['SelectedUser'];
}

if (isset($_POST['Submit'])) {

	//initialise no input errors assumed initially before we test
	$InputError = 0;

	/* actions to take once the user has clicked the submit button
	ie the page has called itself with some user input */

	//first off validate inputs sensible
	if (mb_strlen($_POST['UserID']) < 3) {
		$InputError = 1;
		prnMsg(_('The user ID entered must be at least 3 characters long'), 'error');
	} elseif (ContainsIllegalCharacters($_POST['UserID'])) {
		$InputError = 1;
		prnMsg(_('User names cannot contain any of the following characters') . " - ' &amp; + \" \\ " . _('or a space'), 'error');
	} elseif (mb_strlen($_POST['Password']) < 5) {
		if (!$SelectedUser) {
			$InputError = 1;
			prnMsg(_('The password entered must be at least 5 characters long'), 'error');
		}
	} elseif (mb_strstr($_POST['Password'], $_POST['UserID']) != False) {
		$InputError = 1;
		prnMsg(_('The password cannot contain the user id'), 'error');
	} elseif ((mb_strlen($_POST['Cust']) > 0) AND (mb_strlen($_POST['BranchCode']) == 0)) {
		$InputError = 1;
		prnMsg(_('If you enter a Customer Code you must also enter a Branch Code valid for this Customer'), 'error');
	}
	//comment out except for demo!  Do not want anyone modifying demo user.
	/*
	elseif ($_POST['UserID'] == 'admin') {
	prnMsg(_('The demonstration user called demo cannot be modified.'),'error');
	$InputError = 1;
	}
	*/
	if (!isset($SelectedUser)) {
		/* check to ensure the user id is not already entered */
		$result = DB_query("SELECT userid FROM www_users WHERE userid='" . $_POST['UserID'] . "'", $db);
		if (DB_num_rows($result) == 1) {
			$InputError = 1;
			prnMsg(_('The user ID') . ' ' . $_POST['UserID'] . ' ' . _('already exists and cannot be used again'), 'error');
		}
	}

	if ((mb_strlen($_POST['BranchCode']) > 0) and ($InputError != 1)) {
		// check that the entered branch is valid for the customer code
		$sql = "SELECT custbranch.debtorno
				FROM custbranch
				WHERE custbranch.debtorno='" . $_POST['Cust'] . "'
				AND custbranch.branchcode='" . $_POST['BranchCode'] . "'";

		$ErrMsg = _('The check on validity of the customer code and branch failed because');
		$DbgMsg = _('The SQL that was used to check the customer code and branch was');
		$result = DB_query($sql, $db, $ErrMsg, $DbgMsg);

		if (DB_num_rows($result) == 0) {
			prnMsg(_('The entered Branch Code is not valid for the entered Customer Code'), 'error');
			$InputError = 1;
		}
	}

	/* Make a comma separated list of modules allowed ready to update the database*/
	$i = 0;
	$ModulesAllowed = '';
	while ($i < count($ModuleList)) {
		$FormVbl = 'Module_' . $i;
		$ModulesAllowed .= $_POST[($FormVbl)] . ',';
		$i++;
	}
	$_POST['ModulesAllowed'] = $ModulesAllowed;

	if (isset($SelectedUser) and $InputError != 1) {

		/*SelectedUser could also exist if submit had not been clicked this code would not run in this case cos submit is false of course  see the delete code below*/

		if (!isset($_POST['Cust']) or $_POST['Cust'] == NULL or $_POST['Cust'] == '') {

			$_POST['Cust'] = '';
			$_POST['BranchCode'] = '';
		}
		$UpdatePassword = '';
		if ($_POST['Password'] != '') {
			$UpdatePassword = "password='" . CryptPass($_POST['Password']) . "',";
		}

		if ($SelectedUser == $_SESSION['UserID']) {
			$_SESSION['ScreenFontSize'] = $_POST['FontSize'];
		}
		$sql = "UPDATE www_users SET realname='" . $_POST['RealName'] . "',
						customerid='" . $_POST['Cust'] . "',
						phone='" . $_POST['Phone'] . "',
						email='" . $_POST['Email'] . "',
						" . $UpdatePassword . "
						branchcode='" . $_POST['BranchCode'] . "',
						supplierid='" . $_POST['SupplierID'] . "',
						salesman='" . $_POST['Salesman'] . "',
						pagesize='" . $_POST['PageSize'] . "',
						fullaccess='" . $_POST['Access'] . "',
						cancreatetender='" . $_POST['CanCreateTender'] . "',
						theme='" . $_POST['Theme'] . "',
						language ='" . $_POST['UserLanguage'] . "',
						defaultlocation='" . $_POST['DefaultLocation'] . "',
						restrictlocations='" . $_POST['RestrictLocations'] . "',
						modulesallowed='" . $ModulesAllowed . "',
						blocked='" . $_POST['Blocked'] . "',
						pdflanguage='" . $_POST['PDFLanguage'] . "',
						department='" . $_POST['Department'] . "',
						fontsize='" . $_POST['FontSize'] . "'
					WHERE userid = '" . $SelectedUser . "'";

		prnMsg(_('The selected user record has been updated'), 'success');
	} elseif ($InputError != 1) {

		$sql = "INSERT INTO www_users (userid,
						realname,
						customerid,
						branchcode,
						supplierid,
						salesman,
						password,
						phone,
						email,
						pagesize,
						fullaccess,
						cancreatetender,
						defaultlocation,
						restrictlocations,
						modulesallowed,
						displayrecordsmax,
						theme,
						language,
						pdflanguage,
						department,
						fontsize)
					VALUES ('" . $_POST['UserID'] . "',
						'" . $_POST['RealName'] . "',
						'" . $_POST['Cust'] . "',
						'" . $_POST['BranchCode'] . "',
						'" . $_POST['SupplierID'] . "',
						'" . $_POST['Salesman'] . "',
						'" . CryptPass($_POST['Password']) . "',
						'" . $_POST['Phone'] . "',
						'" . $_POST['Email'] . "',
						'" . $_POST['PageSize'] . "',
						'" . $_POST['Access'] . "',
						'" . $_POST['CanCreateTender'] . "',
						'" . $_POST['DefaultLocation'] . "',
						'" . $_POST['RestrictLocations'] . "',
						'" . $ModulesAllowed . "',
						'" . $_SESSION['DefaultDisplayRecordsMax'] . "',
						'" . $_POST['Theme'] . "',
						'" . $_POST['UserLanguage'] . "',
						'" . $_POST['PDFLanguage'] . "',
						'" . $_POST['Department'] . "',
						'" . $_POST['FontSize'] . "')";
		prnMsg(_('A new user record has been inserted'), 'success');
	}
	if ($_SESSION['UserID'] == $_POST['UserID']) {
		$_SESSION['RestrictLocations'] = $_POST['RestrictLocations'];
	}
	if ($InputError != 1) {
		//run the SQL from either of the above possibilites
		$ErrMsg = _('The user alterations could not be processed because');
		$DbgMsg = _('The SQL that was used to update the user and failed was');
		$result = DB_query($sql, $db, $ErrMsg, $DbgMsg);

		unset($_POST['UserID']);
		unset($_POST['RealName']);
		unset($_POST['Cust']);
		unset($_POST['BranchCode']);
		unset($_POST['SupplierID']);
		unset($_POST['Salesman']);
		unset($_POST['Phone']);
		unset($_POST['Email']);
		unset($_POST['Password']);
		unset($_POST['PageSize']);
		unset($_POST['Access']);
		unset($_POST['CanCreateTender']);
		unset($_POST['DefaultLocation']);
		unset($_POST['ModulesAllowed']);
		unset($_POST['Blocked']);
		unset($_POST['Theme']);
		unset($_POST['UserLanguage']);
		unset($_POST['PDFLanguage']);
		unset($_POST['Department']);
		unset($_POST['FontSize']);
		unset($SelectedUser);
	}

} elseif (isset($_GET['delete'])) {
	//the link to delete a selected record was clicked instead of the submit button

	// comment out except for demo!  Do not want anyopne deleting demo user.


	if ($AllowDemoMode AND $SelectedUser == 'admin') {
		prnMsg(_('The demonstration user called demo cannot be deleted'), 'error');
	} else {

		$sql = "SELECT userid FROM audittrail where userid='" . $SelectedUser . "'";
		$result = DB_query($sql, $db);
		if (DB_num_rows($result) != 0) {
			prnMsg(_('Cannot delete user as entries already exist in the audit trail'), 'warn');
		} else {

			$sql = "DELETE FROM www_users WHERE userid='" . $SelectedUser . "'";
			$ErrMsg = _('The User could not be deleted because');
			$result = DB_query($sql, $db, $ErrMsg);
			prnMsg(_('User Deleted'), 'info');
		}
		unset($SelectedUser);
	}

}

if (!isset($SelectedUser)) {

	/* If its the first time the page has been displayed with no parameters then none of the above are true and the list of Users will be displayed with links to delete or edit each. These will call the same page again and allow update/input or deletion of the records*/

	$sql = "SELECT userid,
					realname,
					phone,
					email,
					customerid,
					branchcode,
					supplierid,
					salesman,
					lastvisitdate,
					fullaccess,
					cancreatetender,
					pagesize,
					theme,
					language,
					fontsize
				FROM www_users";
	$result = DB_query($sql, $db);

	echo '<table class="selection">
			<tr>
				<th>' . _('User Login') . '</th>
				<th>' . _('Full Name') . '</th>
				<th>' . _('Telephone') . '</th>
				<th>' . _('Email') . '</th>
				<th>' . _('Customer Code') . '</th>
				<th>' . _('Branch Code') . '</th>
				<th>' . _('Supplier Code') . '</th>
				<th>' . _('Salesperson') . '</th>
				<th>' . _('Last Visit') . '</th>
				<th>' . _('Security Role') . '</th>
				<th>' . _('Report Size') . '</th>
				<th>' . _('Theme') . '</th>
				<th>' . _('Language') . '</th>
				<th>' . _('Screen Font Size') . '</th>
			</tr>';

	$k = 0; //row colour counter

	while ($myrow = DB_fetch_array($result)) {
		if ($k == 1) {
			echo '<tr class="EvenTableRows">';
			$k = 0;
		} else {
			echo '<tr class="OddTableRows">';
			$k = 1;
		}

		if ($myrow[8] == '') {
			$LastVisitDate = Date($_SESSION['DefaultDateFormat']);
		} else {
			$LastVisitDate = ConvertSQLDate($myrow[8]);
		}

		/*The SecurityHeadings array is defined in config.php */

		switch ($myrow['fontsize']) {

			case 0:
				$FontSize = _('Small');
				break;
			case 1:
				$FontSize = _('Medium');
				break;
			case 2:
				$FontSize = _('Large');
				break;
			default:
				$FontSize = _('Medium');
		}

		printf('<td>%s</td>
				<td>%s</td>
				<td>%s</td>
				<td>%s</td>
				<td>%s</td>
				<td>%s</td>
				<td>%s</td>
				<td>%s</td>
				<td>%s</td>
				<td>%s</td>
				<td>%s</td>
				<td>%s</td>
				<td>%s</td>
				<td>%s</td>
				<td><a href="%s&amp;SelectedUser=%s">' . _('Edit') . '</a></td>
				<td><a href="%s&amp;SelectedUser=%s&amp;delete=1" onclick="return MakeConfirm(\'' . _('Are you sure you wish to delete this user?') . '\', \'Confirm Delete\', this);">' . _('Delete') . '</a></td>
			</tr>', $myrow['userid'], $myrow['realname'], $myrow['phone'], $myrow['email'], $myrow['customerid'], $myrow['branchcode'], $myrow['supplierid'], $myrow['salesman'], $LastVisitDate, $SecurityRoles[($myrow['fullaccess'])], $myrow['pagesize'], $myrow['theme'], $LanguagesArray[$myrow['language']]['LanguageName'], $FontSize, htmlspecialchars($_SERVER['PHP_SELF'], ENT_QUOTES, 'UTF-8') . '?', $myrow['userid'], htmlspecialchars($_SERVER['PHP_SELF'], ENT_QUOTES, 'UTF-8') . '?', $myrow['userid']);

	} //END WHILE LIST LOOP
	echo '</table><br />';
} //end of ifs and buts!


if (isset($SelectedUser)) {
	echo '<div class="toplink"><a class="toplink" href="' . htmlspecialchars($_SERVER['PHP_SELF'], ENT_QUOTES, 'UTF-8') . '">' . _('Review Existing Users') . '</a></div><br />';
}

$FormName = 'User1';
echo '<form name="' . $FormName . '" onSubmit="return VerifyForm(this);" method="post" class="noPrint standard" action="' . htmlspecialchars($_SERVER['PHP_SELF'], ENT_QUOTES, 'UTF-8') . '">';
echo '<input type="hidden" name="FormID" value="' . $_SESSION['FormID'] . '" />';

echo '<p class="page_title_text noPrint" ><img src="' . $RootPath . '/css/' . $Theme . '/images/group_add.png" title="' . _('Search') . '" alt="" />' . ' ' . $Title . '</p>';

if (isset($SelectedUser)) {
	//editing an existing User

	$sql = "SELECT userid,
					realname,
					phone,
					email,
					customerid,
					password,
					branchcode,
					supplierid,
					salesman,
					pagesize,
					fullaccess,
					cancreatetender,
					defaultlocation,
					restrictlocations,
					modulesallowed,
					blocked,
					theme,
					language,
					pdflanguage,
					department,
					fontsize
				FROM www_users
				WHERE userid='" . $SelectedUser . "'";

	$result = DB_query($sql, $db);
	$myrow = DB_fetch_array($result);

	$_POST['UserID'] = $myrow['userid'];
	$_POST['RealName'] = $myrow['realname'];
	$_POST['Phone'] = $myrow['phone'];
	$_POST['Email'] = $myrow['email'];
	$_POST['Cust'] = $myrow['customerid'];
	$_POST['BranchCode'] = $myrow['branchcode'];
	$_POST['SupplierID'] = $myrow['supplierid'];
	$_POST['Salesman'] = $myrow['salesman'];
	$_POST['PageSize'] = $myrow['pagesize'];
	$_POST['Access'] = $myrow['fullaccess'];
	$_POST['CanCreateTender'] = $myrow['cancreatetender'];
	$_POST['DefaultLocation'] = $myrow['defaultlocation'];
	$_POST['RestrictLocations'] = $myrow['restrictlocations'];
	$_POST['ModulesAllowed'] = $myrow['modulesallowed'];
	$_POST['Theme'] = $myrow['theme'];
	$_POST['UserLanguage'] = $myrow['language'];
	$_POST['Blocked'] = $myrow['blocked'];
	$_POST['PDFLanguage'] = $myrow['pdflanguage'];
	$_POST['Department'] = $myrow['department'];
	$_POST['FontSize'] = $myrow['fontsize'];

	echo '<input type="hidden" name="SelectedUser" value="' . $SelectedUser . '" />';
	echo '<input type="hidden" name="UserID" value="' . $_POST['UserID'] . '" />';
	echo '<input type="hidden" name="ModulesAllowed" value="' . $_POST['ModulesAllowed'] . '" />';

	Text('UserID', $FormName, _('User code'), $_POST['UserID']);

} else { //end of if $SelectedUser only do the else when a new record is being entered

	InputText($FormName, 'UserID', _('User Login'), _('The ID that this user will ue to login.'), 20, 20, True, array());
	/*set the default modules to show to all
	this had trapped a few people previously*/
	$i = 0;
	if (!isset($_POST['ModulesAllowed'])) {
		$_POST['ModulesAllowed'] = '';
	}
	foreach ($ModuleList as $ModuleName) {
		if ($i > 0) {
			$_POST['ModulesAllowed'] .= ',';
		}
		$_POST['ModulesAllowed'] .= '1';
		$i++;
	}
}

if (!isset($_POST['Password'])) {
	$_POST['Password'] = '';
}
if (!isset($_POST['RealName'])) {
	$_POST['RealName'] = '';
}
if (!isset($_POST['Phone'])) {
	$_POST['Phone'] = '';
}
if (!isset($_POST['Email'])) {
	$_POST['Email'] = '';
}
InputPassword($FormName, 'Password', _('Password'), _('The password that this user will use to login.'), False, array(), $_POST['Password']);
InputText($FormName, 'RealName', _('Full Name'), _('The users full name..'), 35, 35, True, array(), $_POST['RealName']);
InputTelephone($FormName, 'Phone', _('Telephone No'), _('The telephone number to use to cantact this user..'), False, array(), $_POST['Phone']);
InputEmail($FormName, 'Email', _('Email Address'), _('The email address to use to cantact this user..'), False, array(), $_POST['Email']);

if (!isset($_POST['Access'])) {
	$_POST['Access'] = '';
}
Select($FormName, 'Access', _('Security Role'), _('The security role for this user. This defines what functionality the user can use.'), False, $SecurityRoles, array(), $_POST['Access']);

if (!isset($_POST['CanCreateTender'])) {
	$_POST['CanCreateTender'] = -1;
}
Select($FormName, 'CanCreateTender', _('User Can Create Tenders'), _('Does the user have authority to create tenders.'), False, $YesNo, array(), $_POST['CanCreateTender']);

$sql = "SELECT loccode, locationname FROM locations";
$result = DB_query($sql, $db);
while ($myrow = DB_fetch_array($result)) {
	$Locations[$myrow['loccode']] = $myrow['locationname'];
}
if (!isset($_POST['DefaultLocation'])) {
	$_POST['DefaultLocation'] = '';
}
Select($FormName, 'DefaultLocation', _('Default Location'), _('The default location for this user..'), False, $Locations, array(), $_POST['DefaultLocation']);

if (!isset($_POST['RestrictLocations'])) {
	$_POST['RestrictLocations'] = -1;
}
Select($FormName, 'RestrictLocations', _('Restrict to just this location'), _('Can the user use other locations, or just their default one.'), False, $YesNo, array(), $_POST['RestrictLocations']);

if (!isset($_POST['Cust'])) {
	$_POST['Cust'] = '';
}
InputText($FormName, 'Cust', _('Customer Code'), _('The customer code, if this is a customer only login.'), 10, 10, False, array(), $_POST['Cust']);

if (!isset($_POST['BranchCode'])) {
	$_POST['BranchCode'] = '';
}
InputText($FormName, 'BranchCode', _('Branch Code'), _('The branch code, if this is a customer only login.'), 10, 10, False, array(), $_POST['BranchCode']);

if (!isset($_POST['SupplierID'])) {
	$_POST['SupplierID'] = '';
}
InputText($FormName, 'SupplierID', _('Supplier Code'), _('The supplier code, if this is a supplier only login.'), 10, 10, False, array(), $_POST['SupplierID']);

$sql = "SELECT salesmancode, salesmanname FROM salesman WHERE current = 1 ORDER BY salesmanname";
$result = DB_query($sql, $db);
while ($myrow = DB_fetch_array($result)) {
	$SalesMen[$myrow['salesmancode']] = $myrow['salesmanname'];
}
if (!isset($_POST['Salesman'])) {
	$_POST['Salesman'] = '';
}
Select($FormName, 'Salesman', _('Restrict to Sales Person'), _('If this is a sales person only login, choose the sales person here.'), False, $SalesMen, array(), $_POST['Salesman']);

$PaperSizes['A4'] = 'A4';
$PaperSizes['A4_Landscape'] = 'A4_Landscape';
$PaperSizes['A3'] = 'A3';
$PaperSizes['A3_Landscape'] = 'A3_Landscape';
$PaperSizes['Letter'] = 'Letter';
$PaperSizes['Letter_Landscape'] = 'Letter_Landscape';
$PaperSizes['Legal'] = 'Legal';
$PaperSizes['Legal_Landscape'] = 'Legal_Landscape';
if (!isset($_POST['PageSize'])) {
	$_POST['PageSize'] = '';
}
Select($FormName, 'PageSize', _('Reports Page Size'), _('The page size used in reports.'), False, $PaperSizes, array(), $_POST['PageSize']);

$Themes = scandir('css/');
foreach ($Themes as $ThemeName) {
	if (is_dir('css/' . $ThemeName) and $ThemeName != '.' and $ThemeName != '..' and $ThemeName != '.svn') {
		$ThemeNames[$ThemeName] = $ThemeName;
	}
}
if (!isset($_POST['Theme'])) {
	$_POST['Theme'] = '';
}
Select($FormName, 'Theme', _('Theme'), _('Theme to be used for this user.'), False, $ThemeNames, array(), $_POST['Theme']);

foreach ($LanguagesArray as $LanguageEntry => $LanguageName) {
	$Languages[$LanguageEntry] = $LanguageName['LanguageName'];
}
if (!isset($_POST['UserLanguage'])) {
	$_POST['UserLanguage'] = $_SESSION['Language'];
}
Select($FormName, 'UserLanguage', _('Language'), _('The language the user will see the interface in.'), False, $Languages, array(), $_POST['UserLanguage']);

/*Make an array out of the comma separated list of modules allowed*/
$ModulesAllowed = explode(',', $_POST['ModulesAllowed']);

$i = 0;
foreach ($ModuleList as $ModuleName) {
	Select($FormName, 'Module_' . $i, _('Display') . ' ' . $ModuleName . ' ' . _('module'), _('Can the user view this module.'), False, $YesNo, array(), $ModulesAllowed[$i]);
	$i++;
}
if (!isset($_POST['PDFLanguage'])) {
	$_POST['PDFLanguage'] = -1;
}
Select($FormName, 'PDFLanguage', _('PDF Language Support'), _('The language/character set to be udsed for PDF reports.'), False, $PDFLanguages, array(), $_POST['PDFLanguage']);

/* Allowed Department for Internal Requests */
if (!isset($_POST['Department'])) {
	$_POST['Department'] = -1;
}
$sql = "SELECT departmentid,
			description
		FROM departments
		ORDER BY description";

$result = DB_query($sql, $db);
while ($myrow = DB_fetch_array($result)) {
	$Departments[$myrow['departmentid']] = $myrow['description'];
}
Select($FormName, 'Department', _('Allowed Department for Internal Requests'), _('Allowed Department for Internal Requests. Leave empty if all departments.'), False, $Departments, array(), $_POST['Department']);

/* Account status */
$AccountStatus[0] = _('Open');
$AccountStatus[1] = _('Blocked');
if (!isset($_POST['Blocked'])) {
	$_POST['Blocked'] = -1;
}
Select($FormName, 'Blocked', _('Account Status'), _('Is this account still in use, or is it blocked.'), False, $AccountStatus, array(), $_POST['Blocked']);

/* Screen Font Size */
$FontSizes[0] = _('Small');
$FontSizes[1] = _('Medium');
$FontSizes[2] = _('Large');
if (!isset($_POST['FontSize'])) {
	$_POST['FontSize'] = 0;
}
Select($FormName, 'FontSize', _('Screen Font Size'), _('The size of the screen font that will be used any time this user logs in.'), False, $FontSizes, array(), $_POST['FontSize']);
SubmitButton( _('Enter Information'), 'Submit', 'submitbutton');

echo '</form>';

include('includes/footer.inc');
?>