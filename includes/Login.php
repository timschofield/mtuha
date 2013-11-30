<?php

// Display demo user name and password within login form if $AllowDemoMode is true

include('LanguageSetup.php');

if ((isset($AllowDemoMode)) and ($AllowDemoMode == True) and (!isset($demo_text))) {
	$demo_text = _('Login as user') . ': <i>' . _('admin') . '</i><br />' . _('with password') . ': <i>' . _('kwamoja') . '</i>';
} elseif (!isset($demo_text)) {
	$demo_text = _('Please login here');
}

echo '<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN"
			"http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">';
?>
<html>
<head>
	<title>Mtuha Login screen</title>
	<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
	<link rel="shortcut icon" href="favicon.ico" type="image/x-icon" />
	<link rel="stylesheet" href="css/login.css" type="text/css" />
	<!-- Javascript required for Twitter follow me button-->
	<script>
	  !function(d,s,id){
		var js,fjs=d.getElementsByTagName(s)[0];
		if(!d.getElementById(id)){
		  js=d.createElement(s);
		  js.id=id;
		  js.src="//platform.twitter.com/widgets.js";
		  fjs.parentNode.insertBefore(js,fjs);
		}
	  }(document,"script","twitter-wjs");
	</script>
	<!-- End of Javascript required for Twitter follow me button-->
</head>
<body>

<?php
if (get_magic_quotes_gpc()) {
	echo '<p style="background:white">';
	echo _('Your webserver is configured to enable Magic Quotes. This may cause problems if you use punctuation (such as quotes) when doing data entry. You should contact your webmaster to disable Magic Quotes');
	echo '</p>';
}
?>

<div id="container">
	<table>
		<tr>
			<th colspan="2">
				<div id="login_logo">
					<a href="http://www.kwamoja.com" target="_blank"><img src="css/logo.png" style="width:100%" /></a>
				</div>
			</th>
			<td width="75%">
				<div id="login_box">
					<form action="index.php" method="post" class="noPrint">
					<input type="hidden" name="FormID" value="<?php
echo $_SESSION['FormID'];
?>" />

					<?php
echo '<input type="hidden" name="CompanyNameField"  value="' . $DefaultCompany . '" />';
?>

					<br />
					<label><?php
echo _('User name');
?>:</label>
					<input type="text" autofocus="autofocus" required="required" name="UserNameEntryField" placeholder="<?php echo _('User name'); ?>" size="10" maxlength="20" /><br />
					<label><?php
echo _('Password');
?>:</label>
					<input type="password" required="required" name="Password" placeholder="<?php echo _('Password'); ?>" />
	   <div id="demo_text">
	   <?php
if (isset($demo_text)) {
	echo $demo_text;
}
?>
	   </div>
					<button class="button" type="submit" value="<?php
echo _('Login');
?>" name="SubmitUser">
					<?php
echo _('Login');
?>
					 <img src="css/tick.png" title="' . _('Upgrade') . '" alt="" class="ButtonIcon" /></button>
					 </div>
					</form>
				</div>
			</td>
		</tr>
	</table>
</div>

</body>
</html>