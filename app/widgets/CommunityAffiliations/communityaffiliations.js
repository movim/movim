var CommunityAffiliations = {
    update : function(jid) {
        var parts = MovimUtils.urlParts();
        if (parts.params.length > 0) {
            CommunityAffiliations_ajaxChangeAffiliation(
                parts.params[0],
                parts.params[1],
                MovimUtils.formToJson(jid)
            );
        }
    }
}

MovimWebsocket.attach(function() {
    var parts = MovimUtils.urlParts();
    if (parts.params.length > 0) {
        CommunityAffiliations_ajaxGetSubscriptions(parts.params[0], parts.params[1]);
        CommunityAffiliations_ajaxGetAffiliations(parts.params[0], parts.params[1]);
    }
});
