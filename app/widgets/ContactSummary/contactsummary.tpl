<div id="contactsummary">
    {$c->prepareContactSummary($contact)}
    
    <script type="text/javascript">
        MovimWebsocket.attach(function()
        {
            {$refresh}
        });
    </script>
</div>
