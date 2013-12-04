<?php

function GetFormProperties($FormName) {
	global $db;
	$FormSQL = "SELECT fieldname,
						required,
						visible
					FROM forms
					WHERE formname='" . $FormName . "'";
	$FormResult = DB_query($FormSQL, $db);
	$FormProperties = array();
	while ($FormRow = DB_fetch_array($FormResult)) {
		$FormProperties[$FormRow['fieldname']]['Required'] = $FormRow['required'];
		$FormProperties[$FormRow['fieldname']]['Visible'] = $FormRow['visible'];
	}
	return $FormProperties;
}

function SetFormProperty($FormName, $FieldName) {
	global $db;
	$FieldSQL = "INSERT INTO forms (formname,
									fieldname
								) VALUES (
									'" . $FormName . "',
									'" . $FieldName . "'
								)";
	$FieldResult = DB_query($FieldSQL, $db);
}

function Text($Name, $FormName, $Label, $Text) {
	$FormProperties = GetFormProperties($FormName);
	if (!isset($FormProperties[$Name])) {
		SetFormProperty($FormName, $Name);
		$FormProperties = GetFormProperties($FormName);
	}
	$IsVisible = $FormProperties[$Name]['Visible'];
	if ($IsVisible == 1) {
		$Visible = 'style="visibility:visible"';
		$VisCode = ' checked="checked" ';
	} else {
		$Visible = 'style="display:none"';
		$VisCode = '';
	}
	echo '<div class="inputdata">';
	echo '<label for="' . $Name . '">' . $Label . '</label>';
	echo '<div name="' . $Name . '">' . $Text . '</div>';
	echo '<div class="hideElements"><input type="checkbox" name="view' . $Name . '" ' . $VisCode . ' />' . _('Show');
	echo '<input type="checkbox" name="required' . $Name . '" ' . $ReqCode . ' />' . _('Required') . '</div>';
	echo '</div>';
}

function InputText($FormName, $Name, $Label, $Hint, $Size, $MaxLength, $AutoFocus = False, $Events = array(), $Value='', $ReadOnly = False) {
	$FormProperties = GetFormProperties($FormName);
	if (!isset($FormProperties[$Name])) {
		SetFormProperty($FormName, $Name);
		$FormProperties = GetFormProperties($FormName);
	}
	$IsRequired = $FormProperties[$Name]['Required'];
	$IsVisible = $FormProperties[$Name]['Visible'];
	if ($IsRequired == 1) {
		$Required = 'required="required"';
		$Mandatory = '<font color="#E54210"> ' . _('Must be completed') . '</font>';
		$ReqCode = ' checked="checked" ';
	} else {
		$Required = '';
		$Mandatory = '';
		$ReqCode = '';
	}
	if ($IsVisible == 1) {
		$Visible = 'style="visibility:visible"';
		$VisCode = ' checked="checked" ';
	} else {
		$Visible = 'style="display:none"';
		$VisCode = '';
	}
	if ($AutoFocus) {
		$Focus = 'autofocus="autofocus"';
	} else {
		$Focus = '';
	}
	if ($ReadOnly) {
		$RO = 'readonly="readonly"';
		$Hint = _('You cannot change this field');
		$Mandatory = '';
	} else {
		$RO = '';
	}
	$EventHandler = '';
	foreach ($Events as $Event) {
		$EventHandler .= ' ' . $Event;
	}
	echo '<div class="inputdata">';
	echo '<label for="' . $Name . '" ' . $Visible . '>' . $Label . '</label>';
	echo '<input type="text" ' . $Focus . ' size="' . $Size . '"  maxlength="' . $MaxLength . '" ' . $RO . ' ' . $Required . ' ' . $Visible . ' ' . $EventHandler . ' value="' . $Value . '" id="' . $Name . '" name="' . $Name . '" />';
	echo '<span>' . $Hint . $Mandatory . '</span>';
	echo '<div class="hideElements"><input type="checkbox" name="view' . $Name . '" ' . $VisCode . ' />' . _('Show');
	echo '<input type="checkbox" name="required' . $Name . '" ' . $ReqCode . ' />' . _('Required') . '</div>';
	echo '</div>';
}

function InputSearch($FormName, $Name, $Label, $Hint, $AutoFocus = False, $Events = array(), $Value='') {
	$FormProperties = GetFormProperties($FormName);
	if (!isset($FormProperties[$Name])) {
		SetFormProperty($FormName, $Name);
		$FormProperties = GetFormProperties($FormName);
	}
	$IsRequired = $FormProperties[$Name]['Required'];
	$IsVisible = $FormProperties[$Name]['Visible'];
	if ($IsRequired == 1) {
		$Required = 'required="required"';
		$Mandatory = '<font color="#E54210"> ' . _('Must be completed') . '</font>';
		$ReqCode = ' checked="checked" ';
	} else {
		$Required = '';
		$Mandatory = '';
		$ReqCode = '';
	}
	if ($IsVisible == 1) {
		$Visible = 'style="visibility:visible"';
		$VisCode = ' checked="checked" ';
	} else {
		$Visible = 'style="display:none"';
		$VisCode = '';
	}
	if ($AutoFocus) {
		$Focus = 'autofocus="autofocus"';
	} else {
		$Focus = '';
	}
	$EventHandler = '';
	foreach ($Events as $Event) {
		$EventHandler .= ' ' . $Event;
	}
	echo '<div class="inputdata">';
	echo '<label for="' . $Name . '" ' . $Visible . '>' . $Label . '</label>';
	echo '<input type="search" ' . $Focus . ' ' . $Required . ' ' . $Visible . ' ' . $EventHandler . ' value="' . $Value . '" id="' . $Name . '" name="' . $Name . '" />';
	echo '<span>' . $Hint . $Mandatory . '</span>';
	echo '<div class="hideElements"><input type="checkbox" name="view' . $Name . '" ' . $VisCode . ' />' . _('Show');
	echo '<input type="checkbox" name="required' . $Name . '" ' . $ReqCode . ' />' . _('Required') . '</div>';
	echo '</div>';
}

function InputDate($FormName, $Name, $Label, $Hint, $AutoFocus = False, $Events = array(), $Value='', $ReadOnly=False) {
	$FormProperties = GetFormProperties($FormName);
	if (!isset($FormProperties[$Name])) {
		SetFormProperty($FormName, $Name);
		$FormProperties = GetFormProperties($FormName);
	}
	$IsRequired = $FormProperties[$Name]['Required'];
	$IsVisible = $FormProperties[$Name]['Visible'];
	$Hint = $Hint . ' ' . _('In the format') . ' ' . $_SESSION['DefaultDateFormat'];
	if ($IsRequired) {
		$Required = 'required="required"';
		$Mandatory = '<font color="#E54210"> ' . _('Must be completed') . '</font>';
		$ReqCode = ' checked="checked" ';
	} else {
		$Required = '';
		$Mandatory = '';
		$ReqCode = '';
	}
	if ($IsVisible == 1) {
		$Visible = 'style="visibility:visible"';
		$VisCode = ' checked="checked" ';
	} else {
		$Visible = 'style="display:none"';
		$VisCode = '';
	}
	if ($AutoFocus) {
		$Focus = 'autofocus="autofocus"';
	} else {
		$Focus = '';
	}
	if ($ReadOnly) {
		$RO = 'readonly="readonly"';
		$Hint = _('You cannot change this field');
		$Mandatory = '';
	} else {
		$RO = '';
	}
	$EventHandler = '';
	foreach ($Events as $Event) {
		$EventHandler .= ' ' . $Event;
	}
	echo '<div class="inputdata">';
	echo '<label for="' . $Name . '" ' . $Visible . '>' . $Label . '</label>';
	echo '<input type="date" class="date" alt="' . $_SESSION['DefaultDateFormat'] . '" ' . $EventHandler . $RO . ' size="10" ' . $Focus . ' ' . $Required . ' ' . $Visible . ' value="' . $Value . '" id="' . $Name . '" name="' . $Name . '" />';
	echo '<span>' . $Hint . '. ' . $Mandatory . '</span>';
	echo '<div class="hideElements"><input type="checkbox" name="view' . $Name . '" ' . $VisCode . ' />' . _('Show');
	echo '<input type="checkbox" name="required' . $Name . '" ' . $ReqCode . ' />' . _('Required') . '</div>';
	echo '</div>';
}

function InputEmail($FormName, $Name, $Label, $Hint, $AutoFocus = False, $Events = array(), $Value='') {
	$FormProperties = GetFormProperties($FormName);
	if (!isset($FormProperties[$Name])) {
		SetFormProperty($FormName, $Name);
		$FormProperties = GetFormProperties($FormName);
	}
	$IsRequired = $FormProperties[$Name]['Required'];
	$IsVisible = $FormProperties[$Name]['Visible'];
	if ($IsRequired) {
		$Required = 'required="required"';
		$Mandatory = '<font color="#E54210"> ' . _('Must be completed') . '</font>';
		$ReqCode = ' checked="checked" ';
	} else {
		$Required = '';
		$Mandatory = '';
		$ReqCode = '';
	}
	if ($IsVisible == 1) {
		$Visible = 'style="visibility:visible"';
		$VisCode = ' checked="checked" ';
	} else {
		$Visible = 'style="display:none"';
		$VisCode = '';
	}
	if ($AutoFocus) {
		$Focus = 'autofocus="autofocus"';
	} else {
		$Focus = '';
	}
	$EventHandler = '';
	foreach ($Events as $Event) {
		$EventHandler .= ' ' . $Event;
	}
	echo '<div class="inputdata">';
	echo '<label for="' . $Name . '" ' . $Visible . '>' . $Label . '</label>';
	echo '<input type="email" size="50" ' . $Focus . ' ' . $Required . ' ' . $Visible . ' ' . $EventHandler . ' value="' . $Value . '" id="' . $Name . '" name="' . $Name . '" />';
	echo '<span>' . $Hint . $Mandatory . '</span>';
	echo '<div class="hideElements"><input type="checkbox" name="view' . $Name . '" ' . $VisCode . ' />' . _('Show');
	echo '<input type="checkbox" name="required' . $Name . '" ' . $ReqCode . ' />' . _('Required') . '</div>';
	echo '</div>';
}

function InputInteger($FormName, $Name, $Label, $Hint, $AutoFocus = False, $Events = array(), $Value=0) {
	$FormProperties = GetFormProperties($FormName);
	if (!isset($FormProperties[$Name])) {
		SetFormProperty($FormName, $Name);
		$FormProperties = GetFormProperties($FormName);
	}
	$IsRequired = $FormProperties[$Name]['Required'];
	$IsVisible = $FormProperties[$Name]['Visible'];
	if ($IsRequired) {
		$Required = 'required="required"';
		$Mandatory = '<font color="#E54210"> ' . _('Must be completed') . '</font>';
		$ReqCode = ' checked="checked" ';
	} else {
		$Required = '';
		$Mandatory = '';
		$ReqCode = '';
	}
	if ($IsVisible == 1) {
		$Visible = 'style="visibility:visible"';
		$VisCode = ' checked="checked" ';
	} else {
		$Visible = 'style="display:none"';
		$VisCode = '';
	}
	if ($AutoFocus) {
		$Focus = 'autofocus="autofocus"';
	} else {
		$Focus = '';
	}
	$EventHandler = '';
	foreach ($Events as $Event) {
		$EventHandler .= ' ' . $Event;
	}
	echo '<div class="inputdata">';
	echo '<label for="' . $Name . '" ' . $Visible . '>' . $Label . '</label>';
	echo '<input type="number" class="integer" ' . $EventHandler . ' size="5" ' . $Focus . ' ' . $Required . ' ' . $Visible . ' value="' . $Value . '" id="' . $Name . '" name="' . $Name . '" />';
	echo '<span>' . $Hint . $Mandatory . '</span>';
	echo '<div class="hideElements"><input type="checkbox" name="view' . $Name . '" ' . $VisCode . ' />' . _('Show');
	echo '<input type="checkbox" name="required' . $Name . '" ' . $ReqCode . ' />' . _('Required') . '</div>';
	echo '</div>';
}

function InputNumber($FormName, $Name, $Label, $Hint, $AutoFocus = False, $Events = array(), $Value=0) {
	$FormProperties = GetFormProperties($FormName);
	if (!isset($FormProperties[$Name])) {
		SetFormProperty($FormName, $Name);
		$FormProperties = GetFormProperties($FormName);
	}
	$IsRequired = $FormProperties[$Name]['Required'];
	$IsVisible = $FormProperties[$Name]['Visible'];
	if ($IsRequired) {
		$Required = 'required="required"';
		$Mandatory = '<font color="#E54210"> ' . _('Must be completed') . '</font>';
		$ReqCode = ' checked="checked" ';
	} else {
		$Required = '';
		$Mandatory = '';
		$ReqCode = '';
	}
	if ($IsVisible == 1) {
		$Visible = 'style="visibility:visible"';
		$VisCode = ' checked="checked" ';
	} else {
		$Visible = 'style="display:none"';
		$VisCode = '';
	}
	if ($AutoFocus) {
		$Focus = 'autofocus="autofocus"';
	} else {
		$Focus = '';
	}
	$EventHandler = '';
	foreach ($Events as $Event) {
		$EventHandler .= ' ' . $Event;
	}
	echo '<div class="inputdata">';
	echo '<label for="' . $Name . '" ' . $Visible . '>' . $Label . '</label>';
	echo '<input type="number" class="number" ' . $EventHandler . ' size="3" ' . $Focus . ' ' . $Required . ' ' . $Visible . ' value="' . $Value . '" id="' . $Name . '" name="' . $Name . '" />';
	echo '<span>' . $Hint . $Mandatory . '</span>';
	echo '<div class="hideElements"><input type="checkbox" name="view' . $Name . '" ' . $VisCode . ' />' . _('Show');
	echo '<input type="checkbox" name="required' . $Name . '" ' . $ReqCode . ' />' . _('Required') . '</div>';
	echo '</div>';
}

function InputTelephone($FormName, $Name, $Label, $Hint, $AutoFocus = False, $Events = array(), $Value='') {
	$FormProperties = GetFormProperties($FormName);
	if (!isset($FormProperties[$Name])) {
		SetFormProperty($FormName, $Name);
		$FormProperties = GetFormProperties($FormName);
	}
	$IsRequired = $FormProperties[$Name]['Required'];
	$IsVisible = $FormProperties[$Name]['Visible'];
	if ($IsRequired) {
		$Required = 'required="required"';
		$Mandatory = '<font color="#E54210"> ' . _('Must be completed') . '</font>';
		$ReqCode = ' checked="checked" ';
	} else {
		$Required = '';
		$Mandatory = '';
		$ReqCode = '';
	}
	if ($IsVisible == 1) {
		$Visible = 'style="visibility:visible"';
		$VisCode = ' checked="checked" ';
	} else {
		$Visible = 'style="display:none"';
		$VisCode = '';
	}
	if ($AutoFocus) {
		$Focus = 'autofocus="autofocus"';
	} else {
		$Focus = '';
	}
	$EventHandler = '';
	foreach ($Events as $Event) {
		$EventHandler .= ' ' . $Event;
	}
	echo '<div class="inputdata">';
	echo '<label for="' . $Name . '" ' . $Visible . '>' . $Label . '</label>';
	echo '<input type="tel" ' . $Focus . ' ' . $Required . ' ' . $Visible . ' ' . $EventHandler . ' value="' . $Value . '" id="' . $Name . '" name="' . $Name . '" />';
	echo '<span>' . $Hint . $Mandatory . '</span>';
	echo '<div class="hideElements"><input type="checkbox" name="view' . $Name . '" ' . $VisCode . ' />' . _('Show');
	echo '<input type="checkbox" name="required' . $Name . '" ' . $ReqCode . ' />' . _('Required') . '</div>';
	echo '</div>';
}

function InputPassword($FormName, $Name, $Label, $Hint, $AutoFocus = False, $Events = array(), $Value='') {
	$FormProperties = GetFormProperties($FormName);
	if (!isset($FormProperties[$Name])) {
		SetFormProperty($FormName, $Name);
		$FormProperties = GetFormProperties($FormName);
	}
	$IsRequired = $FormProperties[$Name]['Required'];
	$IsVisible = $FormProperties[$Name]['Visible'];
	if ($IsRequired) {
		$Required = 'required="required"';
		$Mandatory = '<font color="#E54210"> ' . _('Must be completed') . '</font>';
		$ReqCode = ' checked="checked" ';
	} else {
		$Required = '';
		$Mandatory = '';
		$ReqCode = '';
	}
	if ($IsVisible == 1) {
		$Visible = 'style="visibility:visible"';
		$VisCode = ' checked="checked" ';
	} else {
		$Visible = 'style="display:none"';
		$VisCode = '';
	}
	if ($AutoFocus) {
		$Focus = 'autofocus="autofocus"';
	} else {
		$Focus = '';
	}
	$EventHandler = '';
	foreach ($Events as $Event) {
		$EventHandler .= ' ' . $Event;
	}
	echo '<div class="inputdata">';
	echo '<label for="' . $Name . '" ' . $Visible . '>' . $Label . '</label>';
	echo '<input type="password" ' . $Focus . ' ' . $Required . ' ' . $Visible . ' ' . $EventHandler . ' value="' . $Value . '" id="' . $Name . '" name="' . $Name . '" />';
	echo '<span>' . $Hint . $Mandatory . '</span>';
	echo '<div class="hideElements"><input type="checkbox" name="view' . $Name . '" ' . $VisCode . ' />' . _('Show');
	echo '<input type="checkbox" name="required' . $Name . '" ' . $ReqCode . ' />' . _('Required') . '</div>';
	echo '</div>';
}

function Select($FormName, $Name, $Label, $Hint, $AutoFocus = False, $Items = array(), $Events = array(), $Value=-1) {
	$FormProperties = GetFormProperties($FormName);
	if (!isset($FormProperties[$Name])) {
		SetFormProperty($FormName, $Name);
		$FormProperties = GetFormProperties($FormName);
	}
	$IsRequired = $FormProperties[$Name]['Required'];
	$IsVisible = $FormProperties[$Name]['Visible'];
	if ($IsRequired) {
		$Required = 'required="required"';
		$Mandatory = '<font color="#E54210"> ' . _('You must make a selection.') . '</font>';
		$ReqCode = ' checked="checked" ';
	} else {
		$Required = '';
		$Mandatory = '';
		$ReqCode = '';
	}
	if ($IsVisible == 1) {
		$Visible = 'style="visibility:visible"';
		$VisCode = ' checked="checked" ';
	} else {
		$Visible = 'style="display:none"';
		$VisCode = '';
	}
	if ($AutoFocus) {
		$Focus = 'autofocus="autofocus"';
	} else {
		$Focus = '';
	}
	$EventHandler = '';
	foreach ($Events as $Event) {
		$EventHandler .= ' ' . $Event;
	}
	echo '<div class="inputdata">';
	echo '<label for="' . $Name . '" ' . $Visible . '>' . $Label . '</label>';
	echo '<select ' . $Focus . ' ' . $Required . ' ' . $Visible . ' ' . $EventHandler . ' id="' . $Name . '" name="' . $Name . '">';
	echo '<option value=""></option>';
	foreach ($Items as $ID=>$Item) {
		if ($Value == $ID) {
			echo '<option value="' . $ID . '" selected="selected">' . $Item . '</option>';
		} else {
			echo '<option value="' . $ID . '">' . $Item . '</option>';
		}
	}
	echo '</select>';
	echo '<span>' . $Hint . $Mandatory . '</span>';
	echo '<div class="hideElements"><input style="vertical-align: middle;" type="checkbox" name="view' . $Name . '" ' . $VisCode . ' />' . _('Show');
	echo '<input style="vertical-align: middle;" type="checkbox" name="required' . $Name . '" ' . $ReqCode . ' />' . _('Required') . '</div>';
	echo '</div>';
}

function MultipleSelect($FormName, $Name, $Label, $Hint, $AutoFocus = False, $Items = array(), $Events = array(), $Values = array()) {
	$FormProperties = GetFormProperties($FormName);
	if (!isset($FormProperties[$Name])) {
		SetFormProperty($FormName, $Name);
		$FormProperties = GetFormProperties($FormName);
	}
	$IsRequired = $FormProperties[$Name]['Required'];
	$IsVisible = $FormProperties[$Name]['Visible'];
	if ($IsRequired) {
		$Required = 'required="required"';
		$Mandatory = '<font color="#E54210"> ' . _('You must make a selection.') . '</font>';
		$ReqCode = ' checked="checked" ';
	} else {
		$Required = '';
		$Mandatory = '';
		$ReqCode = '';
	}
	if ($IsVisible == 1) {
		$Visible = 'style="visibility:visible"';
		$VisCode = ' checked="checked" ';
	} else {
		$Visible = 'style="display:none"';
		$VisCode = '';
	}
	if ($AutoFocus) {
		$Focus = 'autofocus="autofocus"';
	} else {
		$Focus = '';
	}
	$EventHandler = '';
	foreach ($Events as $Event) {
		$EventHandler .= ' ' . $Event;
	}
	echo '<div class="inputdata">';
	echo '<label for="' . $Name . '" ' . $Visible . '>' . $Label . '</label>';
	echo '<select ' . $Focus . ' ' . $Required . ' ' . $Visible . ' ' . $EventHandler . ' multiple="multiple" size="5" id="' . $Name . '" name="' . $Name . '[]">';
	echo '<option value=""></option>';
	foreach ($Items as $ID=>$Item) {
		if (in_array($ID, $Values)) {
			echo '<option value="' . $ID . '" selected="selected">' . $Item . '</option>';
		} else {
			echo '<option value="' . $ID . '">' . $Item . '</option>';
		}
	}
	echo '</select>';
	echo '<span>' . $Hint . $Mandatory . '</span>';
	echo '<div class="hideElements"><input style="vertical-align: middle;" type="checkbox" name="view' . $Name . '" ' . $VisCode . ' />' . _('Show');
	echo '<input style="vertical-align: middle;" type="checkbox" name="required' . $Name . '" ' . $ReqCode . ' />' . _('Required') . '</div>';
	echo '</div>';
}

function TextArea($FormName, $Name, $Label, $Hint, $AutoFocus = False, $Events = array(), $Value='', $ReadOnly = False) {
	$FormProperties = GetFormProperties($FormName);
	if (!isset($FormProperties[$Name])) {
		SetFormProperty($FormName, $Name);
		$FormProperties = GetFormProperties($FormName);
	}
	$IsRequired = $FormProperties[$Name]['Required'];
	$IsVisible = $FormProperties[$Name]['Visible'];
	if ($IsRequired == 1) {
		$Required = 'required="required"';
		$Mandatory = '<font color="#E54210"> ' . _('Must be completed') . '</font>';
		$ReqCode = ' checked="checked" ';
	} else {
		$Required = '';
		$Mandatory = '';
		$ReqCode = '';
	}
	if ($IsVisible == 1) {
		$Visible = 'style="visibility:visible"';
		$VisCode = ' checked="checked" ';
	} else {
		$Visible = 'style="display:none"';
		$VisCode = '';
	}
	if ($AutoFocus) {
		$Focus = 'autofocus="autofocus"';
	} else {
		$Focus = '';
	}
	if ($ReadOnly) {
		$RO = 'readonly="readonly"';
		$Hint = _('You cannot change this field');
		$Mandatory = '';
	} else {
		$RO = '';
	}
	$EventHandler = '';
	foreach ($Events as $Event) {
		$EventHandler .= ' ' . $Event;
	}
	echo '<div class="inputdata">';
	echo '<label for="' . $Name . '" ' . $Visible . '>' . $Label . '</label>';
	echo '<textarea ' . $Focus . ' ' . $RO . ' ' . $Required . ' ' . $Visible . ' ' . $EventHandler . ' cols="40" rows="6" id="' . $Name . '" name="' . $Name . '">' . $Value . '</textarea>';
	echo '<span>' . $Hint . $Mandatory . '</span>';
	echo '<div class="hideElements"><input type="checkbox" name="view' . $Name . '" ' . $VisCode . ' />' . _('Show');
	echo '<input type="checkbox" name="required' . $Name . '" ' . $ReqCode . ' />' . _('Required') . '</div>';
	echo '</div>';
}

function InputFile($FormName, $Name, $Label, $Hint, $AutoFocus = False, $Events = array(), $Value='', $ReadOnly = False) {
	$FormProperties = GetFormProperties($FormName);
	if (!isset($FormProperties[$Name])) {
		SetFormProperty($FormName, $Name);
		$FormProperties = GetFormProperties($FormName);
	}
	$IsRequired = $FormProperties[$Name]['Required'];
	$IsVisible = $FormProperties[$Name]['Visible'];
	if ($IsRequired == 1) {
		$Required = 'required="required"';
		$Mandatory = '<font color="#E54210"> ' . _('Must be select a file') . '</font>';
		$ReqCode = ' checked="checked" ';
	} else {
		$Required = '';
		$Mandatory = '';
		$ReqCode = '';
	}
	if ($IsVisible == 1) {
		$Visible = 'style="visibility:visible"';
		$VisCode = ' checked="checked" ';
	} else {
		$Visible = 'style="display:none"';
		$VisCode = '';
	}
	if ($AutoFocus) {
		$Focus = 'autofocus="autofocus"';
	} else {
		$Focus = '';
	}
	if ($ReadOnly) {
		$RO = 'readonly="readonly"';
		$Hint = _('You cannot change this field');
		$Mandatory = '';
	} else {
		$RO = '';
	}
	$EventHandler = '';
	foreach ($Events as $Event) {
		$EventHandler .= ' ' . $Event;
	}
	echo '<div class="inputdata">';
	echo '<label for="' . $Name . '" ' . $Visible . '>' . $Label . '</label>';
	echo '<input type="file" ' . $Focus . ' ' . $Required . ' ' . $Visible . ' ' . $EventHandler . ' value="' . $Value . '" id="' . $Name . '" name="' . $Name . '" />';
	echo '<span>' . $Hint . $Mandatory . '</span>';
	echo '<div class="hideElements"><input type="checkbox" name="view' . $Name . '" ' . $VisCode . ' />' . _('Show');
	echo '<input type="checkbox" name="required' . $Name . '" ' . $ReqCode . ' />' . _('Required') . '</div>';
	echo '</div>';
}

function InputCheckBox($FormName, $Name, $Label, $Hint, $Checked = False, $AutoFocus = False, $Events = array(), $Value='', $ReadOnly = False) {
	$FormProperties = GetFormProperties($FormName);
	if (!isset($FormProperties[$Name])) {
		SetFormProperty($FormName, $Name);
		$FormProperties = GetFormProperties($FormName);
	}
	$IsRequired = $FormProperties[$Name]['Required'];
	$IsVisible = $FormProperties[$Name]['Visible'];
	if ($IsRequired == 1) {
		$Required = 'required="required"';
		$Mandatory = '<font color="#E54210"> ' . _('Must be completed') . '</font>';
		$ReqCode = ' checked="checked" ';
	} else {
		$Required = '';
		$Mandatory = '';
		$ReqCode = '';
	}
	if ($Checked == True) {
		$CheckedCode = ' checked="checked" ';
		$ReqCode = ' checked="checked" ';
	} else {
		$CheckedCode = '';
		$ReqCode = '';
	}
	if ($IsVisible == 1) {
		$Visible = 'style="visibility:visible"';
		$VisCode = ' checked="checked" ';
	} else {
		$Visible = 'style="display:none"';
		$VisCode = '';
	}
	if ($AutoFocus) {
		$Focus = 'autofocus="autofocus"';
	} else {
		$Focus = '';
	}
	if ($ReadOnly) {
		$RO = 'readonly="readonly"';
		$Hint = _('You cannot change this field');
		$Mandatory = '';
	} else {
		$RO = '';
	}
	$EventHandler = '';
	foreach ($Events as $Event) {
		$EventHandler .= ' ' . $Event;
	}
	echo '<div class="inputdata">';
	echo '<label for="' . $Name . '" ' . $Visible . '>' . $Label . '</label>';
	echo '<input type="checkbox" ' . $Focus . ' ' . $Required . ' ' . $Visible . ' ' . $EventHandler . ' ' . $CheckedCode . ' value="' . $Value . '" id="' . $Name . '" name="' . $Name . '" />';
	echo '<span>' . $Hint . $Mandatory . '</span>';
	echo '<div class="hideElements"><input type="checkbox" name="view' . $Name . '" ' . $VisCode . ' />' . _('Show');
	echo '<input type="checkbox" name="required' . $Name . '" ' . $ReqCode . ' />' . _('Required') . '</div>';
	echo '</div>';
}

function RadioGroup($FormName, $Name, $Label, $Hint, $Buttons = array(), $Events = array(), $Value='', $ReadOnly = False) {
	$FormProperties = GetFormProperties($FormName);
	if (!isset($FormProperties[$Name])) {
		SetFormProperty($FormName, $Name);
		$FormProperties = GetFormProperties($FormName);
	}
	$IsVisible = $FormProperties[$Name]['Visible'];
	$IsRequired = $FormProperties[$Name]['Required'];
	if ($IsVisible == 1) {
		$Visible = 'style="visibility:visible"';
		$VisCode = ' checked="checked" ';
	} else {
		$Visible = 'style="display:none"';
		$VisCode = '';
	}
	if ($IsRequired == 1) {
		$Required = 'required="required"';
		$Mandatory = '<font color="#E54210"> ' . _('You must make a selection') . '</font>';
		$ReqCode = ' checked="checked" ';
	} else {
		$Required = '';
		$Mandatory = '';
		$ReqCode = '';
	}
	$EventHandler = '';
	foreach ($Events as $Event) {
		$EventHandler .= ' ' . $Event;
	}
	echo '<div class="inputdata">';
	echo '<label for="' . $Name . '" ' . $Visible . '>' . $Label . '</label>';
	foreach ($Buttons as $Key=>$Value) {
		echo '<input type="radio" name="' . $Name . '" ' . $EventHandler . ' ' . $Visible . ' value="' . $Key . '" />' . $Value;
	}
	echo '<span>' . $Hint . $Mandatory . '</span>';
	echo '<div class="hideElements"><input type="checkbox" name="view' . $Name . '" ' . $VisCode . ' />' . _('Show');
	echo '<input type="checkbox" name="required' . $Name . '" ' . $ReqCode . ' />' . _('Required') . '</div>';
	echo '</div>';
}

function SubmitButton($Caption, $Name, $ID) {
	if ($Name == 'Cancel') {
		$EventHandler = 'onclick="return ClearForm()"';
	} else {
		$EventHandler = '';
	}
	echo '<input id="' . $ID . '" ' . $EventHandler . ' name="' . $Name . '" type="submit" value="' . $Caption . '">';
}

?>