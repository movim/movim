<div id="snap">
    <video autoplay poster="{$c->baseUri}theme/img/empty.png"></video>
    <canvas id="snapcanvas"></canvas>
    <select id="snapsource"></select>
    <div class="bottom_center">
        <button id="snapswitch" class="button action color transparent">
            <i class="material-symbols">switch_camera</i>
        </button>
        <button id="snapshoot" class="button action color green">
            <i class="material-symbols">camera</i>
        </button>
        <button id="snapclose" class="button action color transparent">
            <i class="material-symbols">close</i>
        </button>
        <button id="snapupload" class="button action color blue">
            <i class="material-symbols">publish</i>
        </button>
        <button id="snapdraw" class="button action color transparent">
            <i class="material-symbols">gesture</i>
        </button>
        <button id="snapwait" class="button action color gray">
            <i class="material-symbols">more_horiz</i>
        </button>
    </div>
    <ul class="list controls middle">
        <li>
            <span id="snapback" class="primary icon active">
                <i class="material-symbols">arrow_back</i>
            </span>
            <div></div>
        </li>
    </ul>
</div>
