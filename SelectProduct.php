<?php

$PricesSecurity = 1000; //don't show pricing info unless security token 1000 available to user
$SuppliersSecurity = 9; //don't show supplier purchasing info unless security token 9 available to user

include('includes/session.inc');
$Title = _('Search Inventory Items');
/* KwaMoja manual links before header.inc */
$ViewTopic = 'Inventory';
$BookMark = 'SelectingInventory';

include('includes/header.inc');

if (isset($_GET['StockID'])) {
	//The page is called with a StockID
	$_GET['StockID'] = trim(mb_strtoupper($_GET['StockID']));
	$_POST['Select'] = trim(mb_strtoupper($_GET['StockID']));
}
if (isset($_GET['NewSearch']) or isset($_POST['Next']) or isset($_POST['Previous']) or isset($_POST['Go'])) {
	unset($StockID);
	unset($_SESSION['SelectedStockItem']);
	unset($_POST['Select']);
}
if (!isset($_POST['PageOffset'])) {
	$_POST['PageOffset'] = 1;
} else {
	if ($_POST['PageOffset'] == 0) {
		$_POST['PageOffset'] = 1;
	}
}
if (isset($_POST['StockCode'])) {
	$_POST['StockCode'] = trim(mb_strtoupper($_POST['StockCode']));
}
// Always show the search facilities
$SQL = "SELECT categoryid,
				categorydescription
		FROM stockcategory
		ORDER BY categorydescription";
$result1 = DB_query($SQL, $db);
if (DB_num_rows($result1) == 0) {
	echo '<p class="bad">' . _('Problem Report') . ':' . _('There are no stock categories currently defined please use the link below to set them up') . '</p>';
	echo '<a href="' . $RootPath . '/StockCategories.php">' . _('Define Stock Categories') . '</a>';
	exit;
}
// end of showing search facilities
/* displays item options if there is one and only one selected */
if (!isset($_POST['Search']) and (isset($_POST['Select']) or isset($_SESSION['SelectedStockItem']))) {
	if (isset($_POST['Select'])) {
		$_SESSION['SelectedStockItem'] = $_POST['Select'];
		$StockID = $_POST['Select'];
		unset($_POST['Select']);
	} else {
		$StockID = $_SESSION['SelectedStockItem'];
	}
	echo '<p class="page_title_text noPrint" ><img src="' . $RootPath . '/css/' . $Theme . '/images/inventory.png" title="' . _('Inventory Items') . '" alt="" />' . ' ' . _('Inventory Items') . '</p>';
	$result = DB_query("SELECT stockmaster.description,
								stockmaster.longdescription,
								stockmaster.mbflag,
								stockcategory.stocktype,
								stockmaster.units,
								stockmaster.decimalplaces,
								stockmaster.controlled,
								stockmaster.serialised,
								stockmaster.materialcost+stockmaster.labourcost+stockmaster.overheadcost AS cost,
								stockmaster.discontinued,
								stockmaster.eoq,
								stockmaster.volume,
								stockmaster.grossweight,
								stockcategory.categorydescription,
								stockmaster.categoryid
						FROM stockmaster INNER JOIN stockcategory
						ON stockmaster.categoryid=stockcategory.categoryid
						WHERE stockid='" . $StockID . "'", $db);
	$myrow = DB_fetch_array($result);
	$Its_A_Kitset_Assembly_Or_Dummy = false;
	$Its_A_Dummy = false;
	$Its_A_Kitset = false;
	$Its_A_Labour_Item = false;
	if ($myrow['discontinued'] == 1) {
		$ItemStatus = '<p class="bad">' . _('Obsolete') . '</p>';
	} else {
		$ItemStatus = '';
	}
	$FormName = 'GeneralStock';
	echo '<form name="' . $FormName . '" class="standard">';
	echo '<img src="' . $RootPath . '/css/' . $Theme . '/images/inventory.png" title="' . _('Inventory') . '" alt="" /><b title="' . $myrow['longdescription'] . '">' . ' ' . $StockID . ' - ' . $myrow['description'] . '</b> ' . $ItemStatus ;

	$sql = "SELECT abccategory FROM abcstock WHERE stockid='" . $StockID . "'";
	$ABCResult = DB_query($sql, $db);
	$ABCRow = DB_fetch_array($ABCResult);
	if ($ABCRow['abccategory'] == '') {
		$ABCRow['abccategory'] = 'N/A';
	}
	echo '<div class="container">
			<div class="box">';
	Text('Category', $FormName, _('Category:'), $myrow['categorydescription']);
	Text('ABC', $FormName, _('ABC Rank:'), $ABCRow['abccategory']);

	switch ($myrow['mbflag']) {
		case 'A':
			$Type = _('Assembly Item');
			$Its_A_Kitset_Assembly_Or_Dummy = True;
			break;
		case 'K':
			$Type = _('Kitset Item');
			$Its_A_Kitset_Assembly_Or_Dummy = True;
			$Its_A_Kitset = True;
			break;
		case 'D':
			$Type = _('Service/Labour Item');
			$Its_A_Kitset_Assembly_Or_Dummy = True;
			$Its_A_Dummy = True;
			if ($myrow['stocktype'] == 'L') {
				$Its_A_Labour_Item = True;
			}
			break;
		case 'B':
			$Type = _('Purchased Item');
			break;
		default:
			$Type = _('Manufactured Item');
			break;
	}
	Text('ItemType', $FormName, _('Item Type:'), $Type);

	if ($myrow['serialised'] == 1) {
		$ControlType = _('serialised');
	} elseif ($myrow['controlled'] == 1) {
		$ControlType = _('Batchs/Lots');
	} else {
		$ControlType = _('N/A');
	}
	Text('ControlLevel', $FormName, _('Control Level:'), $ControlType);

	Text('Units', $FormName, _('Units:'), $myrow['units']);
	Text('Volume', $FormName, _('Volume:'), locale_number_format($myrow['volume'], 3));
	Text('Weight', $FormName, _('Weight:'), locale_number_format($myrow['grossweight'], 3));
	Text('EOQ', $FormName, _('EOQ:'), locale_number_format($myrow['eoq'], $myrow['decimalplaces']));
	if (in_array($PricesSecurity, $_SESSION['AllowedPageSecurityTokens']) or !isset($PricesSecurity)) {
		$PriceResult = DB_query("SELECT sales_type,
										currabrev,
										price
									FROM prices
									INNER JOIN salestypes
										ON prices.typeabbrev=salestypes.typeabbrev
									WHERE currabrev ='" . $_SESSION['CompanyRecord']['currencydefault'] . "'
										AND debtorno=''
										AND branchcode=''
										AND startdate <= '" . Date('Y-m-d') . "' AND ( enddate >= '" . Date('Y-m-d') . "' OR enddate = '0000-00-00')
										AND stockid='" . $StockID . "'", $db);
		if ($myrow['mbflag'] == 'K' or $myrow['mbflag'] == 'A') {
			$CostResult = DB_query("SELECT SUM(bom.quantity * (stockmaster.materialcost+stockmaster.labourcost+stockmaster.overheadcost)) AS cost
										FROM bom
										INNER JOIN stockmaster
											ON bom.component=stockmaster.stockid
										WHERE bom.parent='" . $StockID . "'
											AND bom.effectiveto > '" . Date('Y-m-d') . "'
											AND bom.effectiveafter < '" . Date('Y-m-d') . "'", $db);
			$CostRow = DB_fetch_row($CostResult);
			$Cost = $CostRow[0];
		} else {
			$Cost = $myrow['cost'];
		}
		if (DB_num_rows($PriceResult) == 0) {
			Text('SalesPrice', $FormName, _('Sell Price'), _('No Default Price'));
			$Price = 0;
		} else {
			while ($PriceRow = DB_fetch_array($PriceResult)) {
				$Price = $PriceRow['price'];
				if ($Price > 0) {
					$GP = locale_number_format(($Price - $Cost) * 100 / $Price, 1);
				} else {
					$GP = _('N/A');
				}
				Text('SalesPrice', $FormName, _('Sell Price'), $PriceRow['sales_type'] . ' - ' . locale_number_format($Price, $_SESSION['CompanyRecord']['decimalplaces']) . ' ' . $PriceRow['currabrev'] . ' - ' . $GP . '%');
			}
		}
		if ($myrow['mbflag'] == 'K' or $myrow['mbflag'] == 'A') {
			$CostResult = DB_query("SELECT SUM(bom.quantity * (stockmaster.materialcost+stockmaster.labourcost+stockmaster.overheadcost)) AS cost
									FROM bom INNER JOIN
										stockmaster
									ON bom.component=stockmaster.stockid
									WHERE bom.parent='" . $StockID . "'
									AND bom.effectiveto > '" . Date('Y-m-d') . "'
									AND bom.effectiveafter < '" . Date('Y-m-d') . "'", $db);
			$CostRow = DB_fetch_row($CostResult);
			$Cost = $CostRow[0];
		} else {
			$Cost = $myrow['cost'];
		}
		Text('Cost', $FormName, _('Cost'), locale_number_format($Cost, $_SESSION['StandardCostDecimalPlaces']) . ' ' . $_SESSION['CompanyRecord']['currencydefault']);
		echo '</div>';
	} //end of if PricesSecuirty allows viewing of prices
	// Item Category Property mod: display the item properties
	echo '<div class="box">';
	$sql = "SELECT stkcatpropid,
					label,
					controltype,
					defaultvalue
				FROM stockcatproperties
				WHERE categoryid ='" . $myrow['categoryid'] . "'
				AND reqatsalesorder =0
				ORDER BY stkcatpropid";
	$PropertiesResult = DB_query($sql, $db);
	$PropertyCounter = 0;
	$PropertyWidth = array();
	while ($PropertyRow = DB_fetch_array($PropertiesResult)) {
		$PropValResult = DB_query("SELECT value
									FROM stockitemproperties
									WHERE stockid='" . $StockID . "'
									AND stkcatpropid ='" . $PropertyRow['stkcatpropid'] . "'", $db);
		$PropValRow = DB_fetch_row($PropValResult);
		if (DB_num_rows($PropValResult) == 0) {
			$PropertyValue = _('Not Set');
		} else {
			$PropertyValue = $PropValRow[0];
		}
		echo '<tr>
				<th align="right">' . $PropertyRow['label'] . ':</th>';
		switch ($PropertyRow['controltype']) {
			case 0:
			case 1:
				echo '<td class="select" style="width:60px">' . $PropertyValue;
				break;
			case 2; //checkbox
				echo '<td class="select" style="width:60px">';
				if ($PropertyValue == _('Not Set')) {
					echo _('Not Set');
				} elseif ($PropertyValue == 1) {
					echo _('Yes');
				} else {
					echo _('No');
				}
				break;
		} //end switch
		echo '</td></tr>';
		$PropertyCounter++;
	} //end loop round properties for the item category
	echo '</div>'; //end of Item Category Property mod
	echo '<div class="box">'; //nested table to show QOH/orders
	$QOH = 0;
	switch ($myrow['mbflag']) {
		case 'A':
		case 'D':
		case 'K':
			$QOH = _('N/A');
			$QOO = _('N/A');
			break;
		case 'M':
		case 'B':
			$QOHResult = DB_query("SELECT sum(quantity)
						FROM locstock
						WHERE stockid = '" . $StockID . "'", $db);
			$QOHRow = DB_fetch_row($QOHResult);
			$QOH = locale_number_format($QOHRow[0], $myrow['decimalplaces']);
			$QOOSQL = "SELECT SUM(purchorderdetails.quantityord -purchorderdetails.quantityrecd) AS QtyOnOrder
					FROM purchorders INNER JOIN purchorderdetails
					ON purchorders.orderno=purchorderdetails.orderno
					WHERE purchorderdetails.itemcode='" . $StockID . "'
					AND purchorderdetails.completed =0
					AND purchorders.status<>'Cancelled'
					AND purchorders.status<>'Pending'
					AND purchorders.status<>'Rejected'";
			$QOOResult = DB_query($QOOSQL, $db);
			if (DB_num_rows($QOOResult) == 0) {
				$QOO = 0;
			} else {
				$QOORow = DB_fetch_row($QOOResult);
				$QOO = $QOORow[0];
			}
			//Also the on work order quantities
			$sql = "SELECT SUM(woitems.qtyreqd-woitems.qtyrecd) AS qtywo
				FROM woitems INNER JOIN workorders
				ON woitems.wo=workorders.wo
				WHERE workorders.closed=0
				AND woitems.stockid='" . $StockID . "'";
			$ErrMsg = _('The quantity on work orders for this product cannot be retrieved because');
			$QOOResult = DB_query($sql, $db, $ErrMsg);
			if (DB_num_rows($QOOResult) == 1) {
				$QOORow = DB_fetch_row($QOOResult);
				$QOO += $QOORow[0];
			}
			$QOO = locale_number_format($QOO, $myrow['decimalplaces']);
			break;
	}
	$Demand = 0;
	$DemResult = DB_query("SELECT SUM(salesorderdetails.quantity-salesorderdetails.qtyinvoiced) AS dem
						FROM salesorderdetails INNER JOIN salesorders
						ON salesorders.orderno = salesorderdetails.orderno
						WHERE salesorderdetails.completed=0
						AND salesorders.quotation=0
						AND salesorderdetails.stkcode='" . $StockID . "'", $db);
	$DemRow = DB_fetch_row($DemResult);
	$Demand = $DemRow[0];
	$DemAsComponentResult = DB_query("SELECT  SUM((salesorderdetails.quantity-salesorderdetails.qtyinvoiced)*bom.quantity) AS dem
									FROM salesorderdetails INNER JOIN salesorders
									ON salesorders.orderno = salesorderdetails.orderno
									INNER JOIN bom ON salesorderdetails.stkcode=bom.parent
									INNER JOIN stockmaster ON stockmaster.stockid=bom.parent
									WHERE salesorderdetails.quantity-salesorderdetails.qtyinvoiced > 0
									AND bom.component='" . $StockID . "'
									AND stockmaster.mbflag='A'
									AND salesorders.quotation=0", $db);
	$DemAsComponentRow = DB_fetch_row($DemAsComponentResult);
	$Demand += $DemAsComponentRow[0];
	//Also the demand for the item as a component of works orders
	$sql = "SELECT SUM(qtypu*(woitems.qtyreqd - woitems.qtyrecd)) AS woqtydemo
		FROM woitems INNER JOIN worequirements
		ON woitems.stockid=worequirements.parentstockid
		INNER JOIN workorders
		ON woitems.wo=workorders.wo
		AND woitems.wo=worequirements.wo
		WHERE  worequirements.stockid='" . $StockID . "'
		AND workorders.closed=0";
	$ErrMsg = _('The workorder component demand for this product cannot be retrieved because');
	$DemandResult = DB_query($sql, $db, $ErrMsg);
	if (DB_num_rows($DemandResult) == 1) {
		$DemandRow = DB_fetch_row($DemandResult);
		$Demand += $DemandRow[0];
	}
	Text('QOH', $FormName, _('Quantity On Hand'), $QOH);
	Text('QOD', $FormName, _('Quantity Demand'), locale_number_format($Demand, $myrow['decimalplaces']));
	Text('QOO', $FormName, _('Quantity On Order'), $QOO);
	if (function_exists('imagecreatefromjpg')) {
		if ($_SESSION['ShowStockidOnImages'] == '0') {
			$StockImgLink = '<img class="product" src="GetStockImage.php?automake=1&amp;textcolor=FFFFFF&amp;bgcolor=CCCCCC' . '&amp;StockID=' . urlencode($StockID) . '&amp;text=' . '&amp;width=100' . '&amp;height=100' . '" alt="" />';
		} else {
			$StockImgLink = '<img class="product" src="GetStockImage.php?automake=1&amp;textcolor=FFFFFF&amp;bgcolor=CCCCCC' . '&amp;StockID=' . urlencode($StockID) . '&amp;text=' . $StockID . '&amp;width=100' . '&amp;height=100' . '" alt="" />';
		}
	} else {
		if (isset($StockID) and file_exists('companies/' . $_SESSION['DatabaseName'] . '/' . $_SESSION['part_pics_dir'] . '/' . $StockID . '.jpg')) {
			$StockImgLink = '<img class="product" src="' . 'companies/' . $_SESSION['DatabaseName'] . '/' . $_SESSION['part_pics_dir'] . '/' . $StockID . '.jpg" height="100" width="100" />';
		} else {
			$StockImgLink = '<img class="product" src="' . 'companies/' . $_SESSION['DatabaseName'] . '/' . $_SESSION['part_pics_dir'] . '/DefaultImage.png" height="100" width="100" />';
		}
	}
	//show the item image if it has been uploaded
	echo '<div class="centre">' . $StockImgLink . '</div>';
	echo '</div>'; //end of nested table
	echo '</div>'; //end cell of master table

	if (($myrow['mbflag'] == 'B' or ($myrow['mbflag'] == 'M')) and (in_array($SuppliersSecurity, $_SESSION['AllowedPageSecurityTokens']))) {

		$SuppResult = DB_query("SELECT suppliers.suppname,
									suppliers.currcode,
									suppliers.supplierid,
									purchdata.price,
									purchdata.effectivefrom,
									purchdata.leadtime,
									purchdata.conversionfactor,
									purchdata.minorderqty,
									purchdata.preferred,
									currencies.decimalplaces
								FROM purchdata INNER JOIN suppliers
								ON purchdata.supplierno=suppliers.supplierid
								INNER JOIN currencies
								ON suppliers.currcode=currencies.currabrev
								WHERE purchdata.stockid = '" . $StockID . "'
							ORDER BY purchdata.preferred DESC, purchdata.effectivefrom DESC", $db);
		if (DB_num_rows($SuppResult) != 0) {
			echo '<td style="width:50%" valign="top">
					<table>
						<tr>
							<th style="width:50%">' . _('Supplier') . '</th>
							<th style="width:15%">' . _('Cost') . '</th>
							<th style="width:5%">' . _('Curr') . '</th>
							<th style="width:15%">' . _('Eff Date') . '</th>
							<th style="width:10%">' . _('Lead Time') . '</th>
							<th style="width:10%">' . _('Min Order Qty') . '</th>
							<th style="width:5%">' . _('Prefer') . '</th>
						</tr>';

			while ($SuppRow = DB_fetch_array($SuppResult)) {
				echo '<tr>
						<td class="select">' . $SuppRow['suppname'] . '</td>
						<td class="select">' . locale_number_format($SuppRow['price'] / $SuppRow['conversionfactor'], $SuppRow['decimalplaces']) . '</td>
						<td class="select">' . $SuppRow['currcode'] . '</td>
						<td class="select">' . ConvertSQLDate($SuppRow['effectivefrom']) . '</td>
						<td class="select">' . $SuppRow['leadtime'] . '</td>
						<td class="select">' . $SuppRow['minorderqty'] . '</td>';

				if ($SuppRow['preferred'] == 1) { //then this is the preferred supplier
					echo '<td class="select">' . _('Yes') . '</td>';
				} else {
					echo '<td class="select">' . _('No') . '</td>';
				}
				echo '<td class="select"><a href="' . $RootPath . '/PO_Header.php?NewOrder=Yes&amp;SelectedSupplier=' . $SuppRow['supplierid'] . '&amp;StockID=' . $StockID . '&amp;Quantity=' . $SuppRow['minorderqty'] . '&amp;LeadTime=' . $SuppRow['leadtime'] . '">' . _('Order') . ' </a></td>';
				echo '</tr>';
			}
			echo '</table>';
			DB_data_seek($result, 0);
		}
	}
	echo '</form>';
	echo '</td></tr></table>'; // end first item details table
	echo '<table width="90%"><tr>
		<th style="width:33%">' . _('Item Inquiries') . '</th>
		<th style="width:33%">' . _('Item Transactions') . '</th>
		<th style="width:33%">' . _('Item Maintenance') . '</th>
	</tr>';
	echo '<tr><td valign="top" class="select">';
	/*Stock Inquiry Options */
	echo '<a href="' . $RootPath . '/StockMovements.php?StockID=' . $StockID . '">' . _('Show Stock Movements') . '</a>';
	if ($Its_A_Kitset_Assembly_Or_Dummy == False) {
		echo '<a href="' . $RootPath . '/StockStatus.php?StockID=' . $StockID . '">' . _('Show Stock Status') . '</a>';
		echo '<a href="' . $RootPath . '/StockUsage.php?StockID=' . $StockID . '">' . _('Show Stock Usage') . '</a>';
	}
	echo '<a href="' . $RootPath . '/SelectSalesOrder.php?SelectedStockItem=' . $StockID . '">' . _('Search Outstanding Sales Orders') . '</a>';
	echo '<a href="' . $RootPath . '/SelectCompletedOrder.php?SelectedStockItem=' . $StockID . '">' . _('Search Completed Sales Orders') . '</a>';
	if ($Its_A_Kitset_Assembly_Or_Dummy == False) {
		echo '<a href="' . $RootPath . '/PO_SelectOSPurchOrder.php?SelectedStockItem=' . $StockID . '">' . _('Search Outstanding Purchase Orders') . '</a>';
		echo '<a href="' . $RootPath . '/PO_SelectPurchOrder.php?SelectedStockItem=' . $StockID . '">' . _('Search All Purchase Orders') . '</a>';
		echo '<a href="' . $RootPath . '/' . $_SESSION['part_pics_dir'] . '/' . $StockID . '.jpg">' . _('Show Part Picture (if available)') . '</a>';
	}
	if ($Its_A_Dummy == False) {
		echo '<a href="' . $RootPath . '/BOMInquiry.php?StockID=' . $StockID . '">' . _('View Costed Bill Of Material') . '</a>';
		echo '<a href="' . $RootPath . '/WhereUsedInquiry.php?StockID=' . $StockID . '">' . _('Where This Item Is Used') . '</a>';
	}
	if ($Its_A_Labour_Item == True) {
		echo '<a href="' . $RootPath . '/WhereUsedInquiry.php?StockID=' . $StockID . '">' . _('Where This Labour Item Is Used') . '</a>';
	}
	wikiLink('Product', $StockID);
	echo '</td><td valign="top" class="select">';
	/* Stock Transactions */
	if ($Its_A_Kitset_Assembly_Or_Dummy == false) {
		echo '<a href="' . $RootPath . '/StockAdjustments.php?StockID=' . $StockID . '">' . _('Quantity Adjustments') . '</a>';
		echo '<a href="' . $RootPath . '/StockTransfers.php?StockID=' . $StockID . '&amp;NewTransfer=true">' . _('Location Transfers') . '</a>';

		if (($myrow['mbflag'] == 'B') and (in_array($SuppliersSecurity, $_SESSION['AllowedPageSecurityTokens'])) and $myrow['discontinued'] == 0) {
			echo '';
			$SuppResult = DB_query("SELECT suppliers.suppname,
										suppliers.supplierid,
										purchdata.preferred,
										purchdata.minorderqty,
										purchdata.leadtime
									FROM purchdata INNER JOIN suppliers
									ON purchdata.supplierno=suppliers.supplierid
									WHERE purchdata.stockid='" . $StockID . "'
									ORDER BY purchdata.effectivefrom DESC", $db);
			$LastSupplierShown = "";
			while ($SuppRow = DB_fetch_array($SuppResult)) {
				if ($LastSupplierShown != $SuppRow['supplierid']) {
					if (($myrow['eoq'] < $SuppRow['minorderqty'])) {
						$EOQ = $SuppRow['minorderqty'];
					} else {
						$EOQ = $myrow['eoq'];
					}
					echo '<a href="' . $RootPath . '/PO_Header.php?NewOrder=Yes' . '&amp;SelectedSupplier=' . $SuppRow['supplierid'] . '&amp;StockID=' . $StockID . '&amp;Quantity=' . $EOQ . '&amp;LeadTime=' . $SuppRow['leadtime'] . '">' . _('Purchase this Item from') . ' ' . $SuppRow['suppname'] . '</a>
				';
					$LastSupplierShown = $SuppRow['supplierid'];
				}
				/**/
			}
			/* end of while */
		}
		/* end of $myrow['mbflag'] == 'B' */
	}
	/* end of ($Its_A_Kitset_Assembly_Or_Dummy == False) */
	echo '</td><td valign="top" class="select">';
	/* Stock Maintenance Options */
	echo '<a href="' . $RootPath . '/Stocks.php?">' . _('Insert New Item') . '</a>';
	echo '<a href="' . $RootPath . '/Stocks.php?StockID=' . $StockID . '">' . _('Modify Item Details') . '</a>';
	if ($Its_A_Kitset_Assembly_Or_Dummy == False) {
		echo '<a href="' . $RootPath . '/StockReorderLevel.php?StockID=' . $StockID . '">' . _('Maintain Reorder Levels') . '</a>';
		echo '<a href="' . $RootPath . '/StockCostUpdate.php?StockID=' . $StockID . '">' . _('Maintain Standard Cost') . '</a>';
		echo '<a href="' . $RootPath . '/PurchData.php?StockID=' . $StockID . '">' . _('Maintain Purchasing Data') . '</a>';
	}
	if ($Its_A_Labour_Item == True) {
		echo '<a href="' . $RootPath . '/StockCostUpdate.php?StockID=' . $StockID . '">' . _('Maintain Standard Cost') . '</a>';
	}
	if (!$Its_A_Kitset) {
		echo '<a href="' . $RootPath . '/Prices.php?Item=' . $StockID . '">' . _('Maintain Pricing') . '</a>';
		if (isset($_SESSION['CustomerID']) and $_SESSION['CustomerID'] != '' and mb_strlen($_SESSION['CustomerID']) > 0) {
			echo '<a href="' . $RootPath . '/Prices_Customer.php?Item=' . $StockID . '">' . _('Special Prices for customer') . ' - ' . $_SESSION['CustomerID'] . '</a>';
		}
		echo '<a href="' . $RootPath . '/DiscountCategories.php?StockID=' . $StockID . '">' . _('Maintain Discount Category') . '</a>';
		echo '<a href="' . $RootPath . '/StockClone.php?OldStockID=' . $StockID . '">' . _('Clone This Item') . '</a>';
	}
	echo '</td></tr></table>';
}

$FormName = 'SearchStock1';
while ($myrow1 = DB_fetch_array($result1)) {
	$Categories[$myrow1['categoryid']] = $myrow1['categorydescription'];
}
$Categories['All'] = _('All');
echo '<form name="' . $FormName . '" onSubmit="return VerifyForm(this);" action="' . htmlspecialchars($_SERVER['PHP_SELF'], ENT_QUOTES, 'UTF-8') . '" method="post" class="noPrint standard">';
echo '<input type="hidden" name="FormID" value="' . $_SESSION['FormID'] . '" />';

echo '<p class="page_title_text noPrint" ><img src="' . $RootPath . '/css/' . $Theme . '/images/magnifier.png" title="' . _('Search') . '" alt="" />' . ' ' . _('Search for Inventory Items') . '</p>';

Select($FormName, 'StockCat', _('In Stock Category'), _('Select a stock category over which to search'), False, $Categories);
InputSearch($FormName, 'Keywords', _('Enter partial') . '<b> ' . _('Description') . '</b>', _('Enter some description to search for the required part'), True);
InputSearch($FormName, 'StockCode', _('OR') . ' ' . '</b>' . _('Enter partial') . ' <b>' . _('Stock Code') . '</b>', _('Enter some of the part code to search for the reuired part'), False);
SubmitButton(_('Search Now'), 'Search', 'submitbutton');

echo '</form>';
// query for list of record(s)
if (isset($_POST['Go']) or isset($_POST['Next']) or isset($_POST['Previous'])) {
	$_POST['Search'] = 'Search';
}
if (isset($_POST['Search']) or isset($_POST['Go']) or isset($_POST['Next']) or isset($_POST['Previous'])) {
	if (!isset($_POST['Go']) and !isset($_POST['Next']) and !isset($_POST['Previous'])) {
		// if Search then set to first page
		$_POST['PageOffset'] = 1;
	}
	if ($_POST['Keywords']) {
		//insert wildcard characters in spaces
		$_POST['Keywords'] = mb_strtoupper($_POST['Keywords']);
		$SearchString = '%' . str_replace(' ', '%', $_POST['Keywords']) . '%';
		if ($_POST['StockCat'] == '') {
			$SQL = "SELECT stockmaster.stockid,
							stockmaster.description,
							stockmaster.longdescription,
							SUM(locstock.quantity) AS qoh,
							stockmaster.units,
							stockmaster.mbflag,
							stockmaster.discontinued,
							stockmaster.decimalplaces
						FROM stockmaster LEFT JOIN stockcategory
						ON stockmaster.categoryid=stockcategory.categoryid,
							locstock
						WHERE stockmaster.stockid=locstock.stockid
						AND stockmaster.description " . LIKE . " '$SearchString'
						GROUP BY stockmaster.stockid,
							stockmaster.description,
							stockmaster.longdescription,
							stockmaster.units,
							stockmaster.mbflag,
							stockmaster.discontinued,
							stockmaster.decimalplaces
						ORDER BY stockmaster.discontinued, stockmaster.stockid";
		} else {
			$SQL = "SELECT stockmaster.stockid,
							stockmaster.description,
							stockmaster.longdescription,
							SUM(locstock.quantity) AS qoh,
							stockmaster.units,
							stockmaster.mbflag,
							stockmaster.discontinued,
							stockmaster.decimalplaces
						FROM stockmaster INNER JOIN locstock
						ON stockmaster.stockid=locstock.stockid
						WHERE description " . LIKE . " '$SearchString'
						AND categoryid='" . $_POST['StockCat'] . "'
						GROUP BY stockmaster.stockid,
							stockmaster.description,
							stockmaster.longdescription,
							stockmaster.units,
							stockmaster.mbflag,
							stockmaster.discontinued,
							stockmaster.decimalplaces
						ORDER BY stockmaster.discontinued, stockmaster.stockid";
		}
	} elseif (isset($_POST['StockCode'])) {
		$_POST['StockCode'] = mb_strtoupper($_POST['StockCode']);
		if ($_POST['StockCat'] == '') {
			$SQL = "SELECT stockmaster.stockid,
							stockmaster.description,
							stockmaster.longdescription,
							stockmaster.mbflag,
							stockmaster.discontinued,
							SUM(locstock.quantity) AS qoh,
							stockmaster.units,
							stockmaster.decimalplaces
						FROM stockmaster
						INNER JOIN stockcategory
						ON stockmaster.categoryid=stockcategory.categoryid
						INNER JOIN locstock ON stockmaster.stockid=locstock.stockid
						WHERE stockmaster.stockid " . LIKE . " '%" . $_POST['StockCode'] . "%'
						GROUP BY stockmaster.stockid,
							stockmaster.description,
							stockmaster.longdescription,
							stockmaster.units,
							stockmaster.mbflag,
							stockmaster.discontinued,
							stockmaster.decimalplaces
						ORDER BY stockmaster.discontinued, stockmaster.stockid";
		} else {
			$SQL = "SELECT stockmaster.stockid,
						stockmaster.description,
						stockmaster.longdescription,
						stockmaster.mbflag,
						stockmaster.discontinued,
						sum(locstock.quantity) as qoh,
						stockmaster.units,
						stockmaster.decimalplaces
					FROM stockmaster INNER JOIN locstock
					ON stockmaster.stockid=locstock.stockid
					WHERE stockmaster.stockid " . LIKE . " '%" . $_POST['StockCode'] . "%'
					AND categoryid='" . $_POST['StockCat'] . "'
					GROUP BY stockmaster.stockid,
						stockmaster.description,
						stockmaster.longdescription,
						stockmaster.units,
						stockmaster.mbflag,
						stockmaster.discontinued,
						stockmaster.decimalplaces
					ORDER BY stockmaster.discontinued, stockmaster.stockid";
		}
	} elseif (!isset($_POST['StockCode']) and !isset($_POST['Keywords'])) {
		if ($_POST['StockCat'] == '') {
			$SQL = "SELECT stockmaster.stockid,
						stockmaster.description,
						stockmaster.longdescription,
						stockmaster.mbflag,
						stockmaster.discontinued,
						SUM(locstock.quantity) AS qoh,
						stockmaster.units,
						stockmaster.decimalplaces
					FROM stockmaster
					LEFT JOIN stockcategory
					ON stockmaster.categoryid=stockcategory.categoryid,
						locstock
					WHERE stockmaster.stockid=locstock.stockid
					GROUP BY stockmaster.stockid,
						stockmaster.description,
						stockmaster.longdescription,
						stockmaster.units,
						stockmaster.mbflag,
						stockmaster.discontinued,
						stockmaster.decimalplaces
					ORDER BY stockmaster.discontinued, stockmaster.stockid";
		} else {
			$SQL = "SELECT stockmaster.stockid,
						stockmaster.description,
						stockmaster.longdescription,
						stockmaster.mbflag,
						stockmaster.discontinued,
						SUM(locstock.quantity) AS qoh,
						stockmaster.units,
						stockmaster.decimalplaces
					FROM stockmaster INNER JOIN locstock
					ON stockmaster.stockid=locstock.stockid
					WHERE categoryid='" . $_POST['StockCat'] . "'
					GROUP BY stockmaster.stockid,
						stockmaster.description,
						stockmaster.longdescription,
						stockmaster.units,
						stockmaster.mbflag,
						stockmaster.discontinued,
						stockmaster.decimalplaces
					ORDER BY stockmaster.discontinued, stockmaster.stockid";
		}
	}
	$ErrMsg = _('No stock items were returned by the SQL because');
	$DbgMsg = _('The SQL that returned an error was');
	$SearchResult = DB_query($SQL, $db, $ErrMsg, $DbgMsg);
	if (DB_num_rows($SearchResult) == 0) {
		prnMsg(_('No stock items were returned by this search please re-enter alternative criteria to try again'), 'info');
	}
	unset($_POST['Search']);
}
/* end query for list of records */
/* display list if there is more than one record */
if (isset($SearchResult) and !isset($_POST['Select'])) {
	echo '<form onSubmit="return VerifyForm(this);" action="' . htmlspecialchars($_SERVER['PHP_SELF'], ENT_QUOTES, 'UTF-8') . '" method="post" class="noPrint">';
	echo '<input type="hidden" name="FormID" value="' . $_SESSION['FormID'] . '" />';
	$ListCount = DB_num_rows($SearchResult);
	if ($ListCount > 0) {
		// If the user hit the search button and there is more than one item to show
		$ListPageMax = ceil($ListCount / $_SESSION['DisplayRecordsMax']);
		if (isset($_POST['Next'])) {
			if ($_POST['PageOffset'] < $ListPageMax) {
				$_POST['PageOffset'] = $_POST['PageOffset'] + 1;
			}
		}
		if (isset($_POST['Previous'])) {
			if ($_POST['PageOffset'] > 1) {
				$_POST['PageOffset'] = $_POST['PageOffset'] - 1;
			}
		}
		if ($_POST['PageOffset'] > $ListPageMax) {
			$_POST['PageOffset'] = $ListPageMax;
		}
		if ($ListPageMax > 1) {
			echo '<div class="centre">&nbsp;&nbsp;' . $_POST['PageOffset'] . ' ' . _('of') . ' ' . $ListPageMax . ' ' . _('pages') . '. ' . _('Go to Page') . ': ';
			echo '<select minlength="0" name="PageOffset">';
			$ListPage = 1;
			while ($ListPage <= $ListPageMax) {
				if ($ListPage == $_POST['PageOffset']) {
					echo '<option value="' . $ListPage . '" selected="selected">' . $ListPage . '</option>';
				} else {
					echo '<option value="' . $ListPage . '">' . $ListPage . '</option>';
				}
				$ListPage++;
			}
			echo '</select>
					<input type="submit" name="Go" value="' . _('Go') . '" />
					<input type="submit" name="Previous" value="' . _('Previous') . '" />
					<input type="submit" name="Next" value="' . _('Next') . '" />
					<input type="hidden" name="Keywords" value="' . $_POST['Keywords'] . '" />
					<input type="hidden" name="StockCat" value="' . $_POST['StockCat'] . '" />
					<input type="hidden" name="StockCode" value="' . $_POST['StockCode'] . '" />

			</div>';
		}
		echo '<table class="selection">
				<tr>
					<th>' . _('Stock Status') . '</th>
					<th class="SortableColumn">' . _('Code') . '</th>
					<th class="SortableColumn">' . _('Description') . '</th>
					<th>' . _('Total Qty On Hand') . '</th>
					<th>' . _('Units') . '</th>
				</tr>';
		$k = 0; //row counter to determine background colour
		if (DB_num_rows($SearchResult) <> 0) {
			DB_data_seek($SearchResult, ($_POST['PageOffset'] - 1) * $_SESSION['DisplayRecordsMax']);
		}
		$RowIndex = 1;
		while (($myrow = DB_fetch_array($SearchResult)) and ($RowIndex <> $_SESSION['DisplayRecordsMax'])) {
			if ($k == 1) {
				echo '<tr class="EvenTableRows">';
				$k = 0;
			} else {
				echo '<tr class="OddTableRows">';
				$k++;
			}
			if ($myrow['mbflag'] == 'D') {
				$qoh = _('N/A');
			} else {
				$qoh = locale_number_format($myrow['qoh'], $myrow['decimalplaces']);
			}
			if ($myrow['discontinued'] == 1) {
				$ItemStatus = '<p class="bad">' . _('Obsolete') . '</p>';
			} else {
				$ItemStatus = '';
			}

			echo '<td>' . $ItemStatus . '</td>
				<td><input type="submit" name="Select" value="' . $myrow['stockid'] . '" /></td>
				<td title="' . $myrow['longdescription'] . '">' . $myrow['description'] . '</td>
				<td class="number">' . $qoh . '</td>
				<td>' . $myrow['units'] . '</td>
				<td><a target="_blank" href="' . $RootPath . '/StockStatus.php?StockID=' . $myrow['stockid'] . '">' . _('View') . '</a></td>
				</tr>';
			$RowIndex++;
		}
		//end of while loop
		echo '</table>
			</form>';
	}
}
/* end display list if there is more than one record */
include('includes/footer.inc');
?>