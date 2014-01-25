<?php

class Form {

	private $Name;
	private $Title;
	private $IconName;
	private $Elements;

	function __construct($FormName, $FormTitle, $FormIcon) {
		$this->Name = $FormName;
		$this->Title = $FormTitle;
		$this->IconName = $FormIcon;
		echo '<form name="' . $FormName . '" id="' . $FormName . '" action="' . htmlspecialchars($_SERVER['PHP_SELF'], ENT_QUOTES, 'UTF-8') . '" method="post" class="standard">';
		echo '<input type="hidden" name="FormID" value="' . $_SESSION['FormID'] . '" />';
		echo '<input type="hidden" name="table" value="' . $this->Name . '" />';
		echo '<p class="page_title_text noPrint" >
				<img src="css/' . $_SESSION['Theme'] . '/images/' . $this->IconName . '.png" title="' . $this->Title . '" alt="' . $this->Title . '" />' . ' ' . $this->Title . '
			</p>';
	}

	public function AddInputText($Name, $Label, $Hint, $Size, $MaxLength, $AutoFocus = False, $Events = array(), $ReadOnly = False) {
		$Elements[] = new InputText($this->Name, $Name, $Label, $Hint, $Size, $MaxLength, $AutoFocus, $Events, $ReadOnly);
	}

	public function AddInteger($Name, $Label, $Hint, $Min, $Max, $Step, $AutoFocus = False, $Events = array(), $ReadOnly = False) {
		$Elements[] = new InputInteger($this->Name, $Name, $Label, $Hint, $Min, $Max, $Step, $AutoFocus, $Events, $ReadOnly);
	}

	public function AddInputSelect($Name, $Label, $Hint, $AutoFocus = False, $Items = array(), $Events = array()) {
		$Elements[] = new InputSelect($this->Name, $Name, $Label, $Hint, $AutoFocus, $Items, $Events);
	}

	public function AddTextArea($Name, $Label, $Hint, $AutoFocus = False, $Items = array(), $Events = array()) {
		$Elements[] = new TextArea($this->Name, $Name, $Label, $Hint, $AutoFocus, $Items, $Events);
	}

	public function AddHidden($Name, $Value) {
		$Elements[] = new Hidden($this->Name, $Name, $Value);
	}

	public function AddInputSubmit($FormName, $Caption, $Name, $ID, $Mode) {
		$Elements[] = new InputSubmit($FormName, $Caption, $Name, $ID, $Mode);
	}

	public function CloseForm() {
		echo '</form>';
	}

}

class InputText {

	function __construct($FormName, $Name, $Label, $Hint, $Size, $MaxLength, $AutoFocus = False, $Events = array(), $ReadOnly = False) {
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
		if (!isset($_POST[$Name])) {
			$_POST[$Name] = '';
		}
		echo '<div class="inputdata">';
		echo '<label for="' . $Name . '" ' . $Visible . '>' . $Label . '</label>';
		echo '<input type="text" ' . $Focus . ' size="' . $Size . '"  maxlength="' . $MaxLength . '" ' . $RO . ' ' . $Required . ' ' . $Visible . ' ' . $EventHandler . ' value="' . $_POST[$Name] . '" id="' . $Name . '" name="' . $Name . '" />';
		echo '<span>' . $Hint . $Mandatory . '</span>';
		echo '<div class="hideElements"><input type="checkbox" name="view' . $Name . '" ' . $VisCode . ' />' . _('Show');
		echo '<input type="checkbox" name="required' . $Name . '" ' . $ReqCode . ' />' . _('Required') . '</div>';
		echo '</div>';
	}
}

class InputInteger {

	function __construct($FormName, $Name, $Label, $Hint, $Min, $Max, $Step = 1, $AutoFocus = False, $Events = array(), $ReadOnly = False) {
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
		if (!isset($_POST[$Name])) {
			$_POST[$Name] = '';
		}
		echo '<div class="inputdata">';
		echo '<label for="' . $Name . '" ' . $Visible . '>' . $Label . '</label>';
		echo '<input type="number" class="integer" min="' . $Min . '" max="' . $Max . '" step="' . $Step . '" ' . $Focus . ' size="7" ' . $RO . ' ' . $Required . ' ' . $Visible . ' ' . $EventHandler . ' value="' . $_POST[$Name] . '" id="' . $Name . '" name="' . $Name . '" />';
		echo '<span>' . $Hint . $Mandatory . '</span>';
		echo '<div class="hideElements"><input type="checkbox" name="view' . $Name . '" ' . $VisCode . ' />' . _('Show');
		echo '<input type="checkbox" name="required' . $Name . '" ' . $ReqCode . ' />' . _('Required') . '</div>';
		echo '</div>';
	}
}

class InputSelect {

	function __construct($FormName, $Name, $Label, $Hint, $AutoFocus = False, $Items = array(), $Events = array()) {
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
		if (!isset($_POST[$Name])) {
			$_POST[$Name] = -1;
		}
		echo '<div class="inputdata">';
		echo '<label for="' . $Name . '" ' . $Visible . '>' . $Label . '</label>';
		echo '<select ' . $Focus . ' ' . $Required . ' ' . $Visible . ' ' . $EventHandler . ' id="' . $Name . '" name="' . $Name . '">';
		echo '<option value=""></option>';
		foreach ($Items as $ID => $Item) {
			if ($_POST[$Name] == $ID) {
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

}

class TextArea {

	function TextArea($FormName, $Name, $Label, $Hint, $AutoFocus = False, $Events = array(), $ReadOnly = False) {
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
		if (!isset($_POST[$Name])) {
			$_POST[$Name] = '';
		}
		echo '<div class="inputdata">';
		echo '<label for="' . $Name . '" ' . $Visible . '>' . $Label . '</label>';
		echo '<textarea ' . $Focus . ' ' . $RO . ' ' . $Required . ' ' . $Visible . ' ' . $EventHandler . ' cols="40" rows="6" id="' . $Name . '" name="' . $Name . '">' . $_POST[$Name] . '</textarea>';
		echo '<span>' . $Hint . $Mandatory . '</span>';
		echo '<div class="hideElements"><input type="checkbox" name="view' . $Name . '" ' . $VisCode . ' />' . _('Show');
		echo '<input type="checkbox" name="required' . $Name . '" ' . $ReqCode . ' />' . _('Required') . '</div>';
		echo '</div>';
	}

}

class Hidden {

	function __construct($FormName, $Name, $Value) {
		$FormProperties = GetFormProperties($FormName);
		if (!isset($FormProperties[$Name])) {
			SetFormProperty($FormName, $Name);
			$FormProperties = GetFormProperties($FormName);
		}
		echo '<input type="hidden" value="' . $Value . '" id="' . $Name . '" name="' . $Name . '" />';
	}
}

class InputSubmit {

	function __construct($Form, $Caption, $Name, $ID, $Mode) {
		if ($Name == 'Cancel') {
			$EventHandler = 'onclick="return ClearForm()"';
		} else {
			$EventHandler = '';
		}
		echo '<input id="' . $ID . '" ' . $EventHandler . ' name="' . $Name . '" type="submit" onclick="return SubmitForm(\'' . $Form . '\', \'result\');" value="' . $Caption . '">';
		echo '<input type="hidden" name="mode" value="' . $Mode . '" />';
	}

}

?>