<?php

include('includes/session.inc');
$Title = _('Patient Admission Form');
include('includes/header.inc');

/* Get list of hospital arrival codes for admission form */
$EncTypeSQL = "SELECT type_nr,
						description
					FROM type_encounter
					ORDER BY description";
$EncTypeResult = DB_query($EncTypeSQL, $db);
while ($EncTypeRow = DB_fetch_array($EncTypeResult)) {
	$TriageCodes[$EncTypeRow['type_nr']] = $EncTypeRow['description'];
}

/* Get list of stock items that can be used for admission form */
$StockItems = array(); /* Array needs to be initialised in case the query returns no items */
$StockSQL = "SELECT stockid,
				description
			FROM stockmaster
			INNER JOIN stockcategory
				ON stockcategory.categoryid=stockmaster.categoryid
			INNER JOIN stocktypes
				ON stockcategory.stocktype=stocktypes.type
			WHERE stocktypes.type='A'
			ORDER BY description";
$StockResult = DB_query($StockSQL, $db);
while ($StockRow = DB_fetch_array($StockResult)) {
	$StockItems[$StockRow['stockid']] = $StockRow['description'];
}

/* Get list of triage statuses for admission form */
$TriageStatuses[0] = _('White');
$TriageStatuses[1] = '<font color="green">' . _('Green') . '</font>';
$TriageStatuses[2] = '<font color="yellow">' . _('Yellow') . '</font>';
$TriageStatuses[3] = '<font color="red">' . _('Red') . '</font>';

$InOutPatient[0] = _('Inpatient');
$InOutPatient[1] = _('Outpatient');

/* Get list of insurance companies for admission form */
$InsuranceSQL = "SELECT debtorno,
						name
					FROM debtorsmaster
					WHERE typeid=127
					ORDER BY name";
$InsuranceResult = DB_query($InsuranceSQL, $db);
while ($InsuranceRow = DB_fetch_array($InsuranceResult)) {
	$InsuranceCompanies[$InsuranceRow['debtorno']] = $InsuranceRow['name'];
}

/* Get list of wards for admission form */
$WardsSQL = "SELECT nr,
					name
				FROM wards
				ORDER BY name";
$WardsResult = DB_query($WardsSQL, $db);
while ($WardsRow = DB_fetch_array($WardsResult)) {
	$Wards[$WardsRow['nr']] = $WardsRow['name'];
}

/* Get list of departments for admission form */
$DepartmentsSQL = "SELECT nr,
					name_formal
				FROM departments
				WHERE admit_outpatient=1
				ORDER BY name_formal";
$DepartmentsResult = DB_query($DepartmentsSQL, $db);
while ($DepartmentRow = DB_fetch_array($DepartmentsResult)) {
	$Departments[$DepartmentRow['nr']] = $DepartmentRow['name_formal'];
}

if (isset($_POST['Submit'])) {
	if ($_POST['Ward'] != '') {
		$InWard = 1;
	} else {
		$InWard = 0;
	}
	if ($_POST['Department'] != '') {
		$InDepartment = 1;
	} else {
		$InDepartment = 0;
	}
	if (empty($_POST['Edit'])) {
		$InsertSQL = "INSERT INTO encounter (pid,
											encounter_date,
											encounter_class_nr,
											encounter_status,
											referrer_diagnosis,
											referrer_recom_therapy,
											referrer_dr,
											referrer_notes,
											triage,
											admit_type,
											insurance_nr,
											insurance_firm_id,
											current_ward_nr,
											in_ward,
											current_dept_nr,
											in_dept,
											history,
											create_id,
											create_time
										) VALUES (
											'" . $_POST['PID'] . "',
											'" . $_POST['AdmissionDate'] . "',
											'" . $_POST['Triage'] . "',
											'" . _('Admitted') . "',
											'" . $_POST['Diagnosis'] . "',
											'" . $_POST['Therapy'] . "',
											'" . $_POST['ReferredBy'] . "',
											'" . $_POST['ReferrerNotes'] . "',
											'" . $_POST['TriageStatus'] . "',
											'" . $_POST['AdmissionClass'] . "',
											'" . $_POST['InsuranceNumber'] . "',
											'" . $_POST['Insurance'] . "',
											'" . $_POST['Ward'] . "',
											'" . $InWard . "',
											'" . $_POST['Department'] . "',
											'" . $InDepartment . "',
											'" . _('Admitted') . ' ' . date('Y-m-d H:i:s') . ' By ' . $_SESSION['UserID'] . "',
											'" . $_SESSION['UserID'] . "',
											'" . date('Y-m-d H:i:s') . "'
										)";
		$ErrMsg = _('An error occurred while admitting the patient');
		$DbgMsg = _('The SQL that was used to admit the patient was');
		$result = DB_query($InsertSQL, $db, $ErrMsg, $DbgMsg);
		for ($Counter = 0; $Counter < $_POST['Items']; $Counter++) {
			$InsertItemSQL = "INSERT INTO registrationitems (pid,
															itemid,
															quantity,
															date
														) VALUES (
															'" . $_POST['FileNumber'] . "',
															'" . $_POST['Item' . $Counter] . "',
															1,
															CURRENT_DATE
														)";
			$InsertItemResult = DB_query($InsertItemSQL, $db);
		}
	} else {
	}
} else {
	/* Get list of genders for registration form */
	$Genders['m'] = _('Male');
	$Genders['f'] = _('Female');

	$AdmissionEvents[] = 'onclick="SwapFields(this, \'Ward\', \'Department\')"';
	$ItemEvents[] = 'onchange="NewItem(this)"';
	$FormName = 'Admissions1';
	echo '<form name="' . $FormName . '" onSubmit="return VerifyForm(this);" action="' . htmlspecialchars($_SERVER['PHP_SELF'], ENT_QUOTES, 'UTF-8') . '" method="post" class="noPrint standard">';
	echo '<input type="hidden" name="FormID" value="' . $_SESSION['FormID'] . '" />';
	echo '<p class="page_title_text noPrint" >
			<img src="' . $RootPath . '/css/' . $Theme . '/images/admissions.png" title="' . $Title . '" alt="' . $Title . '" />' . ' ' . $Title . '
		</p>';
	$SelectSQL = "SELECT pid,
						name_first,
						name_middle,
						name_last,
						date_birth,
						sex,
						addr_str,
						addr_str_nr,
						addr_zip,
						addr_citytown_nr,
						address5,
						address6,
						blood_group,
						rh,
						insurance_nr,
						insurance_company
					FROM person
					WHERE pid='" . $_GET['PID'] . "'";
	$SelectResult = DB_query($SelectSQL, $db);
	$SelectRow = DB_fetch_array($SelectResult);
	if ($SelectRow['rh'] == 'pos') {
		$BloodGroup = $SelectRow['blood_group'] . '+';
	} else {
		$BloodGroup = $SelectRow['blood_group'] . '-';
	}
	$Address = $SelectRow['addr_str'] . "\n" . $SelectRow['addr_str_nr'] . "\n" . $SelectRow['addr_zip'] . "\n" . $SelectRow['addr_citytown_nr'] . "\n" . $SelectRow['address5'] . "\n" . $SelectRow['address6'];
	Text('AdmissionNumber', $FormName,  _('Admission Number'), _('Not yet admitted'));
	echo '<input type="hidden" name="PID" value="' . $_GET['PID'] . '" />';
	Text('FileNumber', $FormName, _('Hospital File Number'), $_GET['PID']);
	Text('Name', $FormName, _('Patient Name'), $SelectRow['name_first'] . ' '. $SelectRow['name_middle'] . ' '. $SelectRow['name_last']);
	Text('DateOfBirth', $FormName, _('Date Of Birth'), ConvertSQLDate($SelectRow['date_birth']));
	Text('Gender', $FormName, _('Gender'), $Genders[$SelectRow['sex']]);
	Text('BloodGroup', $FormName, _('Blood Group'), $BloodGroup);
	TextArea($FormName, 'PatientAddress', _('Patient Address'), _('The patients Address.'), False, array(), $Address, True);
	InputDate($FormName, 'AdmissionDate', _('Date of Admission'), _('The date the patient was admitted to the hospital.'), True, array(), date($_SESSION['DefaultDateFormat']), False);
	Select($FormName, 'Triage', _('Hospital Arrival Type'), _('How the patient arrived at the hospital.'), False, $TriageCodes, array());
	RadioGroup($FormName, 'TriageStatus', _('Triage Priority'), _('Triage status.'), $TriageStatuses);
	RadioGroup($FormName, 'AdmissionClass', _('Admission Class'), _('Is the patient to be admitted as an inpatient or outpatient.'), $InOutPatient, $AdmissionEvents, False);
	Select($FormName, 'Ward', _('Ward.'), _('The hospital ward where the patient will be admitted to..'), False, $Wards, array());
	Select($FormName, 'Department', _('Department.'), _('The hospital ward where the patient will be admitted to..'), False, $Departments, array());
	TextArea($FormName, 'Diagnosis', _('Referrers Diagnosis'), _('The diagnosis of referrer.'), False);
	InputText($FormName, 'ReferredBy', _('Referrered By'), _('Who is referring the patient.'), 30, 30, False);
	TextArea($FormName, 'Therapy', _('Therapy'), _('Any therapy the patient has been receiving.'), False);
	TextArea($FormName, 'ReferrerNotes', _('Referrer Notes'), _('Any notes received from the referrer.'), False);
	echo '<h3>' . _('Payment Details.') . '</h3>';
	Select($FormName, 'Insurance', _('Insurance Company'), _('Insurance company or none for cash paying patients.'), False, $InsuranceCompanies, array(), $SelectRow['insurance_company']);
	InputText($FormName, 'InsuranceNumber', _('Insurance Company Number'), _('Enter the insurance company number for this patient.'), 15, 15, False, array(), $SelectRow['insurance_nr']);
	if (count($StockItems) > 0) {
		echo '<h3>' . _('Billable Items.') . '</h3>';
		echo '<div id="SoldItems"></div>';
		echo '<div id="Count"></div>';
		Select($FormName, 'StockItem', _('Billable Item'), _('Any items that can be sold at registration time.'), False, $StockItems, $ItemEvents, $SelectRow['insurance_company']);
	}
	SubmitButton( _('Admit This Patient'), 'Submit', 'submitbutton');
	SubmitButton( _('Cancel Admission'), 'Cancel', 'cancelbutton');

	echo '</form>';
}

include('includes/footer.inc');

?>