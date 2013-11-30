<?php

include('includes/session.inc');
$Title = _('New Patient Registration Form');
include('includes/header.inc');
include('includes/barcodepack/class.code128.php');

/* Get list of tribes for registration form */
$CivilStatuses[0] = _('Single');
$CivilStatuses[1] = _('Married');
$CivilStatuses[2] = _('Divorced');
$CivilStatuses[3] = _('Widowed');
$CivilStatuses[4] = _('Separated');

/* Get list of tribes for registration form */
$sql = "SELECT tribe_id,
				tribe_name
			FROM tribes
			ORDER BY tribe_name";
$result = DB_query($sql, $db);
while ($myrow = DB_fetch_array($result)) {
	$Tribes[$myrow['tribe_id']] = $myrow['tribe_name'];
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
			WHERE stocktypes.type='R'
			ORDER BY description";
$StockResult = DB_query($StockSQL, $db);
while ($StockRow = DB_fetch_array($StockResult)) {
	$StockItems[$StockRow['stockid']] = $StockRow['description'];
}

if (isset($_POST['Submit'])) {
	$BloodGroup = substr($_POST['BloodGroup'], 0, strlen($_POST['BloodGroup'])-1);
	if (substr($_POST['BloodGroup'], -1) == '+') {
		$Rhesus = 'pos';
	} else {
		$Rhesus = 'neg';
	}
	if (empty($_POST['Edit'])) {
		$InsertSQL = "INSERT INTO person (  pid,
											date_reg,
											name_first,
											name_middle,
											name_last,
											ethnic_orig,
											date_birth,
											sex,
											religion,
											blood_group,
											rh,
											civil_status,
											insurance_nr,
											insurance_company,
											occupation,
											employer,
											allergy,
											addr_str,
											addr_str_nr,
											addr_zip,
											addr_citytown_nr,
											address5,
											address6,
											email,
											phone_1_nr,
											contact_person,
											contact_tel,
											contact_relation,
											create_id,
											create_time
										) VALUES (
											'" . $_POST['FileNumber'] . "',
											'" . date('Y-m-d H:i:s') . "',
											'" . $_POST['FirstName'] . "',
											'" . $_POST['MiddleName'] . "',
											'" . $_POST['LastName'] . "',
											'" . $_POST['Tribe'] . "',
											'" . FormatDateForSQL($_POST['DateOfBirth']) . "',
											'" . $_POST['Gender'] . "',
											'" . $_POST['Religion'] . "',
											'" . $BloodGroup . "',
											'" . $Rhesus . "',
											'" . $_POST['CivilStatus'] . "',
											'" . $_POST['InsuranceNumber'] . "',
											'" . $_POST['Insurance'] . "',
											'" . $_POST['Occupation'] . "',
											'" . $_POST['Employer'] . "',
											'" . $_POST['Allergies'] . "',
											'" . $_POST['Address1'] . "',
											'" . $_POST['Address2'] . "',
											'" . $_POST['Address3'] . "',
											'" . $_POST['Address4'] . "',
											'" . $_POST['Address5'] . "',
											'" . $_POST['Address6'] . "',
											'" . $_POST['Email'] . "',
											'" . $_POST['Telephone'] . "',
											'" . $_POST['FullName'] . "',
											'" . $_POST['NOKTelephone'] . "',
											'" . $_POST['Relationship'] . "',
											'" . $_SESSION['UserID'] . "',
											'" . date('Y-m-d H:i:s') . "'
										)";
		$ErrMsg = _('An error occurred while registering the patient');
		$DbgMsg = _('The SQL that was used to register the patient was');
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
		$_POST['FileNumber'] = $_POST['Edit'];
		$UpdateSQL = "UPDATE person SET name_first='" . $_POST['FirstName'] . "',
										name_middle='" . $_POST['MiddleName'] . "',
										name_last='" . $_POST['LastName'] . "',
										ethnic_orig='" . $_POST['Tribe'] . "',
										date_birth='" . FormatDateForSQL($_POST['DateOfBirth']) . "',
										sex='" . $_POST['Gender'] . "',
										religion='" . $_POST['Religion'] . "',
										blood_group='" . $BloodGroup . "',
										rh='" . $Rhesus . "',
										civil_status='" . $_POST['CivilStatus'] . "',
										insurance_company='" . $_POST['Insurance'] . "',
										insurance_nr='" . $_POST['InsuranceNumber'] . "',
										occupation='" . $_POST['Occupation'] . "',
										employer='" . $_POST['Employer'] . "',
										allergy='" . $_POST['Allergies'] . "',
										addr_str='" . $_POST['Address1'] . "',
										addr_str_nr='" . $_POST['Address2'] . "',
										addr_zip='" . $_POST['Address3'] . "',
										addr_citytown_nr='" . $_POST['Address4'] . "',
										address5='" . $_POST['Address5'] . "',
										address6='" . $_POST['Address6'] . "',
										email='" . $_POST['Email'] . "',
										phone_1_nr='" . $_POST['Telephone'] . "',
										contact_person='" . $_POST['FullName'] . "',
										contact_tel='" . $_POST['NOKTelephone'] . "',
										contact_relation='" . $_POST['Relationship'] . "',
										modify_id='" . $_SESSION['UserID'] . "',
										modify_time='" . date('Y-m-d H:i:s') . "'
									WHERE pid='" . $_POST['FileNumber'] . "'";
		$ErrMsg = _('An error occurred while registering the patient');
		$DbgMsg = _('The SQL that was used to register the patient was');
		$result = DB_query($UpdateSQL, $db, $ErrMsg, $DbgMsg);
	}
	if (DB_error_no($db) == 0) {
		prnMsg(_('The registration details were successfully saved'), 'success');
	} else {
		prnMsg(_('There was a problem saving the registration details'), 'error');
	}
	if (!file_exists('barcodes/' . $_POST['FileNumber'] . '.png')) {
		$BarCode = new code128($_POST['FileNumber']);
		$im = imagepng($BarCode->draw(true), 'barcodes/' . $_POST['FileNumber'] . '.png');
	}
	echo '<div id="SideMenu">
			<div id="dialog_header">' . _('Available options') . '</div>
			<div id="link"><img src="css/aguapop/images/printer.png" /><a class="menu" href="" onclick="window.print(); return false;">' . _('Print this page') . '</a></div>
			<div id="link"><img src="css/aguapop/images/register.png" /><a class="menu" href="Registration.php">' . _('Register another patient') . '</a></div>
			<div id="link"><img src="css/aguapop/images/magnifier.png" /><a class="menu" href="FindPerson.php">' . _('Search for a patient') . '</a></div>
			<div id="link"><img src="css/aguapop/images/modify.png" /><a class="menu" href="Registration.php?PID=' . $_POST['FileNumber'] . '">' . _('Modify this patients registration details') . '</a></div>
			<div id="link"><img src="css/aguapop/images/admissions.png" /><a class="menu" href="Admissions.php?PID=' . $_POST['FileNumber'] . '">' . _('Admit this patient to the hospital') . '</a></div>
		</div>';
	echo '<div id="PatientFileCover">
				<img class="barcode" src="barcodes/' . $_POST['FileNumber'] . '.png" />
				<div id="PatientDetails">' . ucwords($_POST['FirstName'] . ' ' . $_POST['MiddleName'] . ' ' . $_POST['LastName']) . '<br />';
	for ($i=0; $i<6; $i++) {
		if ($_POST['Address' . ($i+1)] != '') {
			echo ucwords($_POST['Address' . ($i+1)]) . '<br />';
		}
	}
	echo '<br /><label id="PersonLine">' . _('PID') . ' : ' . $_POST['FileNumber'] . '</label>';
	echo '<label id="PersonLine">' . _('Ethnic Origin') . ' : ' . $Tribes[$_POST['Tribe']] . '</label><br />';
	echo '<label id="PersonLine">' . _('Date of birth') . ' : ' . $_POST['DateOfBirth'] . '</label>';
	if ($_POST['Gender'] == 'm') {
		$_POST['Gender'] = _('Male');
	} else {
		$_POST['Gender'] = _('Female');
	}
	echo '<label id="PersonLine">' . _('Gender') . ' : ' . $_POST['Gender'] . '</label>';
	echo '<br /><label id="PersonLine">' . _('Occupation') . ' : ' . $_POST['Occupation'] . '</label>';
	echo '<label id="PersonLine">' . _('Employer') . ' : ' . $_POST['Employer'] . '</label>';
	echo '<br /><label id="PersonLine">' . _('Blood Group') . ' : ' . $_POST['BloodGroup'] . '</label>';
	echo '<label id="PersonLine">' . _('Civil Status') . ' : ' . $CivilStatuses[$_POST['CivilStatus']] . '</label>';
	echo '<br /><label id="PersonLine">' . _('Telephone') . ' : ' . $_POST['Telephone'] . '</label>';
	echo '<label id="PersonLine">' . _('Email') . ' : ' . $_POST['Email'] . '</label>';
	echo '<br /><label id="PersonLine">' . _('NOK Name') . ' : ' . $_POST['FullName'] . '</label>';
	echo '<label id="PersonLine">' . _('NOK Relationship') . ' : ' . $_POST['Relationship'] . '</label>';
	echo '<br /></div>
		</div>';
	include('includes/footer.inc');
	exit;
} else {

	/* Get list of genders for registration form */
	$Genders['m'] = _('Male');
	$Genders['f'] = _('Female');

	/* Get list of religions for registration form */
	$sql = "SELECT nr,
					name
				FROM religions
				ORDER BY name";
	$result = DB_query($sql, $db);
	while ($myrow = DB_fetch_array($result)) {
		$Religions[$myrow['nr']] = $myrow['name'];
	}

	/* Get list of blood groups for registration form */
	$BloodGroups['A+'] = 'A+';
	$BloodGroups['A-'] = 'A-';
	$BloodGroups['B+'] = 'B+';
	$BloodGroups['B-'] = 'B-';
	$BloodGroups['AB+'] = 'AB+';
	$BloodGroups['AB-'] = 'AB-';
	$BloodGroups['O+'] = 'O+';
	$BloodGroups['O-'] = 'O-';

	/* Get list of insurance companies for registration form */
	$InsuranceSQL = "SELECT debtorno,
							name
						FROM debtorsmaster
						WHERE typeid=127
						ORDER BY name";
	$InsuranceResult = DB_query($InsuranceSQL, $db);
	while ($InsuranceRow = DB_fetch_array($InsuranceResult)) {
		$InsuranceCompanies[$InsuranceRow['debtorno']] = $InsuranceRow['name'];
	}
	$AgeEvents[] = 'onkeyup="setDatebyAge(this, DateOfBirth, \'' . $_SESSION['DefaultDateFormat'] . '\', \'en\')"';
	$ItemEvents[] = 'onchange="NewItem(this)"';
	$FormName = 'Registration1';
	echo '<form name="' . $FormName . '" onSubmit="return VerifyForm(this);" action="' . htmlspecialchars($_SERVER['PHP_SELF'], ENT_QUOTES, 'UTF-8') . '" method="post" class="noPrint standard">';
	echo '<input type="hidden" name="FormID" value="' . $_SESSION['FormID'] . '" />';
	echo '<p class="page_title_text noPrint" >
			<img src="' . $RootPath . '/css/' . $Theme . '/images/AddPatient.png" title="' . $Title . '" alt="' . $Title . '" />' . ' ' . $Title . '
		</p>';
	if (!isset($_GET['PID'])) {
		InputText($FormName, 'FileNumber', _('Hospital File Number'), _('Enter the hospital file number for this patient.'), 20, 20, True);
		$SelectRow['name_first'] = '';
		$SelectRow['name_middle'] = '';
		$SelectRow['name_last'] = '';
		$SelectRow['ethnic_orig'] = '';
		$SelectRow['date_birth'] = '';
		$SelectRow['sex'] = '';
		$SelectRow['religion'] = '';
		$SelectRow['blood_group'] = '';
		$SelectRow['rh'] = '';
		$SelectRow['civil_status'] = '-1';
		$SelectRow['insurance_nr'] = '';
		$SelectRow['insurance_company'] = '';
		$SelectRow['occupation'] = '';
		$SelectRow['employer'] = '';
		$SelectRow['allergy'] = '';
		$SelectRow['addr_str'] = '';
		$SelectRow['addr_str_nr'] = '';
		$SelectRow['addr_zip'] = '';
		$SelectRow['addr_citytown_nr'] = '';
		$SelectRow['address5'] = '';
		$SelectRow['address6'] = '';
		$SelectRow['email'] = '';
		$SelectRow['phone_1_nr'] = '';
		$SelectRow['contact_person'] = '';
		$SelectRow['contact_tel'] = '';
		$SelectRow['contact_relation'] = '';
		if ($SelectRow['rh'] == 'pos') {
			$BloodGroup = $SelectRow['blood_group'] . '+';
		} else {
			$BloodGroup = $SelectRow['blood_group'] . '-';
		}
		$AgeInYears = '';
		$DateOfBirth = '';
	} else {
		$SelectSQL = "SELECT pid,
							date_reg,
							name_first,
							name_middle,
							name_last,
							ethnic_orig,
							date_birth,
							sex,
							religion,
							blood_group,
							rh,
							civil_status,
							insurance_nr,
							insurance_company,
							occupation,
							employer,
							allergy,
							addr_str,
							addr_str_nr,
							addr_zip,
							addr_citytown_nr,
							address5,
							address6,
							email,
							phone_1_nr,
							contact_person,
							contact_tel,
							contact_relation,
							create_id,
							create_time
						FROM person
						WHERE pid='" . $_GET['PID'] . "'";
		$SelectResult = DB_query($SelectSQL, $db);
		$SelectRow = DB_fetch_array($SelectResult);
		if ($SelectRow['rh'] == 'pos') {
			$BloodGroup = $SelectRow['blood_group'] . '+';
		} else {
			$BloodGroup = $SelectRow['blood_group'] . '-';
		}
		$AgeInYears = date('Y') - substr($SelectRow['date_birth'], 0 , 4);
		echo '<input type="hidden" name="Edit" value="' . $SelectRow['pid'] . '" />';
		Text($FormName, 'FileNumber', _('Hospital File Number'), $SelectRow['pid']);
		$DateOfBirth = ConvertSQLDate($SelectRow['date_birth']);
	}
	InputText($FormName, 'FirstName', _('First Name'), _('The patients first or given name.'), 30, 30, True, array(), $SelectRow['name_first']);
	InputText($FormName, 'MiddleName', _('Middle Name'), _('The patients middle name if any.'), 30, 30, False, array(), $SelectRow['name_middle']);
	InputText($FormName, 'LastName', _('Last Name'), _('The patients surname or family name.'), 30, 30, False, array(), $SelectRow['name_last']);
	Select($FormName, 'Tribe', _('Tribe'), _('The patients tribe.'), False, $Tribes, array(), $SelectRow['ethnic_orig']);
	InputInteger($FormName, 'Age', _('Age in Years'), _('The patients age in years.'), False, $AgeEvents, $AgeInYears);
	InputDate($FormName, 'DateOfBirth', _('Date Of Birth'), _('The patients date of birth.'), False, array(), $DateOfBirth);
	Select($FormName, 'Gender', _('Gender'), _('The sex of the patient.'), False, $Genders, array(), $SelectRow['sex']);
	Select($FormName, 'Religion', _('Religion'), _('The Religion of the patient, if any.'), False, $Religions, array(), $SelectRow['religion']);
	Select($FormName, 'BloodGroup', _('Blood Group'), _('The blood group of the patient.'), False, $BloodGroups, array(), $BloodGroup);
	Select($FormName, 'CivilStatus', _('Civil Status'), _('The civil status of the patient.'), False, $CivilStatuses, array(), $SelectRow['civil_status']);
	Select($FormName, 'Insurance', _('Insurance Company'), _('Insurance company or none for cash paying patients.'), False, $InsuranceCompanies, array(), $SelectRow['insurance_company']);
	InputText($FormName, 'InsuranceNumber', _('Insurance Company Number'), _('Enter the insurance company number for this patient.'), 20, 20, False, array(), $SelectRow['insurance_nr']);
	InputText($FormName, 'Occupation', _('Occupation'), _('The patients occupation.'), 50, 50, False, array(), $SelectRow['occupation']);
	InputText($FormName, 'Employer', _('Employer'), _('The patients employers name.'), 50, 50, False, array(), $SelectRow['employer']);
	InputText($FormName, 'Allergies', _('Allergies'), _('Any allergies the patient suffers from.'), 30, 30, False, array(), $SelectRow['allergy']);
	InputText($FormName, 'Address1', _('First Address Line'), _('The first line of the patients address.'), 60, 60, False, array(), $SelectRow['addr_str']);
	InputText($FormName, 'Address2', _('Second Address Line'), _('The second line of the patients address.'), 50, 50, False, array(), $SelectRow['addr_str_nr']);
	InputText($FormName, 'Address3', _('Third Address Line'), _('The third line of the patients address.'), 50, 50, False, array(), $SelectRow['addr_zip']);
	InputText($FormName, 'Address4', _('Fourth Address Line'), _('The fourth line of the patients address.'), 50, 50, False, array(), $SelectRow['addr_citytown_nr']);
	InputText($FormName, 'Address5', _('Fifth Address Line'), _('The fifth line of the patients address.'), 40, 40, False, array(), $SelectRow['address5']);
	InputText($FormName, 'Address6', _('Sixth Address Line'), _('The sixth line of the patients address.'), 40, 40, False, array(), $SelectRow['address6']);
	InputEmail($FormName, 'Email', _('Email address'), _('The patients email address.'), False, array(), $SelectRow['email']);
	InputTelephone($FormName, 'Telephone', _('Phone number'), _('The patients phone number.'), False, array(), $SelectRow['phone_1_nr']);
	echo '<h3>' . _('Next of Kin Details.') . '</h3>';
	InputText($FormName, 'FullName', _('Full Name'), _('Full name of patients next of kin.'), 50, 50, False, array(), $SelectRow['contact_person']);
	InputText($FormName, 'Relationship', _('Relationship'), _('Relationship of next of kin to the patient.'), 25, 25, False, array(), $SelectRow['contact_relation']);
	InputTelephone($FormName, 'NOKTelephone', _('Phone number'), _('The contact phone number for the next of kin.'), False, array(), $SelectRow['contact_tel']);
	if (count($StockItems) > 0 and !isset($_GET['PID'])) {
		echo '<h3>' . _('Billable Items.') . '</h3>';
		echo '<div id="SoldItems"></div>';
		echo '<div id="Count"></div>';
		Select($FormName, 'StockItem', _('Billable Item'), _('Any items that can be sold at registration time.'), False, $StockItems, $ItemEvents, $SelectRow['insurance_company']);
	}
	SubmitButton( _('Save Patient Details'), 'Submit', 'submitbutton');
	SubmitButton( _('Cancel Registration'), 'Cancel', 'cancelbutton');
	echo '</form>';
}
include('includes/footer.inc');

?>