<?php
$PageSecurity = 0;
include('includes/session.inc');

$Title = _('Find a previously registered person');
include('includes/header.inc');

$FormName = 'SearchPerson1';
echo '<form name="' . $FormName . '" onSubmit="return VerifyForm(this);" action="' . htmlspecialchars($_SERVER['PHP_SELF'], ENT_QUOTES, 'UTF-8') . '" method="post" class="noPrint standard">';
echo '<input type="hidden" name="FormID" value="' . $_SESSION['FormID'] . '" />';

echo '<p class="page_title_text noPrint" >
			<img src="' . $RootPath . '/css/' . $Theme . '/images/SearchPerson.png" title="' . $Title . '" alt="' . $Title . '" />' . ' ' . $Title . '
		</p>';

$SearchEvents[] = ' onkeyup="SubmitSearchForm(' . $FormName . ', \'SearchResult\')" ';
InputSearch($FormName, 'PIDKeywords', _('File number'), _('You can search on part or all of the patients file number.'), False, $SearchEvents);
InputSearch($FormName, 'NameKeywords', _('Part or all of name'), _('You can enter part or all of any of the patients names.'), True, $SearchEvents);
InputSearch($FormName, 'PhoneKeywords', _('Part or all of phone number'), _('Search on the persons phone number'), False, $SearchEvents);

$MenuBox = '<div id="dialog_header">
				<img src="css/' . $_SESSION['Theme'] . '/images/maintenance.png" />' . _('Menu options for') . ' :
				<span id="person"></span>
			</div>
			<div id="dialog_main">
				<a href="Registration.php?PID=" id="MenuLink">' . _('Modify the patients registration details') . '</a><br />
				<a href="Admissions.php?PID=" id="MenuLink">' . _('Admit this patient') . '</a>
			</div>';

echo '<table class="results">
		<tr>
			<th>' . _('PID Nr') . '</th>
			<th>' . _('Sex') . '</th>
			<th>' . _('Name') . '</th>
			<th>' . _('Date Of Birth') . '</th>
			<th>' . _('Telephone number') . '</th>
		</tr>
		<tbody id="SearchResult">
		</tbody>
		</table>';
echo '</form>';
include('includes/footer.inc');

?>