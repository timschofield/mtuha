<?php
$PageSecurity = 1;
include('includes/session.inc');
$Title = _('Patient Billing Form');
include('includes/header.inc');

if (isset($_GET['PID'])) {
	$NameSQL = "SELECT pid,
					name_first,
					name_middle,
					name_last
				FROM person
				WHERE pid='" . $_GET['PID'] . "'";
	$NameResult = DB_query($NameSQL, $db);
	$NameRow = DB_fetch_array($NameResult);
	$Name = $NameRow['name_first'] . ' '. $NameRow['name_middle'] . ' '. $NameRow['name_last'];
}

if (isset($_POST['Submit'])) {
} else {
	$FormName = 'Billing1';
	echo '<form name="' . $FormName . '" onSubmit="return VerifyForm(this);" action="' . htmlspecialchars($_SERVER['PHP_SELF'], ENT_QUOTES, 'UTF-8') . '" method="post" class="noPrint standard">';
	echo '<input type="hidden" name="FormID" value="' . $_SESSION['FormID'] . '" />';
	echo '<p class="page_title_text noPrint" >
			<img src="' . $RootPath . '/css/' . $Theme . '/images/5.png" title="' . $Title . '" alt="' . $Title . '" />' . ' ' . $Title . '
		</p>';
	echo '<h3>' . _('Patient Details') . '</h3>';
	Text('PID', $FormName, _('Patients ID'), $_GET['PID']);
	Text('Name', $FormName, _('Patients Full Name'), $Name);
	echo '<h3>' . _('Registration Items') . '</h3>';
	Text('Reg1', $FormName, _('New Patient File') . ' ' . '1 @150KES', '150 KES');
	echo '<h3>' . _('Admission Items') . '</h3>';
	Text('Adm1', $FormName, _('Triage Assessment') . ' ' . '1 @250KES', '250 KES');
	Text('Adm2', $FormName, _('Initial Consultation') . ' ' . '1 @250KES', '250 KES');
	echo '<h3>' . _('Summary') . '</h3>';
	Text('Sum1', $FormName, _('Total Due'), '650 KES');
	Text('Sum2', $FormName, _('Amount Payable'), '650 KES');
	SubmitButton( _('Print Bill and Process Payment'), 'Submit', 'submitbutton');

	echo '</form>';
}

include('includes/footer.inc');

?>