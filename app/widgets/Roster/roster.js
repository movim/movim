var Roster = {
    init : function() {
        var search      = document.querySelector('#rostersearch');
        if(search == null) return;

        var roster      = document.querySelector('#roster');
        var rosterlist  = document.querySelector('#rosterlist');

        search.oninput = function(event) {
            if(search.value.length > 0) {
                roster.classList.add('search');
            } else {
                roster.classList.remove('search');
            }

            document.querySelectorAll(
                '#rosterlist > li.found'
            ).forEach(item => item.classList.remove('found'));

            document.querySelectorAll(
                '#rosterlist > li[name*="' + MovimUtils.cleanupId(search.value).slice(3) + '"]'
            ).forEach(item => item.classList.add('found'));;
        };
    },
    setFound : function(jid) {
        document.querySelector('input[name=searchjid]').value = jid;
    },
    addGatewayPrompt : function(jid, prompt, desc) {
        var ctx = document.querySelector('select[name=gateway]');
        ctx.prompts = ctx.prompts || {};
        ctx.prompts[jid] = { prompt: prompt, desc: desc };
    },
    drawGatewayPrompt : function() {
        var ctx = document.querySelector('select[name=gateway]');
        if(!ctx) return;
        var prompt = (ctx.prompts && ctx.prompts[ctx.value]) || {};
        document.querySelector('label[for=searchjid]').textContent = prompt.prompt;
        document.querySelector('input[name=gatewayprompt]').value = prompt.prompt ? 1 : '';

        var searchjid = document.querySelector('input[name=searchjid]');
        searchjid.title = prompt.desc;
        searchjid.placeholder = prompt.desc === 'JID' ? 'user@server.tld' : '';
    },
    errorGatewayPrompt : function(errorid, message) {
        document.querySelector('input[name=searchjid] ~ .error').textContent = message || errorid;
    }
};

MovimWebsocket.attach(function() {
    Notification.current('contacts');
});


movim_add_onload(function() {
    Roster.init();
});
