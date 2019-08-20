<div id="draw">
    <div class="canvas">
        <canvas id="draw-background"></canvas>
        <canvas id="draw-canvas">¯\_(ツ)_/¯</canvas>
    </div>
    <div class="draw-control">
        <ul class="list middle">
            <li id="draw-save">
                <span class="primary active icon bubble color blue">
                    <i class="material-icons">publish</i>
                </span>
                <p></p>
            </li>
            <li id="draw-clear">
                <span class="primary active icon bubble color gray">
                    <i class="material-icons">undo</i>
                </span>
                <p></p>
            </li>
        </ul>
        <br />
        <ul class="list draw-widths">
            <li data-width="small">
                <span class="primary active icon bubble color gray">
                    <i class="material-icons">brush</i>
                </span>
                <p></p>
            </li>
            <li data-width="medium" class="selected">
                <span class="primary active icon bubble color gray">
                    <i class="material-icons">brush</i>
                </span>
                <p></p>
            </li>
            <li data-width="big">
                <span class="primary active icon bubble color gray">
                    <i class="material-icons">brush</i>
                </span>
                <p></p>
            </li>
            <li>
                <span class="primary active icon bubble color gray draw-eraser">
                    <i class="material-icons">crop_landscape</i>
                </span>
                <p></p>
            </li>
        </ul>
        <br />
        <ul class="list draw-colors">
            <li class="selected">
                <span class="primary active icon bubble color black" data-color="black"></span>
                <p></p>
            </li>
            <li>
                <span class="primary active icon bubble color blue" data-color="blue"></span>
                <p></p>
            </li>
            <li>
                <span class="primary active icon bubble color red" data-color="red"></span>
                <p></p>
            </li>
            <li>
                <span class="primary active icon bubble color green" data-color="green"></span>
                <p></p>
            </li>
            <li>
                <span class="primary active icon bubble color purple" data-color="purple"></span>
                <p></p>
            </li>
        </ul>
        <br />
    </div>
    <ul class="list controls">
        <li>
            <span id="drawback" class="primary active icon black active">
                <i class="material-icons">arrow_back</i>
            </span>
        </li>
    </ul>
</div>