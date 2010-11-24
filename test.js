function testreturn()
{
	if(movimAjax.readyState == 4 && movimAjax.status == 200) {
		document.getElementById("testzone").innerHTML = movimAjax.responseText;
	}
}
