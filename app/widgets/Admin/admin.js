var tz_list;
var original = true;

function update(elt){
	original = false;
    if (elt.selectedIndex == -1)
        return null;
	text = elt.options[elt.selectedIndex].text;
	h_m = text.split("(")[1].split(")")[0].split(".");
	var today = new Date();
	if(h_m[0]<0){
		h_m[0] = h_m[0].substr(1); 
		today.setHours(today.getHours() - parseInt(h_m[0]));
		today.setMinutes(today.getMinutes() - parseInt(h_m[1]));
	}
	else{
		today.setHours(today.getHours() + parseInt(h_m[0]));
		today.setMinutes(today.getMinutes() + parseInt(h_m[1]));
	}
    return today;
}
movim_add_onload(function()
{
	tz_list = document.querySelector("#timezone");
	
	tz_list.onchange = function(e){
		document.querySelector(".dTimezone").innerHTML = update(tz_list).toUTCString();
	}
	setInterval(
	function(){
		if(original){
			date = new Date();
			document.querySelector(".dTimezone").innerHTML = date.toUTCString();
		}
		else{
			date = new Date(document.querySelector(".dTimezone").innerHTML);
			date.setSeconds(date.getSeconds() + 1);
			document.querySelector(".dTimezone").innerHTML = date.toUTCString();
		}
	}
	,1000);
});
