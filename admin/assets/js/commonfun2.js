function isNumberKey(evt) {

	var charCode = (evt.which) ? evt.which : event.keyCode

	if (charCode > 31 && (charCode < 48 || charCode > 57))

		return false;



	return true;

}



function formReset(val) {

	document.getElementById(val).reset();

	return false;

}



function addTextToCombo(combo, index, newText, newValue) {

	var newOpt1 = new Option(newText, newValue);

	combo.options[index] = newOpt1;

	combo.selectedIndex = 0;

}



// for open window in center position //

function PopupCenter(pageURL, title, w, h) {

	//alert('hi');

	var left = (screen.width / 2) - (w / 2);

	var top = (screen.height / 2) - (h / 2);

	var targetWin = window.open(pageURL, title, 'toolbar=no, location=no, directories=no, status=no, menubar=no, scrollbars=no, resizable=no, copyhistory=no, titlebar=no, width=' + w + ', height=' + h + ', top=' + top + ', left=' + left);

}


// JavaScript Document

function showimg() {

	document.getElementById('imgload').style.display = "block";

	return true;

}





// for masters //

function checkinputmaster(id) {

	//alert(id)

	var n = id.split(",");

	var msg = 0;

	for (var i = 0; i < n.length; i++) {

		//alert(n[i])

		var idname = n[i].split(" ");

		var name = document.getElementById(idname[0]).value.trim();

		var datatype = idname[1];

		//alert(name)

		if (name == "") {

			//alert("Fill Mandatory Field");

			document.getElementById(idname[0]).value = "";

			document.getElementById(idname[0]).focus();





			//document.getElementById(idname[0]).style.borderColor = "red";

			//jQuery(idname[0]).append("this text was appended");

			//document.getElementById(idname[0]).style.color = "red";

			$('#func_date_temp').remove();

			inputNode = document.getElementById(idname[0]);

			var spanTag = document.createElement("span");

			spanTag.style.color = "red";

			spanTag.innerHTML = " Mandatory field ";

			spanTag.id = 'error-span';

			//spanTag.addClass('myclass-new');

			spanTag.classList.add("mystyle");

			inputNode.parentNode.insertBefore(spanTag, inputNode.nextSibling);

			return false;

			msg = 1;

			break;

		}

		else {

			$('#error-span').remove();

		}





		if (datatype == "al") {

			if (!onlyalphabets(name)) {

				alert("Please Enter only Alphabet");

				document.getElementById(idname[0]).value = "";

				document.getElementById(idname[0]).focus();

				msg = 1;

				break;

			}

		}

		else if (datatype == "an") {

			if (!alphanumeric(name)) {

				alert("Please Enter only Alpha Numeric Value");

				document.getElementById(idname[0]).value = "";

				document.getElementById(idname[0]).focus();

				msg = 1;

				break;

			}

		}

		else if (datatype == "nu") {

			if (!numeric(name)) {

				alert("Please Enter only Numbers");

				document.getElementById(idname[0]).value = "";

				document.getElementById(idname[0]).focus();

				msg = 1;

				break;

			}

		}

		else if (datatype == "dt") {

			if (!parseDate(name)) {

				alert("Please Enter only Date");

				document.getElementById(idname[0]).value = "";

				document.getElementById(idname[0]).focus();

				msg = 1;

				break;

			}

		}

	}

	//alert(msg)

	if (msg == 1) {

		//alert("false")

		return false;

	}

	else {

		//alert("ok")

		showimg();

		return true;

	}

}





function onlyalphabets(name) {

	var reg = /^[a-zA-Z -]*$/;

	return reg.test(name);

}



function alphanumeric(name) {

	var reg = /^[a-zA-Z 0-9 -.]*$/;

	return reg.test(name);

}



function numeric(name) {

	var reg = /^[0-9 -.]*$/;

	return reg.test(name);

}



function parseDate(name) {

	var reg = /^(\d{1,2})-(\d{1,2})-(\d{4})$/;

	return reg.test(name);

}





function showdiv() {

	//document.getElementById('divmsg').style.display="block";

	$('#divmsg').animate({ backgroundColor: '#FFF2DF' }, 300).fadeIn(1000, function () {

		$('#divmsg').show();

	});

	setTimeout("hidediv()", 3000);



}

function hidediv() {

	//document.getElementById('divmsg').style.display="none";

	$('#divmsg').animate({ backgroundColor: '#FEBAA7' }, 300).fadeOut(1000, function () {

		$('#divmsg').hide();

	});

}



function removeexistmsg() {

	document.getElementById('existsid').style.display = "none";

}



function removeexistmsg1() {

	document.getElementById('existsid1').style.display = "none";

}



// Date Validation and Format Javascript

// copyright 11th June 2007 by Stephen Chapman

// permission to use this Javascript on your web page is granted

// provided that all of the code in this script (including these

// comments) is used without any alteration

// you may swap the 12 and 31 around if you want mm/dd instead of dd/mm



function dtval(d, e) {

	var pK = e ? e.which : window.event.keyCode;

	if (pK == 8) { d.value = substr(0, d.value.length - 1); return; }

	var dt = d.value;

	var da = dt.split('-');

	for (var a = 0; a < da.length; a++) { if (da[a] != +da[a]) da[a] = da[a].substr(0, da[a].length - 1); }

	if (da[0] > 31) { da[1] = da[0].substr(da[0].length - 1, 1); da[0] = '0' + da[0].substr(0, da[0].length - 1); }

	if (da[1] > 12) { da[2] = da[1].substr(da[1].length - 1, 1); da[1] = '0' + da[1].substr(0, da[1].length - 1); }

	if (da[2] > 9999) da[1] = da[2].substr(0, da[2].length - 1);

	dt = da.join('-');

	if (dt.length == 2 || dt.length == 5) dt += '-';

	d.value = dt;

}