<div id="publishstories">
    <video autoplay poster="{$c->baseUri}theme/img/empty.png" disablePictureInPicture></video>
    <canvas id="publishstoriescanvas"></canvas>
    <select id="publishstoriessource"></select>
    <input type="file" accept="image/*" onchange="PublishStories.applyImage()"/>

    <div class="bottom_center">
        <button id="publishstoriesshoot" class="button action color green" onclick="PublishStories.shoot()">
            <i class="material-symbols">camera</i>
        </button>
        <button id="publishstoriesgallery" class="button action color" onclick="PublishStories.openImage()">
            <i class="material-symbols">photo_library</i>
        </button>
    </div>
    <ul class="list controls middle">
        <li>
            <span id="publishstoriesback" class="primary icon color transparent active">
                <i class="material-symbols">arrow_back</i>
            </span>
            <span id="publishstoriesbackedit" class="primary icon color transparent active" onclick="PublishStories.goToEdit()">
                <i class="material-symbols">arrow_back</i>
            </span>
            <div></div>
            <span id="publishstoriesswitch" class="control icon color transparent active">
                <i class="material-symbols">switch_camera</i>
            </span>
            <span id="publishstoriesclose" class="control icon color transparent active" onclick="PublishStories.reset()">
                <i class="material-symbols">close</i>
            </span>
            <span id="publishstoriesnext" class="control icon color transparent active toggleable" onclick="PublishStories.goToPublish()">
                <i class="material-symbols">arrow_forward</i>
            </span>
        </li>
    </ul>

    <form onsubmit="return: false;" name="metadata">
        <div>
            <textarea id="title" type="text" dir="auto" name="title" required data-autoheight="true" placeholder="{$c->__('publish.placeholder')}" spellcheck="false"></textarea>
        </div>
    </form>

    <div id="publishactions">
        <ul class="list active middle">
            <li onclick="PublishStories.publish()">
                <span class="primary icon bubble color green">
                    <i class="material-symbols">group</i>
                </span>
                <span class="control icon active">
                    <i class="material-symbols">chevron_right</i>
                </span>
                <div>
                    <p class="line">{$c->__('story.to_roster')}</p>
                    <p>{$rostercount} <i class="material-symbols">group</i></p>
                </div>
            </li>
            <li>
                <span class="primary icon spin">
                    <i class="material-symbols">progress_activity</i>
                </span>
                <div>
                    <p class="line">{$c->__('story.uploading')}</p>
                    <p id="publishactionsprogress">0%</p>
                </div>
            </li>
            <li>
                <span class="primary icon">
                    <i class="material-symbols">publish</i>
                </span>
                <div>
                    <p class="line">{$c->__('story.publishing')}</p>
                    <p>{$c->__('story.publishing_text')}</p>
                </div>
            </li>
        </ul>
    </div>
</div>
