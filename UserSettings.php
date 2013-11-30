<?php

include('includes/session.inc');
$Title = _('User Settings');
include('includes/header.inc');

$PDFLanguages = array(
	_('Latin Western Languages - Times'),
	_('Eastern European Russian Japanese Korean Hebrew Arabic Thai'),
	_('Chinese'),
	_('Free Serif')
);

foreach ($LanguagesArray as $LanguageEntry => $LanguageName) {
	$Languages[$LanguageEntry] = $LanguageName['LanguageName'];
}

if (isset($_POST['Modify'])) {
	// no input errors assumed initially before we test
	$InputError = 0;

	/* actions to take once the user has clicked the submit button
	ie the page has called itself with some user input */

	//first off validate inputs sensible
	if ($_POST['DisplayRecordsMax'] <= 0) {
		$InputError = 1;
		prnMsg(_('The Maximum Number of Records on Display entered must not be negative') . '. ' . _('0 will default to system setting'), 'error');
	}

	//!!!for the demo only - enable this check so password is not changed

	if ($AllowDemoMode and $_POST['Password'] != '') {
		$InputError = 1;
		prnMsg(_('Cannot change password in the demo or others would be locked out!'), 'warn');
	}

	$UpdatePassword = 'N';

	if ($_POST['PasswordCheck'] != '') {
		if (mb_strlen($_POST['Password']) < 5) {
			$InputError = 1;
			prnMsg(_('The password entered must be at least 5 characters long'), 'error');
		} elseif (mb_strstr($_POST['Password'], $_SESSION['UserID']) != False) {
			$InputError = 1;
			prnMsg(_('The password cannot contain the user id'), 'error');
		}
		if ($_POST['Password'] != $_POST['PasswordCheck']) {
			$InputError = 1;
			prnMsg(_('The password and password confirmation fields entered do not match'), 'error');
		} else {
			$UpdatePassword = 'Y';
		}
	}


	if ($InputError != 1) {
		// no errors
		if ($UpdatePassword != 'Y') {
			$sql = "UPDATE www_users
				SET displayrecordsmax='" . $_POST['DisplayRecordsMax'] . "',
					theme='" . $_POST['Theme'] . "',
					language='" . $_POST['Language'] . "',
					email='" . $_POST['email'] . "',
					pdflanguage='" . $_POST['PDFLanguage'] . "',
					fontsize='" . $_POST['FontSize'] . "'
				WHERE userid = '" . $_SESSION['UserID'] . "'";

			$ErrMsg = _('The user alterations could not be processed because');
			$DbgMsg = _('The SQL that was used to update the user and failed was');

			$result = DB_query($sql, $db, $ErrMsg, $DbgMsg);

			prnMsg(_('The user settings have been updated') . '. ' . _('Be sure to remember your password for the next time you login'), 'success');
		} else {
			$sql = "UPDATE www_users
				SET displayrecordsmax='" . $_POST['DisplayRecordsMax'] . "',
					theme='" . $_POST['Theme'] . "',
					language='" . $_POST['Language'] . "',
					email='" . $_POST['email'] . "',
					pdflanguage='" . $_POST['PDFLanguage'] . "',
					password='" . CryptPass($_POST['Password']) . "',
					fontsize='" . $_POST['FontSize'] . "'
				WHERE userid = '" . $_SESSION['UserID'] . "'";

			$ErrMsg = _('The user alterations could not be processed because');
			$DbgMsg = _('The SQL that was used to update the user and failed was');

			$result = DB_query($sql, $db, $ErrMsg, $DbgMsg);

			prnMsg(_('The user settings have been updated'), 'success');
		}
		// update the session variables to reflect user changes on-the-fly
		$_SESSION['DisplayRecordsMax'] = $_POST['DisplayRecordsMax'];
		$_SESSION['Theme'] = trim($_POST['Theme']);
		/*already set by session.inc but for completeness */
		$Theme = $_SESSION['Theme'];
		$_SESSION['Language'] = trim($_POST['Language']);
		$_SESSION['PDFLanguage'] = $_POST['PDFLanguage'];
		include('includes/LanguageSetup.php');

	}
}

$FormName = 'UserSettings1';
echo '<form onSubmit="return name="' . $FormName . '" VerifyForm(this);" method="post" class="noPrint standard" action="' . htmlspecialchars($_SERVER['PHP_SELF'], ENT_QUOTES, 'UTF-8') . '">';
echo '<input type="hidden" name="FormID" value="' . $_SESSION['FormID'] . '" />';

echo '<p class="page_title_text noPrint" ><img src="' . $RootPath . '/css/' . $Theme . '/images/user.png" title="' . _('User Settings') . '" alt="" />' . ' ' . _('User Settings') . '</p>';

if (!isset($_POST['DisplayRecordsMax']) or $_POST['DisplayRecordsMax'] == '') {

	$_POST['DisplayRecordsMax'] = $_SESSION['DefaultDisplayRecordsMax'];

}

Text('UserID', $FormName, _('User ID'), $_SESSION['UserID']);
Text('UserName', $FormName, _('User Name'), $_SESSION['UsersRealName']);
InputText($FormName, 'DisplayRecordsMax', _('Maximum Number of Records to Display'), _('The maximum number of records to display when doing searches.'), 3, 3, True, array(), $_POST['DisplayRecordsMax'], False);

if (!isset($_POST['Language'])) {
	$_POST['Language'] = $_SESSION['Language'];
}
Select($FormName, 'Language', _('Language'), _('The language the user will see the interface in.'), False, $Languages, array(), $_POST['Language']);

$Themes = scandir('css/');
foreach ($Themes as $ThemeName) {
	if (is_dir('css/' . $ThemeName) and $ThemeName != '.' and $ThemeName != '..') {
		$UserThemes[$ThemeName] = $ThemeName;
	}
}
Select($FormName, 'Theme', _('Theme'), _('Theme for this users interface.'), False, $UserThemes, array(), $_SESSION['Theme']);

if (!isset($_POST['PasswordCheck'])) {
	$_POST['PasswordCheck'] = '';
}
if (!isset($_POST['Password'])) {
	$_POST['Password'] = '';
}
InputPassword($FormName, 'Password', _('New Password'), _('Your new password.'), True, array(), $_POST['Password'], False);
InputPassword($FormName, 'PasswordCheck', _('Confirm Password'), _('if you leave the password boxes empty your password will not change'), True, array(), $_POST['PasswordCheck'], False);

$sql = "SELECT email from www_users WHERE userid = '" . $_SESSION['UserID'] . "'";
$result = DB_query($sql, $db);
$myrow = DB_fetch_array($result);
if (!isset($_POST['email'])) {
	$_POST['email'] = $myrow['email'];
}
InputEmail($FormName, 'email', _('Email'), _('The users email address'), True, array(), $_POST['email'], False);

/* Screen Font Size */
$FontSizes[0] = _('Small');
$FontSizes[1] = _('Medium');
$FontSizes[2] = _('Large');
Select($FormName, 'FontSize', _('Screen Font Size'), _('Size of the text on the screen.'), False, $FontSizes, array(), $_SESSION['ScreenFontSize']);

if (!isset($_POST['PDFLanguage'])) {
	$_POST['PDFLanguage'] = $_SESSION['PDFLanguage'];
}
Select($FormName, 'PDFLanguage', _('PDF Language Support'), _('Character set to be used for PDF reports.'), False, $PDFLanguages, array(), $_POST['PDFLanguage']);
SubmitButton(_('Modify'), 'Modify', 'submitbutton');
echo '</form>';

include('includes/footer.inc');
?>