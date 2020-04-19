<div id="visio">
    <header class="fixed">
        <ul class="list">
            <li>
                <span class="primary icon color transparent active" onclick="Visio.onTerminate(); window.close()">
                    <i class="material-icons">close</i>
                </span>
                <span id="toggle_fullscreen" class="control icon color transparent active" onclick="VisioUtils.toggleFullScreen()">
                    <i class="material-icons">fullscreen</i>
                </span>
                <span id="toggle_audio" class="control icon color transparent active" onclick="VisioUtils.toggleAudio()">
                    <i class="material-icons">mic</i>
                </span>
                {if="$withvideo"}
                    <span id="toggle_video" class="control icon color transparent active" onclick="VisioUtils.toggleVideo()">
                        <i class="material-icons">videocam</i>
                    </span>
                    <span id="switch_camera" class="control icon color transparent active">
                        <i class="material-icons">switch_camera</i>
                    </span>
                {/if}
                <div><p></p></div>
            </li>
        </ul>
    </header>
    <select id="visio_source"></select>

    <ul class="list infos" class="list middle">
        {$url = $contact->getPhoto('l')}
        <li>
            <div>
                {if="$url"}
                    <p class="center">
                        <img src="{$url}">
                    </p>
                {/if}
                <p class="normal center">{$contact->truename}</p>
                <p class="normal state center"></p>
            </div>
        </li>
    </ul>

    <audio id="remote_audio" autoplay></audio>
    <audio id="audio" autoplay muted></audio>

    {if="$withvideo"}
        <video id="remote_video" autoplay poster="/theme/img/empty.png"></video>
        <video id="video" autoplay muted poster="/theme/img/empty.png"></video>
    {/if}

    <div class="controls">
        <a id="main" class="button action color gray">
            <i class="material-icons">phone</i>
        </a>
    </div>
</div>
<script type="text/javascript">
Visio.states = {
    calling: '{$c->__('visio.calling')}',
    ringing: '{$c->__('visio.ringing')}',
    in_call: '{$c->__('visio.in_call')}',
    failed: '{$c->__('visio.failed')}',
    connecting: '{$c->__('visio.connecting')}',
    ended: '{$c->__('visio.ended')}',
    declined: '{$c->__('visio.declined')}'
};

Visio.externalServices = {autoescape="off"}{$externalservices}{/autoescape};
</script>
