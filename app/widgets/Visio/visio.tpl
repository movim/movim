<div id="visio">
    <header class="fixed">
        <ul class="list">
            <li>
                <span id="toggle_fullscreen" class="control icon color transparent active" onclick="VisioUtils.toggleFullScreen()">
                    <i class="material-icons">fullscreen</i>
                </span>
                <span id="toggle_audio" class="divided control icon color transparent active" onclick="VisioUtils.toggleAudio()">
                    <i class="material-icons">mic_off</i>
                </span>
                {if="$withvideo"}
                    <span id="toggle_video" class="control icon color transparent active" onclick="VisioUtils.toggleVideo()">
                        <i class="material-icons">videocam_off</i>
                    </span>
                    <span id="switch_camera" class="control icon color transparent active">
                        <i class="material-icons">switch_camera</i>
                    </span>
                    <span id="screen_sharing" class="control icon color transparent active" onclick="VisioUtils.toggleScreenSharing()">
                        <i class="material-icons">screen_share</i>
                    </span>
                {/if}
                <div>
                    <p></p>
                    <p id="no_mic_sound" class="disabled all">
                        <i class="material-icons">mic_none</i>
                        {$c->__('visioconfig.no_mic_sound')}
                    </p>
                </div>
            </li>
        </ul>
    </header>
    <select id="visio_source"></select>

    <ul class="list infos" class="list middle">
        {$url = $contact->getPhoto('l')}
        <li>
            <div>
                <div id="remote_level">
                    {if="$url"}
                        <div class="avatar">
                            <img src="{$url}">
                        </div>
                    {else}
                        <div class="avatar color {$contact->jid|stringToColor}">
                            {$contact->truename|firstLetterCapitalize}
                        </div>
                    {/if}
                </div>
                <p class="normal center">{$contact->truename}</p>
                <p class="normal state center"></p>
            </div>
        </li>
    </ul>

    <audio id="remote_audio" autoplay></audio>
    <audio id="audio" autoplay muted></audio>

    {if="$withvideo"}
        <video id="remote_video" autoplay poster="/theme/img/empty.png"></video>
        <video id="screen_sharing_video" autoplay muted poster="/theme/img/empty.png"></video>
        <video id="video" autoplay muted poster="/theme/img/empty.png"></video>
    {/if}

    <span id="remote_state">
        <i class="voice material-icons"></i>
        {if="$withvideo"}
            <i class="webcam material-icons"></i>
        {/if}
    </span>

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
</script>
