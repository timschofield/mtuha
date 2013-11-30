<?php

include('includes/session.inc');

$Title = _('Stock Category Maintenance');

include('includes/header.inc');

if (isset($_GET['SelectedCategory'])) {
	$SelectedCategory = mb_strtoupper($_GET['SelectedCategory']);
} else if (isset($_POST['SelectedCategory'])) {
	$SelectedCategory = mb_strtoupper($_POST['SelectedCategory']);
}

if (isset($_GET['DeleteProperty'])) {

	$ErrMsg = _('Could not delete the property') . ' ' . $_GET['DeleteProperty'] . ' ' . _('because');
	$sql = "DELETE FROM stockitemproperties WHERE stkcatpropid='" . $_GET['DeleteProperty'] . "'";
	$result = DB_query($sql, $db, $ErrMsg);
	$sql = "DELETE FROM stockcatproperties WHERE stkcatpropid='" . $_GET['DeleteProperty'] . "'";
	$result = DB_query($sql, $db, $ErrMsg);
	prnMsg(_('Deleted the property') . ' ' . $_GET['DeleteProperty'], 'success');
}

if (isset($_POST['UpdateProperties'])) {
	if ($_POST['PropertyCounter'] == 0 and $_POST['PropLabel0'] != '') {
		$_POST['PropertyCounter'] = 0;
	}

	for ($i = 0; $i <= $_POST['PropertyCounter']; $i++) {

		if (isset($_POST['PropReqSO' . $i]) and $_POST['PropReqSO' . $i] == true) {
			$_POST['PropReqSO' . $i] = 1;
		} else {
			$_POST['PropReqSO' . $i] = 0;
		}
		if (isset($_POST['PropNumeric' . $i]) and $_POST['PropNumeric' . $i] == true) {
			$_POST['PropNumeric' . $i] = 1;
		} else {
			$_POST['PropNumeric' . $i] = 0;
		}
		if ($_POST['PropID' . $i] == 'NewProperty' and mb_strlen($_POST['PropLabel' . $i]) > 0) {
			$sql = "INSERT INTO stockcatproperties (categoryid,
													label,
													controltype,
													defaultvalue,
													minimumvalue,
													maximumvalue,
													numericvalue,
													reqatsalesorder)
											VALUES ('" . $SelectedCategory . "',
													'" . $_POST['PropLabel' . $i] . "',
													'" . $_POST['PropControlType' . $i] . "',
													'" . $_POST['PropDefault' . $i] . "',
													'" . filter_number_format($_POST['PropMinimum' . $i]) . "',
													'" . filter_number_format($_POST['PropMaximum' . $i]) . "',
													'" . $_POST['PropNumeric' . $i] . "',
													'" . $_POST['PropReqSO' . $i] . "')";
			$ErrMsg = _('Could not insert a new category property for') . $_POST['PropLabel' . $i];
			$result = DB_query($sql, $db, $ErrMsg);
		} elseif ($_POST['PropID' . $i] != 'NewProperty') { //we could be amending existing properties
			$sql = "UPDATE stockcatproperties SET label ='" . $_POST['PropLabel' . $i] . "',
											  controltype = '" . $_POST['PropControlType' . $i] . "',
											  defaultvalue = '" . $_POST['PropDefault' . $i] . "',
											  minimumvalue = '" . filter_number_format($_POST['PropMinimum' . $i]) . "',
											  maximumvalue = '" . filter_number_format($_POST['PropMaximum' . $i]) . "',
											  numericvalue = '" . $_POST['PropNumeric' . $i] . "',
											  reqatsalesorder = '" . $_POST['PropReqSO' . $i] . "'
										WHERE stkcatpropid ='" . $_POST['PropID' . $i] . "'";
			$ErrMsg = _('Updated the stock category property for') . ' ' . $_POST['PropLabel' . $i];
			$result = DB_query($sql, $db, $ErrMsg);
		}
	}
}

if (isset($_POST['submit'])) {

	//initialise no input errors assumed initially before we test
	$InputError = 0;

	/* actions to take once the user has clicked the submit button
	ie the page has called itself with some user input */

	//first off validate inputs sensible

	$_POST['CategoryID'] = mb_strtoupper($_POST['CategoryID']);

	if (empty($_POST['PropertyCounter'])) {
		$_POST['PropertyCounter'] = 0;
	}
	if (mb_strlen($_POST['CategoryID']) > 6) {
		$InputError = 1;
		prnMsg(_('The Inventory Category code must be six characters or less long'), 'error');
	} elseif (mb_strlen($_POST['CategoryID']) == 0) {
		$InputError = 1;
		prnMsg(_('The Inventory category code must be at least 1 character but less than six characters long'), 'error');
	} elseif (mb_strlen($_POST['CategoryDescription']) > 20) {
		$InputError = 1;
		prnMsg(_('The Sales category description must be twenty characters or less long'), 'error');
	}
	$StockTypesSQL = "SELECT type,
							name
						FROM stocktypes
						WHERE type='" . $_POST['StockType'] . "'
						ORDER BY name";
	$StockTypesResult = DB_query($StockTypesSQL, $db);
	if (DB_num_rows($StockTypesResult) == 0) {
		$InputError = 1;
		prnMsg(_('You must select a valid stock type for this category'), 'error');
	}
	for ($i = 0; $i <= $_POST['PropertyCounter']; $i++) {
		if (isset($_POST['PropNumeric' . $i]) and $_POST['PropNumeric' . $i] == true) {
			if (!is_numeric(filter_number_format($_POST['PropMinimum' . $i]))) {
				$InputError = 1;
				prnMsg(_('The minimum value is expected to be a numeric value'), 'error');
			}
			if (!is_numeric(filter_number_format($_POST['PropMaximum' . $i]))) {
				$InputError = 1;
				prnMsg(_('The maximum value is expected to be a numeric value'), 'error');
			}
		}
	} //check the properties are sensible

	if (isset($SelectedCategory) and $InputError != 1) {

		/*SelectedCategory could also exist if submit had not been clicked this code
		would not run in this case cos submit is false of course  see the
		delete code below*/

		$sql = "UPDATE stockcategory SET stocktype = '" . $_POST['StockType'] . "',
									 categorydescription = '" . $_POST['CategoryDescription'] . "',
									 defaulttaxcatid = '" . $_POST['DefaultTaxCatID'] . "',
									 stockact = '" . $_POST['StockAct'] . "',
									 adjglact = '" . $_POST['AdjGLAct'] . "',
									 issueglact = '" . $_POST['IssueGLAct'] . "',
									 purchpricevaract = '" . $_POST['PurchPriceVarAct'] . "',
									 materialuseagevarac = '" . $_POST['MaterialUseageVarAc'] . "',
									 wipact = '" . $_POST['WIPAct'] . "'
								WHERE categoryid = '" . $SelectedCategory . "'";
		$ErrMsg = _('Could not update the stock category') . $_POST['CategoryDescription'] . _('because');
		$result = DB_query($sql, $db, $ErrMsg);

		if ($_POST['PropertyCounter'] == 0 and $_POST['PropLabel0'] != '') {
			$_POST['PropertyCounter'] = 0;
		}

		for ($i = 0; $i <= $_POST['PropertyCounter']; $i++) {

			if (isset($_POST['PropReqSO' . $i]) and $_POST['PropReqSO' . $i] == true) {
				$_POST['PropReqSO' . $i] = 1;
			} else {
				$_POST['PropReqSO' . $i] = 0;
			}
			if (isset($_POST['PropNumeric' . $i]) and $_POST['PropNumeric' . $i] == true) {
				$_POST['PropNumeric' . $i] = 1;
			} else {
				$_POST['PropNumeric' . $i] = 0;
			}
			if ($_POST['PropID' . $i] == 'NewProperty' and mb_strlen($_POST['PropLabel' . $i]) > 0) {
				$sql = "INSERT INTO stockcatproperties (categoryid,
														label,
														controltype,
														defaultvalue,
														minimumvalue,
														maximumvalue,
														numericvalue,
														reqatsalesorder)
											VALUES ('" . $SelectedCategory . "',
													'" . $_POST['PropLabel' . $i] . "',
													'" . $_POST['PropControlType' . $i] . "',
													'" . $_POST['PropDefault' . $i] . "',
													'" . filter_number_format($_POST['PropMinimum' . $i]) . "',
													'" . filter_number_format($_POST['PropMaximum' . $i]) . "',
													'" . $_POST['PropNumeric' . $i] . "',
													'" . $_POST['PropReqSO' . $i] . "')";
				$ErrMsg = _('Could not insert a new category property for') . $_POST['PropLabel' . $i];
				$result = DB_query($sql, $db, $ErrMsg);
			} elseif ($_POST['PropID' . $i] != 'NewProperty') { //we could be amending existing properties
				$sql = "UPDATE stockcatproperties SET label ='" . $_POST['PropLabel' . $i] . "',
													  controltype = '" . $_POST['PropControlType' . $i] . "',
													  defaultvalue = '" . $_POST['PropDefault' . $i] . "',
													  minimumvalue = '" . filter_number_format($_POST['PropMinimum' . $i]) . "',
													  maximumvalue = '" . filter_number_format($_POST['PropMaximum' . $i]) . "',
													  numericvalue = '" . $_POST['PropNumeric' . $i] . "',
													  reqatsalesorder = '" . $_POST['PropReqSO' . $i] . "'
												WHERE stkcatpropid ='" . $_POST['PropID' . $i] . "'";
				$ErrMsg = _('Updated the stock category property for') . ' ' . $_POST['PropLabel' . $i];
				$result = DB_query($sql, $db, $ErrMsg);
			}

		} //end of loop round properties

		prnMsg(_('Updated the stock category record for') . ' ' . stripslashes($_POST['CategoryDescription']), 'success');
		unset($SelectedCategory);
		unset($_POST['CategoryID']);
		unset($_POST['StockType']);
		unset($_POST['CategoryDescription']);
		unset($_POST['StockAct']);
		unset($_POST['AdjGLAct']);
		unset($_POST['IssueGLAct']);
		unset($_POST['PurchPriceVarAct']);
		unset($_POST['MaterialUseageVarAc']);
		unset($_POST['WIPAct']);
	} elseif ($InputError != 1) {

		/*Selected category is null cos no item selected on first time round so must be adding a	record must be submitting new entries in the new stock category form */

		$sql = "INSERT INTO stockcategory (categoryid,
											stocktype,
											categorydescription,
											defaulttaxcatid,
											stockact,
											adjglact,
											issueglact,
											purchpricevaract,
											materialuseagevarac,
											wipact)
										VALUES (
											'" . $_POST['CategoryID'] . "',
											'" . $_POST['StockType'] . "',
											'" . $_POST['CategoryDescription'] . "',
											'" . $_POST['DefaultTaxCatID'] . "',
											'" . $_POST['StockAct'] . "',
											'" . $_POST['AdjGLAct'] . "',
											'" . $_POST['IssueGLAct'] . "',
											'" . $_POST['PurchPriceVarAct'] . "',
											'" . $_POST['MaterialUseageVarAc'] . "',
											'" . $_POST['WIPAct'] . "')";
		$ErrMsg = _('Could not insert the new stock category') . $_POST['CategoryDescription'] . _('because');
		$result = DB_query($sql, $db, $ErrMsg);
		prnMsg(_('A new stock category record has been added for') . ' ' . $_POST['CategoryDescription'], 'success');

	}
	//run the SQL from either of the above possibilites

	unset($_POST['StockType']);
	unset($_POST['CategoryDescription']);
	unset($_POST['StockAct']);
	unset($_POST['AdjGLAct']);
	unset($_POST['IssueGLAct']);
	unset($_POST['PurchPriceVarAct']);
	unset($_POST['MaterialUseageVarAc']);
	unset($_POST['WIPAct']);


} elseif (isset($_GET['delete'])) {
	//the link to delete a selected record was clicked instead of the submit button

	// PREVENT DELETES IF DEPENDENT RECORDS IN 'StockMaster'

	$sql = "SELECT stockid FROM stockmaster WHERE stockmaster.categoryid='" . $SelectedCategory . "'";
	$result = DB_query($sql, $db);

	if (DB_num_rows($result) > 0) {
		prnMsg(_('Cannot delete this stock category because stock items have been created using this stock category') . '<br /> ' . _('There are') . ' ' . $myrow[0] . ' ' . _('items referring to this stock category code'), 'warn');

	} else {
		$sql = "SELECT stkcat FROM salesglpostings WHERE stkcat='" . $SelectedCategory . "'";
		$result = DB_query($sql, $db);

		if (DB_num_rows($result) > 0) {
			prnMsg(_('Cannot delete this stock category because it is used by the sales') . ' - ' . _('GL posting interface') . '. ' . _('Delete any records in the Sales GL Interface set up using this stock category first'), 'warn');
		} else {
			$sql = "SELECT stkcat FROM cogsglpostings WHERE stkcat='" . $SelectedCategory . "'";
			$result = DB_query($sql, $db);

			if (DB_num_rows($result) > 0) {
				prnMsg(_('Cannot delete this stock category because it is used by the cost of sales') . ' - ' . _('GL posting interface') . '. ' . _('Delete any records in the Cost of Sales GL Interface set up using this stock category first'), 'warn');
			} else {
				$sql = "DELETE FROM stockcategory WHERE categoryid='" . $SelectedCategory . "'";
				$result = DB_query($sql, $db);
				prnMsg(_('The stock category') . ' ' . $SelectedCategory . ' ' . _('has been deleted') . ' !', 'success');
				unset($SelectedCategory);
			}
		}
	} //end if stock category used in debtor transactions
}

if (!isset($SelectedCategory)) {

	/* It could still be the second time the page has been run and a record has been selected for modification - SelectedCategory will exist because it was sent with the new call. If its the first time the page has been displayed with no parameters
	then none of the above are true and the list of stock categorys will be displayed with
	links to delete or edit each. These will call the same page again and allow update/input
	or deletion of the records*/

	$sql = "SELECT categoryid,
					categorydescription,
					stocktype,
					stockact,
					adjglact,
					issueglact,
					purchpricevaract,
					materialuseagevarac,
					wipact
				FROM stockcategory";
	$result = DB_query($sql, $db);

	echo '<table class="selection">
			<tr>
				<th class="SortableColumn">' . _('Cat Code') . '</th>
				<th class="SortableColumn">' . _('Description') . '</th>
				<th class="SortableColumn">' . _('Type') . '</th>
				<th>' . _('Stock GL') . '</th>
				<th>' . _('Adjts GL') . '</th>
				<th>' . _('Issues GL') . '</th>
				<th>' . _('Price Var GL') . '</th>
				<th>' . _('Usage Var GL') . '</th>
				<th>' . _('WIP GL') . '</th>
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
		printf('<td>%s</td>
				<td>%s</td>
				<td>%s</td>
				<td class="number">%s</td>
				<td class="number">%s</td>
				<td class="number">%s</td>
				<td class="number">%s</td>
				<td class="number">%s</td>
				<td class="number">%s</td>
				<td><a href="%sSelectedCategory=%s">' . _('Edit') . '</td>
				<td><a href="%sSelectedCategory=%s&delete=yes" onclick="return MakeConfirm("' . _('Are you sure you wish to delete this stock category? Additional checks will be performed before actual deletion to ensure data integrity is not compromised.') . '", \'Confirm Delete\', this);">' . _('Delete') . '</td>
			</tr>', $myrow['categoryid'], $myrow['categorydescription'], $myrow['stocktype'], $myrow['stockact'], $myrow['adjglact'], $myrow['issueglact'], $myrow['purchpricevaract'], $myrow['materialuseagevarac'], $myrow['wipact'], htmlspecialchars($_SERVER['PHP_SELF'], ENT_QUOTES, 'UTF-8') . '?', $myrow['categoryid'], htmlspecialchars($_SERVER['PHP_SELF'], ENT_QUOTES, 'UTF-8') . '?', $myrow['categoryid']);
	}
	//END WHILE LIST LOOP
	echo '</table>';
} //end of ifs and buts!

$FormName = 'CategoryForm';
echo '<form name="' . $FormName . '" onSubmit="return VerifyForm(this);" action="' . htmlspecialchars($_SERVER['PHP_SELF'], ENT_QUOTES, 'UTF-8') . '" method="post" class="noPrint standard wide">';
echo '<input type="hidden" name="FormID" value="' . $_SESSION['FormID'] . '" />';
echo '<p class="page_title_text noPrint" >
		<img src="' . $RootPath . '/css/' . $Theme . '/images/admissions.png" title="' . $Title . '" alt="' . $Title . '" />' . ' ' . $Title . '
	</p>';

if (isset($SelectedCategory)) {
	//editing an existing stock category
	if (!isset($_POST['UpdateTypes'])) {
		$sql = "SELECT categoryid,
						stocktype,
						categorydescription,
						stockact,
						adjglact,
						issueglact,
						purchpricevaract,
						materialuseagevarac,
						wipact,
						defaulttaxcatid
					FROM stockcategory
					WHERE categoryid='" . $SelectedCategory . "'";

		$result = DB_query($sql, $db);
		$myrow = DB_fetch_array($result);

		$_POST['CategoryID'] = $myrow['categoryid'];
		$_POST['StockType'] = $myrow['stocktype'];
		$_POST['CategoryDescription'] = $myrow['categorydescription'];
		$_POST['StockAct'] = $myrow['stockact'];
		$_POST['AdjGLAct'] = $myrow['adjglact'];
		$_POST['IssueGLAct'] = $myrow['issueglact'];
		$_POST['PurchPriceVarAct'] = $myrow['purchpricevaract'];
		$_POST['MaterialUseageVarAc'] = $myrow['materialuseagevarac'];
		$_POST['WIPAct'] = $myrow['wipact'];
		$_POST['DefaultTaxCatID']  = $myrow['defaulttaxcatid'];
	}
	echo '<input type="hidden" name="SelectedCategory" value="' . $SelectedCategory . '" />';
	echo '<input type="hidden" name="CategoryID" value="' . $_POST['CategoryID'] . '" />';

} else { //end of if $SelectedCategory only do the else when a new record is being entered
	InputText($FormName, 'CategoryID', _('Category Code'), _('Enter an identifier for the new category'), 10, 10, False, array());
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

/* Get list of departments for admission form */
$StockTypesSQL = "SELECT type,
						name
					FROM stocktypes
					ORDER BY name";
$StockTypesResult = DB_query($StockTypesSQL, $db);
while ($StockTypesRow = DB_fetch_array($StockTypesResult)) {
	$StockTypes[$StockTypesRow['type']] = $StockTypesRow['name'];
}

if (!isset($_POST['CategoryDescription'])) {
	$_POST['CategoryDescription'] = '';
}

$TaxCatSQL = "SELECT taxcatid,
					taxcatname
				FROM taxcategories
				ORDER BY taxcatname";
$TaxCatResult = DB_query($TaxCatSQL, $db);
while ($TaxCatRow = DB_fetch_array($TaxCatResult)) {
	$TaxCategories[$TaxCatRow['taxcatid']] = $TaxCatRow['taxcatname'];
}

if (!isset($_POST['CategoryDescription'])) {
	$_POST['CategoryDescription'] = '';
}
InputText($FormName, 'CategoryDescription', _('Category Description'), _('A description of this stock category'), 20, 20, False, array(), $_POST['CategoryDescription']);
if (!isset($_POST['StockType'])) {
	$_POST['StockType'] = '';
}
Select($FormName, 'StockType', _('Stock Type'), _('Type of stock to be included in this category.'), False, $StockTypes, array(), $_POST['StockType']);

if (!isset($_POST['DefaultTaxCatID'])) {
	$_POST['DefaultTaxCatID'] = $_SESSION['DefaultTaxCategory'];
}
Select($FormName, 'DefaultTaxCatID', _('Default Tax Category'), _('Tax category to be used when selling items in this category.'), False, $TaxCategories, array(), $_POST['DefaultTaxCatID']);

if (!isset($_POST['StockAct'])) {
	$_POST['StockAct'] = '';
}
if (isset($_POST['StockType']) and $_POST['StockType'] == 'L') {
	Select($FormName, 'StockAct', _('Recovery GL Code'), _('The Profit and Loss account to be used for recovery of the labour cost.'), False, $PnLAccounts, array(), $_POST['StockAct']);
} else {
	Select($FormName, 'StockAct', _('Stock GL Code'), _('The Balance Sheet account to be used for the stock value.'), False, $BSAccounts, array(), $_POST['StockAct']);
}

if (!isset($_POST['WIPAct'])) {
	$_POST['WIPAct'] = '';
}
Select($FormName, 'WIPAct', _('WIP GL Code'), _('The Balance Sheet account to be used for the work in progress stock value.'), False, $BSAccounts, array(), $_POST['WIPAct']);

if (!isset($_POST['AdjGLAct'])) {
	$_POST['AdjGLAct'] = '';
}
Select($FormName, 'AdjGLAct', _('Stock Adjustments GL Code'), _('The Profit and Loss account to be used for the value of stock adjustments.'), False, $PnLAccounts, array(), $_POST['AdjGLAct']);

if (!isset($_POST['IssueGLAct'])) {
	$_POST['IssueGLAct'] = '';
}
Select($FormName, 'IssueGLAct', _('Internal Stock Issues GL Code'), _('The Profit and Loss account to be used for the issue of stock on internal stock requests.'), False, $PnLAccounts, array(), $_POST['IssueGLAct']);

if (!isset($_POST['PurchPriceVarAct'])) {
	$_POST['PurchPriceVarAct'] = '';
}
Select($FormName, 'PurchPriceVarAct', _('Price Variance GL Code'), _('The Profit and Loss account to be used for price variances.'), False, $PnLAccounts, array(), $_POST['PurchPriceVarAct']);

if (!isset($_POST['MaterialUseageVarAc'])) {
	$_POST['MaterialUseageVarAc'] = '';
}
if (isset($_POST['StockType']) and $_POST['StockType'] == 'L') {
	Select($FormName, 'MaterialUseageVarAc', _('Labour Efficiency Variance GL Code'), _('The Profit and Loss account to be used for the labour efficiency variances.'), False, $PnLAccounts, array(), $_POST['MaterialUseageVarAc']);
} else {
	Select($FormName, 'MaterialUseageVarAc', _('Usage Variance GL Code'), _('The Profit and Loss account to be used for the usage variances.'), False, $PnLAccounts, array(), $_POST['MaterialUseageVarAc']);
}

if (isset($SelectedCategory)) {
	//editing an existing stock category

	$sql = "SELECT stkcatpropid,
					label,
					controltype,
					defaultvalue,
					numericvalue,
					reqatsalesorder,
					minimumvalue,
					maximumvalue
				FROM stockcatproperties
				WHERE categoryid='" . $SelectedCategory . "'
				ORDER BY stkcatpropid";

	$result = DB_query($sql, $db);

	echo '<table>
			<tr>
				<th>' . _('Property Label') . '</th>
				<th>' . _('Control Type') . '</th>
				<th>' . _('Default Value') . '</th>
				<th>' . _('Numeric Value') . '</th>
				<th>' . _('Minimum Value') . '</th>
				<th>' . _('Maximum Value') . '</th>
				<th>' . _('Require in SO') . '</th>
			</tr>';
	$PropertyCounter = 0;
	while ($myrow = DB_fetch_array($result)) {
		echo '<input type="hidden" name="PropID' . $PropertyCounter . '" value="' . $myrow['stkcatpropid'] . '" />';
		echo '<tr>
				<td><input type="text" name="PropLabel' . $PropertyCounter . '" size="50" minlength="0" maxlength="100" value="' . $myrow['label'] . '" /></td>
				<td><select minlength="0" name="PropControlType' . $PropertyCounter . '">';
		if ($myrow['controltype'] == 0) {
			echo '<option selected="selected" value="0">' . _('Text Box') . '</option>';
		} else {
			echo '<option value="0">' . _('Text Box') . '</option>';
		}
		if ($myrow['controltype'] == 1) {
			echo '<option selected="selected" value="1">' . _('Select Box') . '</option>';
		} else {
			echo '<option value="1">' . _('Select Box') . '</option>';
		}
		if ($myrow['controltype'] == 2) {
			echo '<option selected="selected" value="2">' . _('Check Box') . '</option>';
		} else {
			echo '<option value="2">' . _('Check Box') . '</option>';
		}
		if ($myrow['controltype'] == 3) {
			echo '<option selected="selected" value="3">' . _('Date Box') . '</option>';
		} else {
			echo '<option value="3">' . _('Date Box') . '</option>';
		}
		echo '</select></td>
					<td><input type="text" name="PropDefault' . $PropertyCounter . '" value="' . $myrow['defaultvalue'] . '" /></td>';

		if ($myrow['numericvalue'] == 1) {
			echo '<td><input type="checkbox" name="PropNumeric' . $PropertyCounter . '" checked="checked" /></td>';
		} else {
			echo '<td><input type="checkbox" name="PropNumeric' . $PropertyCounter . '" /></td>';
		}

		echo '<td><input type="text" name="PropMinimum' . $PropertyCounter . '" value="' . $myrow['minimumvalue'] . '" /></td>
				<td><input type="text" name="PropMaximum' . $PropertyCounter . '" value="' . $myrow['maximumvalue'] . '" /></td>';

		if ($myrow['reqatsalesorder'] == 1) {
			echo '<td align="center"><input type="checkbox" name="PropReqSO' . $PropertyCounter . '" checked="True" /></td>';
		} else {
			echo '<td align="center"><input type="checkbox" name="PropReqSO' . $PropertyCounter . '" /></td>';
		}

		echo '<td><a href="' . htmlspecialchars($_SERVER['PHP_SELF'], ENT_QUOTES, 'UTF-8') . '?DeleteProperty=' . $myrow['stkcatpropid'] . '&SelectedCategory=' . $SelectedCategory . '" onclick=\'return MakeConfirm("' . _('Are you sure you wish to delete this property? All properties of this type set up for stock items will also be deleted.') . '", \'Confirm Delete\', this);\'>' . _('Delete') . '</td>
			</tr>';

		$PropertyCounter++;
	} //end loop around defined properties for this category
	echo '<input type="hidden" name="PropID' . $PropertyCounter . '" value="NewProperty" />';
	echo '<tr>
			<td><input type="text" name="PropLabel' . $PropertyCounter . '" size="50" minlength="0" maxlength="100" /></td>
			<td><select minlength="0" name="PropControlType' . $PropertyCounter . '">
				<option selected="selected" value="0">' . _('Text Box') . '</option>
				<option value="1">' . _('Select Box') . '</option>
				<option value="2">' . _('Check Box') . '</option>
				<option value="3">' . _('Date Box') . '</option>
				</select></td>
			<td><input type="text" name="PropDefault' . $PropertyCounter . '" /></td>
			<td><input type="checkbox" name="PropNumeric' . $PropertyCounter . '" /></td>
			<td><input type="text" class="number" name="PropMinimum' . $PropertyCounter . '" /></td>
			<td><input type="text" class="number" name="PropMaximum' . $PropertyCounter . '" /></td>
			<td align="center"><input type="checkbox" name="PropReqSO' . $PropertyCounter . '" /></td>
			<td><button type="submit" name="UpdateProperties">' . _('Add new Property') . '</button></td>
			</tr>';
	echo '</table>';
	echo '<input type="hidden" name="PropertyCounter" value="' . $PropertyCounter . '" />';

}
/* end if there is a category selected */

SubmitButton( _('Enter Information'), 'submit', 'submitbutton');

echo '</form>';

if (isset($SelectedCategory)) {
	echo '<div style="text-align: right"><a href="' . htmlspecialchars($_SERVER['PHP_SELF'], ENT_QUOTES, 'UTF-8') . '">' . _('Show All Stock Categories') . '</a></div>';
}

include('includes/footer.inc');
?>