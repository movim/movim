<div id="snap">
    <video autoplay poster="{$c->baseUri}theme/img/empty.png"></video>
    <canvas id="snapcanvas"></canvas>
    <select id="snapsource"></select>
    <div class="bottom_center">
        <button id="snapshoot" class="button action color green">
            <i class="material-symbols">camera</i>
        </button>
        <button id="snapupload" class="button action color blue">
            <i class="material-symbols">publish</i>
        </button>
        <button id="snapdraw" class="button action color green">
            <i class="material-symbols">gesture</i>
        </button>
        <button id="snapwait" class="button action color gray">
            <i class="material-symbols">more_horiz</i>
        </button>
    </div>
    <ul class="list controls middle">
        <li>
            <span id="snapback" class="primary icon color transparent active">
                <i class="material-symbols">arrow_back</i>
            </span>
            <div></div>
            <span id="snapswitch" class="control icon color transparent active">
                <i class="material-symbols">switch_camera</i>
            </span>
            <span id="snapclose" class="control icon color transparent active">
                <i class="material-symbols">close</i>
            </span>
        </li>
    </ul>
</div>
