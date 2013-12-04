<?php

include('includes/session.inc');

$Title = _('System Parameters');
$ViewTopic = 'GettingStarted';
$BookMark = 'SystemConfiguration';
include('includes/header.inc');
include('includes/CountriesArray.php');

if (isset($_POST['Submit'])) {

	//initialise no input errors assumed initially before we test
	$InputError = 0;

	/* actions to take once the user has clicked the submit button
	ie the page has called itself with some user input */

	//first off validate inputs sensible
	/*
	Note: the X_ in the POST variables, the reason for this is to overcome globals=on replacing
	the actial system/overidden variables.
	*/
	if (mb_strlen($_POST['X_PastDueDays1']) > 3 or !is_numeric($_POST['X_PastDueDays1'])) {
		$InputError = 1;
		prnMsg(_('First overdue deadline days must be a number'), 'error');
	} elseif (mb_strlen($_POST['X_PastDueDays2']) > 3 or !is_numeric($_POST['X_PastDueDays2'])) {
		$InputError = 1;
		prnMsg(_('Second overdue deadline days must be a number'), 'error');
	} elseif (mb_strlen($_POST['X_DefaultCreditLimit']) > 12 or !is_numeric($_POST['X_DefaultCreditLimit'])) {
		$InputError = 1;
		prnMsg(_('Default Credit Limit must be a number'), 'error');
	} elseif (mb_strstr($_POST['X_RomalpaClause'], "'") or mb_strlen($_POST['X_RomalpaClause']) > 5000) {
		$InputError = 1;
		prnMsg(_('The Romalpa Clause may not contain single quotes and may not be longer than 5000 chars'), 'error');
	} elseif (mb_strlen($_POST['X_QuickEntries']) > 2 or !is_numeric($_POST['X_QuickEntries']) or $_POST['X_QuickEntries'] < 1 or $_POST['X_QuickEntries'] > 99) {
		$InputError = 1;
		prnMsg(_('No less than 1 and more than 99 Quick entries allowed'), 'error');
	} elseif (mb_strlen($_POST['X_FreightChargeAppliesIfLessThan']) > 12 or !is_numeric($_POST['X_FreightChargeAppliesIfLessThan'])) {
		$InputError = 1;
		prnMsg(_('Freight Charge Applies If Less Than must be a number'), 'error');
	} elseif (!is_numeric($_POST['X_StandardCostDecimalPlaces']) or $_POST['X_StandardCostDecimalPlaces'] < 0 or $_POST['X_StandardCostDecimalPlaces'] > 4) {
		$InputError = 1;
		prnMsg(_('Standard Cost Decimal Places must be a number between 0 and 4'), 'error');
	} elseif (mb_strlen($_POST['X_NumberOfPeriodsOfStockUsage']) > 2 or !is_numeric($_POST['X_NumberOfPeriodsOfStockUsage']) or $_POST['X_NumberOfPeriodsOfStockUsage'] < 1 or $_POST['X_NumberOfPeriodsOfStockUsage'] > 12) {
		$InputError = 1;
		prnMsg(_('Financial period per year must be a number between 1 and 12'), 'error');
	} elseif (mb_strlen($_POST['X_TaxAuthorityReferenceName']) > 25) {
		$InputError = 1;
		prnMsg(_('The Tax Authority Reference Name must be 25 characters or less long'), 'error');
	} elseif (mb_strlen($_POST['X_OverChargeProportion']) > 3 or !is_numeric($_POST['X_OverChargeProportion']) or $_POST['X_OverChargeProportion'] < 0 or $_POST['X_OverChargeProportion'] > 100) {
		$InputError = 1;
		prnMsg(_('Over Charge Proportion must be a percentage'), 'error');
	} elseif (mb_strlen($_POST['X_OverReceiveProportion']) > 3 or !is_numeric($_POST['X_OverReceiveProportion']) or $_POST['X_OverReceiveProportion'] < 0 or $_POST['X_OverReceiveProportion'] > 100) {
		$InputError = 1;
		prnMsg(_('Over Receive Proportion must be a percentage'), 'error');
	} elseif (mb_strlen($_POST['X_PageLength']) > 3 or !is_numeric($_POST['X_PageLength']) or $_POST['X_PageLength'] < 1) {
		$InputError = 1;
		prnMsg(_('Lines per page must be greater than 1'), 'error');
	} elseif (mb_strlen($_POST['X_MonthsAuditTrail']) > 2 or !is_numeric($_POST['X_MonthsAuditTrail']) or $_POST['X_MonthsAuditTrail'] < 0) {
		$InputError = 1;
		prnMsg(_('The number of months of audit trail to keep must be zero or a positive number less than 100 months'), 'error');
	} elseif (mb_strlen($_POST['X_DefaultTaxCategory']) > 1 or !is_numeric($_POST['X_DefaultTaxCategory']) or $_POST['X_DefaultTaxCategory'] < 1) {
		$InputError = 1;
		prnMsg(_('DefaultTaxCategory must be between 1 and 9'), 'error');
	} elseif (mb_strlen($_POST['X_DefaultDisplayRecordsMax']) > 3 or !is_numeric($_POST['X_DefaultDisplayRecordsMax']) or $_POST['X_DefaultDisplayRecordsMax'] < 1) {
		$InputError = 1;
		prnMsg(_('Default maximum number of records to display must be between 1 and 500'), 'error');
	} elseif (mb_strlen($_POST['X_MaxImageSize']) > 3 or !is_numeric($_POST['X_MaxImageSize']) or $_POST['X_MaxImageSize'] < 1) {
		$InputError = 1;
		prnMsg(_('The maximum size of item image files must be between 50 and 500 (NB this figure refers to KB)'), 'error');
	} elseif (!IsEmailAddress($_POST['X_FactoryManagerEmail'])) {
		$InputError = 1;
		prnMsg(_('The Factory Manager Email address does not appear to be valid'), 'error');
	} elseif (!IsEmailAddress($_POST['X_PurchasingManagerEmail'])) {
		$InputError = 1;
		prnMsg(_('The Purchasing Manager Email address does not appear to be valid'), 'error');
	} elseif (!IsEmailAddress($_POST['X_InventoryManagerEmail']) and $_POST['X_InventoryManagerEmail'] != '') {
		$InputError = 1;
		prnMsg(_('The Inventory Manager Email address does not appear to be valid'), 'error');
	} elseif (mb_strlen($_POST['X_FrequentlyOrderedItems']) > 2 or !is_numeric($_POST['X_FrequentlyOrderedItems'])) {
		$InputError = 1;
		prnMsg(_('The number of frequently ordered items to display must be numeric'), 'error');
	} elseif (strlen($_POST['X_SmtpSetting']) != 1 OR !is_numeric($_POST['X_SmtpSetting'])) {
		$InputError = 1;
		prnMsg(_('The SMTP setting should be selected as Yes or No'), 'error');
	}


	if ($InputError != 1) {

		$sql = array();

		if ($_SESSION['DefaultDateFormat'] != $_POST['X_DefaultDateFormat']) {
			$sql[] = "UPDATE config SET confvalue = '" . $_POST['X_DefaultDateFormat'] . "' WHERE confname = 'DefaultDateFormat'";
		}
		if ($_SESSION['DefaultTheme'] != $_POST['X_DefaultTheme']) {
			$sql[] = "UPDATE config SET confvalue = '" . $_POST['X_DefaultTheme'] . "' WHERE confname = 'DefaultTheme'";
		}
		if ($_SESSION['PastDueDays1'] != $_POST['X_PastDueDays1']) {
			$sql[] = "UPDATE config SET confvalue = '" . $_POST['X_PastDueDays1'] . "' WHERE confname = 'PastDueDays1'";
		}
		if ($_SESSION['PastDueDays2'] != $_POST['X_PastDueDays2']) {
			$sql[] = "UPDATE config SET confvalue = '" . $_POST['X_PastDueDays2'] . "' WHERE confname = 'PastDueDays2'";
		}
		if ($_SESSION['DefaultCreditLimit'] != $_POST['X_DefaultCreditLimit']) {
			$sql[] = "UPDATE config SET confvalue = '" . $_POST['X_DefaultCreditLimit'] . "' WHERE confname = 'DefaultCreditLimit'";
		}
		if ($_SESSION['Show_Settled_LastMonth'] != $_POST['X_Show_Settled_LastMonth']) {
			$sql[] = "UPDATE config SET confvalue = '" . $_POST['X_Show_Settled_LastMonth'] . "' WHERE confname = 'Show_Settled_LastMonth'";
		}
		if ($_SESSION['RomalpaClause'] != $_POST['X_RomalpaClause']) {
			$sql[] = "UPDATE config SET confvalue = '" . $_POST['X_RomalpaClause'] . "' WHERE confname = 'RomalpaClause'";
		}
		if ($_SESSION['QuickEntries'] != $_POST['X_QuickEntries']) {
			$sql[] = "UPDATE config SET confvalue = '" . $_POST['X_QuickEntries'] . "' WHERE confname = 'QuickEntries'";
		}

		if ($_SESSION['WorkingDaysWeek'] != $_POST['X_WorkingDaysWeek']) {
			$sql[] = "UPDATE config SET confvalue = '" . $_POST['X_WorkingDaysWeek'] . "' WHERE confname = 'WorkingDaysWeek'";
		}

		if ($_SESSION['DispatchCutOffTime'] != $_POST['X_DispatchCutOffTime']) {
			$sql[] = "UPDATE config SET confvalue = '" . $_POST['X_DispatchCutOffTime'] . "' WHERE confname = 'DispatchCutOffTime'";
		}
		if ($_SESSION['AllowSalesOfZeroCostItems'] != $_POST['X_AllowSalesOfZeroCostItems']) {
			$sql[] = "UPDATE config SET confvalue = '" . $_POST['X_AllowSalesOfZeroCostItems'] . "' WHERE confname = 'AllowSalesOfZeroCostItems'";
		}
		if ($_SESSION['CreditingControlledItems_MustExist'] != $_POST['X_CreditingControlledItems_MustExist']) {
			$sql[] = "UPDATE config SET confvalue = '" . $_POST['X_CreditingControlledItems_MustExist'] . "' WHERE confname = 'CreditingControlledItems_MustExist'";
		}
		if ($_SESSION['DefaultPriceList'] != $_POST['X_DefaultPriceList']) {
			$sql[] = "UPDATE config SET confvalue = '" . $_POST['X_DefaultPriceList'] . "' WHERE confname = 'DefaultPriceList'";
		}
		if ($_SESSION['Default_Shipper'] != $_POST['X_Default_Shipper']) {
			$sql[] = "UPDATE config SET confvalue = '" . $_POST['X_Default_Shipper'] . "' WHERE confname = 'Default_Shipper'";
		}
		if ($_SESSION['DoFreightCalc'] != $_POST['X_DoFreightCalc']) {
			$sql[] = "UPDATE config SET confvalue = '" . $_POST['X_DoFreightCalc'] . "' WHERE confname = 'DoFreightCalc'";
		}
		if ($_SESSION['FreightChargeAppliesIfLessThan'] != $_POST['X_FreightChargeAppliesIfLessThan']) {
			$sql[] = "UPDATE config SET confvalue = '" . $_POST['X_FreightChargeAppliesIfLessThan'] . "' WHERE confname = 'FreightChargeAppliesIfLessThan'";
		}
		if ($_SESSION['DefaultTaxCategory'] != $_POST['X_DefaultTaxCategory']) {
			$sql[] = "UPDATE config SET confvalue = '" . $_POST['X_DefaultTaxCategory'] . "' WHERE confname = 'DefaultTaxCategory'";
		}
		if ($_SESSION['TaxAuthorityReferenceName'] != $_POST['X_TaxAuthorityReferenceName']) {
			$sql[] = "UPDATE config SET confvalue = '" . $_POST['X_TaxAuthorityReferenceName'] . "' WHERE confname = 'TaxAuthorityReferenceName'";
		}
		if ($_SESSION['CountryOfOperation'] != $_POST['X_CountryOfOperation']) {
			$sql[] = "UPDATE config SET confvalue = '" . $_POST['X_CountryOfOperation'] . "' WHERE confname = 'CountryOfOperation'";
		}
		if ($_SESSION['StandardCostDecimalPlaces'] != $_POST['X_StandardCostDecimalPlaces']) {
			$sql[] = "UPDATE config SET confvalue = '" . $_POST['X_StandardCostDecimalPlaces'] . "' WHERE confname = 'StandardCostDecimalPlaces'";
		}
		if ($_SESSION['NumberOfPeriodsOfStockUsage'] != $_POST['X_NumberOfPeriodsOfStockUsage']) {
			$sql[] = "UPDATE config SET confvalue = '" . $_POST['X_NumberOfPeriodsOfStockUsage'] . "' WHERE confname = 'NumberOfPeriodsOfStockUsage'";
		}
		if ($_SESSION['Check_Qty_Charged_vs_Del_Qty'] != $_POST['X_Check_Qty_Charged_vs_Del_Qty']) {
			$sql[] = "UPDATE config SET confvalue = '" . $_POST['X_Check_Qty_Charged_vs_Del_Qty'] . "' WHERE confname = 'Check_Qty_Charged_vs_Del_Qty'";
		}
		if ($_SESSION['Check_Price_Charged_vs_Order_Price'] != $_POST['X_Check_Price_Charged_vs_Order_Price']) {
			$sql[] = "UPDATE config SET confvalue = '" . $_POST['X_Check_Price_Charged_vs_Order_Price'] . "' WHERE confname = 'Check_Price_Charged_vs_Order_Price'";
		}
		if ($_SESSION['OverChargeProportion'] != $_POST['X_OverChargeProportion']) {
			$sql[] = "UPDATE config SET confvalue = '" . $_POST['X_OverChargeProportion'] . "' WHERE confname = 'OverChargeProportion'";
		}
		if ($_SESSION['OverReceiveProportion'] != $_POST['X_OverReceiveProportion']) {
			$sql[] = "UPDATE config SET confvalue = '" . $_POST['X_OverReceiveProportion'] . "' WHERE confname = 'OverReceiveProportion'";
		}
		if ($_SESSION['PO_AllowSameItemMultipleTimes'] != $_POST['X_PO_AllowSameItemMultipleTimes']) {
			$sql[] = "UPDATE config SET confvalue = '" . $_POST['X_PO_AllowSameItemMultipleTimes'] . "' WHERE confname = 'PO_AllowSameItemMultipleTimes'";
		}
		if ($_SESSION['SO_AllowSameItemMultipleTimes'] != $_POST['X_SO_AllowSameItemMultipleTimes']) {
			$sql[] = "UPDATE config SET confvalue = '" . $_POST['X_SO_AllowSameItemMultipleTimes'] . "' WHERE confname = 'SO_AllowSameItemMultipleTimes'";
		}
		if ($_SESSION['YearEnd'] != $_POST['X_YearEnd']) {
			$sql[] = "UPDATE config SET confvalue = '" . $_POST['X_YearEnd'] . "' WHERE confname = 'YearEnd'";
		}
		if ($_SESSION['PageLength'] != $_POST['X_PageLength']) {
			$sql[] = "UPDATE config SET confvalue = '" . $_POST['X_PageLength'] . "' WHERE confname = 'PageLength'";
		}
		if ($_SESSION['DefaultDisplayRecordsMax'] != $_POST['X_DefaultDisplayRecordsMax']) {
			$sql[] = "UPDATE config SET confvalue = '" . $_POST['X_DefaultDisplayRecordsMax'] . "' WHERE confname = 'DefaultDisplayRecordsMax'";
		}
		if ($_SESSION['MaxImageSize'] != $_POST['X_MaxImageSize']) {
			$sql[] = "UPDATE config SET confvalue = '" . $_POST['X_MaxImageSize'] . "' WHERE confname = 'MaxImageSize'";
		}
		if ($_SESSION['ShowStockidOnImages'] != $_POST['X_ShowStockidOnImages']) {
			$sql[] = "UPDATE config SET confvalue = '" . $_POST['X_ShowStockidOnImages'] . "' WHERE confname = 'ShowStockidOnImages'";
		}
		//new number must be shown
		if ($_SESSION['NumberOfMonthMustBeShown'] != $_POST['X_NumberOfMonthMustBeShown']) {
			$sql[] = "UPDATE config SET confvalue = '" . $_POST['X_NumberOfMonthMustBeShown'] . "' WHERE confname = 'NumberOfMonthMustBeShown'";
		}
		if ($_SESSION['part_pics_dir'] != $_POST['X_part_pics_dir']) {
			$sql[] = "UPDATE config SET confvalue = 'companies/" . $_SESSION['DatabaseName'] . '/' . $_POST['X_part_pics_dir'] . "' WHERE confname = 'part_pics_dir'";
		}
		if ($_SESSION['reports_dir'] != $_POST['X_reports_dir']) {
			$sql[] = "UPDATE config SET confvalue = 'companies/" . $_SESSION['DatabaseName'] . '/' . $_POST['X_reports_dir'] . "' WHERE confname = 'reports_dir'";
		}
		if ($_SESSION['AutoDebtorNo'] != $_POST['X_AutoDebtorNo']) {
			$sql[] = "UPDATE config SET confvalue = '" . ($_POST['X_AutoDebtorNo']) . "' WHERE confname = 'AutoDebtorNo'";
		}
		if ($_SESSION['HTTPS_Only'] != $_POST['X_HTTPS_Only']) {
			$sql[] = "UPDATE config SET confvalue = '" . ($_POST['X_HTTPS_Only']) . "' WHERE confname = 'HTTPS_Only'";
		}
		if ($_SESSION['DB_Maintenance'] != $_POST['X_DB_Maintenance']) {
			$sql[] = "UPDATE config SET confvalue = '" . ($_POST['X_DB_Maintenance']) . "' WHERE confname = 'DB_Maintenance'";
		}
		if ($_SESSION['DefaultBlindPackNote'] != $_POST['X_DefaultBlindPackNote']) {
			$sql[] = "UPDATE config SET confvalue = '" . ($_POST['X_DefaultBlindPackNote']) . "' WHERE confname = 'DefaultBlindPackNote'";
		}
		if ($_SESSION['ShowValueOnGRN'] != $_POST['X_ShowValueOnGRN']) {
			$sql[] = "UPDATE config SET confvalue = '" . ($_POST['X_ShowValueOnGRN']) . "' WHERE confname = 'ShowValueOnGRN'";
		}
		if ($_SESSION['PackNoteFormat'] != $_POST['X_PackNoteFormat']) {
			$sql[] = "UPDATE config SET confvalue = '" . ($_POST['X_PackNoteFormat']) . "' WHERE confname = 'PackNoteFormat'";
		}
		if ($_SESSION['CheckCreditLimits'] != $_POST['X_CheckCreditLimits']) {
			$sql[] = "UPDATE config SET confvalue = '" . ($_POST['X_CheckCreditLimits']) . "' WHERE confname = 'CheckCreditLimits'";
		}
		if ($_SESSION['WikiApp'] != $_POST['X_WikiApp']) {
			$sql[] = "UPDATE config SET confvalue = '" . $_POST['X_WikiApp'] . "' WHERE confname = 'WikiApp'";
		}
		if ($_SESSION['WikiPath'] != $_POST['X_WikiPath']) {
			$sql[] = "UPDATE config SET confvalue = '" . $_POST['X_WikiPath'] . "' WHERE confname = 'WikiPath'";
		}
		if ($_SESSION['ProhibitJournalsToControlAccounts'] != $_POST['X_ProhibitJournalsToControlAccounts']) {
			$sql[] = "UPDATE config SET confvalue = '" . $_POST['X_ProhibitJournalsToControlAccounts'] . "' WHERE confname = 'ProhibitJournalsToControlAccounts'";
		}
		if ($_SESSION['InvoicePortraitFormat'] != $_POST['X_InvoicePortraitFormat']) {
			$sql[] = "UPDATE config SET confvalue = '" . $_POST['X_InvoicePortraitFormat'] . "' WHERE confname = 'InvoicePortraitFormat'";
		}
		if ($_SESSION['AllowOrderLineItemNarrative'] != $_POST['X_AllowOrderLineItemNarrative']) {
			$sql[] = "UPDATE config SET confvalue = '" . $_POST['X_AllowOrderLineItemNarrative'] . "' WHERE confname = 'AllowOrderLineItemNarrative'";
		}
		if ($_SESSION['RequirePickingNote'] != $_POST['X_RequirePickingNote']) {
			$sql[] = "UPDATE config SET confvalue = '" . $_POST['X_RequirePickingNote'] . "' WHERE confname = 'RequirePickingNote'";
		}
		if ($_SESSION['geocode_integration'] != $_POST['X_geocode_integration']) {
			$sql[] = "UPDATE config SET confvalue = '" . $_POST['X_geocode_integration'] . "' WHERE confname = 'geocode_integration'";
		}
		if ($_SESSION['Extended_SupplierInfo'] != $_POST['X_Extended_SupplierInfo']) {
			$sql[] = "UPDATE config SET confvalue = '" . $_POST['X_Extended_SupplierInfo'] . "' WHERE confname = 'Extended_SupplierInfo'";
		}
		if ($_SESSION['Extended_CustomerInfo'] != $_POST['X_Extended_CustomerInfo']) {
			$sql[] = "UPDATE config SET confvalue = '" . $_POST['X_Extended_CustomerInfo'] . "' WHERE confname = 'Extended_CustomerInfo'";
		}
		if ($_SESSION['ProhibitPostingsBefore'] != $_POST['X_ProhibitPostingsBefore']) {
			$sql[] = "UPDATE config SET confvalue = '" . $_POST['X_ProhibitPostingsBefore'] . "' WHERE confname = 'ProhibitPostingsBefore'";
		}
		if ($_SESSION['WeightedAverageCosting'] != $_POST['X_WeightedAverageCosting']) {
			$sql[] = "UPDATE config SET confvalue = '" . $_POST['X_WeightedAverageCosting'] . "' WHERE confname = 'WeightedAverageCosting'";
		}
		if ($_SESSION['AutoIssue'] != $_POST['X_AutoIssue']) {
			$sql[] = "UPDATE config SET confvalue='" . $_POST['X_AutoIssue'] . "' WHERE confname='AutoIssue'";
		}
		if ($_SESSION['ProhibitNegativeStock'] != $_POST['X_ProhibitNegativeStock']) {
			$sql[] = "UPDATE config SET confvalue='" . $_POST['X_ProhibitNegativeStock'] . "' WHERE confname='ProhibitNegativeStock'";
		}
		if ($_SESSION['MonthsAuditTrail'] != $_POST['X_MonthsAuditTrail']) {
			$sql[] = "UPDATE config SET confvalue='" . $_POST['X_MonthsAuditTrail'] . "' WHERE confname='MonthsAuditTrail'";
		}
		if ($_SESSION['LogSeverity'] != $_POST['X_LogSeverity']) {
			$sql[] = "UPDATE config SET confvalue='" . $_POST['X_LogSeverity'] . "' WHERE confname='LogSeverity'";
		}
		if ($_SESSION['LogPath'] != $_POST['X_LogPath']) {
			$sql[] = "UPDATE config SET confvalue='" . $_POST['X_LogPath'] . "' WHERE confname='LogPath'";
		}
		if ($_SESSION['UpdateCurrencyRatesDaily'] != $_POST['X_UpdateCurrencyRatesDaily']) {
			if ($_POST['X_UpdateCurrencyRatesDaily'] == 1) {
				$sql[] = "UPDATE config SET confvalue='" . Date('Y-m-d') . "' WHERE confname='UpdateCurrencyRatesDaily'";
			} else {
				$sql[] = "UPDATE config SET confvalue='0' WHERE confname='UpdateCurrencyRatesDaily'";
			}
		}
		if ($_SESSION['ExchangeRateFeed'] != $_POST['X_ExchangeRateFeed']) {
			$sql[] = "UPDATE config SET confvalue='" . $_POST['X_ExchangeRateFeed'] . "' WHERE confname='ExchangeRateFeed'";
		}
		if ($_SESSION['FactoryManagerEmail'] != $_POST['X_FactoryManagerEmail']) {
			$sql[] = "UPDATE config SET confvalue='" . $_POST['X_FactoryManagerEmail'] . "' WHERE confname='FactoryManagerEmail'";
		}
		if ($_SESSION['PurchasingManagerEmail'] != $_POST['X_PurchasingManagerEmail']) {
			$sql[] = "UPDATE config SET confvalue='" . $_POST['X_PurchasingManagerEmail'] . "' WHERE confname='PurchasingManagerEmail'";
		}
		if ($_SESSION['InventoryManagerEmail'] != $_POST['X_InventoryManagerEmail']) {
			$sql[] = "UPDATE config SET confvalue='" . $_POST['X_InventoryManagerEmail'] . "' WHERE confname='InventoryManagerEmail'";
		}
		if ($_SESSION['AutoCreateWOs'] != $_POST['X_AutoCreateWOs']) {
			$sql[] = "UPDATE config SET confvalue='" . $_POST['X_AutoCreateWOs'] . "' WHERE confname='AutoCreateWOs'";
		}
		if ($_SESSION['DefaultFactoryLocation'] != $_POST['X_DefaultFactoryLocation']) {
			$sql[] = "UPDATE config SET confvalue='" . $_POST['X_DefaultFactoryLocation'] . "' WHERE confname='DefaultFactoryLocation'";
		}
		if ($_SESSION['DefineControlledOnWOEntry'] != $_POST['X_DefineControlledOnWOEntry']) {
			$sql[] = "UPDATE config SET confvalue='" . $_POST['X_DefineControlledOnWOEntry'] . "' WHERE confname='DefineControlledOnWOEntry'";
		}
		if ($_SESSION['FrequentlyOrderedItems'] != $_POST['X_FrequentlyOrderedItems']) {
			$sql[] = "UPDATE config SET confvalue='" . $_POST['X_FrequentlyOrderedItems'] . "' WHERE confname='FrequentlyOrderedItems'";
		}
		if ($_SESSION['AutoAuthorisePO'] != $_POST['X_AutoAuthorisePO']) {
			$sql[] = "UPDATE config SET confvalue='" . $_POST['X_AutoAuthorisePO'] . "' WHERE confname='AutoAuthorisePO'";
		}
		if (isset($_POST['X_ItemDescriptionLanguages'])) {
			$ItemDescriptionLanguages = '';
			foreach ($_POST['X_ItemDescriptionLanguages'] as $ItemLanguage) {
				$ItemDescriptionLanguages .= $ItemLanguage . ',';
			}
			$sql[] = "UPDATE config SET confvalue='" . $ItemDescriptionLanguages . "' WHERE confname='ItemDescriptionLanguages'";
			$_SESSION['ItemDescriptionLanguages'] = $ItemDescriptionLanguages;
		}
		if ($_SESSION['SmtpSetting'] != $_POST['X_SmtpSetting']) {
			$sql[] = "UPDATE config SET confvalue = '" . $_POST['X_SmtpSetting'] . "' WHERE confname='SmtpSetting'";
		}
		$ErrMsg = _('The system configuration could not be updated because');
		$result = DB_Txn_Begin($db);
		foreach ($sql as $line) {
			$result = DB_query($line, $db, $ErrMsg);
		}
		$result = DB_Txn_Commit($db);

		prnMsg(_('System configuration updated'), 'success');

		$ForceConfigReload = True; // Required to force a load even if stored in the session vars
		include('includes/GetConfig.php');
		$ForceConfigReload = False;
	} else {
		prnMsg(_('Validation failed') . ', ' . _('no updates or deletes took place'), 'warn');
	}

}
/* end of if submit */

$DateFormats['d/m/Y'] = 'd/m/Y';
$DateFormats['d.m.Y'] = 'd.m.Y';
$DateFormats['m/d/Y'] = 'm/d/Y';
$DateFormats['Y/m/d'] = 'Y/m/d';
$DateFormats['Y-m-d'] = 'Y-m-d';
$FormName = 'System1';
echo '<form name="' . $FormName . '" onSubmit="return VerifyForm(this);" method="post" class="noPrint standard" action="' . htmlspecialchars($_SERVER['PHP_SELF'], ENT_QUOTES, 'UTF-8') . '">';
echo '<input type="hidden" name="FormID" value="' . $_SESSION['FormID'] . '" />';
echo '<p class="page_title_text noPrint" ><img src="' . $RootPath . '/css/' . $Theme . '/images/maintenance.png" title="' . _('System Parameters') . '" alt="" />' . $Title . '</p>';

echo '<h3>' . _('General Settings') . '</h3>';

// DefaultDateFormat
Select($FormName, 'X_DefaultDateFormat', _('Default Date Format'), _('The default date format for entry of dates and display.'), True, $DateFormats, array(), $_SESSION['DefaultDateFormat']);

// DefaultTheme
$Themes = scandir('css/');
foreach ($Themes as $ThemeName) {
	if (is_dir('css/' . $ThemeName) and $ThemeName != '.' and $ThemeName != '..') {
		$UserThemes[$ThemeName] = $ThemeName;
	}
}
Select($FormName, 'X_DefaultTheme', _('New Users Default Theme'), _('The default theme is used for new users who have not yet defined the display colour scheme theme of their choice'), False, $UserThemes, array(), $_SESSION['DefaultTheme']);

echo '<h3>' . _('Accounts Receivable/Payable Settings') . '</h3>';

// PastDueDays1
InputInteger($FormName, 'X_PastDueDays1', _('First Overdue Deadline in (days)'), _('Customer and supplier balances are displayed as overdue by this many days. This parameter is used on customer and supplier enquiry screens and aged listings'), True, array(), $_SESSION['PastDueDays1'], False);

// PastDueDays2
InputInteger($FormName, 'X_PastDueDays2', _('Second Overdue Deadline in (days)'), _('As above but the next level of overdue'), True, array(), $_SESSION['PastDueDays2'], False);

// DefaultCreditLimit
InputInteger($FormName, 'X_DefaultCreditLimit', _('Default Credit Limit'), _('The default used in new customer set up'), True, array(), $_SESSION['DefaultCreditLimit'], False);

// Check Credit Limits
$CheckCreditLimits[0] = _('Do not check');
$CheckCreditLimits[1] = _('Warn on breach');
$CheckCreditLimits[2] = _('Prohibit Sales');
Select($FormName, 'X_CheckCreditLimits', _('Check Credit Limits'), _('Credit limits can be checked at order entry to warn only or to stop the order from being entered where it would take a customer account balance over their limit'), False, $CheckCreditLimits, array(), $_SESSION['CheckCreditLimits']);

// Show_Settled_LastMonth
$YesNo[0] = _('No');
$YesNo[1] = _('Yes');
Select($FormName, 'X_Show_Settled_LastMonth', _('Show Settled Last Month'), _('This setting refers to the format of customer statements. If the invoices and credit notes that have been paid and settled during the course of the current month should be shown then select Yes. Selecting No will only show currently outstanding invoices, credits and payments that have not been allocated.'), False, $YesNo, array(), $_SESSION['Show_Settled_LastMonth']);

//RomalpaClause
TextArea($FormName, 'X_RomalpaClause', _('Romalpa Clause'), _('This text appears on invoices and credit notes in small print. Normally a reservation of title clause that gives the company rights to collect goods which have not been paid for - to give some protection for bad debts.'), False, array(), $_SESSION['RomalpaClause']);

// QuickEntries
InputInteger($FormName, 'X_QuickEntries', _('Quick Entries'), _('This parameter defines the layout of the sales order entry screen. The number of fields available for quick entries. Any number from 1 to 99 can be entered.'), True, array(), $_SESSION['QuickEntries'], False);

// Frequently Ordered Items
InputInteger($FormName, 'X_FrequentlyOrderedItems', _('Frequently Ordered Items'), _('To show the most frequently ordered items enter the number of frequently ordered items you wish to display from 1 to 99. If you do not wish to display the frequently ordered item list enter 0.'), True, array(), $_SESSION['FrequentlyOrderedItems'], False);

// SO_AllowSameItemMultipleTimes
Select($FormName, 'X_SO_AllowSameItemMultipleTimes', _('Sales Order Allows Same Item Multiple Times'), _('Sales Order Allows Same Item Multiple Times.'), False, $YesNo, array(), $_SESSION['SO_AllowSameItemMultipleTimes']);

//'AllowOrderLineItemNarrative'
$AllowOrderLineItemNarrative[0] = _('No Narrative Line');
$AllowOrderLineItemNarrative[1] = _('Allow Narrative Entry');
Select($FormName, 'X_AllowOrderLineItemNarrative', _('Order Entry allows Line Item Narrative'), _('Select whether or not to allow entry of narrative on order line items. This narrative will appear on invoices and packing slips. Useful mainly for service businesses.'), False, $AllowOrderLineItemNarrative, array(), $_SESSION['AllowOrderLineItemNarrative']);

//ItemDescriptionLanguages
foreach ($LanguagesArray as $LanguageEntry => $LanguageName) {
	$Languages[$LanguageEntry] = $LanguageName['LanguageName'];
}
$ItemLanguages = explode(',', $_SESSION['ItemDescriptionLanguages']);
MultipleSelect($FormName, 'X_ItemDescriptionLanguages', _('Languages to Maintain Translations for Item Descriptions'), _('Select all the languages for which item description translations are to be maintained.'), False, $Languages, array(), $ItemLanguages);

//'RequirePickingNote'
Select($FormName, 'X_RequirePickingNote', _('A picking note must be produced before an order can be delivered'), _('Select whether or not a picking note must be produced before an order can be delivered to a customer.'), False, $YesNo, array(), $_SESSION['RequirePickingNote']);

//UpdateCurrencyRatesDaily
$UpdateCurrencyRatesDaily[0] = _('Manually');
$UpdateCurrencyRatesDaily[1] = _('Automatic');
Select($FormName, 'X_UpdateCurrencyRatesDaily', _('Auto Update Exchange Rates Daily'), _('Automatic updates to exchange rates will retrieve the latest daily rates from either the European Central Bank or Google once per day - when the first user logs in for the day. Manual will never update the rates automatically - exchange rates will need to be maintained manually'), False, $UpdateCurrencyRatesDaily, array(), $_SESSION['UpdateCurrencyRatesDaily']);

//ExchangeRateFeed
$ExchangeRateFeed['ECB'] = _('European Central Bank');
$ExchangeRateFeed['Google'] = _('Google');
Select($FormName, 'X_ExchangeRateFeed', _('Source Exchange Rates From'), _('Specify the source to use for exchange rates'), False, $ExchangeRateFeed, array(), $_SESSION['ExchangeRateFeed']);

//Default Packing Note Format
$PackNoteFormat[1] = _('Laser Printed');
$PackNoteFormat[2] = _('Special Stationery');
Select($FormName, 'X_PackNoteFormat', _('Format of Packing Slips'), _('Choose the format that packing notes should be printed by default'), False, $PackNoteFormat, array(), $_SESSION['PackNoteFormat']);

//Default Invoice Format
$InvoicePortraitFormat[0] = _('Landscape');
$InvoicePortraitFormat[1] = _('Portrait');
Select($FormName, 'X_InvoicePortraitFormat', _('Invoice Orientation'), _('Select the invoice layout'), False, $InvoicePortraitFormat, array(), $_SESSION['InvoicePortraitFormat']);

//Blind packing note
$DefaultBlindPackNote[1] = _('Show Company Details');
$DefaultBlindPackNote[2] = _('Hide Company Details');
Select($FormName, 'X_DefaultBlindPackNote', _('Show company details on packing slips'), _('Customer branches can be set by default not to print packing slips with the company logo and address. This is useful for companies that ship to customers customers and to show the source of the shipment would be inappropriate. There is an option on the setup of customer branches to ship blind, this setting is the default applied to all new customer branches'), False, $DefaultBlindPackNote, array(), $_SESSION['DefaultBlindPackNote']);

// Working days on a week
$WorkingDaysWeek[7] = '7 ' . _('working days');
$WorkingDaysWeek[6] = '6 ' . _('working days');
$WorkingDaysWeek[5] = '5 ' . _('working days');
Select($FormName, 'X_WorkingDaysWeek', _('Working Days in a Week'), _('Number of working days on a week'), False, $WorkingDaysWeek, array(), $_SESSION['WorkingDaysWeek']);

// DispatchCutOffTime
for ($i = 0; $i < 24; $i++) {
	$DispatchCutOffTime[$i] = $i . ' ' . _('Days');
}
Select($FormName, 'X_DispatchCutOffTime', _('Dispatch Cut-Off Time'), _('Orders entered after this time will default to be dispatched the following day, this can be over-ridden at the time of sales order entry'), False, $DispatchCutOffTime, array(), $_SESSION['DispatchCutOffTime']);

// AllowSalesOfZeroCostItems
Select($FormName, 'X_AllowSalesOfZeroCostItems', _('Allow Sales Of Zero Cost Items'), _('If an item selected at order entry does not have a cost set up then if this parameter is set to No then the order line will not be able to be entered'), False, $YesNo, array(), $_SESSION['AllowSalesOfZeroCostItems']);

// CreditingControlledItems_MustExist
Select($FormName, 'X_CreditingControlledItems_MustExist', _('Controlled Items Must Exist For Crediting'), _('This parameter relates to the behaviour of the controlled items code. If a serial numbered item has not previously existed then a credit note for it will not be allowed if this is set to Yes'), False, $YesNo, array(), $_SESSION['CreditingControlledItems_MustExist']);

// DefaultPriceList
$sql = "SELECT typeabbrev,
				sales_type
			FROM salestypes
			ORDER BY sales_type";
$ErrMsg = _('Could not load price lists');
$result = DB_query($sql, $db, $ErrMsg);
while ($row = DB_fetch_array($result)) {
	$PriceLists[$row['typeabbrev']] = $row['sales_type'];
}
Select($FormName, 'X_DefaultPriceList', _('Default Price List'), _('This price list is used as a last resort where there is no price set up for an item in the price list that the customer is set up for'), False, $PriceLists, array(), $_SESSION['DefaultPriceList']);

// Default_Shipper
$sql = "SELECT shipper_id,
				shippername
			FROM shippers
			ORDER BY shippername";
$ErrMsg = _('Could not load shippers');
$result = DB_query($sql, $db, $ErrMsg);
while ($row = DB_fetch_array($result)) {
	$Shippers[$row['shipper_id']] = $row['shippername'];
}
Select($FormName, 'X_Default_Shipper', _('Default Shipper'), _('This shipper is used where the best shipper for a customer branch has not been defined previously'), False, $Shippers, array(), $_SESSION['Default_Shipper']);

// DoFreightCalc
Select($FormName, 'X_DoFreightCalc', _('Do Freight Calculation'), _('If this is set to Yes then the system will attempt to calculate the freight cost of a dispatch based on the weight and cubic and the data defined for each shipper and their rates for shipping to various locations. The results of this calculation will only be meaningful if the data is entered for the item weight and volume in the stock item setup for all items and the freight costs for each shipper properly maintained.'), False, $YesNo, array(), $_SESSION['DoFreightCalc']);

//FreightChargeAppliesIfLessThan
InputInteger($FormName, 'X_FreightChargeAppliesIfLessThan', _('Apply freight charges if an order is less than'), _('This parameter is only effective if Do Freight Calculation is set to Yes. If it is set to 0 then freight is always charged. The total order value is compared to this value in deciding whether or not to charge freight'), False, array(), $_SESSION['FreightChargeAppliesIfLessThan'], False);

// AutoDebtorNo
$AutoDebtorNo[0] = _('Manual Entry');
$AutoDebtorNo[1] = _('Automatic');
Select($FormName, 'X_AutoDebtorNo', _('Create Debtor Codes Automatically'), _('Set to Automatic - customer codes are automatically created - as a sequential number'), False, $AutoDebtorNo, array(), $_SESSION['AutoDebtorNo']);

//==HJ== drop down list for tax category
$sql = "SELECT taxcatid,
				taxcatname
			FROM taxcategories
			ORDER BY taxcatname";
$ErrMsg = _('Could not load tax categories table');
$result = DB_query($sql, $db, $ErrMsg);
while ($row = DB_fetch_array($result)) {
	$DefaultTaxCategory[$row['taxcatid']] = $row['taxcatname'];
}
Select($FormName, 'X_DefaultTaxCategory', _('Default Tax Category'), _('This is the tax category used for entry of supplier invoices and the category at which freight attracts tax'), False, $DefaultTaxCategory, array(), $_SESSION['DefaultTaxCategory']);

//TaxAuthorityReferenceName
InputText($FormName, 'X_TaxAuthorityReferenceName', _('Tax Authority Reference Name'), _('This parameter is what is displayed on tax invoices and credits for the tax authority of the company eg. in Australian this would by A.B.N.: - in NZ it would be GST No: in the UK it would be VAT Regn. No'), 25, 25, False, array(), $_SESSION['TaxAuthorityReferenceName'], False);

// CountryOfOperation
Select($FormName, 'X_CountryOfOperation', _('Country Of Operation'), _('This parameter is only effective if Do Freight Calculation is set to Yes.'), False, $CountriesArray, array(), $_SESSION['CountryOfOperation']);

// StandardCostDecimalPlaces
for ($i = 0; $i <= 4; $i++) {
	$StandardCostDecimalPlaces[$i] = $i;
}
Select($FormName, 'X_StandardCostDecimalPlaces', _('Standard Cost Decimal Places'), _('Decimal Places to be used in Standard Cost'), False, $StandardCostDecimalPlaces, array(), $_SESSION['StandardCostDecimalPlaces']);

// NumberOfPeriodsOfStockUsage
for ($i = 0; $i <= 24; $i++) {
	$NumberOfPeriodsOfStockUsage[$i] = $i;
}
Select($FormName, 'X_NumberOfPeriodsOfStockUsage', _('Number Of Periods Of StockUsage'), _('In stock usage inquiries this determines how many periods of stock usage to show. An average is calculated over this many periods'), False, $NumberOfPeriodsOfStockUsage, array(), $_SESSION['NumberOfPeriodsOfStockUsage']);

//Show values on GRN
Select($FormName, 'X_ShowValueOnGRN', _('Show order values on GRN'), _('Should the value of the purchased stock be shown on the GRN screen'), False, $YesNo, array(), $_SESSION['ShowValueOnGRN']);

// Check_Qty_Charged_vs_Del_Qty
Select($FormName, 'X_Check_Qty_Charged_vs_Del_Qty', _('Check Quantity Charged vs Deliver Qty'), _('In entry of AP invoices this determines whether or not to check the quantities received into stock tie up with the quantities invoiced'), False, $YesNo, array(), $_SESSION['Check_Price_Charged_vs_Order_Price']);

// Check_Price_Charged_vs_Order_Price
Select($FormName, 'X_Check_Price_Charged_vs_Order_Price', _('Check Price Charged vs Order Price'), _('In entry of AP invoices this parameter determines whether or not to check invoice prices tie up to ordered prices'), False, $YesNo, array(), $_SESSION['Check_Price_Charged_vs_Order_Price']);

// OverChargeProportion
InputInteger($FormName, 'X_OverChargeProportion', _('Allowed Over Charge Proportion'), _('If check price charges vs Order price is set to yes then this proportion determines the percentage by which invoices can be overcharged with respect to price'), False, array(), $_SESSION['OverChargeProportion'], False);

// OverReceiveProportion
InputInteger($FormName, 'X_OverReceiveProportion', _('Allowed Over Receive Proportion'), _('If check quantity charged vs delivery quantity is set to yes then this proportion determines the percentage by which invoices can be overcharged with respect to delivery'), False, array(), $_SESSION['OverReceiveProportion'], False);

// PO_AllowSameItemMultipleTimes
Select($FormName, 'X_PO_AllowSameItemMultipleTimes', _('Purchase Order Allows Same Item Multiple Times'), _('If a purchase order can have the same item on the order several times this parameter should be set to yes'), False, $YesNo, array(), $_SESSION['PO_AllowSameItemMultipleTimes']);

// AutoAuthorisePO
Select($FormName, 'X_AutoAuthorisePO', _('Authorise purchase orders if user has authority'), _('If the user changing an existing purchase order or adding a new puchase order is set up to authorise purchase orders and the order is within their limit, then the purchase order status is automatically set to authorised'), False, $YesNo, array(), $_SESSION['AutoAuthorisePO']);

echo '<h3>' . _('General Settings') . '</h3>';

// YearEnd
$MonthNames = array(
	1 => _('January'),
	2 => _('February'),
	3 => _('March'),
	4 => _('April'),
	5 => _('May'),
	6 => _('June'),
	7 => _('July'),
	8 => _('August'),
	9 => _('September'),
	10 => _('October'),
	11 => _('November'),
	12 => _('December')
);
Select($FormName, 'X_YearEnd', _('Financial Year Ends At End Of'), _('Defining the month in which the financial year ends enables the system to provide useful defaults for general ledger reports'), False, $MonthNames, array(), $_SESSION['YearEnd']);

//PageLength
InputInteger($FormName, 'X_PageLength', _('Report Page Length'), _('Numberof lines to show on one page of a report.'), False, array(), $_SESSION['PageLength'], False);

//DefaultDisplayRecordsMax
InputInteger($FormName, 'X_DefaultDisplayRecordsMax', _('Default Maximum Number of Records to Show'), _('When pages have code to limit the number of returned records - such as select customer, select supplier and select item, then this will be the default number of records to show for a user who has not changed this for themselves in user settings.'), False, array(), $_SESSION['DefaultDisplayRecordsMax'], False);

// ShowStockidOnImage
Select($FormName, 'X_ShowStockidOnImages', _('Show Stockid on images'), _('Show the code inside the thumbnail image of the items'), False, $YesNo, array(), $_SESSION['ShowStockidOnImages']);

//MaxImageSize
InputInteger($FormName, 'X_MaxImageSize', _('Maximum Size in KB of uploaded images'), _('Picture files of items can be uploaded to the server. The system will check that files uploaded are less than this size (in KB) before they will be allowed to be uploaded. Large pictures will make the system slow and will be difficult to view in the stock maintenance screen.'), False, array(), $_SESSION['MaxImageSize'], False);

//NumberOfMonthMustBeShown
InputInteger($FormName, 'X_NumberOfMonthMustBeShown', _('Number Of Month Must Be Shown'), _('Number of month must be shown on report can be changed with this parameters ex: in CustomerInquiry.php '), False, array(), $_SESSION['NumberOfMonthMustBeShown'], False);

//part_pics_dir
$CompanyDirectory = 'companies/' . $_SESSION['DatabaseName'] . '/';
$DirHandle = dir($CompanyDirectory);
while ($DirEntry = $DirHandle->read()) {
	if (is_dir($CompanyDirectory . $DirEntry) and $DirEntry != '..' and $DirEntry != '.' and $DirEntry != 'locale' and $DirEntry != 'fonts') {
		$Directories[$DirEntry] = $DirEntry;
	}
}
Select($FormName, 'X_part_pics_dir', _('The directory where images are stored'), _('The directory under which all image files should be stored. Image files take the format of ItemCode.jpg - they must all be .jpg files and the part code will be the name of the image file. This is named automatically on upload. The system will check to ensure that the image is a .jpg file'), False, $Directories, array(), $_SESSION['part_pics_dir']);
Select($FormName, 'X_reports_dir', _('The directory where reports are stored'), _('The directory under which all report pdf files should be created in. A separate directory is recommended'), False, $Directories, array(), $_SESSION['reports_dir']);

// HTTPS_Only
Select($FormName, 'X_HTTPS_Only', _('Only allow secure socket connections'), _('Force connections to be only over secure sockets - ie encrypted data only'), False, $YesNo, array(), $_SESSION['HTTPS_Only']);

/*Perform Database maintenance DB_Maintenance*/
$DatabaseMaintenance[-1] = _('Allow SysAdmin Access Only');
$DatabaseMaintenance[0] = _('Never');
$DatabaseMaintenance[30] = _('Monthly');
$DatabaseMaintenance[7] = _('Weekly');
$DatabaseMaintenance[1] = _('Daily');
Select($FormName, 'X_DB_Maintenance', _('Perform database maintenance at logon'), _('Uses the function DB_Maintenance defined in ConnectDB_XXXX.inc to perform database maintenance tasks, to run at regular intervals - checked at each and every user login'), False, $DatabaseMaintenance, array(), $_SESSION['DB_Maintenance']);

$WikiApplications = array(
	_('Disabled')=>_('Disabled'),
	_('WackoWiki')=>_('WackoWiki'),
	_('MediaWiki')=>_('MediaWiki'),
	_('DokuWiki')=>_('DokuWiki')
);
Select($FormName, 'X_WikiApp', _('Wiki application'), _('This feature makes KwaMoja show links to a free form company knowledge base using a wiki. This allows sharing of important company information - about customers, suppliers and products and the set up of work flow menus and/or company procedures documentation'), False, $WikiApplications, array(), $_SESSION['WikiApp']);

InputText($FormName, 'X_WikiPath', _('Wiki Path'), _('The path to the wiki installation to form the basis of wiki URLs - or the full URL of the wiki.'), 40, 40, False, array(), $_SESSION['WikiPath'], False);

$Geocode[0] = _('Geocode Integration Disabled');
$Geocode[1] = _('Geocode Integration Enabled');
Select($FormName, 'X_geocode_integration', _('Geocode Customers and Suppliers'), _('This feature will give Latitude and Longitude coordinates to customers and suppliers. Requires access to a mapping provider. You must setup this facility under Main Menu - Setup - Geocode Setup. This feature is experimental.'), False, $Geocode, array(), $_SESSION['geocode_integration']);

$ExtendedCustomerInfo[0] = _('Extended Customer Info Disabled');
$ExtendedCustomerInfo[1] = _('Extended Customer Info Enabled');
Select($FormName, 'X_Extended_CustomerInfo', _('Extended Customer Information'), _('This feature will give extended information in the Select Customer screen.'), False, $ExtendedCustomerInfo, array(), $_SESSION['Extended_CustomerInfo']);

$ExtendedSupplierInfo[0] = _('Extended Supplier Info Disabled');
$ExtendedSupplierInfo[1] = _('Extended Supplier Info Enabled');
Select($FormName, 'X_Extended_SupplierInfo', _('Extended Supplier Information'), _('This feature will give extended information in the Select Supplier screen.'), False, $ExtendedSupplierInfo, array(), $_SESSION['Extended_SupplierInfo']);

$ProhibitJournals[0] = _('Allowed');
$ProhibitJournals[1] = _('Prohibited');
Select($FormName, 'X_ProhibitJournalsToControlAccounts', _('Prohibit GL Journals to Control Accounts'), _('Setting this to prohibited prevents accidentally entering a journal to the automatically posted and reconciled control accounts for creditors (AP) and debtors (AR)'), False, $ProhibitJournals, array(), $_SESSION['ProhibitJournalsToControlAccounts']);

$sql = "SELECT lastdate_in_period FROM periods orDER BY periodno DESC";
$ErrMsg = _('Could not load periods table');
$result = DB_query($sql, $db, $ErrMsg);
while ($PeriodRow = DB_fetch_row($result)) {
	$Periods[$PeriodRow[0]] = ConvertSQLDate($PeriodRow[0]);
}
Select($FormName, 'X_ProhibitPostingsBefore', _('Prohibit GL Journals to Periods Prior To'), _('This allows all periods before the selected date to be locked from postings. All postings for transactions dated prior to this date will be posted in the period following this date.'), False, $Periods, array(), $_SESSION['ProhibitPostingsBefore']);

$CostingMethods[0] = _('Standard Costing');
$CostingMethods[1] = _('Weighted Average Costing');
Select($FormName, 'X_WeightedAverageCosting', _('Prohibit GL Journals to Control Accounts'), _('KwaMoja allows inventory to be costed based on the weighted average of items in stock or full standard costing with price variances reported. The selection here determines the method used and the general ledger postings resulting from purchase invoices and shipment closing'), False, $CostingMethods, array(), $_SESSION['WeightedAverageCosting']);

Select($FormName,'X_AutoIssue', _('Auto Issue Components'), _('When items are manufactured it is possible for the components of the item to be automatically decremented from stock in accordance with the Bill of Material setting'), False, $YesNo, array(), False, $_SESSION['AutoIssue']);
Select($FormName,'X_ProhibitNegativeStock', _('Prohibit Negative Stock'), _('Setting this parameter to Yes prevents invoicing and the issue of stock if this would result in negative stock. The stock problem must be corrected before the invoice or issue is allowed to be processed.'), False, $YesNo, array(), False, $_SESSION['ProhibitNegativeStock']);

//Months of Audit Trail to Keep
InputInteger($FormName, 'X_MonthsAuditTrail', _('Months of Audit Trail to Retain'), _('If this parameter is set to 0 (zero) then no audit trail is retained. An audit trail is a log of which users performed which additions updates and deletes of database records. The full SQL is retained'), False, array(), $_SESSION['MonthsAuditTrail'], False);

//Which messages to log
$LogMessages[0] = _('None');
$LogMessages[1] = _('Errors Only');
$LogMessages[2] = _('Errors and Warnings');
$LogMessages[3] = _('Errors, Warnings and Info');
$LogMessages[4] = _('All');
Select($FormName,'X_LogSeverity', _('Log Severity Level'), _('Choose which Status messages to keep in your log file.'), False, $LogMessages, array(), False, $_SESSION['LogSeverity']);

//Path to keep log files in
InputText($FormName, 'X_LogPath', _('Path to log files'), _('The path to the directory where the log files will be stored. Note the apache user must have write permissions on this directory.'), 40, 79, False, array(), $_SESSION['LogPath']);

//DefineControlledOnWOEntry
Select($FormName,'X_DefineControlledOnWOEntry', _('Controlled Items Defined At Work Order Entry'), _('When set to yes, controlled items are defined at the time of the work order creation. Otherwise controlled items (serial numbers and batch/roll/lot references) are entered at the time the finished items are received against the work order'), False, $YesNo, array(), False, $_SESSION['DefineControlledOnWOEntry']);

//AutoCreateWOs
Select($FormName,'X_AutoCreateWOs', _('Auto Create Work Orders'), _('Setting this parameter to Yes will ensure that when a sales order is placed if there is insufficient stock then a new work order is created at the default factory location'), False, $YesNo, array(), False, $_SESSION['AutoCreateWOs']);

$sql = "SELECT loccode,
				locationname
			FROM locations
			ORDER BY locationname";
$ErrMsg = _('Could not load locations table');
$result = DB_query($sql, $db, $ErrMsg);
while ($LocationRow = DB_fetch_array($result)) {
	$Locations[$LocationRow['loccode']] = $LocationRow['locationname'];
}
Select($FormName, 'X_DefaultFactoryLocation', _('Default Factory Location'), _('This location is the location where work orders will be created from when the auto create work orders option is activated'), False, $Locations, array(), $_SESSION['DefaultFactoryLocation']);

InputEmail($FormName, 'X_FactoryManagerEmail', _('Factory Manager Email Address'), _('Work orders automatically created when sales orders are entered will be emailed to this address'), False, array(), $_SESSION['FactoryManagerEmail']);
InputEmail($FormName, 'X_PurchasingManagerEmail', _('Purchasing Manager Email Address'), _('The email address for the purchasing manager, used to receive notifications by the tendering system'), False, array(), $_SESSION['PurchasingManagerEmail']);
InputEmail($FormName, 'X_InventoryManagerEmail', _('Inventory Manager Email Address'), _('The email address for the inventory manager, where notifications of all manual stock adjustments created are sent by the system. Leave blank if no emails should be sent to the factory manager for manual stock adjustments'), False, array(), $_SESSION['InventoryManagerEmail']);

Select($FormName, 'X_SmtpSetting', _('Using Smtp Mail'), _('The default setting is using mail in default php.ini, if you choose Yes for this selection, you can use the SMTP set in the setup section.'), False, $YesNo, array(), $_SESSION['SmtpSetting']);

SubmitButton( _('Update The Mtuha System Settings'), 'Submit', 'submitbutton');
echo '</form>';

include('includes/footer.inc');
?>