function SubmitForm(FN, Element) {
//function SubmitForm(FormName) {
	FormName=document.getElementById(FN)
	Target='includes/ProcessForm.php';
	var PostData='';
	for(var i=0,fLen=FormName.length;i<fLen;i++){
		if(FormName.elements[i].type=='checkbox' && !FormName.elements[i].checked) {
			FormName.elements[i].value=null;
		}
		if(FormName.elements[i].name.substring(0,4)!='view'&&FormName.elements[i].name.substring(0,8)!='required') {
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
