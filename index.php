<?php

include('includes/session.inc');
$Title = _('Main Menu');
include('includes/header.inc');
?>


<table cellspacing=3 cellpadding=0 border="0" width="100%">

	<tr>
		<td valign="top" width="100%">

			<table width=100%>
	<tr>
		<td>

			<!--    The title text bouding box   -->
			<table border=0 bgcolor=#cfcfcf cellpadding=1 cellspacing=0 width="100%">
				<tr>
					<td>

						<table border="0" bgcolor=#ffffff cellpadding=1 cellspacing=0 width="100%">
							<tr>
								<td>
									<font size=6 color=#800000>
									<b>News for <?php echo $_SESSION['CompanyRecord']['coyname']; ?></b>
									</font>
								</td>
							</tr>
						</table>

					</td>
				</tr>
			</table>		</td>
	</tr>
<?php

$NewsSQL = "SELECT pic,
					title,
					preface,
					body,
					author
				FROM news_article
				ORDER BY create_time DESC
				LIMIT 3";

$NewsResult = DB_query($NewsSQL, $db);

$Sequence[0] = _('first');
$Sequence[1] = _('second');
$Sequence[2] = _('third');
for ($i=0; $i<3; $i++) {
	echo '<tr valign=top>
			<td>';

	if ($NewsRow = DB_fetch_array($NewsResult)) {

		if ($i % 2 == 0) {
			echo '<img src="css/aguapop/images/pplanu-s.jpg" border=0 width="130" height="98" align="left" border=0 />';
		} else {
			echo '<img src="css/aguapop/images/pplanu-s.jpg" border=0 width="130" height="98" align="right" border=0 />';
		}
		echo '<font  size=+1 color="#cc0000">
				<b>' . $NewsRow['title'] . '</b>
			</font>
			<br />
			<font size=-1 color="#000000">
			<b>
			' . $NewsRow['preface'] . '</b><p>
			' . $NewsRow['body'] . '
			</p></font>';
	} else {

		if ($i % 2 == 0) {
			echo '<img src="css/aguapop/images/pplanu-s.jpg" border=0 width="130" height="98" align="left" border=0 />';
		} else {
			echo '<img src="css/aguapop/images/pplanu-s.jpg" border=0 width="130" height="98" align="right" border=0 />';
		}
		echo '<font  size=+1 color="#cc0000">
				<b>' . ucfirst($Sequence[$i]) . ' ' . 'article</b>
			</font>
			<br />
			<font size=-1 color="#000000">
			<b>
			The ' . $Sequence[$i] . ' news article or information will be shown here.</b><p>
			You can publish your news, information, memoranda, etc. in this area.
			With the user friendly editor you can compose and publish your article
			in a very short time and in a very easy way. The editor will show you
			how along the way.

			You can also attach a picture to enhance the article.</p>
			Write your article now...</font><br />';
	}


	echo '<br />
	<font size=1><a href="editor-pass.php?sid=fht45d34s99tcfhbtdv5dhot42&lang=en&target=headline&title=Headline">Click me to submit news</a></font>
			<br /><hr>
		</td>
	</tr>';
}
?>

</table>
		</td>
		<!--      Vertical spacer column      -->
		<td   width=1  background="../../gui/img/common/biju/vert_reuna_20b.gif"></td>

		<TD valign="top">

			<table cellspacing=0 cellpadding=1 border=0 bgcolor="#999999" width="100%">
	<tr>
		<td>
			<table  cellspacing=0 cellpadding=2 width="100%">
			<tr>
				<td bgcolor=maroon align=center>	<FONT  SIZE=2 FACE="verdana,Arial" color=white>
				<b> Quick Informer </b>
				</td>
			</tr>
			<tr>
				<td bgcolor="#ffffcc" >
				<FONT  SIZE=1 FACE="verdana,Arial" color=navy>
				<b> Police :</b> <br>&nbsp;&nbsp; <font color="#cc0000"> 11? </font><br>
				<b> Fire Dept. :</b> <br>&nbsp;&nbsp; <font color="#cc0000"> 12? </font><br>
				<b> Emergency :</b> <br>&nbsp;&nbsp; <font color="#cc0000"> 13? </font><br>
				<b> Phone (Hospital) :</b> <br>&nbsp;&nbsp; 1234567 <br>
				<b> Fax :</b> <br>&nbsp;&nbsp; 567890 <br>
				<b> Address :</b> <br>&nbsp;&nbsp; Virtualstr. 89AA<br />
Cyberia 89300<br />
Las Vegas County <br>
				<b> Email :</b><a href="mailto: contact@care2x.com"> <?php echo $_SESSION['CompanyRecord']['email']; ?> </a>&nbsp;&nbsp;
				</td>
			</tr>
			</table>

		</td>
	</tr>
</table>

<table  cellspacing=0 cellpadding=0 >
	<tr>
		<td>
		<font face="Arial,Verdana,Tahoma" size=2>&nbsp;<br>
		<A HREF="open-time.php?ntid=false&lang=en"> Admission Hours </A>
		<br>
		<A HREF="newscolumns.php?ntid=false&lang=en&dept_nr=28&user_origin=dept"> Management  </A>
		<br>
		<A HREF="../../modules/news/departments.php?ntid=false&lang=en"> Departments  </A>
		<br>
		<A HREF="../../modules/cafeteria/cafenews.php?ntid=false&lang=en"> Cafeteria News  </A>
		<br>
		<A HREF="newscolumns.php?ntid=false&lang=en&dept_nr=33&user_origin=dept"> Admission  </A>
		<br>
		<A HREF="newscolumns.php?ntid=false&lang=en&dept_nr=29&user_origin=dept"> Exhibitions  </A>
		<br>
		<a href="newscolumns.php?ntid=false&lang=en&dept_nr=30&user_origin=dept"> Education  </A>
		<br>
		<A HREF="newscolumns.php?ntid=false&lang=en&dept_nr=31&user_origin=dept"> Studies  </A>
		<br>
		<A HREF="newscolumns.php?ntid=false&lang=en&dept_nr=10&user_origin=dept"> Physical Therapy  </A>
		<br>
		<A HREF="newscolumns.php?ntid=false&lang=en&dept_nr=32&user_origin=dept"> Health tips  </A>
		<br>
		<A HREF="../../modules/calendar/calendar.php?ntid=false&lang=en&retpath=home"> Calendar  </A>
		<br>
		<A HREF="javascript:gethelp()"> Help  </A>
		<br>
		<a href="editor-pass.php?ntid=false&lang=en"> Submit News  </A>
		<br>
		<a href="javascript:openCreditsWindow()"> Credits  </a>
		<br>
		</td>
	</tr>
</table>


		</TD>
	</tr>
</table>
 <?php
include('includes/footer.inc');

?>