<div id="visio">
    <header>
        <ul class="list">
            <li>
                <span id="switch_chat" class="primary icon color transparent active" onclick="VisioUtils.switchChat()">
                    <i class="material-symbols">back_to_tab</i>
                </span>
                <span id="toggle_fullscreen" class="control icon color transparent active" onclick="VisioUtils.toggleFullScreen()">
                    <i class="material-symbols">fullscreen</i>
                </span>
                <span id="toggle_dtmf" class="control icon color transparent active" onclick="VisioUtils.toggleDtmf()">
                    <i class="material-symbols">dialpad</i>
                </span>
                <span id="toggle_audio" class="divided control icon color transparent active" onclick="VisioUtils.toggleAudio()">
                    <i class="material-symbols">mic</i>
                </span>

                <span id="toggle_video" class="control icon color transparent active" onclick="VisioUtils.toggleVideo()">
                    <i class="material-symbols">videocam</i>
                </span>
                <span id="switch_camera" class="control icon color transparent active">
                    <i class="material-symbols">switch_camera</i>
                </span>
                <span id="screen_sharing" class="control icon color transparent active toggleable" onclick="VisioUtils.toggleScreenSharing()">
                    <i class="material-symbols">screen_share</i>
                </span>

                <div>
                    <p></p>
                    <p id="no_mic_sound" class="disabled all">
                        <i class="material-symbols">mic_none</i>
                        {$c->__('visiolobby.no_mic_sound')}
                    </p>
                </div>
            </li>
        </ul>
    </header>
    <select id="visio_source"></select>

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

    <div id="participants"></div>

    <ul class="list infos" class="list middle">
        <li>
            <div id="visio_contact"></div>
        </li>
    </ul>

    <audio id="local_audio" autoplay muted></audio>
    <video id="screen_sharing_video" autoplay muted poster="{$c->baseUri}theme/img/empty.png"></video>
    <video id="local_video" autoplay muted poster="{$c->baseUri}theme/img/empty.png"></video>

    <div class="controls">
        <a id="main" class="button action color red" onclick="MovimVisio.goodbye()">
            <i class="material-symbols">call_end</i>
        </a>
    </div>
</div>
