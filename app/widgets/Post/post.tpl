<div id="post_widget">
    {$c->prepareEmpty()}
    <script type="text/javascript">
        MovimWebsocket.attach(function() {
            var nodeid = MovimUtils.urlParts().params[0];
            if(nodeid) {
                Post_ajaxGetPost(nodeid);
                MovimTpl.showPanel();
            }
        });
    </script>
</div>
