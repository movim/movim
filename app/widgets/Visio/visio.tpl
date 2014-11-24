<div id="visio">
    <div id="log">
        
    </div>
	<video autoplay="true" id="remote-video">
	
	</video>
	<video autoplay="true" id="local-video" muted="true">
	
	</video>

    <div id="avatar">

    </div>
    <div class="menu">
        <a id="toggle-microphone" class="button color alone merged right oppose">
            <i class="fa fa-microphone"></i> 
        </a><a id="toggle-camera" class="button color alone merged left oppose">
            <i class="fa fa-video-camera"></i> 
        </a>
        
        <a id="call" class="button color green">
            <i class="fa fa-phone"></i> {$c->__('visio.call')}
        </a>
        <a id="hang-up" class="button color red icon hang-up">
            <i class="fa fa-stop"></i> {$c->__('visio.hang_up')}
        </a>

        <a id="toggle-screen" class="button color blue alone oppose"><i class="fa fa-expand"></i></a>
    </div>
</div>
<script type="text/javascript">
    VISIO_TURN_LIST = {$turn_list};
</script>
<div id="connection">
	{$c->__('visio.connection')}...
</div>
