<?php

include('includes/session.inc');
include('includes/DefineFormClass.php');
include('includes/DefineTableClass.php');
$Title = _('Create or Modify Ward Details');

include('includes/header.inc');

if (isset($_GET['Edit'])) {
	$Mode = 'Edit';
} else {
	$Mode = 'Insert';
}

$WardStatus = array('open', 'closed','deleted','hidden','inactive','void');
$DepartmentsSQL = "SELECT nr, name_formal
						FROM departments";
$DepartmentsResult = DB_query($DepartmentsSQL, $db);
while ($DepartmentsRow = DB_fetch_array($DepartmentsResult)) {
	$Departments[$DepartmentsRow['nr']] = $DepartmentsRow['name_formal'];
}

$Form = new Form('wards', _('Ward Management'), 'maintenance');
$Form->AddInputText('ward_id', _('Ward Identifier'), _('An Identifier used for this ward.'), 10, 10, True);
$Form->AddInputText('name', _('Ward Name'), _('The name of this ward.'), 35, 35);
$Form->AddInputSelect('dept_nr', _('Department'), _('Please select a department.'), False, $Departments);
$Form->AddTextArea('description', _('Ward Description'), _('The description of this ward.'));
$Form->AddInteger('room_nr_start', _('Room number of the first room'), _('Room number of the first room.'), 1, 99, 1);
$Form->AddInteger('room_nr_end', _('Room number of the last room'), _('Room number of the last room.'), 1, 99, 1);
$Form->AddInputText('roomprefix', _('Room prefix'), _('Prefix to be prepended to the rooms in this ward.'), 4, 4);
$Form->AddInputSubmit('wards', _('Enter Information'), 'Submit', 'submitbutton', $Mode);
$Form->AddHidden('date_create', date('Y-m-d'));
$Form->AddHidden('status', 'open');
$Form->CloseForm();

$SQL = "SELECT  ward_id,
				name,
				DATE_FORMAT(date_create, '%d/%m/%Y'),
				wards.description,
				departments.name_formal,
				CONCAT(roomprefix, room_nr_start),
				CONCAT(roomprefix, room_nr_end),
				CONCAT(UCASE(SUBSTRING(wards.status, 1, 1)),LOWER(SUBSTRING(wards.status, 2)))
			FROM wards
			INNER JOIN departments
				ON wards.dept_nr=departments.nr";

$Table = new Table('WardTable', 'selection', True, True);
$Table->SetHeaders(array(_('Ward ID'), _('Ward Name'), _('Created'), _('Description'), _('Department'), _('From Room'), _('To Room'), _('Status')));
$Table->SetSQL($SQL);
$Table->Draw();

include('includes/footer.inc');
?>