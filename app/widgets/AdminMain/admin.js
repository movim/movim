var tz_list;
var original = true;
var operators = {
    '+': function(a, b) { return a + b },
    '-': function(a, b) { return a - b },
};

function update(elt){
    if (elt.selectedIndex == -1)
        return null;
    //Get the offset from the selected option
    text = elt.options[elt.selectedIndex].text;
    //Determine if it is a positive or negative offset
    sign = text.indexOf("+") > -1 ? "+" : "-";
    //Seperate hours and minutes and get the offset in ms
    h_m = text.split(sign)[1].split(")")[0].split(":");
    tzOffset = parseInt(h_m[0]) * 3600000 + parseInt(h_m[1]) * 60000;
    //Get the offset between your computer and UTC
    pcOffset = new Date().getTimezoneOffset() * 60000;
    
    return new Date(operators[sign]((new Date().getTime() + pcOffset), tzOffset));
}
movim_add_onload(function()
{
    tz_list = document.querySelector("#timezone");
    tz_list.onchange = function(e){
        newTime = update(tz_list);
        formatDate(newTime);
    }
    setInterval(
    function(){ //increment time each second
        date = new Date(document.querySelector(".dTimezone").innerHTML).getTime() + 1000;
        date = formatDate(new Date(date));
    }
    ,1000);
    
    formatDate = function (newTime){
        h = newTime.getHours()<10 ? "0" + newTime.getHours() : newTime.getHours();
        m = newTime.getMinutes()<10 ? "0" + newTime.getMinutes() : newTime.getMinutes();
        s = newTime.getSeconds()<10 ? "0" + newTime.getSeconds() : newTime.getSeconds();
        document.querySelector(".dTimezone").innerHTML = newTime.toDateString() + " " + h+ ":" + m + ":" + s;
    }
});
