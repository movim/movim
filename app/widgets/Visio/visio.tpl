<div id="visio">
    <header class="fixed">
        <ul class="list">
            <li>
                <span class="primary icon color transparent active on_mobile" onclick="Visio.onTerminate(); window.close()">
                    <i class="material-icons">close</i>
                </span>
                <span id="toggle_fullscreen" class="control icon color transparent active" onclick="Visio.toggleFullScreen()">
                    <i class="material-icons">fullscreen</i>
                </span>
                <span id="toggle_audio" class="control icon color transparent active" onclick="Visio.toggleAudio()">
                    <i class="material-icons">mic</i>
                </span>
                <span id="toggle_video" class="control icon color transparent active" onclick="Visio.toggleVideo()">
                    <i class="material-icons">videocam</i>
                </span>
                <span id="switch_camera" class="control icon color transparent active" onclick="Visio.toggleVideo()">
                    <i class="material-icons">switch_camera</i>
                </span>
            </li>
        </ul>
    </header>
    <select id="visio_source"></select>

    <ul class="list infos" class="list middle">
        {$url = $contact->getPhoto('l')}
        <li>
            {if="$url"}
                <p class="center">
                    <img src="{$url}">
                </p>
            {/if}

            <p class="normal center	">
                {$contact->truename}
            </p>
            <p class="normal state center"></p>
        </li>
    </ul>

    <video id="remote_video" autoplay poster="noposter"></video>
    <video id="video" autoplay muted poster="noposter"></video>
    <canvas class="level"></canvas>
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
    ended: '{$c->__('visio.ended')}'
}
</script>
