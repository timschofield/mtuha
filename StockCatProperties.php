<?php


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

?>