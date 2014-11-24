<div id="contactsummary_widget">
    {$c->prepareContactSummary($contact)}
    
    <script type="text/javascript">
        MovimWebsocket.attach(function()
        {
            {$refresh}
            MovimMap.addContact();
        });
    </script>
</div>
