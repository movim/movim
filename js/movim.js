var movimAjax;

function makeXMLHttpRequest()
{
	if (window.XMLHttpRequest) {// code for real browsers
		return new XMLHttpRequest();
	} else {// code for IE6, IE5
		return new ActiveXObject("Microsoft.XMLHTTP");
	}
}

function xmlToString(xml){
	var xmlString;

	if(xml.xml) { // IE
		xmlString = xml.xml;
	} else { // Real browsers
		xmlString = (new XMLSerializer).serializeToString(xml);
	}
	
	return xmlString;
}

movimAjax = makeXMLHttpRequest();

function movimPack(data)
{
	var outBuffer = "";
	
	// The given array must be "associative".
	if(data.length % 2 != 0) {
		return "";
	}
		
	for(var i = 0; i < data.length; i += 2) {
		outBuffer += '<param name="' + data[i]
			+ '" value="' + data[i + 1] + '" />' + "\n";
	}
	
	return outBuffer;
}

function movim_ajaxSend(widget, func, parameters, callback)
{
	// Regenerating the client everytime (necessary for IE)
	movimAjax = makeXMLHttpRequest();
	
	var request = '<funcall widget="'+ widget
		+ '" name="' + func + '">' + "\n"
		+ parameters + '</funcall>' + "\n";

	movimAjax.open('POST', 'jajax.php', true);
	movimAjax.onreadystatechange = callback;
	movimAjax.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");
//	movimAjax.send(request);
	movimAjax.send("<?xml version='1.0' encoding='UTF-8'?>" + request);
}