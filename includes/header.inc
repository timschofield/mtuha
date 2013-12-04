<?php

// Titles and screen header
// Needs the file config.php loaded where the variables are defined for
//  $RootPath
//  $Title - should be defined in the page this file is included with
if (!isset($RootPath)) {
	$RootPath = dirname(htmlspecialchars($_SERVER['PHP_SELF']));
	if ($RootPath == '/' or $RootPath == "\\") {
		$RootPath = '';
	}
}
$debug = 0;
$ViewTopic = isset($ViewTopic) ? '?ViewTopic=' . $ViewTopic : '';
$BookMark = isset($BookMark) ? '#' . $BookMark : '';
$StrictXHTML = False;
if (!headers_sent()) {
	if ($StrictXHTML) {
		header('Content-type: application/xhtml+xml; charset=utf-8');
	} else {
		header('Content-type: text/html; charset=utf-8');
		header('Accept-Encoding: gzip, deflate');
	}
}
if (isset($Title) and $Title == _('Copy a BOM to New Item Code')) { //solve the cannot modify heaer information in CopyBOM.php scritps
	ob_start();
}
echo '<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN"
			"http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">';

echo '<html xmlns="http://www.w3.org/1999/xhtml"><head><title>' . $Title . '</title>';
echo '<link href="http://fonts.googleapis.com/css?family=ABeeZee" rel="stylesheet" type="text/css">';
echo '<link rel="shortcut icon" href="' . $RootPath . '/favicon.ico" />';
echo '<link rel="icon" href="' . $RootPath . '/favicon.ico" />';
if ($StrictXHTML) {
	echo '<meta http-equiv="Content-Type" content="application/xhtml+xml; charset=utf-8" />';
} else {
	echo '<meta http-equiv="Content-Type" content="application/html; charset=utf-8" />';
}
echo '<link href="' . $RootPath . '/css/' . $_SESSION['Theme'] . '/default.css" rel="stylesheet" type="text/css" />';
echo '<link href="' . $RootPath . '/css/tooltip.css" rel="stylesheet" type="text/css" />';
if ($debug == 1) {
	echo '<link href="' . $RootPath . '/css/holmes.css" rel="stylesheet" type="text/css" />';
}

echo '<script type="text/javascript" src = "' . $RootPath . '/javascripts/MiscFunctions.js"></script>';
echo '<style media="screen">
			.noPrint{ display: block; }
			.yesPrint{ display: none !important; }
		</style>
		<style media="print">
			.noPrint{ display: none; }
			.yesPrint{ display: block !important; }
		</style>';
echo '</head>';
if ($debug == 1) {
	echo '<body class="holmes-debug" onclick="ShutTopMenus()">';
} else {
	echo '<body onclick="ShutTopMenus()">';
}

if (isset($_GET['FontSize'])) {
	$sql = "UPDATE www_users
						SET fontsize='" . $_GET['FontSize'] . "'
						WHERE userid = '" . $_SESSION['UserID'] . "'";
	$result = DB_query($sql, $db);
}

if (isset($_POST['FontSize'])) {
	$_SESSION['ScreenFontSize'] = $_POST['FontSize'];
} else if (isset($_GET['FontSize'])) {
	$_SESSION['ScreenFontSize'] = $_GET['FontSize'];
}

switch ($_SESSION['ScreenFontSize']) {
	case 0:
		$FontSize = '8pt';
		break;
	case 1:
		$FontSize = '10pt';
		break;
	case 2:
		$FontSize = '12pt';
		break;
	default:
		$FontSize = '10pt';
}
echo '<style>
		body {
			font-size: ' . $FontSize . ';
		}
	</style>';

if (isset($Title)) {

	echo '<input type="hidden" name="Theme" id="Theme" value="' . $Theme . '" />';
	echo '<ul>
			<div class="HeaderToolBar">
					<a class="ImageLink" style="float:left" href="UserSettings.php"><img id="action" src="' . $RootPath . '/css/' . $Theme . '/images/user.png" alt="' . _('User settings') . '" title="' . _('Adjust user settings for') . ' ' . $_SESSION['UsersRealName'] . '" /></a>
				<ul class="menu">';
	foreach ($_SESSION['AllowedPageSecurityTokens'] as $SecurityToken) {
		if ($SecurityToken != 0 and $SecurityToken < 1000) {
			$TokenNameSQL = "SELECT tokenname FROM securitytokens WHERE tokenid='" . $SecurityToken . "'";
			$TokenNameResult = DB_query($TokenNameSQL, $db);
			$TokenNameRow = DB_fetch_array($TokenNameResult);
			$ScriptsSQL = "SELECT script,
									description
								FROM scripts
								WHERE pagesecurity='" . $SecurityToken . "'
									AND description<>''";
			$ScriptsResult = DB_query($ScriptsSQL, $db);
			echo '<li><a class="ImageLink" href="#"><img src="' . $RootPath . '/css/' . $Theme . '/images/' . $SecurityToken . '.png" title="" alt="' . $SecurityToken . '" /></a>';
			echo '<ul>';
			while ($ScriptsRow = DB_fetch_array($ScriptsResult)) {
				if (file_exists($ScriptsRow['script'])) {
					echo '<li><a href="' . $ScriptsRow['script'] . '">' . $ScriptsRow['description'] . '</a></li>';
				}
			}
			echo '</ul>';

			echo '</li>';
		}
	}
	echo '</ul>
		<div class="right">';
	if (isset($SystemAdministrator) and in_array($SystemAdministrator, $_SESSION['AllowedPageSecurityTokens'])) {
		echo '<a class="ImageLink" href="" onclick="return edit();"><img id="EditAction" src="' . $RootPath . '/css/' . $Theme . '/images/edit.png" alt="' . _('Edit') . '" /></a>';
	}
	echo '<a class="ImageLink"  href="' . $RootPath . '/Logout.php" onclick="return MakeConfirm(\'' . _('Are you sure you wish to logout?') . '\', \'' . _('Confirm Logout') . '\', this);"><img src="' . $RootPath . '/css/' . $Theme . '/images/quit.png" alt="' . _('Logout') . '" /></a>
		</div>';
	echo '<div class="clearfix"></div>
	</div>
</ul>';
}

?>