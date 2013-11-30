function makeAlert(e, t) {
	theme = document.getElementById("Theme").value;
	document.getElementById("mask").style["display"] = "inline";
	document.getElementById("dialog").style["display"] = "inline";
	html = '<div id="dialog_header"><img src="css/' + theme + '/images/help.png" />' + t + '</div><img style="float: left;vertical-align:middle" src="css/' + theme + '/images/alert.png" /><div id="dialog_main">' + e;
	html = html + '</div><div id="dialog_buttons"><input type="submit" class="okButton" value="OK" onClick="hideAlert()" /></div>';
	document.getElementById("dialog").innerHTML = html;
	document.getElementById("dialog").style.marginTop = -document.getElementById("dialog").offsetHeight + "px";
	document.getElementById("dialog").style.marginLeft = -(document.getElementById("dialog").offsetWidth / 2) + "px";
	return false
}

function hideAlert() {
	document.getElementById("dialog").innerHTML = "";
	document.getElementById("mask").style["display"] = "none";
	document.getElementById("dialog").style["display"] = "none";
	return true
}

function MakeConfirm(e, t, n) {
	url = n.href;
	th = document.getElementById("Theme").value;
	document.getElementById("mask").style["display"] = "inline";
	document.getElementById("dialog").style["display"] = "inline";
	h = '<div id="dialog_header"><img src="css/' + th + '/images/help.png" />' + t + '</div><div id="dialog_main">' + e;
	h = h + '</div><div id="dialog_buttons"><input type="submit" class="okButton" value="Cancel" onClick="hideConfirm(\'\')" />';
	h = h + '<a href="' + url + '" ><input type="submit" class="okButton" value="OK" onClick="hideConfirm(\'OK\')" /></a></div></div>';
	document.getElementById("dialog").innerHTML = h;
	document.getElementById("dialog").style.marginTop = -document.getElementById("dialog").offsetHeight + "px";
	document.getElementById("dialog").style.marginLeft = -(document.getElementById("dialog").offsetWidth / 2) + "px";
	return false
}

function hideConfirm(e) {
	if (e == "") {
		document.getElementById("dialog").innerHTML = "";
		document.getElementById("mask").style["display"] = "none"
		document.getElementById("dialog").style["display"] = "none"
	}
	return true
}

function ShowPersonMenu(PID, Name, event) {
	theme = document.getElementById("Theme").value;
	document.getElementById("mask").style["display"] = "inline";
	document.getElementById("person").innerHTML = PID+' - '+Name;
	document.getElementById("menu").style["display"] = "inline";
	var elem = document.getElementById("menu");
	var DivWidth = window.getComputedStyle(elem, null).getPropertyValue("width");
	DivLeft = event.clientX-parseInt(DivWidth)-5;
	document.getElementById("menu").style.left = DivLeft+'px';
	href=document.getElementById("MenuLink").href;
	document.getElementById("MenuLink").href=href+PID;
}

function ShutTopMenus() {
	for (i = 0; i < 18; i++) {
		if (document.getElementsByName("TopMenu"+i)[0]) {
			document.getElementsByName("TopMenu"+i)[0].style["display"] = 'none';
		}
	}
}

function ShowTopMenu(token, event) {
	document.getElementsByName("TopMenu"+token)[0].style["display"] = "inline";
	document.getElementsByName("TopMenu"+token)[0].style.left = event.clientX+10+'px';
	document.getElementsByName("TopMenu"+token)[0].style.top = event.clientY+10+'px';
	return false;
}

function isInteger(e) {
	return e.toString().search(/^-?[0-9]+$/) == 0
}

function validateEmail(e) {
	var t = /^(([^<>()[\]\\.,;:\s@\"]+(\.[^<>()[\]\\.,;:\s@\"]+)*)|(\".+\"))@((\[[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\])|(([a-zA-Z\-0-9]+\.)+[a-zA-Z]{2,}))$/;
	return t.test(e)
}

function ReloadForm(e) {
	e.click()
}

function ShowTable(e) {
	document.getElementById(e).style["display"] = "table"
}

function HideTable(e) {
	document.getElementById(e).style["display"] = "none"
}

function rTN(e) {
	if (window.event) k = window.event.keyCode;
	else if (e) k = e.which;
	else return true;
	kC = String.fromCharCode(k);
	if (k == null || k == 0 || k == 8 || k == 9 || k == 13 || k == 27) return true;
	else if ("0123456789.,-".indexOf(kC) > -1) return true;
	else return false
}

function rTI(e) {
	if (window.event) k = window.event.keyCode;
	else if (e) k = e.which;
	else return true;
	kC = String.fromCharCode(k);
	if (k == null || k == 0 || k == 8 || k == 9 || k == 13 || k == 27) return true;
	else if ("0123456789".indexOf(kC) > -1) return true;
	else return false
}

function assignComboToInput(e, t) {
	t.value = e.value
}

function inArray(e, t, n) {
	for (i = 0; i < t.length; i++) {
		if (e == t[i].value) {
			return true
		}
	}
	makeAlert(n, "Error");
	return false
}

function isDate(e, t) {
	var n = e.match(/^(\d{1,2})(\/|-|.)(\d{1,2})(\/|-|.)(\d{4})$/);
	if (n == null) {
		makeAlert("Please enter the date in the format " + t, "Date Error");
		return false
	}
	if (t == "d/m/Y") {
		d = n[1];
		m = n[3]
	} else {
		d = n[3];
		m = n[1]
	}
	y = n[5];
	if (m < 1 || m > 12) {
		makeAlert("Month must be between 1 and 12", "Date Error");
		return false
	}
	if (d < 1 || d > 31) {
		makeAlert("Day must be between 1 and 31", "Date Error");
		return false
	}
	if ((m == 4 || m == 6 || m == 9 || m == 11) && d == 31) {
		makeAlert("Month " + m + " doesn`t have 31 days", "Date Error");
		return false
	}
	if (m == 2) {
		var r = y % 4 == 0;
		if (d > 29 || d == 29 && !r) {
			makeAlert("February " + y + " doesn`t have " + d + " days", "Date Error");
			return false
		}
	}
	return true
}

function eitherOr(e, t) {
	if (e.value != "") t.value = "";
	else if (e.value == "NaN") e.value = ""
}

function Calendar(e, t) {
	iF = document.getElementsByName(e).item(0);
	pB = iF;
	x = pB.offsetLeft;
	y = pB.offsetTop + pB.offsetHeight;
	var n = pB;
	while (n.offsetParent) {
		n = n.offsetParent;
		x += n.offsetLeft;
		y += n.offsetTop
	}
	dt = convertDate(iF.value, t);
	nN = document.createElement("div");
	nN.setAttribute("id", dateDivID);
	nN.setAttribute("style", "visibility:hidden;");
	document.body.appendChild(nN);
	cD = document.getElementById(dateDivID);
	cD.style.position = "absolute";
	cD.style.left = x + "px";
	cD.style.top = y + "px";
	cD.style.visibility = cD.style.visibility == "visible" ? "hidden" : "visible";
	cD.style.display = cD.style.display == "block" ? "none" : "block";
	cD.style.zIndex = 1e4;
	drawCalendar(e, dt.getFullYear(), dt.getMonth(), dt.getDate(), t)
}

function drawCalendar(e, t, n, r, s) {
	var o = new Date;
	if (n >= 0 && t > 0) o = new Date(t, n, 1);
	else {
		r = o.getDate();
		o.setDate(1)
	}
	TR = "<tr>";
	xTR = "</tr>";
	TD = "<td class='dpTD' onMouseOut='this.className=\"dpTD\";' onMouseOver='this.className=\"dpTDHover\";'";
	xTD = "</td>";
	html = "<table class='dpTbl'>" + TR + '<th class="dpTH" colspan="3">' + months[o.getMonth()] + " " + o.getFullYear() + "</th>" + '<td colspan="2">' + getButtonCode(e, o, -1, "<", s) + xTD + '<td colspan="2">' + getButtonCode(e, o, 1, ">", s) + xTD + xTR + TR;
	for (i = 0; i < days.length; i++) html += "<th class=\"dpTH\">" + days[i] + "</th>";
	html += xTR + TR;
	for (i = 0; i < o.getDay(); i++) html += TD + " " + xTD;
	do {
		dN = o.getDate();
		TD_onclick = " onclick=\"postDate('" + e + "','" + formatDate(o, s) + "');\">";
		if (dN == r) html += "<td" + TD_onclick + "<div class='dpDayHighlight'>" + dN + "</div>" + xTD;
		else html += TD + TD_onclick + dN + xTD; if (o.getDay() == 6) html += xTR + TR;
		o.setDate(o.getDate() + 1)
	} while (o.getDate() > 1);
	if (o.getDay() > 0)
		for (i = 6; i > o.getDay(); i--) html += TD + " " + xTD;
	html += "</table>";
	document.getElementById(dateDivID).innerHTML = html
}

function getButtonCode(e, t, n, r, i) {
	nM = (t.getMonth() + n) % 12;
	nY = t.getFullYear() + parseInt((t.getMonth() + n) / 12, 10);
	if (nM < 0) {
		nM += 12;
		nY += -1
	}
	return "<button onClick='drawCalendar(\"" + e + '",' + nY + "," + nM + "," + 1 + ',"' + i + "\");'>" + r + "</button>"
}

function formatDate(e, t) {
	ds = String(e.getDate());
	ms = String(e.getMonth() + 1);
	d = ("0" + e.getDate()).substring(ds.length - 1, ds.length + 1);
	m = ("0" + (e.getMonth() + 1)).substring(ms.length - 1, ms.length + 1);
	y = e.getFullYear();
	switch (t) {
	case "d/m/Y":
		return d + "/" + m + "/" + y;
	case "d.m.Y":
		return d + "." + m + "." + y;
	case "Y/m/d":
		return y + "/" + m + "/" + d;
	case "Y-m-d":
		return y + "-" + m + "-" + d;
	default:
		return m + "/" + d + "/" + y
	}
}

function convertDate(e, t) {
	var n, r, i;
	if (t == "d.m.Y") dA = e.split(".");
	else dA = e.split("/");
	switch (t) {
	case "d/m/Y":
		n = parseInt(dA[0], 10);
		r = parseInt(dA[1], 10) - 1;
		i = parseInt(dA[2], 10);
		break;
	case "d.m.Y":
		n = parseInt(dA[0], 10);
		r = parseInt(dA[1], 10) - 1;
		i = parseInt(dA[2], 10);
		break;
	case "Y/m/d":
		n = parseInt(dA[2], 10);
		r = parseInt(dA[1], 10) - 1;
		i = parseInt(dA[0], 10);
		break;
	default:
		n = parseInt(dA[1], 10);
		r = parseInt(dA[0], 10) - 1;
		i = parseInt(dA[2], 10);
		break
	}
	return new Date(i, r, n)
}

function postDate(e, t) {
	var n = document.getElementsByName(e).item(0);
	n.value = t;
	var r = document.getElementById(dateDivID);
	r.style.visibility = "hidden";
	r.style.display = "none";
	n.focus()
}

function clickDate() {
	Calendar(this.name, this.alt)
}

function changeDate() {
	isDate(this.value, this.alt)
}

function VerifyForm(e) {
	Clean = true;
	Alert = "";
	for (var t = 0, n = e.length; t < n; t++) {
		if (e.elements[t].type == "text") {
			var r = document.getElementsByName(e.elements[t].name);
			Class = r[0].getAttribute("class");
			if (r[0].getAttribute("minlength") > e.elements[t].value.length) {
				if (e.elements[t].value.length == 0) {
					Alert = Alert + "You must input a value in the field " + r[0].getAttribute("name") + "<br />"
				} else {
					Alert = Alert + r[0].getAttribute("name") + " field must be at least " + r[0].getAttribute("minlength") + " characters long" + "<br />"
				}
				r[0].className = Class + " inputerror";
				Clean = false
			} else {
				r[0].className = Class
			}
		}
		if (e.elements[t].type == "select-one") {
			Class = e.elements[t].getAttribute("class");
			if (e.elements[t].getAttribute("minlength") > 0 && e.elements[t].value.length == 0) {
				Alert = Alert + "You must make a selection in the field " + e.elements[t].getAttribute("name") + "<br />";
				e.elements[t].className = Class + " inputerror";
				Clean = false
			}
		}
		if (e.elements[t].type == "password") {
			Class = e.elements[t].getAttribute("class");
			if (e.elements[t].getAttribute("minlength") > 0 && e.elements[t].value.length == 0) {
				Alert = Alert + "You must make a selection in the field " + e.elements[t].getAttribute("name") + "<br />";
				e.elements[t].className = Class + " inputerror";
				Clean = false
			}
		}
		if (e.elements[t].type == "email") {
			Class = e.elements[t].getAttribute("class");
			if (e.elements[t].value.length > 0 && !validateEmail(e.elements[t].value)) {
				Alert = Alert + "You have not entered a valid email address <br />";
				e.elements[t].className = Class + " inputerror";
				Clean = false
			}
		}
	}
	if (Alert != "") {
		makeAlert(Alert, "Input Error")
	}
	return Clean
}

function SortSelect() {
	selElem = this;
	var e = new Array;
	th = document.getElementById("Theme").value;
	columnText = selElem.innerHTML;
	table = selElem.parentNode.parentNode;
	i = table.rows[0];
	for (var t = 0, n; n = i.cells[t]; t++) {
		if (i.cells[t].innerHTML == columnText) {
			columnNumber = t;
			s = getComputedStyle(i.cells[t], null);
			if (s.cursor == "s-resize") {
				i.cells[t].style.cursor = "n-resize";
				i.cells[t].style.backgroundImage = "url('css/" + th + "/images/descending.png')";
				i.cells[t].style.backgroundPosition = "right center";
				i.cells[t].style.backgroundRepeat = "no-repeat";
				i.cells[t].style.backgroundSize = "12px";
				direction = "a"
			} else {
				i.cells[t].style.cursor = "s-resize";
				i.cells[t].style.backgroundImage = "url('css/" + th + "/images/ascending.png')";
				i.cells[t].style.backgroundPosition = "right center";
				i.cells[t].style.backgroundRepeat = "no-repeat";
				i.cells[t].style.backgroundSize = "12px";
				direction = "d"
			}
		}
	}
	for (var r = 1, i; i = table.rows[r]; r++) {
		var o = new Array;
		for (var t = 0, n; n = i.cells[t]; t++) {
			if (i.cells[t].tagName == "TD") {
				o[t] = i.cells[t].innerHTML;
				columnClass = i.cells[columnNumber].className
			}
		}
		e[r] = o
	}
	e.sort(function (e, t) {
		if (direction == "a") {
			if (columnClass == "number") {
				return parseFloat(e[columnNumber]) - parseFloat(t[columnNumber])
			} else if (columnClass == "date") {
				da = new Date(e[columnNumber]);
				db = new Date(t[columnNumber]);
				return da > db
			} else {
				return e[columnNumber].localeCompare(t[columnNumber])
			}
		} else {
			if (columnClass == "number") {
				return parseFloat(t[columnNumber]) - parseFloat(e[columnNumber])
			} else if (columnClass == "date") {
				da = new Date(e[columnNumber]);
				db = new Date(t[columnNumber]);
				return da <= db
			} else {
				return t[columnNumber].localeCompare(e[columnNumber])
			}
		}
	});
	for (var r = 0, i; i = table.rows[r + 1]; r++) {
		var o = new Array;
		o = e[r];
		for (var t = 0, n; n = i.cells[t]; t++) {
			if (i.cells[t].tagName == "TD") {
				i.cells[t].innerHTML = o[t]
			}
		}
	}
	return
}

function remSelOpt(e, t) {
	len1 = t.options.length;
	for (i = 0; i < len1; i++) {
		if (t.options[i].value == e) {
			t.options[i] = null;
			break
		}
	}
}

function AddScript(e, t) {
	theme = document.getElementById("Theme").value;
	document.getElementById("favourites").innerHTML = document.getElementById("favourites").innerHTML + '<option value="' + e + '">' + t + "</option>";
	document.getElementById("PlusMinus").src = "css/" + theme + "/images/subtract.png";
	document.getElementById("PlusMinus").setAttribute("onClick", "javascript: RemoveScript('" + e + "', '" + t + "');");
	UpdateFavourites(e, t)
}

function RemoveScript(e, t) {
	theme = document.getElementById("Theme").value;
	remSelOpt(e, document.getElementById("favourites"));
	document.getElementById("PlusMinus").src = "css/" + theme + "/images/add.png";
	document.getElementById("PlusMinus").setAttribute("onClick", "javascript: AddScript('" + e + "', '" + t + "');");
	UpdateFavourites(e, t)
}

function UpdateFavourites(e, t) {
	Target = "UpdateFavourites.php?Script=" + e + "&Title=" + t;
	if (window.XMLHttpRequest) {
		xmlhttp = new XMLHttpRequest
	} else {
		xmlhttp = new ActiveXObject("Microsoft.XMLHTTP")
	}
	xmlhttp.open("GET", Target, true);
	xmlhttp.send();
	return false
}

/**
*  setDatebyAge will set the date by a given age
*  param elindex = a form object of the type input text
*  param date = the date format any one of ff:  yyyy-mm-dd, dd.mm.yyyy, mm/dd/yyyy
*  param lang = the ISO code of the language
*  return true if date is created, other wise false and the input entry will be erased
*/
function setDatebyAge(elindex, birth, date_format, lang) {
	var make_time = 0;
	var actual = '';
	/* Prepare the language dependent shortcuts */
	switch(lang.toLowerCase()) {
		case 'de': today = 'h';   // h = heute
				   yesterday = 'g';// g = gestern
				  break;
		case 'it': today = 'o';   // o = oggi
				   yesterday = 'i';// i = ieri
				  break;
		case 'es': today = 'h';  // h = hoy
				   yesterday = 'a';// a = ayer
				  break;
		case 'fr': today = 'a';  // h = aujourd'hui
				   yesterday = 'h';// a = hier
				  break;

		default:   today = 't';      // t = today
				   yesterday = 'y';  // y = yesterday
	}
	/* Extract the value of the input element an convert to lower case to be sure */
	var jetzt = new Date();
	var Jahr = jetzt.getFullYear();
	buf = Jahr-(elindex.value);
	if(elindex.value>0) {
		birth.value='01/07/'.concat(buf); //* Now set the value of the element
	}
	return true;
}

function ClearForm() {
	document.forms[0].reset();
	return false;
}

function edit() {
	var a = document.getElementById("EditAction");
	theme = document.getElementById("Theme").value;
	a.src = 'css/'+theme+'/images/save.png';
	a.onclick = save;
	var n = document.getElementsByTagName("div");
	for (i = 0; i < n.length; i++) {
		if (n[i].className == 'hideElements'){
			n[i].className = 'showElements';
		}
	}
	var n = document.getElementsByTagName("input");
	for (i = 0; i < n.length; i++) {
		if (n[i].type != 'checkbox' && n[i].type != 'hidden') {
			n[i].disabled = true;
			n[i].style.display = 'inline';
		}
	}
	var n = document.getElementsByTagName("label");
	for (i = 0; i < n.length; i++) {
		n[i].style.display = 'inline';
	}
	return false;
}

function SwapFields(radio, Field1, Field2) {
	var labels = document.getElementsByTagName('label');
	for (var i = 0; i < labels.length; i++) {
		if (labels[i].htmlFor != '') {
			var elem = document.getElementById(labels[i].htmlFor);
			if (elem)
				elem.label = labels[i];
		}
	}
	if (radio.value == 0) {
		document.getElementById(Field1).label.style.display = 'inline';
		document.getElementById(Field2).label.style.display = 'none';
		document.getElementById(Field1).style.display = 'inline';
		document.getElementById(Field2).style.display = 'none';
	} else {
		document.getElementById(Field2).label.style.display = 'inline';
		document.getElementById(Field1).label.style.display = 'none';
		document.getElementById(Field2).style.display = 'inline';
		document.getElementById(Field1).style.display = 'none';
	}
}
var counter=0;
function NewItem(select) {
	var html = document.getElementById('SoldItems').innerHTML;
	html = html + '<input type="hidden" name="Item'+counter+'" value="' + select.value + '" />';
	html = html + '<div class="inputdata">';
	html = html + '<label for="Name">Item Number&nbsp'+(counter+1)+'</label>';
	html = html + '<div name="Name">' + select.options[select.selectedIndex].text + '</div></div>';
	document.getElementById('SoldItems').innerHTML = html;
	select.selectedIndex = 0;
	select.required = false;
	counter++;
	document.getElementById('Count').innerHTML = '<input type="hidden" name="Items" value="' + counter + '" />';
}

function SaveFormProperties(FormName) {
	Target='SaveForm.php';
	var PostData='';
	PostData=PostData+'FormID='+document.getElementsByName('FormID')[0].value+'&';
	PostData=PostData+'FormName='+FormName.name+'&';
	for(var i=0,fLen=FormName.length;i<fLen;i++){
		if(FormName.elements[i].type=='checkbox' && !FormName.elements[i].checked) {
			FormName.elements[i].value='off';
			PostData=PostData+FormName.elements[i].name+'='+FormName.elements[i].value+'&';
		}
		if(FormName.elements[i].type=='checkbox') {
			PostData=PostData+FormName.elements[i].name+'='+FormName.elements[i].value+'&';
		}
	}
	if (window.XMLHttpRequest) {// code for IE7+, Firefox, Chrome, Opera, Safari
		xmlhttp=new XMLHttpRequest();
	} else {// code for IE6, IE5
		xmlhttp=new ActiveXObject("Microsoft.XMLHTTP");
	}
	xmlhttp.onreadystatechange=function() {
		if (xmlhttp.readyState==4 && xmlhttp.status==200) {
			document.getElementById('test').innerHTML=xmlhttp.responseText;
		}
	}
	xmlhttp.open("POST",Target,true);
	xmlhttp.setRequestHeader("Content-type","application/x-www-form-urlencoded");
	xmlhttp.setRequestHeader("Cache-Control","no-store, no-cache, must-revalidate");
	xmlhttp.setRequestHeader("Pragma","no-cache");
	xmlhttp.send(PostData);
	return true;
}

function save() {
	ThisForm=document.getElementsByTagName("form");
	SaveFormProperties(ThisForm[0]);
	ThisForm[0].submit();
}

function SubmitSearchForm(FormName, Element) {
	Target='SearchForm.php';
	var PostData='';
	for(var i=0,fLen=FormName.length;i<fLen;i++){
		if(FormName.elements[i].type=='checkbox' && !FormName.elements[i].checked) {
			FormName.elements[i].value=null;
		}
		PostData=PostData+FormName.elements[i].name+'='+FormName.elements[i].value+'&';
	}
	if (window.XMLHttpRequest) {// code for IE7+, Firefox, Chrome, Opera, Safari
		xmlhttp=new XMLHttpRequest();
	} else {// code for IE6, IE5
		xmlhttp=new ActiveXObject("Microsoft.XMLHTTP");
	}
	xmlhttp.onreadystatechange=function() {
		if (xmlhttp.readyState==4 && xmlhttp.status==200) {
			document.getElementById(Element).innerHTML=xmlhttp.responseText;
		}
	}
	xmlhttp.open("POST",Target,true);
	xmlhttp.setRequestHeader("Content-type","application/x-www-form-urlencoded");
	xmlhttp.setRequestHeader("Cache-Control","no-store, no-cache, must-revalidate");
	xmlhttp.setRequestHeader("Pragma","no-cache");
	xmlhttp.send(PostData);
	return false;
}

function initial() {
	if (document.getElementsByTagName) {
		var e = document.getElementsByTagName("a");
		for (i = 0; i < e.length; i++) {
			var t = e[i];
			if (t.getAttribute("href") && t.getAttribute("rel") == "external") t.target = "_blank"
		}
	}
	var n = document.getElementsByTagName("input");
	for (i = 0; i < n.length; i++) {
		if (n[i].className == "date") {
			n[i].onclick = clickDate;
			n[i].onchange = changeDate
		}
		if (n[i].className == "number") n[i].onkeypress = rTN;
		if (n[i].className == "integer") n[i].onkeypress = rTI;
		if (n[i].type == "tel") n[i].pattern = "[0-9 +s()]*";
		if (n[i].type == "email") n[i].pattern = "^[_a-z0-9-]+(.[_a-z0-9-]+)*@[a-z0-9-]+(.[a-z0-9-]+)*(.[a-z]{2,4})$"
	}
	var n = document.getElementsByTagName("th");
	for (i = 0; i < n.length; i++) {
		if (n[i].className == "SortableColumn") {
			n[i].onclick = SortSelect
		}
	}
}
days = new Array("Su", "Mo", "Tu", "We", "Th", "Fr", "Sa");
months = new Array("Jan", "Feb", "Mar", "Apr", "May", "Jun", "Jul", "Aug", "Sep", "Oct", "Nov", "Dec");
dateDivID = "calendar";
window.onload = initial
