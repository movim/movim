<div id="visio" class="">
    <header class="fixed">
        <ul class="list">
            <li>
                <span id="toggle_fullscreen" class="control icon color transparent active" onclick="Visio.toggleFullScreen()">
                    <i class="zmdi zmdi-fullscreen"></i>
                </span>
                <span id="toggle_audio" class="control icon color transparent active" onclick="Visio.toggleAudio()">
                    <i class="zmdi zmdi-volume-up"></i>
                </span>
                <span id="toggle_video" class="control icon color transparent active" onclick="Visio.toggleVideo()">
                    <i class="zmdi zmdi-eye"></i>
                </span>
            </li>
        </ul>
    </header>
    <video id="video" autoplay muted></video>
    <canvas class="level"></canvas>
    <video id="remote_video" autoplay></video>
    <canvas class="level"></canvas>
    <div class="controls">
        <a id="main" class="button action color green">
            <i class="zmdi zmdi-phone"></i>
        </a>
    </div>
</div>
