<script type="text/javascript">
    function ackRequest(arg) {
        var to = arg[0];
        var id = arg[1];
        {$ack}
    }
    
    function discoInfoRequest(arg) {
        var to = arg[0];
        var id = arg[1];
        //{$discoinfo}
    }
    
    function capsRequest(arg) {
        var to      = arg[0];
        var node    = arg[1];
        {$caps}
    }
</script>
