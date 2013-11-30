<?php

include('includes/session.inc');

$Title = _('Hospital Details');
/* KwaMoja manual links before header.inc */
$ViewTopic = 'CreatingNewSystem';
$BookMark = 'CompanyParameters';
include('includes/header.inc');

$SQL = "SELECT currabrev,
				currency
			FROM currencies";
$Result = DB_query($SQL, $db);
while ($CurrencyRow = DB_fetch_array($Result)) {
	$Currencies[$CurrencyRow['currabrev']] = $CurrencyRow['currency'];
}

//SQL to poulate account selection boxes
$sql = "SELECT accountcode,
				accountname
			FROM chartmaster
			LEFT JOIN accountgroups
				ON chartmaster.group_=accountgroups.groupname
			WHERE accountgroups.pandl=0
			ORDER BY accountcode";

$BSAccountsResult = DB_query($sql, $db);

while ($myrow = DB_fetch_array($BSAccountsResult)) {
	$BSAccounts[$myrow['accountcode']] = $myrow['accountname'];
}

$sql = "SELECT accountcode,
				accountname
			FROM chartmaster
			LEFT JOIN accountgroups
				ON chartmaster.group_=accountgroups.groupname
			WHERE accountgroups.pandl=1
			ORDER BY accountcode";

$PnLAccountsResult = DB_query($sql, $db);

while ($myrow = DB_fetch_array($PnLAccountsResult)) {
	$PnLAccounts[$myrow['accountcode']] = $myrow['accountname'];
}

$YesNo[0] = _('No');
$YesNo[1] = _('Yes');

if (isset($Errors)) {
	unset($Errors);
}

//initialise no input errors assumed initially before we test
$InputError = 0;
$Errors = array();
$i = 1;

if (isset($_POST['Submit'])) {


	/* actions to take once the user has clicked the submit button
	ie the page has called itself with some user input */

	//first off validate inputs sensible

	if (mb_strlen($_POST['CoyName']) > 50 or mb_strlen($_POST['CoyName']) == 0) {
		$InputError = 1;
		prnMsg(_('The company name must be entered and be fifty characters or less long'), 'error');
		$Errors[$i] = 'CoyName';
		$i++;
	}
	if (mb_strlen($_POST['RegOffice1']) > 40) {
		$InputError = 1;
		prnMsg(_('The Line 1 of the address must be forty characters or less long'), 'error');
		$Errors[$i] = 'RegOffice1';
		$i++;
	}
	if (mb_strlen($_POST['RegOffice2']) > 40) {
		$InputError = 1;
		prnMsg(_('The Line 2 of the address must be forty characters or less long'), 'error');
		$Errors[$i] = 'RegOffice2';
		$i++;
	}
	if (mb_strlen($_POST['RegOffice3']) > 40) {
		$InputError = 1;
		prnMsg(_('The Line 3 of the address must be forty characters or less long'), 'error');
		$Errors[$i] = 'RegOffice3';
		$i++;
	}
	if (mb_strlen($_POST['RegOffice4']) > 40) {
		$InputError = 1;
		prnMsg(_('The Line 4 of the address must be forty characters or less long'), 'error');
		$Errors[$i] = 'RegOffice4';
		$i++;
	}
	if (mb_strlen($_POST['RegOffice5']) > 20) {
		$InputError = 1;
		prnMsg(_('The Line 5 of the address must be twenty characters or less long'), 'error');
		$Errors[$i] = 'RegOffice5';
		$i++;
	}
	if (mb_strlen($_POST['RegOffice6']) > 15) {
		$InputError = 1;
		prnMsg(_('The Line 6 of the address must be fifteen characters or less long'), 'error');
		$Errors[$i] = 'RegOffice6';
		$i++;
	}
	if (mb_strlen($_POST['Telephone']) > 25) {
		$InputError = 1;
		prnMsg(_('The telephone number must be 25 characters or less long'), 'error');
		$Errors[$i] = 'Telephone';
		$i++;
	}
	if (mb_strlen($_POST['Fax']) > 25) {
		$InputError = 1;
		prnMsg(_('The fax number must be 25 characters or less long'), 'error');
		$Errors[$i] = 'Fax';
		$i++;
	}
	if (mb_strlen($_POST['Email']) > 55) {
		$InputError = 1;
		prnMsg(_('The email address must be 55 characters or less long'), 'error');
		$Errors[$i] = 'Email';
		$i++;
	}
	if (mb_strlen($_POST['Email']) > 0 and !IsEmailAddress($_POST['Email'])) {
		$InputError = 1;
		prnMsg(_('The email address is not correctly formed'), 'error');
		$Errors[$i] = 'Email';
		$i++;
	}

	if ($InputError != 1) {

		if (isset($_SESSION['FirstStart'])) {
			$sql = "INSERT INTO companies (coycode,
											coyname,
											companynumber,
											gstno,
											regoffice1,
											regoffice2,
											regoffice3,
											regoffice4,
											regoffice5,
											regoffice6,
											telephone,
											fax,
											email,
											currencydefault,
											debtorsact,
											pytdiscountact,
											creditorsact,
											payrollact,
											grnact,
											exchangediffact,
											purchasesexchangediffact,
											retainedearnings,
											gllink_debtors,
											gllink_creditors,
											gllink_stock,
											freightact
										) VALUES (
											1,
											'" . $_POST['CoyName'] . "',
											'" . $_POST['CompanyNumber'] . "',
											'" . $_POST['GSTNo'] . "',
											'" . $_POST['RegOffice1'] . "',
											'" . $_POST['RegOffice2'] . "',
											'" . $_POST['RegOffice3'] . "',
											'" . $_POST['RegOffice4'] . "',
											'" . $_POST['RegOffice5'] . "',
											'" . $_POST['RegOffice6'] . "',
											'" . $_POST['Telephone'] . "',
											'" . $_POST['Fax'] . "',
											'" . $_POST['Email'] . "',
											'" . $_POST['CurrencyDefault'] . "',
											'" . $_POST['DebtorsAct'] . "',
											'" . $_POST['PytDiscountAct'] . "',
											'" . $_POST['CreditorsAct'] . "',
											'" . $_POST['PayrollAct'] . "',
											'" . $_POST['GRNAct'] . "',
											'" . $_POST['ExchangeDiffAct'] . "',
											'" . $_POST['PurchasesExchangeDiffAct'] . "',
											'" . $_POST['RetainedEarnings'] . "',
											'" . $_POST['GLLink_Debtors'] . "',
											'" . $_POST['GLLink_Creditors'] . "',
											'" . $_POST['GLLink_Stock'] . "',
											'" . $_POST['FreightAct'] . "'
										)";
		} else {

			$sql = "UPDATE companies SET coyname='" . $_POST['CoyName'] . "',
										companynumber = '" . $_POST['CompanyNumber'] . "',
										gstno='" . $_POST['GSTNo'] . "',
										regoffice1='" . $_POST['RegOffice1'] . "',
										regoffice2='" . $_POST['RegOffice2'] . "',
										regoffice3='" . $_POST['RegOffice3'] . "',
										regoffice4='" . $_POST['RegOffice4'] . "',
										regoffice5='" . $_POST['RegOffice5'] . "',
										regoffice6='" . $_POST['RegOffice6'] . "',
										telephone='" . $_POST['Telephone'] . "',
										fax='" . $_POST['Fax'] . "',
										email='" . $_POST['Email'] . "',
										currencydefault='" . $_POST['CurrencyDefault'] . "',
										debtorsact='" . $_POST['DebtorsAct'] . "',
										pytdiscountact='" . $_POST['PytDiscountAct'] . "',
										creditorsact='" . $_POST['CreditorsAct'] . "',
										payrollact='" . $_POST['PayrollAct'] . "',
										grnact='" . $_POST['GRNAct'] . "',
										exchangediffact='" . $_POST['ExchangeDiffAct'] . "',
										purchasesexchangediffact='" . $_POST['PurchasesExchangeDiffAct'] . "',
										retainedearnings='" . $_POST['RetainedEarnings'] . "',
										gllink_debtors='" . $_POST['GLLink_Debtors'] . "',
										gllink_creditors='" . $_POST['GLLink_Creditors'] . "',
										gllink_stock='" . $_POST['GLLink_Stock'] . "',
										freightact='" . $_POST['FreightAct'] . "'
									WHERE coycode=1";
		}

		$ErrMsg = _('The company preferences could not be updated because');
		$result = DB_query($sql, $db, $ErrMsg);
		prnMsg(_('Company preferences updated'), 'success');

		/* Alter the exchange rates in the currencies table */

		/* Get default currency rate */
		$sql = "SELECT rate from currencies WHERE currabrev='" . $_POST['CurrencyDefault'] . "'";
		$result = DB_query($sql, $db);
		$myrow = DB_fetch_row($result);
		$NewCurrencyRate = $myrow[0];

		/* Set new rates */
		$sql = "UPDATE currencies SET rate=rate/" . $NewCurrencyRate;
		$ErrMsg = _('Could not update the currency rates');
		$result = DB_query($sql, $db, $ErrMsg);

		/* End of update currencies */

		$ForceConfigReload = True; // Required to force a load even if stored in the session vars
		include('includes/GetConfig.php');
		$ForceConfigReload = False;

	} else {
		prnMsg(_('Validation failed') . ', ' . _('no updates or deletes took place'), 'warn');
	}

}
/* end of if submit */
$FormName = 'Company1';
echo '<form name="' . $FormName . '" onSubmit="return VerifyForm(this);" method="post" class="noPrint standard" action="' . htmlspecialchars($_SERVER['PHP_SELF'], ENT_QUOTES, 'UTF-8') . '">';
echo '<input type="hidden" name="FormID" value="' . $_SESSION['FormID'] . '" />';

echo '<p class="page_title_text noPrint" ><img src="' . $RootPath . '/css/' . $Theme . '/images/company.png" title="' . $Title . '" alt="" />' . ' ' . $Title . '</p>';

if ($InputError != 1) {
	$sql = "SELECT coyname,
					gstno,
					companynumber,
					regoffice1,
					regoffice2,
					regoffice3,
					regoffice4,
					regoffice5,
					regoffice6,
					telephone,
					fax,
					email,
					currencydefault,
					debtorsact,
					pytdiscountact,
					creditorsact,
					payrollact,
					grnact,
					exchangediffact,
					purchasesexchangediffact,
					retainedearnings,
					gllink_debtors,
					gllink_creditors,
					gllink_stock,
					freightact
				FROM companies
				WHERE coycode=1";

	$ErrMsg = _('The company preferences could not be retrieved because');
	$result = DB_query($sql, $db, $ErrMsg);

	$myrow = DB_fetch_array($result);

	$_POST['CoyName'] = $myrow['coyname'];
	$_POST['GSTNo'] = $myrow['gstno'];
	$_POST['CompanyNumber'] = $myrow['companynumber'];
	$_POST['RegOffice1'] = $myrow['regoffice1'];
	$_POST['RegOffice2'] = $myrow['regoffice2'];
	$_POST['RegOffice3'] = $myrow['regoffice3'];
	$_POST['RegOffice4'] = $myrow['regoffice4'];
	$_POST['RegOffice5'] = $myrow['regoffice5'];
	$_POST['RegOffice6'] = $myrow['regoffice6'];
	$_POST['Telephone'] = $myrow['telephone'];
	$_POST['Fax'] = $myrow['fax'];
	$_POST['Email'] = $myrow['email'];
	$_POST['CurrencyDefault'] = $myrow['currencydefault'];
	$_POST['DebtorsAct'] = $myrow['debtorsact'];
	$_POST['PytDiscountAct'] = $myrow['pytdiscountact'];
	$_POST['CreditorsAct'] = $myrow['creditorsact'];
	$_POST['PayrollAct'] = $myrow['payrollact'];
	$_POST['GRNAct'] = $myrow['grnact'];
	$_POST['ExchangeDiffAct'] = $myrow['exchangediffact'];
	$_POST['PurchasesExchangeDiffAct'] = $myrow['purchasesexchangediffact'];
	$_POST['RetainedEarnings'] = $myrow['retainedearnings'];
	$_POST['GLLink_Debtors'] = $myrow['gllink_debtors'];
	$_POST['GLLink_Creditors'] = $myrow['gllink_creditors'];
	$_POST['GLLink_Stock'] = $myrow['gllink_stock'];
	$_POST['FreightAct'] = $myrow['freightact'];
}

	if (DB_num_rows($result) == 0) {
		echo '<div class="page_help_text">' . _('As this is the first time that the system has been used, you must first fill out the company details.') .
				'<br />' . _('Once you have filled in all the details, click on the button at the bottom of the screen') . '</div>';
		include('companies/' . $_SESSION['DatabaseName'] . '/Companies.php');
		$_POST['CoyName'] = $CompanyName[$_SESSION['DatabaseName']];
	} elseif (DB_num_rows($result) == 1 and isset($_SESSION['FirstStart'])) {
		echo '<meta http-equiv="refresh" content="0; url=' . $RootPath . '/TaxProvinces.php">';
		exit;
	}
InputText($FormName, 'CoyName', _('Hospital name') . ' (' . _('to appear on reports') . '):', _('The name of the hospital that will appear on all reports'), 50, 50, True, array(), stripslashes($_POST['CoyName']));
InputText($FormName, 'CompanyNumber', _('Company number'), _('The company number if the hospital is a registered company'), 50, 50, False, array(), $_POST['CompanyNumber']);
InputText($FormName, 'GSTNo', _('Tax Authority Reference'), _('The tax reference of the hospital if it is registered for taxes'), 20, 20, False, array(), $_POST['GSTNo']);
InputText($FormName, 'RegOffice1', _('Address Line 1'), _('Address Line 1'), 40, 40, False, array(), $_POST['RegOffice1']);
InputText($FormName, 'RegOffice2', _('Address Line 1'), _('Address Line 2'), 40, 40, False, array(), $_POST['RegOffice2']);
InputText($FormName, 'RegOffice3', _('Address Line 1'), _('Address Line 3'), 40, 40, False, array(), $_POST['RegOffice3']);
InputText($FormName, 'RegOffice4', _('Address Line 1'), _('Address Line 4'), 40, 40, False, array(), $_POST['RegOffice4']);
InputText($FormName, 'RegOffice5', _('Address Line 1'), _('Address Line 5'), 20, 20, False, array(), $_POST['RegOffice5']);
InputText($FormName, 'RegOffice6', _('Address Line 1'), _('Address Line 6'), 15, 15, False, array(), $_POST['RegOffice6']);

InputTelephone($FormName, 'Telephone', _('Telephone Number'), _('Hospital\'s main telephone number'), False, array(), $_POST['Telephone']);
InputTelephone($FormName, 'Fax', _('Facsimile Number'), _('Hospital\'s main fax number'), False, array(), $_POST['Fax']);

InputEmail($FormName, 'Email', _('Email Address'), _('Hospital\'s main email address'), False, array(), $_POST['Email']);

Select($FormName, 'CurrencyDefault', _('Home Currency'), _('The base currency to be used for accounting.'), False, $Currencies, array(), $_POST['CurrencyDefault']);

Select($FormName, 'DebtorsAct', _('Debtors Control GL Account'), _('Debtors Control GL Account'), False, $BSAccounts, array(), $_POST['DebtorsAct']);
Select($FormName, 'CreditorsAct', _('Creditors Control GL Account'), _('Creditors Control GL Account'), False, $BSAccounts, array(), $_POST['CreditorsAct']);
Select($FormName, 'PayrollAct', _('Payroll Net Pay Clearing GL Account'), _('Payroll Net Pay Clearing GL Account'), False, $BSAccounts, array(), $_POST['PayrollAct']);
Select($FormName, 'GRNAct', _('Goods Received Clearing GL Account'), _('Goods Received Clearing GL Account'), False, $BSAccounts, array(), $_POST['GRNAct']);
Select($FormName, 'RetainedEarnings', _('Retained Earning Clearing GL Account'), _('Retained Earning Clearing GL Account'), False, $BSAccounts, array(), $_POST['RetainedEarnings']);

Select($FormName, 'FreightAct', _('Freight Re-charged GL Account'), _('Freight Re-charged GL Account'), False, $PnLAccounts, array(), $_POST['FreightAct']);
Select($FormName, 'ExchangeDiffAct', _('Sales Exchange Variances GL Account'), _('Sales Exchange Variances GL Account'), False, $PnLAccounts, array(), $_POST['ExchangeDiffAct']);
Select($FormName, 'PurchasesExchangeDiffAct', _('Purchases Exchange Variances GL Account'), _('Purchases Exchange Variances GL Account'), False, $PnLAccounts, array(), $_POST['PurchasesExchangeDiffAct']);
Select($FormName, 'PytDiscountAct', _('Payment Discount GL Account'), _('Payment Discount GL Account'), False, $PnLAccounts, array(), $_POST['PytDiscountAct']);

Select($FormName, 'GLLink_Debtors', _('Create GL entries for accounts receivable transactions'), _('Create GL entries for accounts receivable transactions'), False, $YesNo, array(), $_POST['GLLink_Debtors']);
Select($FormName, 'GLLink_Creditors', _('Create GL entries for accounts payable transactions'), _('Create GL entries for accounts payable transactions'), False, $YesNo, array(), $_POST['GLLink_Creditors']);
Select($FormName, 'GLLink_Stock', _('Create GL entries for stock transactions'), _('Create GL entries for stock transactions'), False, $YesNo, array(), $_POST['GLLink_Stock']);

SubmitButton( _('Update the Hospital Details'), 'Submit', 'submitbutton');

echo '</form>';

include('includes/footer.inc');
?>