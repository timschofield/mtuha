<?php

include('includes/session.inc');
$Title = _('Item Maintenance');
/* KwaMoja manual links before header.inc */
$ViewTopic = 'Inventory';
$BookMark = 'InventoryAddingItems';
include('includes/header.inc');
include('includes/SQL_CommonFunctions.inc');

$MBFlags['A'] = _('Assembly');
$MBFlags['K'] = _('Kit');
$MBFlags['M'] = _('Manufactured');
$MBFlags['G'] = _('Phantom');
$MBFlags['B'] = _('Purchased');
$MBFlags['D'] = _('Service/Labour');

/*If this form is called with the StockID then it is assumed that the stock item is to be modified */

if (isset($_GET['StockID'])) {
	$StockID = trim(mb_strtoupper($_GET['StockID']));
} elseif (isset($_POST['StockID'])) {
	$StockID = trim(mb_strtoupper($_POST['StockID']));
} else {
	$StockID = '';
}
$ItemDescriptionLanguages = explode(',', $_SESSION['ItemDescriptionLanguages']);

if (isset($StockID) and !isset($_POST['UpdateCategories'])) {
	$sql = "SELECT COUNT(stockid)
			FROM stockmaster
			WHERE stockid='" . $StockID . "'
			GROUP BY stockid";

	$result = DB_query($sql, $db);
	$myrow = DB_fetch_row($result);
	if ($myrow[0] == 0) {
		$New = 1;
	} else {
		$New = 0;
	}
}

if (isset($_POST['New'])) {
	$New = $_POST['New'];
}

if (isset($_FILES['ItemPicture']) and $_FILES['ItemPicture']['name'] != '') {

	$result = $_FILES['ItemPicture']['error'];
	$UploadTheFile = 'Yes'; //Assume all is well to start off with
	$filename = $_SESSION['part_pics_dir'] . '/' . $StockID . '.jpg';

	//But check for the worst
	if (mb_strtoupper(mb_substr(trim($_FILES['ItemPicture']['name']), mb_strlen($_FILES['ItemPicture']['name']) - 3)) != 'JPG') {
		prnMsg(_('Only jpg files are supported - a file extension of .jpg is expected'), 'warn');
		$UploadTheFile = 'No';
	} elseif ($_FILES['ItemPicture']['size'] > ($_SESSION['MaxImageSize'] * 1024)) { //File Size Check
		prnMsg(_('The file size is over the maximum allowed. The maximum size allowed in KB is') . ' ' . $_SESSION['MaxImageSize'], 'warn');
		$UploadTheFile = 'No';
	} elseif ($_FILES['ItemPicture']['type'] == 'text/plain') { //File Type Check
		prnMsg(_('Only graphics files can be uploaded'), 'warn');
		$UploadTheFile = 'No';
	} elseif ($_FILES['ItemPicture']['error'] == 6 ) {  //upload temp directory check
		prnMsg( _('No tmp directory set. You must have a tmp directory set in your PHP for upload of files.'), 'warn');
		$UploadTheFile ='No';
	} elseif (file_exists($filename)) {
		prnMsg(_('Attempting to overwrite an existing item image'), 'warn');
		$result = unlink($filename);
		if (!$result) {
			prnMsg(_('The existing image could not be removed'), 'error');
			$UploadTheFile = 'No';
		}
	}

	if ($UploadTheFile == 'Yes') {
		$result = move_uploaded_file($_FILES['ItemPicture']['tmp_name'], $filename);
		$message = ($result) ? _('File url') . '<a href="' . $filename . '">' . $filename . '</a>' : _('Something is wrong with uploading a file');
	}
}

if (isset($Errors)) {
	unset($Errors);
}
$Errors = array();
$InputError = 0;

if (isset($_POST['Submit'])) {

	//initialise no input errors assumed initially before we test

	/* actions to take once the user has clicked the submit button
	ie the page has called itself with some user input */

	//first off validate inputs sensible
	$i = 1;


	if (!isset($_POST['Description']) or mb_strlen($_POST['Description']) > 50 or mb_strlen($_POST['Description']) == 0) {
		$InputError = 1;
		prnMsg(_('The stock item description must be entered and be fifty characters or less long') . '. ' . _('It cannot be a zero length string either') . ' - ' . _('a description is required'), 'error');
		$Errors[$i] = 'Description';
		$i++;
	}
	if (mb_strlen($_POST['LongDescription']) == 0) {
		$InputError = 1;
		prnMsg(_('The stock item description cannot be a zero length string') . ' - ' . _('a long description is required'), 'error');
		$Errors[$i] = 'LongDescription';
		$i++;
	}
	if (mb_strlen($StockID) == 0) {
		$InputError = 1;
		prnMsg(_('The Stock Item code cannot be empty'), 'error');
		$Errors[$i] = 'StockID';
		$i++;
	}
	if (ContainsIllegalCharacters($StockID) or mb_strpos($StockID, ' ')) {
		$InputError = 1;
		prnMsg(_('The stock item code cannot contain any of the following characters') . " - ' &amp; + \" \\ ." . _('or a space'), 'error');
		$Errors[$i] = 'StockID';
		$i++;
		$StockID = '';
	}
	if (mb_strlen($_POST['Units']) > 20) {
		$InputError = 1;
		prnMsg(_('The unit of measure must be 20 characters or less long'), 'error');
		$Errors[$i] = 'Units';
		$i++;
	}
	if (mb_strlen($_POST['BarCode']) > 20) {
		$InputError = 1;
		prnMsg(_('The barcode must be 20 characters or less long'), 'error');
		$Errors[$i] = 'BarCode';
		$i++;
	}
	if (!is_numeric(filter_number_format($_POST['Volume']))) {
		$InputError = 1;
		prnMsg(_('The volume of the packaged item in cubic metres must be numeric'), 'error');
		$Errors[$i] = 'Volume';
		$i++;
	}
	if (filter_number_format($_POST['Volume']) < 0) {
		$InputError = 1;
		prnMsg(_('The volume of the packaged item must be a positive number'), 'error');
		$Errors[$i] = 'Volume';
		$i++;
	}
	if (!is_numeric(filter_number_format($_POST['GrossWeight']))) {
		$InputError = 1;
		prnMsg(_('The weight of the packaged item in KGs must be numeric'), 'error');
		$Errors[$i] = 'KGS';
		$i++;
	}
	if (filter_number_format($_POST['GrossWeight']) < 0) {
		$InputError = 1;
		prnMsg(_('The weight of the packaged item must be a positive number'), 'error');
		$Errors[$i] = 'KGS';
		$i++;
	}
	if (!is_numeric(filter_number_format($_POST['NetWeight']))) {
		$InputError = 1;
		prnMsg(_('The net weight of the item in Net Weight must be numeric'),'error');
		$Errors[$i] = 'NetWeight';
		$i++;
	}
	if (filter_number_format($_POST['NetWeight'])<0) {
		$InputError = 1;
		prnMsg(_('The net weight of the item must be a positive number'),'error');
		$Errors[$i] = 'NetWeight';
		$i++;
	}
	if (!is_numeric(filter_number_format($_POST['EOQ']))) {
		$InputError = 1;
		prnMsg(_('The economic order quantity must be numeric'), 'error');
		$Errors[$i] = 'EOQ';
		$i++;
	}
	if (filter_number_format($_POST['EOQ']) < 0) {
		$InputError = 1;
		prnMsg(_('The economic order quantity must be a positive number'), 'error');
		$Errors[$i] = 'EOQ';
		$i++;
	}
	if ($_POST['Controlled'] == 0 and $_POST['Serialised'] == 1) {
		$InputError = 1;
		prnMsg(_('The item can only be serialised if there is lot control enabled already') . '. ' . _('Batch control') . ' - ' . _('with any number of items in a lot/bundle/roll is enabled when controlled is enabled') . '. ' . _('Serialised control requires that only one item is in the batch') . '. ' . _('For serialised control') . ', ' . _('both controlled and serialised must be enabled'), 'error');
		$Errors[$i] = 'Serialised';
		$i++;
	}
	if (isset($_POST['NextSerialNo']) and $_POST['NextSerialNo'] != 0 and $_POST['Serialised'] == 0) {
		$InputError = 1;
		prnMsg(_('The item can only have automatically generated serial numbers if it is a serialised item'), 'error');
		$Errors[$i] = 'NextSerialNo';
		$i++;
	}
	if (isset($_POST['NextSerialNo']) and $_POST['NextSerialNo'] != 0 and $_POST['MBFlag'] != 'M') {
		$InputError = 1;
		prnMsg(_('The item can only have automatically generated serial numbers if it is a manufactured item'), 'error');
		$Errors[$i] = 'NextSerialNo';
		$i++;
	}
	if (($_POST['MBFlag'] == 'A' or $_POST['MBFlag'] == 'K' or $_POST['MBFlag'] == 'D' or $_POST['MBFlag'] == 'G') and $_POST['Controlled'] == 1) {

		$InputError = 1;
		prnMsg(_('Assembly/Kitset/Phantom/Service/Labour items cannot also be controlled items') . '. ' . _('Assemblies/Dummies/Phantom and Kitsets are not physical items and batch/serial control is therefore not appropriate'), 'error');
		$Errors[$i] = 'Controlled';
		$i++;
	}
	if (trim($_POST['CategoryID']) == '') {
		$InputError = 1;
		prnMsg(_('There are no inventory categories defined. All inventory items must belong to a valid inventory category,'), 'error');
		$Errors[$i] = 'CategoryID';
		$i++;
	}
	if (!is_numeric(filter_number_format($_POST['Pansize']))) {
		$InputError = 1;
		prnMsg(_('Pansize quantity must be numeric'), 'error');
		$Errors[$i] = 'Pansize';
		$i++;
	}
	if (!is_numeric(filter_number_format($_POST['ShrinkFactor']))) {
		$InputError = 1;
		prnMsg(_('Shrinkage factor quantity must be numeric'), 'error');
		$Errors[$i] = 'ShrinkFactor';
		$i++;
	}

	if ($InputError != 1) {
		if ($_POST['Serialised'] == 1) {
			/*Not appropriate to have several dp on serial items */
			$_POST['DecimalPlaces'] = 0;
		}
		if ($New == 0) {
			/*so its an existing one */

			/*first check on the changes being made we must disallow:
			- changes from manufactured or purchased to Service, Assembly or Kitset if there is stock			- changes from manufactured, kitset or assembly where a BOM exists
			*/
			$sql = "SELECT mbflag,
							controlled,
							serialised,
							materialcost+labourcost+overheadcost AS itemcost,
							stockcategory.stockact,
							stockcategory.wipact
					FROM stockmaster
					INNER JOIN stockcategory
					ON stockmaster.categoryid=stockcategory.categoryid
					WHERE stockid = '" . $StockID . "'";
			$MBFlagResult = DB_query($sql, $db);
			$myrow = DB_fetch_row($MBFlagResult);
			$OldMBFlag = $myrow[0];
			$OldControlled = $myrow[1];
			$OldSerialised = $myrow[2];
			$UnitCost = $myrow[3];
			$OldStockAccount = $myrow[4];
			$OldWIPAccount = $myrow[5];


			$sql = "SELECT SUM(locstock.quantity)
					FROM locstock
					WHERE stockid='" . $StockID . "'
					GROUP BY stockid";
			$result = DB_query($sql, $db);
			$StockQtyRow = DB_fetch_row($result);

			/*Now check the GL account of the new category to see if it is different to the old stock gl account */

			$result = DB_query("SELECT stockact,
										wipact
								FROM stockcategory
								WHERE categoryid='" . $_POST['CategoryID'] . "'", $db);
			$NewStockActRow = DB_fetch_array($result);
			$NewStockAct = $NewStockActRow['stockact'];
			$NewWIPAct = $NewStockActRow['wipact'];

			if ($OldMBFlag != $_POST['MBFlag']) {
				if (($OldMBFlag == 'M' or $OldMBFlag == 'B') and ($_POST['MBFlag'] == 'A' or $_POST['MBFlag'] == 'K' or $_POST['MBFlag'] == 'D' or $_POST['MBFlag'] == 'G')) {
					/*then need to check that there is no stock holding first */
					/* stock holding OK for phantom (ghost) items */
					if ($StockQtyRow[0] != 0 and $OldMBFlag != 'G') {
						$InputError = 1;
						prnMsg(_('The make or buy flag cannot be changed from') . ' ' . $OldMBFlag . ' ' . _('to') . ' ' . $_POST['MBFlag'] . ' ' . _('where there is a quantity of stock on hand at any location') . '. ' . _('Currently there are') . ' ' . $StockQtyRow[0] . ' ' . _('on hand'), 'errror');
					}
					/* don't allow controlled/serialized  */
					if ($_POST['Controlled'] == 1) {
						$InputError = 1;
						prnMsg(_('The make or buy flag cannot be changed from') . ' ' . $OldMBFlag . ' ' . _('to') . ' ' . $_POST['MBFlag'] . ' ' . _('where the item is to be lot controlled') . '. ' . _('Kitset, phantom, dummy and assembly items cannot be lot controlled'), 'error');
					}
				}
				/*now check that if the item is being changed to a kitset, there are no items on sales orders or purchase orders*/
				if ($_POST['MBFlag'] == 'K') {
					$sql = "SELECT quantity-qtyinvoiced
							FROM salesorderdetails
							WHERE stkcode = '" . $StockID . "'
							AND completed=0";

					$result = DB_query($sql, $db);
					$ChkSalesOrds = DB_fetch_row($result);
					if ($ChkSalesOrds[0] != 0) {
						$InputError = 1;
						prnMsg(_('The make or buy flag cannot be changed to a kitset where there is a quantity outstanding to be delivered on sales orders') . '. ' . _('Currently there are') . ' ' . $ChkSalesOrds[0] . ' ' . _('outstanding'), 'error');
					}
				}
				/*now check that if it is to be a kitset or assembly or dummy there is no quantity on purchase orders outstanding*/
				if ($_POST['MBFlag'] == 'K' or $_POST['MBFlag'] == 'A' or $_POST['MBFlag'] == 'D') {

					$sql = "SELECT quantityord-quantityrecd
							FROM purchorderdetails INNER JOIN purchorders
							ON purchorders.orderno=purchorderdetails.orderno
							WHERE itemcode = '" . $StockID . "'
							AND purchorderdetails.completed=0
							AND purchorders.status<>'Cancelled'
							AND purchorders.status<>'Completed'
							AND purchorders.status<>'Rejected'";

					$result = DB_query($sql, $db);
					$ChkPurchOrds = DB_fetch_row($result);
					if ($ChkPurchOrds[0] != 0) {
						$InputError = 1;
						prnMsg(_('The make or buy flag cannot be changed to') . ' ' . $_POST['MBFlag'] . ' ' . _('where there is a quantity outstanding to be received on purchase orders') . '. ' . _('Currently there are') . ' ' . $ChkPurchOrds[0] . ' ' . _('yet to be received') . 'error');
					}
				}

				/*now check that if it was a Manufactured, Kitset, Phantom or Assembly and is being changed to a purchased or dummy - that no BOM exists */
				if (($OldMBFlag == 'M' or $OldMBFlag == 'K' or $OldMBFlag == 'A' or $OldMBFlag == 'G') and ($_POST['MBFlag'] == 'B' or $_POST['MBFlag'] == 'D')) {
					$sql = "SELECT COUNT(*)
							FROM bom
							WHERE parent = '" . $StockID . "'
							GROUP BY parent";
					$result = DB_query($sql, $db);
					$ChkBOM = DB_fetch_row($result);
					if ($ChkBOM[0] != 0) {
						$InputError = 1;
						prnMsg(_('The make or buy flag cannot be changed from manufactured, kitset or assembly to') . ' ' . $_POST['MBFlag'] . ' ' . _('where there is a bill of material set up for the item') . '. ' . _('Bills of material are not appropriate for purchased or dummy items'), 'error');
					}
				}

				/*now check that if it was Manufac, Phantom or Purchased and is being changed to assembly or kitset, it is not a component on an existing BOM */
				if (($OldMBFlag == 'M' or $OldMBFlag == 'B' or $OldMBFlag == 'D' or $OldMBFlag == 'G') and ($_POST['MBFlag'] == 'A' or $_POST['MBFlag'] == 'K')) {
					$sql = "SELECT COUNT(*)
							FROM bom
							WHERE component = '" . $StockID . "'
							GROUP BY component";
					$result = DB_query($sql, $db);
					$ChkBOM = DB_fetch_row($result);
					if ($ChkBOM[0] != 0) {
						$InputError = 1;
						prnMsg(_('The make or buy flag cannot be changed from manufactured, purchased or dummy to a kitset or assembly where the item is a component in a bill of material') . '. ' . _('Assembly and kitset items are not appropriate as components in a bill of materials'), 'error');
					}
				}
			}

			/* Do some checks for changes in the Serial & Controlled setups */
			if ($OldControlled != $_POST['Controlled'] and $StockQtyRow[0] != 0) {
				$InputError = 1;
				prnMsg(_('You can not change a Non-Controlled Item to Controlled (or back from Controlled to non-controlled when there is currently stock on hand for the item'), 'error');

			}
			if ($OldSerialised != $_POST['Serialised'] and $StockQtyRow[0] != 0) {
				$InputError = 1;
				prnMsg(_('You can not change a Serialised Item to Non-Serialised (or vice-versa) when there is a quantity on hand for the item'), 'error');
			}
			/* Do some check for property input */

			for ($i = 0; $i < $_POST['PropertyCounter']; $i++) {
				if ($_POST['PropNumeric' . $i] == 1) {
					if (filter_number_format($_POST['PropValue' . $i]) < $_POST['PropMin' . $i] or filter_number_format($_POST['PropValue' . $i]) > $_POST['PropMax' . $i]) {
						$InputError = 1;
						prnMsg(_('The property value should between') . ' ' . $_POST['PropMin' . $i] . ' ' . _('and') . $_POST['PropMax' . $i], 'error');
					}
				}
			}



			if ($InputError == 0) {

				DB_Txn_Begin($db);
				if (!isset($_POST['NextSerialNo'])) {
					$_POST['NextSerialNo'] = '';
				}

				$sql = "UPDATE stockmaster
						SET longdescription='" . $_POST['LongDescription'] . "',
							description='" . $_POST['Description'] . "',
							discontinued='" . $_POST['Discontinued'] . "',
							controlled='" . $_POST['Controlled'] . "',
							serialised='" . $_POST['Serialised'] . "',
							perishable='" . $_POST['Perishable'] . "',
							categoryid='" . $_POST['CategoryID'] . "',
							units='" . $_POST['Units'] . "',
							mbflag='" . $_POST['MBFlag'] . "',
							eoq='" . filter_number_format($_POST['EOQ']) . "',
							volume='" . filter_number_format($_POST['Volume']) . "',
							grossweight='" . filter_number_format($_POST['GrossWeight']) . "',
							netweight='" . filter_number_format($_POST['NetWeight']) . "',
							barcode='" . $_POST['BarCode'] . "',
							discountcategory='" . $_POST['DiscountCategory'] . "',
							taxcatid='" . $_POST['TaxCat'] . "',
							decimalplaces='" . $_POST['DecimalPlaces'] . "',
							shrinkfactor='" . filter_number_format($_POST['ShrinkFactor']) . "',
							pansize='" . filter_number_format($_POST['Pansize']) . "',
							nextserialno='" . $_POST['NextSerialNo'] . "'
					WHERE stockid='" . $StockID . "'";

				$ErrMsg = _('The stock item could not be updated because');
				$DbgMsg = _('The SQL that was used to update the stock item and failed was');
				$result = DB_query($sql, $db, $ErrMsg, $DbgMsg, true);

				$ErrMsg = _('Could not update the language description because');
				$DbgMsg = _('The SQL that was used to update the language description and failed was');

				if (count($ItemDescriptionLanguages) > 0) {
					foreach ($ItemDescriptionLanguages as $DescriptionLanguage) {
						if ($DescriptionLanguage != '') {
							$result = DB_query("DELETE FROM stockdescriptiontranslations WHERE stockid='" . $StockID . "' AND language_id='" . $DescriptionLanguage . "'", $db, $ErrMsg, $DbgMsg, true);
							$result = DB_query("INSERT INTO stockdescriptiontranslations VALUES('" . $StockID . "','" . $DescriptionLanguage . "', '" . $_POST['Description_' . str_replace('.', '_', $DescriptionLanguage)] . "')", $db, $ErrMsg, $DbgMsg, true);
						}
					}
				}

				//delete any properties for the item no longer relevant with the change of category
				$result = DB_query("DELETE FROM stockitemproperties
									WHERE stockid ='" . $StockID . "'", $db, $ErrMsg, $DbgMsg, true);

				//now insert any item properties
				for ($i = 0; $i < $_POST['PropertyCounter']; $i++) {

					if ($_POST['PropType' . $i] == 2) {
						if ($_POST['PropValue' . $i] == 'on') {
							$_POST['PropValue' . $i] = 1;
						} else {
							$_POST['PropValue' . $i] = 0;
						}
					}
					if ($_POST['PropNumeric' . $i] == 1) {
						$_POST['PropValue' . $i] = filter_number_format($_POST['PropValue' . $i]);
					} else {
						$_POST['PropValue' . $i] = $_POST['PropValue' . $i];
					}
					$result = DB_query("INSERT INTO stockitemproperties (stockid,
																		stkcatpropid,
																		value)
														VALUES ('" . $StockID . "',
																'" . $_POST['PropID' . $i] . "',
																'" . $_POST['PropValue' . $i] . "')", $db, $ErrMsg, $DbgMsg, true);
				} //end of loop around properties defined for the category

				if ($OldStockAccount != $NewStockAct and $_SESSION['CompanyRecord']['gllink_stock'] == 1) {
					/*Then we need to make a journal to transfer the cost to the new stock account */
					$JournalNo = GetNextTransNo(0, $db); //enter as a journal
					$SQL = "INSERT INTO gltrans (type,
												typeno,
												trandate,
												periodno,
												account,
												narrative,
												amount)
										VALUES ( 0,
												'" . $JournalNo . "',
												'" . Date('Y-m-d') . "',
												'" . GetPeriod(Date($_SESSION['DefaultDateFormat']), $db, true) . "',
												'" . $NewStockAct . "',
												'" . $StockID . ' ' . _('Change stock category') . "',
												'" . ($UnitCost * $StockQtyRow[0]) . "')";
					$ErrMsg = _('The stock cost journal could not be inserted because');
					$DbgMsg = _('The SQL that was used to create the stock cost journal and failed was');
					$result = DB_query($SQL, $db, $ErrMsg, $DbgMsg, true);
					$SQL = "INSERT INTO gltrans (type,
												typeno,
												trandate,
												periodno,
												account,
												narrative,
												amount)
										VALUES ( 0,
												'" . $JournalNo . "',
												'" . Date('Y-m-d') . "',
												'" . GetPeriod(Date($_SESSION['DefaultDateFormat']), $db, true) . "',
												'" . $OldStockAccount . "',
												'" . $StockID . ' ' . _('Change stock category') . "',
												'" . (-$UnitCost * $StockQtyRow[0]) . "')";
					$result = DB_query($SQL, $db, $ErrMsg, $DbgMsg, true);

				}
				/* end if the stock category changed and forced a change in stock cost account */
				if ($OldWIPAccount != $NewWIPAct and $_SESSION['CompanyRecord']['gllink_stock'] == 1) {
					/*Then we need to make a journal to transfer the cost  of WIP to the new WIP account */
					/*First get the total cost of WIP for this category */

					$WOCostsResult = DB_query("SELECT workorders.costissued,
													SUM(woitems.qtyreqd * woitems.stdcost) AS costrecd
												FROM woitems INNER JOIN workorders
												ON woitems.wo = workorders.wo
												INNER JOIN stockmaster
												ON woitems.stockid=stockmaster.stockid
												WHERE stockmaster.stockid='" . $StockID . "'
												AND workorders.closed=0
												GROUP BY workorders.costissued", $db, _('Error retrieving value of finished goods received and cost issued against work orders for this item'));
					$WIPValue = 0;
					while ($WIPRow = DB_fetch_array($WOCostsResult)) {
						$WIPValue += ($WIPRow['costissued'] - $WIPRow['costrecd']);
					}
					if ($WIPValue != 0) {
						$JournalNo = GetNextTransNo(0, $db); //enter as a journal
						$SQL = "INSERT INTO gltrans (type,
													typeno,
													trandate,
													periodno,
													account,
													narrative,
													amount)
											VALUES ( 0,
													'" . $JournalNo . "',
													'" . Date('Y-m-d') . "',
													'" . GetPeriod(Date($_SESSION['DefaultDateFormat']), $db, true) . "',
													'" . $NewWIPAct . "',
													'" . $StockID . ' ' . _('Change stock category') . "',
													'" . $WIPValue . "')";
						$ErrMsg = _('The WIP cost journal could not be inserted because');
						$DbgMsg = _('The SQL that was used to create the WIP cost journal and failed was');
						$result = DB_query($SQL, $db, $ErrMsg, $DbgMsg, true);
						$SQL = "INSERT INTO gltrans (type,
													typeno,
													trandate,
													periodno,
													account,
													narrative,
													amount)
											VALUES ( 0,
													'" . $JournalNo . "',
													'" . Date('Y-m-d') . "',
													'" . GetPeriod(Date($_SESSION['DefaultDateFormat']), $db, true) . "',
													'" . $OldWIPAccount . "',
													'" . $StockID . ' ' . _('Change stock category') . "',
													'" . (-$WIPValue) . "')";
						$result = DB_query($SQL, $db, $ErrMsg, $DbgMsg, true);
					}
				}
				/* end if the stock category changed and forced a change in WIP account */
				DB_Txn_Commit($db);
				prnMsg(_('Stock Item') . ' ' . $StockID . ' ' . _('has been updated'), 'success');
			}
			if (DB_error_no($db) != 0){
				$result = DB_Txn_Rollback($db);
			}

		} else { //it is a NEW part
			//but lets be really sure here
			$result = DB_query("SELECT stockid
								FROM stockmaster
								WHERE stockid='" . $StockID . "'", $db);
			if (DB_num_rows($result) == 1) {
				prnMsg(_('The stock code entered is already in the database - duplicate stock codes are prohibited by the system. Try choosing an alternative stock code'), 'error');
				$InputError = 1;
				$Errors[$i] = 'StockID';
				$i++;
			} else {
				DB_Txn_Begin($db);
				$sql = "INSERT INTO stockmaster (stockid,
												description,
												longdescription,
												categoryid,
												units,
												mbflag,
												eoq,
												discontinued,
												controlled,
												serialised,
												perishable,
												volume,
												grossweight,
												netweight,
												barcode,
												discountcategory,
												taxcatid,
												decimalplaces,
												shrinkfactor,
												pansize)
							VALUES ('" . $StockID . "',
								'" . $_POST['Description'] . "',
								'" . $_POST['LongDescription'] . "',
								'" . $_POST['CategoryID'] . "',
								'" . $_POST['Units'] . "',
								'" . $_POST['MBFlag'] . "',
								'" . filter_number_format($_POST['EOQ']) . "',
								'" . $_POST['Discontinued'] . "',
								'" . $_POST['Controlled'] . "',
								'" . $_POST['Serialised'] . "',
								'" . $_POST['Perishable'] . "',
								'" . filter_number_format($_POST['Volume']) . "',
								'" . filter_number_format($_POST['GrossWeight']) . "',
								'" . filter_number_format($_POST['NetWeight']) . "',
								'" . $_POST['BarCode'] . "',
								'" . $_POST['DiscountCategory'] . "',
								'" . $_POST['TaxCat'] . "',
								'" . $_POST['DecimalPlaces'] . "',
								'" . filter_number_format($_POST['ShrinkFactor']) . "',
								'" . filter_number_format($_POST['Pansize']) . "')";

				$ErrMsg = _('The item could not be added because');
				$DbgMsg = _('The SQL that was used to add the item failed was');
				$result = DB_query($sql, $db, $ErrMsg, $DbgMsg, true);
				if (DB_error_no($db) == 0) {
					//now insert the language descriptions
					$ErrMsg = _('Could not update the language description because');
					$DbgMsg = _('The SQL that was used to update the language description and failed was');
					if (count($ItemDescriptionLanguages) > 0) {
						foreach ($ItemDescriptionLanguages as $DescriptionLanguage) {
							if ($DescriptionLanguage != '') {
								$result = DB_query("INSERT INTO stockdescriptiontranslations VALUES('" . $StockID . "','" . $DescriptionLanguage . "', '" . $_POST['Description_' . str_replace('.', '_', $DescriptionLanguage)] . "')", $db, $ErrMsg, $DbgMsg, true);
							}
						}
					}
					//now insert any item properties
					for ($i = 0; $i < $_POST['PropertyCounter']; $i++) {

						if ($_POST['PropType' . $i] == 2) {
							if ($_POST['PropValue' . $i] == 'on') {
								$_POST['PropValue' . $i] = 1;
							} else {
								$_POST['PropValue' . $i] = 0;
							}
						}

						if ($_POST['PropNumeric' . $i] == 1) {
							$_POST['PropValue' . $i] = filter_number_format($_POST['PropValue' . $i]);
						} else {
							$_POST['PropValue' . $i] = $_POST['PropValue' . $i];
						}

						$result = DB_query("INSERT INTO stockitemproperties (stockid,
													stkcatpropid,
													value)
													VALUES ('" . $StockID . "',
														'" . $_POST['PropID' . $i] . "',
														'" . $_POST['PropValue' . $i] . "')", $db, $ErrMsg, $DbgMsg, true);
					} //end of loop around properties defined for the category

					//Add data to locstock

					$sql = "INSERT INTO locstock (loccode,
													stockid)
										SELECT locations.loccode,
										'" . $StockID . "'
										FROM locations";

					$ErrMsg = _('The locations for the item') . ' ' . $StockID . ' ' . _('could not be added because');
					$DbgMsg = _('NB Locations records can be added by opening the utility page') . ' <i>Z_MakeStockLocns.php</i> ' . _('The SQL that was used to add the location records that failed was');
					$InsResult = DB_query($sql, $db, $ErrMsg, $DbgMsg, true);
					DB_Txn_Commit($db);

					if (DB_error_no($db) == 0) {
						prnMsg(_('New Item') . ' ' . '<a href="SelectProduct.php?StockID=' . $StockID . '">' . $StockID . '</a> ' . _('has been added to the database') . '<br />' . _('NB: The item cost and pricing must also be setup') . '<br />' . '<a target="_blank" href="StockCostUpdate.php?StockID=' . $StockID . '">' . _('Enter Item Cost') . '</a>
							<br />' . '<a target="_blank" href="Prices.php?Item=' . $StockID . '">' . _('Enter Item Prices') . '</a> ', 'success');
						echo '<br />';
						unset($_POST['Description']);
						unset($_POST['LongDescription']);
						unset($_POST['EOQ']);
						// Leave Category ID set for ease of batch entry
						//						unset($_POST['CategoryID']);
						unset($_POST['Units']);
						unset($_POST['MBFlag']);
						unset($_POST['Discontinued']);
						unset($_POST['Controlled']);
						unset($_POST['Serialised']);
						unset($_POST['Perishable']);
						unset($_POST['Volume']);
						unset($_POST['GrossWeight']);
						unset($_POST['NetWeight']);
						unset($_POST['BarCode']);
						unset($_POST['ReorderLevel']);
						unset($_POST['DiscountCategory']);
						unset($_POST['DecimalPlaces']);
						unset($_POST['ShrinkFactor']);
						unset($_POST['Pansize']);
						unset($StockID);
						foreach ($ItemDescriptionLanguages as $DescriptionLanguage) {
							unset($_POST['Description_' . str_replace('.', '_', $DescriptionLanguage)]);
						}
						$New = 1;
					} else {
						$InsResult = DB_Txn_Rollback($db);
					}
				} //THE INSERT OF THE NEW CODE WORKED SO BANG IN THE STOCK LOCATION RECORDS TOO
			} //END CHECK FOR ALREADY EXISTING ITEM OF THE SAME CODE
		}


	} else {
		echo '<br />' . "\n";
		prnMsg(_('Validation failed, no updates or deletes took place'), 'error');
	}

} else if (isset($_POST['delete']) and mb_strlen($_POST['delete']) > 1) {
	//the button to delete a selected record was clicked instead of the submit button

	$CancelDelete = 0;

	// PREVENT DELETES IF DEPENDENT RECORDS IN 'StockMoves'

	$sql = "SELECT COUNT(*) FROM stockmoves WHERE stockid='" . $StockID . "' GROUP BY stockid";
	$result = DB_query($sql, $db);
	$myrow = DB_fetch_row($result);
	if ($myrow[0] > 0) {
		$CancelDelete = 1;
		prnMsg(_('Cannot delete this stock item because there are stock movements that refer to this item'), 'warn');
		echo '<br />' . _('There are') . ' ' . $myrow[0] . ' ' . _('stock movements that refer to this item');

	} else {
		$sql = "SELECT COUNT(*) FROM bom WHERE component='" . $StockID . "' GROUP BY component";
		$result = DB_query($sql, $db);
		$myrow = DB_fetch_row($result);
		if ($myrow[0] > 0) {
			$CancelDelete = 1;
			prnMsg(_('Cannot delete this item record because there are bills of material that require this part as a component'), 'warn');
			echo '<br />' . _('There are') . ' ' . $myrow[0] . ' ' . _('bills of material that require this part as a component');
		} else {
			$sql = "SELECT COUNT(*) FROM salesorderdetails WHERE stkcode='" . $StockID . "' GROUP BY stkcode";
			$result = DB_query($sql, $db);
			$myrow = DB_fetch_row($result);
			if ($myrow[0] > 0) {
				$CancelDelete = 1;
				prnMsg(_('Cannot delete this item record because there are existing sales orders for this part'), 'warn');
				echo '<br />' . _('There are') . ' ' . $myrow[0] . ' ' . _('sales order items against this part');
			} else {
				$sql = "SELECT COUNT(*) FROM salesanalysis WHERE stockid='" . $StockID . "' GROUP BY stockid";
				$result = DB_query($sql, $db);
				$myrow = DB_fetch_row($result);
				if ($myrow[0] > 0) {
					$CancelDelete = 1;
					prnMsg(_('Cannot delete this item because sales analysis records exist for it'), 'warn');
					echo '<br />' . _('There are') . ' ' . $myrow[0] . ' ' . _('sales analysis records against this part');
				} else {
					$sql = "SELECT COUNT(*) FROM purchorderdetails WHERE itemcode='" . $StockID . "' GROUP BY itemcode";
					$result = DB_query($sql, $db);
					$myrow = DB_fetch_row($result);
					if ($myrow[0] > 0) {
						$CancelDelete = 1;
						prnMsg(_('Cannot delete this item because there are existing purchase order items for it'), 'warn');
						echo '<br />' . _('There are') . ' ' . $myrow[0] . ' ' . _('purchase order item record relating to this part');
					} else {
						$sql = "SELECT SUM(quantity) AS qoh FROM locstock WHERE stockid='" . $StockID . "' GROUP BY stockid";
						$result = DB_query($sql, $db);
						$myrow = DB_fetch_row($result);
						if ($myrow[0] != 0) {
							$CancelDelete = 1;
							prnMsg(_('Cannot delete this item because there is currently some stock on hand'), 'warn');
							echo '<br />' . _('There are') . ' ' . $myrow[0] . ' ' . _('on hand for this part');
						} else {
							$sql = "SELECT COUNT(*) FROM offers WHERE stockid='" . $StockID . "' GROUP BY stockid";
							$result = DB_query($sql, $db);
							$myrow = DB_fetch_row($result);
							if ($myrow[0] != 0) {
								$CancelDelete = 1;
								prnMsg(_('Cannot delete this item because there are offers for this item'), 'warn');
								echo '<br />' . _('There are') . ' ' . $myrow[0] . ' ' . _('offers from suppliers for this part');
							} else {
								$sql = "SELECT COUNT(*) FROM tenderitems WHERE stockid='" . $StockID . "' GROUP BY stockid";
								$result = DB_query($sql, $db);
								$myrow = DB_fetch_row($result);
								if ($myrow[0] != 0) {
									$CancelDelete = 1;
									prnMsg(_('Cannot delete this item because there are tenders for this item'), 'warn');
									echo '<br />' . _('There are') . ' ' . $myrow[0] . ' ' . _('tenders from suppliers for this part');
								}
							}
						}
					}
				}
			}
		}

	}
	if ($CancelDelete == 0) {
		$result = DB_Txn_Begin($db);

		/*Deletes LocStock records*/
		$sql = "DELETE FROM locstock WHERE stockid='" . $StockID . "'";
		$result = DB_query($sql, $db, _('Could not delete the location stock records because'), '', true);
		/*Deletes Price records*/
		$sql = "DELETE FROM prices WHERE stockid='" . $StockID . "'";
		$result = DB_query($sql, $db, _('Could not delete the prices for this stock record because'), '', true);
		/*and cascade deletes in PurchData */
		$sql = "DELETE FROM purchdata WHERE stockid='" . $StockID . "'";
		$result = DB_query($sql, $db, _('Could not delete the purchasing data because'), '', true);
		/*and cascade delete the bill of material if any */
		$sql = "DELETE FROM bom WHERE parent='" . $StockID . "'";
		$result = DB_query($sql, $db, _('Could not delete the bill of material because'), '', true);
		//and cascade delete the item properties
		$sql = "DELETE FROM stockitemproperties WHERE stockid='" . $StockID . "'";
		$result = DB_query($sql, $db, _('Could not delete the item properties'), '', true);
		//and cascade delete the item descriptions in other languages
		$sql = "DELETE FROM stockdescriptiontranslations WHERE stockid='" . $StockID . "'";
		$result = DB_query($sql, $db, _('Could not delete the item language descriptions'), '', true);
		//and finally remove the stockmaster
		$sql = "DELETE FROM stockmaster WHERE stockid='" . $StockID . "'";
		$result = DB_query($sql, $db, _('Could not delete the item record'), '', true);

		$result = DB_Txn_Commit($db);

		prnMsg(_('Deleted the stock master record for') . ' ' . $StockID . '....' . '<br />. . ' . _('and all the location stock records set up for the part') . '<br />. . .' . _('and any bill of material that may have been set up for the part') . '<br /> . . . .' . _('and any purchasing data that may have been set up for the part') . '<br /> . . . . .' . _('and any prices that may have been set up for the part'), 'success');
		echo '<br />';
		unset($_POST['LongDescription']);
		unset($_POST['Description']);
		unset($_POST['EOQ']);
		unset($_POST['CategoryID']);
		unset($_POST['Units']);
		unset($_POST['MBFlag']);
		unset($_POST['Discontinued']);
		unset($_POST['Controlled']);
		unset($_POST['Serialised']);
		unset($_POST['Perishable']);
		unset($_POST['Volume']);
		unset($_POST['GrossWeight']);
		unset($_POST['NetWeight']);
		unset($_POST['BarCode']);
		unset($_POST['ReorderLevel']);
		unset($_POST['DiscountCategory']);
		unset($_POST['TaxCat']);
		unset($_POST['DecimalPlaces']);
		unset($_SESSION['SelectedStockItem']);
		foreach ($ItemDescriptionLanguages as $DescriptionLanguage) {
			unset($_POST['Description_' . str_replace('.', '_', $DescriptionLanguage)]);
		}
		unset($StockID);

		$New = 1;
	} //end if Delete Part
}

$FormName = 'Stocks1';
echo '<form name="' . $FormName . '" onSubmit="return VerifyForm(this);" action="' . htmlspecialchars($_SERVER['PHP_SELF'], ENT_QUOTES, 'UTF-8') . '" method="post" class="noPrint standard">';
echo '<input type="hidden" name="FormID" value="' . $_SESSION['FormID'] . '" />';
echo '<div class="toplink">
		<a class="toplink" href="' . $RootPath . '/SelectProduct.php">' . _('Back to Items') . '</a>
	</div>';
echo '<p class="page_title_text noPrint" >
		<img src="' . $RootPath . '/css/' . $Theme . '/images/inventory.png" title="' . $Title . '" alt="' . $Title . '" />' . ' ' . $Title . '
	</p>';

echo '<input type="hidden" name="New" value="' . $New . '" />';

if (!isset($StockID) or $StockID == '' or isset($_POST['UpdateCategories'])) {

	/*If the page was called without $StockID passed to page then assume a new stock item is to be entered show a form with a part Code field other wise the form showing the fields with the existing entries against the part will show for editing with only a hidden StockID field. New is set to flag that the page may have called itself and still be entering a new part, in which case the page needs to know not to go looking up details for an existing part*/
	if (!isset($StockID)) {
		$StockID = '';
	}
	if ($New == 1) {
		InputText($FormName, 'StockID', _('Item Code'), _('The unique stock code to be used as the identifier for this item'), 21, 20, True, array());
	} else {
		Text('StockID', $FormName, _('Item Code'), $StockID);
		echo '<input type="hidden" name ="StockID" value="' . $StockID . '" />';
	}

} elseif (!isset($_POST['UpdateCategories']) and $InputError != 1) { // Must be modifying an existing item and no changes made yet

	$sql = "SELECT stockid,
					description,
					longdescription,
					categoryid,
					units,
					mbflag,
					discontinued,
					controlled,
					serialised,
					perishable,
					eoq,
					volume,
					grossweight,
					netweight,
					barcode,
					discountcategory,
					taxcatid,
					decimalplaces,
					nextserialno,
					pansize,
					shrinkfactor
			FROM stockmaster
			WHERE stockid = '" . $StockID . "'";

	$result = DB_query($sql, $db);
	$myrow = DB_fetch_array($result);

	$_POST['LongDescription'] = $myrow['longdescription'];
	$_POST['Description'] = $myrow['description'];
	$_POST['EOQ'] = $myrow['eoq'];
	$_POST['CategoryID'] = $myrow['categoryid'];
	$_POST['Units'] = $myrow['units'];
	$_POST['MBFlag'] = $myrow['mbflag'];
	$_POST['Discontinued'] = $myrow['discontinued'];
	$_POST['Controlled'] = $myrow['controlled'];
	$_POST['Serialised'] = $myrow['serialised'];
	$_POST['Perishable'] = $myrow['perishable'];
	$_POST['Volume'] = $myrow['volume'];
	$_POST['GrossWeight'] = $myrow['grossweight'];
	$_POST['NetWeight']  = $myrow['netweight'];
	$_POST['BarCode'] = $myrow['barcode'];
	$_POST['DiscountCategory'] = $myrow['discountcategory'];
	$_POST['TaxCat'] = $myrow['taxcatid'];
	$_POST['DecimalPlaces'] = $myrow['decimalplaces'];
	$_POST['NextSerialNo'] = $myrow['nextserialno'];
	$_POST['Pansize'] = $myrow['pansize'];
	$_POST['ShrinkFactor'] = $myrow['shrinkfactor'];
	$sql = "SELECT descriptiontranslation, language_id FROM stockdescriptiontranslations WHERE stockid='" . $StockID . "' AND (";

	foreach ($ItemDescriptionLanguages as $DescriptionLanguage) {
		$sql .= "language_id='" . $DescriptionLanguage . "' OR ";
	}
	$sql = mb_substr($sql, 0, mb_strlen($sql) - 3) . ')';
	$result = DB_query($sql, $db);
	while ($myrow = DB_fetch_array($result)) {
		$_POST['Description_' . str_replace('.', '_', $myrow['language_id'])] = $myrow['descriptiontranslation'];
	}

	Text('StockID', $FormName, _('Item Code'), $StockID);
	echo '<input type="hidden" name ="StockID" value="' . $StockID . '" />';

} else { // some changes were made to the data so don't re-set form variables to DB ie the code above
	Text('StockID', $FormName, _('Item Code'), $StockID);
	echo '<input type="hidden" name ="StockID" value="' . $StockID . '" />';
}

if (isset($_POST['Description'])) {
	$Description = $_POST['Description'];
} else {
	$Description = '';
}
InputText($FormName, 'Description', _('Part Description') . ' (' . _('short') . '):', _('Description of the item, (limited to 50 characters).'), 52, 50, False, array(), stripslashes($Description));

foreach ($ItemDescriptionLanguages as $DescriptionLanguage) {
	if ($DescriptionLanguage != '') {
		//unfortunately cannot have points in POST variables so have to mess with the language id
		$PostVariableName = 'Description_' . str_replace('.', '_', $DescriptionLanguage);
		if (!isset($_POST[$PostVariableName])) {
			$_POST[$PostVariableName] = '';
		}
		InputText($FormName, 'Description_' . $DescriptionLanguage, $LanguagesArray[$DescriptionLanguage]['LanguageName'] . ' ' . _('Description') . ' (' . _('short') . '):', _('Description of the item in') . ' ' . $LanguagesArray[$DescriptionLanguage]['LanguageName'] . '.', 52, 50, False, array(), stripslashes($_POST[$PostVariableName]));
	}
}

if (isset($_POST['LongDescription'])) {
	$LongDescription = AddCarriageReturns($_POST['LongDescription']);
} else {
	$LongDescription = '';
}
TextArea($FormName, 'LongDescription', _('Part Description') . ' (' . _('long') . '):', _('Description of the item using unlimited characters'), false, array(), stripslashes($LongDescription));
InputFile($FormName, 'ItemPicture', _('Image File (.jpg)'), _('Picture file of this item'), false, array());
InputCheckBox($FormName, 'ClearImage', _('Clear Image'), _('Remove this image from the database.'), False);

if (function_exists('imagecreatefromjpg') and isset($StockID) and !empty($StockID)) {
	$StockImgLink = '<img src="GetStockImage.php?automake=1&amp;textcolor=FFFFFF&amp;bgcolor=CCCCCC' . '&amp;StockID=' . urlencode($StockID) . '&amp;text=' . '&amp;width=100' . '&amp;height=100' . '" alt="" />';
} else {
	if(isset($StockID) and  !empty($StockID) and file_exists($_SESSION['part_pics_dir'] . '/' . $StockID . '.jpg')) {
		$StockImgLink = '<img src="GetStockImage.php?automake=1&amp;textcolor=FFFFFF&amp;bgcolor=CCCCCC&amp;StockID=' . $StockID . '&amp;text=&amp;width=120&amp;height=120" alt="" />';
		if (isset($_POST['ClearImage']) ) {
			//workaround for many variations of permission issues that could cause unlink fail
			@unlink($_SESSION['part_pics_dir'] . '/' .$StockID.'.jpg');
			if(is_file($_SESSION['part_pics_dir'] . '/' .$StockID.'.jpg')) {
				prnMsg(_('You do not have access to delete this item image file.'),'error');
			} else {
				$StockImgLink = _('No Image');
			}
		}
	} else {
		$StockImgLink = _('No Image');
	}
}

$sql = "SELECT categoryid, categorydescription FROM stockcategory";
$ErrMsg = _('The stock categories could not be retrieved because');
$DbgMsg = _('The SQL used to retrieve stock categories and failed was');
$result = DB_query($sql, $db, $ErrMsg, $DbgMsg);

while ($myrow = DB_fetch_array($result)) {
	$Categories[$myrow['categoryid']] = $myrow['categorydescription'];
}

Select($FormName, 'CategoryID', _('Category'), _('The stock category that this item belongs to.'), False, $Categories, array(), $_POST['CategoryID']);

if (!isset($_POST['EOQ']) or $_POST['EOQ'] == '') {
	$_POST['EOQ'] = 0;
}
InputNumber($FormName, 'EOQ', _('Economic Order Quantity'), _('The most economic quantities to order in.'), False, array(), $_POST['EOQ']);

if (!isset($_POST['Volume']) or $_POST['Volume'] == '') {
	$_POST['Volume'] = 0;
}
InputNumber($FormName, 'Volume', _('Packaged Volume (metres cubed)'), _('The most volume the item.'), False, array(), $_POST['Volume']);

if (!isset($_POST['GrossWeight']) or $_POST['GrossWeight'] == '') {
	$_POST['GrossWeight'] = 0;
}
InputNumber($FormName, 'GrossWeight', _('Packaged Weight (KGs)'), _('The weight of the item fully packaged.'), False, array(), $_POST['GrossWeight']);

if (!isset($_POST['NetWeight']) or $_POST['NetWeight']==''){
	$_POST['NetWeight'] = 0;
}
InputNumber($FormName, 'NetWeight', _('Net Weight (KGs)'), _('The weight of the item fully packaged.'), False, array(), $_POST['NetWeight']);

$sql = "SELECT unitname FROM unitsofmeasure ORDER by unitname";
$UOMResult = DB_query($sql, $db);

if (!isset($_POST['Units'])) {
	$UOMrow['unitname'] = _('each');
}
while ($UOMRow = DB_fetch_array($UOMResult)) {
	$Units[$UOMRow['unitname']] = $UOMRow['unitname'];
}
Select($FormName, 'Units', _('Units of Measure'), _('The unit of measure that this item will be stocked in.'), False, $Units, array(), $_POST['Units']);
Select($FormName, 'MBFlag', _('Assembly, Kit, Manufactured or Service/Labour'), _('The type of item being created.'), False, $MBFlags, array(), $_POST['MBFlag']);

if (!isset($_POST['Discontinued']) or $_POST['Discontinued'] == '') {
	$_POST['Discontinued'] = 0;
}
$Obsolete[0] = _('Current');
$Obsolete[1] = _('Obsolete');
Select($FormName, 'Discontinued', _('Current or Obsolete'), _('Is the part current or has it been discontinued.'), False, $Obsolete, array(), $_POST['Discontinued']);

$Control[0] = _('No Control');
$Control[1] = _('Controlled');
if (!isset($_POST['Controlled']) or $_POST['Controlled'] == '') {
	$_POST['Controlled'] = 0;
}
Select($FormName, 'Controlled', _('Batch, Serial or Lot Control'), _('Is this item batch or serial numbered, or is there no control.'), False, $Control, array(), $_POST['Controlled']);

$Serial[0] = _('No');
$Serial[1] = _('Yes');
if (!isset($_POST['']) or $_POST['Serialised'] == '' or $_POST['Controlled'] == 0) {
	$_POST['Serialised'] = 0;
}
Select($FormName, 'Serialised', _('Serialised'), _('Does the item have individual serial numbers?. Note') . ', ' . _('this has no effect if the item is not Controlled.'), False, $Serial, array(), $_POST['Serialised']);

$Perishable[0] = _('No');
$Perishable[1] = _('Yes');
Select($FormName, 'Perishable', _('Perishable'), _('Is the item of a perishable nature.'), False, $Serial, array(), $_POST['Perishable']);

if (!isset($_POST['DecimalPlaces']) or $_POST['DecimalPlaces'] == '') {
	$_POST['DecimalPlaces'] = 0;
}
InputInteger($FormName, 'DecimalPlaces', _('Decimal Places for display Quantity'), _('Decimal Places for display Quantity'), False, array(), $_POST['DecimalPlaces']);

if (isset($_POST['BarCode'])) {
	$BarCode = $_POST['BarCode'];
} else {
	$BarCode = '';
}
InputText($FormName, 'BarCode', _('Bar Code'), _('Bar code number for this item.'), 15, 15, False, array(), $BarCode);

if (isset($_POST['DiscountCategory'])) {
	$DiscountCategory = $_POST['DiscountCategory'];
} else {
	$DiscountCategory = '';
}
InputText($FormName, 'DiscountCategory', _('Discount Category'), _('Bar code number for this item.'), 3, 2, False, array(), $DiscountCategory);

$sql = "SELECT taxcatid, taxcatname FROM taxcategories ORDER BY taxcatname";
$result = DB_query($sql, $db);
while ($myrow = DB_fetch_array($result)) {
	$TaxCategories[$myrow['taxcatid']] = $myrow['taxcatname'];
} //end while loop

if (!isset($_POST['TaxCat'])) {
	$_POST['TaxCat'] = $_SESSION['DefaultTaxCategory'];
}
Select($FormName, 'TaxCat', _('Tax Category'), _('Tax Category'), False, $TaxCategories, array(), $_POST['TaxCat']);

if (!isset($_POST['Pansize'])) {
	$_POST['Pansize'] = 0;
}
InputNumber($FormName, 'Pansize', _('Pan Size'), _('Pan Size'), False, array(), locale_number_format($_POST['Pansize'], 0));

if (!isset($_POST['ShrinkFactor'])) {
	$_POST['ShrinkFactor'] = 0;
}
if (!isset($_POST['NextSerialNo'])) {
	$_POST['NextSerialNo'] = 0;
}
InputNumber($FormName, 'ShrinkFactor', _('Shrinkage Factor'), _('Shrinkage Factor'), False, array(), locale_number_format($_POST['ShrinkFactor'], 0));

if (!isset($_POST['CategoryID'])) {
	$_POST['CategoryID'] = '';
}

$sql = "SELECT stkcatpropid,
				label,
				controltype,
				defaultvalue,
				numericvalue,
				minimumvalue,
				maximumvalue
		FROM stockcatproperties
		WHERE categoryid ='" . $_POST['CategoryID'] . "'
		AND reqatsalesorder =0
		ORDER BY stkcatpropid";

$PropertiesResult = DB_query($sql, $db);
$PropertyCounter = 0;
$PropertyWidth = array();

if (DB_num_rows($PropertiesResult) > 0) {
	echo '<br />
	<table class="selection">';
	echo '<tr>
			<th colspan="2">' . _('Item Category Properties') . '</th>
		</tr>';

	while ($PropertyRow = DB_fetch_array($PropertiesResult)) {

		if (isset($StockID)) {
			$PropValResult = DB_query("SELECT value FROM
									stockitemproperties
									WHERE stockid='" . $StockID . "'
									AND stkcatpropid ='" . $PropertyRow['stkcatpropid'] . "'", $db);
			$PropValRow = DB_fetch_row($PropValResult);
			$PropertyValue = $PropValRow[0];
		} else {
			$PropertyValue = '';
		}
		echo '<tr>
			<td>';
		echo '<input type="hidden" name="PropID' . $PropertyCounter . '" value="' . $PropertyRow['stkcatpropid'] . '" />';
		echo '<input type="hidden" name="PropNumeric' . $PropertyCounter . '" value="' . $PropertyRow['numericvalue'] . '" />';
		echo $PropertyRow['label'] . '</td>

			<td>';
		switch ($PropertyRow['controltype']) {
			case 0; //textbox
				if ($PropertyRow['numericvalue'] == 1) {
					echo '<input type="hidden" name="PropMin' . $PropertyCounter . '" value="' . $PropertyRow['minimumvalue'] . '" />';
					echo '<input type="hidden" name="PropMax' . $PropertyCounter . '" value="' . $PropertyRow['maximumvalue'] . '" />';

					echo '<input type="text" class="number" name="PropValue' . $PropertyCounter . '" size="20" minlength="0" maxlength="100" value="' . locale_number_format($PropertyValue, 'Variable') . '" />';
					echo _('A number between') . ' ' . locale_number_format($PropertyRow['minimumvalue'], 'Variable') . ' ' . _('and') . ' ' . locale_number_format($PropertyRow['maximumvalue'], 'Variable') . ' ' . _('is expected');
				} else {
					echo '<input type="text" name="PropValue' . $PropertyCounter . '" size="20" minlength="0" maxlength="100" value="' . $PropertyValue . '" />';
				}
				break;
			case 1; //select box
				$OptionValues = explode(',', $PropertyRow['defaultvalue']);
				echo '<select minlength="0" name="PropValue' . $PropertyCounter . '">';
				foreach ($OptionValues as $PropertyOptionValue) {
					if ($PropertyOptionValue == $PropertyValue) {
						echo '<option selected="selected" value="' . $PropertyOptionValue . '">' . $PropertyOptionValue . '</option>';
					} else {
						echo '<option value="' . $PropertyOptionValue . '">' . $PropertyOptionValue . '</option>';
					}
				}
				echo '</select>';
				break;
			case 2; //checkbox
				echo '<input type="checkbox" name="PropValue' . $PropertyCounter . '"';
				if ($PropertyValue == 1) {
					echo 'checked';
				}
				echo ' />';
				break;
		} //end switch
		echo '<input type="hidden" name="PropType' . $PropertyCounter . '" value="' . $PropertyRow['controltype'] . '" />';
		echo '</td></tr>';
		$PropertyCounter++;

	} //end loop round properties for the item category
	unset($StockID);
	echo '</table>';
}
echo '<input type="hidden" name="PropertyCounter" value="' . $PropertyCounter . '" />';
echo '<br />';

if ($New == 1) {
	SubmitButton( _('Insert New Item'), 'Submit', 'submitbutton');
	echo '<input type="submit" name="UpdateCategories" style="visibility:hidden;width:1px" value="' . _('Categories') . '" />';

} else {

	// Now the form to enter the item properties

	SubmitButton( _('Update This Item'), 'Submit', 'submitbutton');
	echo '<input type="submit" name="UpdateCategories" style="visibility:hidden;width:1px" value="' . _('Categories') . '" />';
	echo '<br />';
	prnMsg(_('Only click the Delete button if you are sure you wish to delete the item!') . _('Checks will be made to ensure that there are no stock movements, sales analysis records, sales order items or purchase order items for the item') . '. ' . _('No deletions will be allowed if they exist'), 'warn', _('WARNING'));
	SubmitButton( _('Delete This Item'), 'delete', 'deletebutton');
}

echo '</form>';
include('includes/footer.inc');
?>