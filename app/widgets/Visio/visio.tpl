<div id="visio">
    <header class="fixed">
        <ul class="list">
            <li>
                <span id="toggle_fullscreen" class="control icon color transparent active" onclick="VisioUtils.toggleFullScreen()">
                    <i class="material-symbols">fullscreen</i>
                </span>
                {if="!$withvideo"}
                    <span id="toggle_dtmf" class="control icon color transparent active" onclick="VisioUtils.toggleDtmf()">
                        <i class="material-symbols">dialpad</i>
                    </span>
                {/if}
                <span id="toggle_audio" class="divided control icon color transparent active" onclick="VisioUtils.toggleAudio()">
                    <i class="material-symbols">mic_off</i>
                </span>
                {if="$withvideo"}
                    <span id="toggle_video" class="control icon color transparent active" onclick="VisioUtils.toggleVideo()">
                        <i class="material-symbols">videocam_off</i>
                    </span>
                    <span id="switch_camera" class="control icon color transparent active">
                        <i class="material-symbols">switch_camera</i>
                    </span>
                    <span id="screen_sharing" class="control icon color transparent active" onclick="VisioUtils.toggleScreenSharing()">
                        <i class="material-symbols">screen_share</i>
                    </span>
                {/if}
                <div>
                    <p></p>
                    <p id="no_mic_sound" class="disabled all">
                        <i class="material-symbols">mic_none</i>
                        {$c->__('visioconfig.no_mic_sound')}
                    </p>
                </div>
            </li>
        </ul>
    </header>
    <select id="visio_source"></select>

    {if="!$withvideo"}
        <div id="dtmf" class="hide">
            <div>
                <button class="flat button color gray" type="button" onclick="VisioUtils.insertDtmf('1')">1</button>
                <button class="flat button color gray" type="button" onclick="VisioUtils.insertDtmf('2')">2</button>
                <button class="flat button color gray" type="button" onclick="VisioUtils.insertDtmf('3')">3</button>
            </div>
            <div>
                <button class="flat button color gray" type="button" onclick="VisioUtils.insertDtmf('4')">4</button>
                <button class="flat button color gray" type="button" onclick="VisioUtils.insertDtmf('5')">5</button>
                <button class="flat button color gray" type="button" onclick="VisioUtils.insertDtmf('6')">6</button>
            </div>
            <div>
                <button class="flat button color gray" type="button" onclick="VisioUtils.insertDtmf('7')">7</button>
                <button class="flat button color gray" type="button" onclick="VisioUtils.insertDtmf('8')">8</button>
                <button class="flat button color gray" type="button" onclick="VisioUtils.insertDtmf('9')">9</button>
            </div>
            <div>
                <button class="flat button color gray" type="button" onclick="VisioUtils.insertDtmf('*')">🞳</button>
                <button class="flat button color gray" type="button" onclick="VisioUtils.insertDtmf('0')">0</button>
                <button class="flat button color gray" type="button" onclick="VisioUtils.insertDtmf('#')">#</button>
            </div>

            <p class="dtmf"></p>
        </div>
    {/if}

    <ul class="list infos" class="list middle">
        <li>
            <div>
                <div id="remote_level">
                    <div class="avatar">
                        <img src="{$contact->getPicture(\Movim\ImageSize::L)}">
                    </div>
                </div>
                <p class="normal center">{$contact->truename}</p>
                <p class="normal state center"></p>
            </div>
        </li>
    </ul>

    <audio id="remote_audio" autoplay></audio>
    <audio id="audio" autoplay muted></audio>

    {if="$withvideo"}
        <video id="remote_video" autoplay poster="{$c->baseUri}theme/img/empty.png"></video>
        <video id="screen_sharing_video" autoplay muted poster="{$c->baseUri}theme/img/empty.png"></video>
        <video id="video" autoplay muted poster="{$c->baseUri}theme/img/empty.png"></video>
    {/if}

    <span id="remote_state">
        <i class="voice material-symbols"></i>
        {if="$withvideo"}
            <i class="webcam material-symbols"></i>
        {/if}
    </span>

    <div class="controls">
        <a id="main" class="button action color gray">
            <i class="material-symbols">phone</i>
        </a>
    </div>
</div>
<script type="text/javascript">
    document.addEventListener('DOMContentLoaded', function dcl() {
        document.removeEventListener('DOMContentLoaded', dcl, false);
        Visio.states = {
            calling: '{$c->__('visio.calling')}',
            ringing: '{$c->__('visio.ringing')}',
            in_call: '{$c->__('visio.in_call')}',
            failed: '{$c->__('visio.failed')}',
            connecting: '{$c->__('visio.connecting')}',
            ended: '{$c->__('visio.ended')}',
            declined: '{$c->__('visio.declined')}'
        };
    }, false);
</script>
