<div id="visio">
    <div id="log">
    
    </div>
	<video autoplay="true" id="remote-video">
	
	</video>
	<video autoplay="true" id="local-video" muted="true">
	
	</video>

    <div id="avatar">
        <img src="{$avatar}"/>
        <span class="name">{$name}</span>
        <div id="status"></div>
    </div>
    <div class="menu">
        <a id="toggle-microphone" class="button color icon microphone alone merged right oppose">
        </a><a id="toggle-camera" class="button color icon camera alone merged left oppose">
        </a>
        
        <a id="call" class="button color green icon call">
            {$c->t('Call')}
        </a>
        <a id="hang-up" class="button color red icon hang-up">
            {$c->t('Hang Up')}
        </a>

        <a id="toggle-screen" class="button icon expand color blue alone oppose"></a>
    </div>
</div>
<script type="text/javascript">
    VISIO_JID = '{$jid}';
    VISIO_RESSOURCE = '{$ressource}';
    VISIO_TURN_LIST = {$turn_list};
</script>
<div id="connection">
	{$c->t('Connection')}...
</div>
