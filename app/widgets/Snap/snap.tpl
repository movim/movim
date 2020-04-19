<div id="snap">
    <video autoplay poster="/theme/img/empty.png"></video>
    <canvas id="snapcanvas"></canvas>
    <select id="snapsource"></select>
    <div class="bottom_center">
        <button id="snapshoot" class="button action color green">
            <i class="material-icons">camera</i>
        </button>
        <button id="snapupload" class="button action color blue">
            <i class="material-icons">publish</i>
        </button>
        <button id="snapdraw" class="button action color green">
            <i class="material-icons">gesture</i>
        </button>
        <button id="snapwait" class="button action color gray">
            <i class="material-icons">more_horiz</i>
        </button>
    </div>
    <ul class="list controls middle">
        <li>
            <span id="snapback" class="primary icon color transparent active">
                <i class="material-icons">arrow_back</i>
            </span>
            <div></div>
            <span id="snapswitch" class="control icon color transparent active">
                <i class="material-icons">switch_camera</i>
            </span>
            <span id="snapclose" class="control icon color transparent active">
                <i class="material-icons">close</i>
            </span>
        </li>
    </ul>
</div>
