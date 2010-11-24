// wait for the DOM to be loaded 
$(document).ready(function() {

$('#right') .css({'height': (($(window).height()) - 25)+'px'});
$('#center') .css({'height': (($(window).height()) - 0)+'px'});
window.onresize = function() {
	$('#right') .css({'height': (($(window).height()) - 0)+'px'});
	$('#center') .css({'height': (($(window).height()) - 25)+'px'});
}

$('#config').ajaxForm(function() { 
	window.location.replace("?page=config");
}); 

$('#rosterList').click(function() {
	if( $('#rosterL').is(':visible') ) {
		$('#rosterL').css('display', 'none');
	}
	else {
		$('#rosterL').css('display', 'block');
	}
});
            
/*$('#authForm').submit(function() {
	$.ajax({
	    type: "POST",
	    url: "../page/login.php?login="+$('#login').val()+"&pass="+$('#pass').val(),
	    success: result,
	    dataType: "html"
    });
    //window.location.replace("");
	return false;
});*/
			
$('li#home').click(function() {
	$.ajax({
	    type: "POST",
	    url: "../page/ajax.php?page=home",
	    success: result,
	    dataType: "html"
    });
	return false;
});
			
$('li#config').click(function() {
	$.ajax({
	    type: "POST",
	    url: "../page/ajax.php?page=config",
	    success: result,
	    dataType: "html"
    });
	return false;
});

$('li#menuAddUser').click(function() {
	if( $('#dialog').is(':visible') ) {
		$('#dialog').fadeOut(300);
	}
	else {
		$('#dialog').fadeIn(300);
	}		
});
			
$('li#menuLogout').click(function() {

    jaxl.disconnect();
	$.ajax({
	    type: "POST",
	    url: "../page/ajax.php?logout=on",
	    success: result,
	    dataType: "html"
    });
	window.location.replace("");
	return false;	
});
					
function result(data){
    $('div#content').html(data);
}

            	$('#right') .css({'height': (($(window).height()) - 25)+'px'});
            	$('#center') .css({'height': (($(window).height()) - 25)+'px'});
            window.onresize = function() {
            	$('#right') .css({'height': (($(window).height()) - 25)+'px'});
            	$('#center') .css({'height': (($(window).height()) - 25)+'px'});
        	}

			$('.submit').click(function() {
				obj = new Object;
				obj['jaxl'] = 'addUser';
				obj['jid'] = $("#jid").val();
				obj['name'] = $("#name").val();
				obj['group'] = $("#group").val();
			
				jaxl.sendPayload(obj);
				
				obj = new Object;
				obj['jaxl'] = 'getRosterList';
				jaxl.sendPayload(obj);
			});
			
			$('.write').focus(function() {
			    $(this).val('');
			    $(this).css('color', '#444');
			});
			
			jaxl.payloadHandler = new Array('boshchat', 'payloadHandler');
			$('#button input').click(function() {
			    if($(this).val() == 'Connect') {
			        $(this).val('Connecting...');
				        obj = new Object;
				        jaxl.connect(obj);
			    }
			    else if($(this).val() == 'Disconnect') {
			        $(this).val('Disconnecting...');
			        jaxl.disconnect();
			    }
			});
			
			$('#write').focus(function() {
			    $(this).val('');
			    $(this).css('color', '#444');
			});
			$('#write').blur(function() {
			    if($(this).val() == '') $(this).val('Type your message');
			    $(this).css('color', '#AAA');
			});
			$('#write').keydown(function(e) {
			    if(e.keyCode == 13 && jaxl.connected) {
			        message = $.trim($(this).val());
			        if(message.length == 0) return false;
			        $(this).val('');
			
			        boshchat.appendMessage(boshchat.prepareMessage(jaxl.jid, message));
			
			        obj = new Object;
			        obj['jaxl'] = 'message';
			        obj['message'] = message;
			        jaxl.sendPayload(obj);
			    }
			});
			
			/*$('#status').keydown(function(e) {
			    if(e.keyCode == 13 && jaxl.connected) {
			        status = $.trim($(this).val());
			        if(status.length == 0) return false;
			        $(this).val('');
			
			        obj = new Object;
			        obj['jaxl'] = 'setStatus';
			        obj['status'] = status;
			        obj['priority'] = -8;
			        jaxl.sendPayload(obj);
			    }
			});*/
			
			$('#statusForm').click(function() {
					obj = new Object;
			        obj['jaxl'] = 'setStatus';
			        obj['status'] = $("#statusStatus").val();
			        obj['show'] = $("#statusShow").val();
			        jaxl.sendPayload(obj);
			});
}); 
/* END */


/* JAXL HANDLER */
var boshchat = {
   payloadHandler: function(payload) {
		if(payload.jaxl == 'authFailed') {
			jaxl.connected = false;
			$('#button input').attr('src', template_base+'img/connect.png');
		}
		else if(payload.jaxl == 'connected') {
		        jaxl.connected = true;
		        jaxl.jid = payload.jid;

				jaxl.sendPayload(obj);
		        $('#button img').attr('src', template_base+'img/disconnect.png');
		        $('#menuAddUser').fadeIn(300);
		        $('#rosterList').fadeIn(300);
		        $('#menuNotif').fadeIn(300);
		        obj = new Object;
		        obj['jaxl'] = 'getRosterList';
		        jaxl.sendPayload(obj);
		        
		        $.ajax({
					type: "POST",
					url: "../page/ajax.php?page=home",
					success: function(results,status) {
							$('div#content').html(results);
						}
					//dataType: "html"
				});
		}
		
		else if(payload.jaxl == 'rosterList') {
		        obj = new Object;
		        obj['jaxl'] = 'setStatus';
				html ='';
				var n=0;
				html += '<div id="roster">';               
		        for (var i in payload.roster)
		        {
					html += '<div class="user">'+payload.roster[i].name+i+'</div>';
					n++;
				}
		
				html += '</div>';
				$('#rosterL').html(html);
				$('#rosterList').html('<img src="'+template_base+'img/group.png"/> Liste de contacts ('+n+')');
			    jaxl.sendPayload(obj);
		}
		else if(payload.jaxl == 'disconnected') {
		        jaxl.connected = false;
		        jaxl.disconnecting = false;
		        $('#read').css('display', 'none');
		        $('#how').css('display', 'none');
		        $('#uname').css('display', 'block');
		        $('#passwd').css('display', 'block');
		        
	        $('#button input').attr('src', template_base+'img/connect.png');
			        console.log('disconnected');
		}
		else if(payload.jaxl == 'message') {
			boshchat.appendMessage(jaxl.urldecode(payload.message));
		    jaxl.ping();
		}
		else if(payload.jaxl == 'presence') {
			$('#notification').fadeIn(300);
			$('#notification').html(jaxl.urldecode(payload.presence)).delay(1000);
			$('#notification').fadeOut(300);
		    jaxl.ping();
		}
		else if(payload.jaxl == 'pinged') {
		    jaxl.ping();
		}
	},
	appendMessage: function(message) {
		$('#read').prepend($(message).hide().fadeIn(1500));
		$('#read').animate({ scrollTop: $('#read').attr('scrollHeight') }, 300);
		$('#right') .css({'height': (($(window).height()) - 25)+'px'});
		$('#center') .css({'height': (($(window).height()) - 25)+'px'});
	},
	prepareMessage: function(jid, message) {
		html = '';
		html += '<div class="roll">';
		html += '<div class="roll_title"><p class="from">'+jid+'</p></div>';
		html += '<p class="body">'+message+'</div>';
		html += '</div>';
		return html;
	}
};
