<div id="chat_widget">
    <script type="text/javascript">
        Chat.pagination = {$pagination};
        Chat.delivery_error = '{$c->__("message.error")}';
    </script>
    {autoescape="off"}
        {$c->prepareEmpty()}
    {/autoescape}
</div>
