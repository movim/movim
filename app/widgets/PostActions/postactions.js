var PostActions = {
    handleDelete : function(url) {
        var page = MovimUtils.urlParts().page;
        if (page == 'post') {
            MovimUtils.redirect(url);
        }
    }
}
