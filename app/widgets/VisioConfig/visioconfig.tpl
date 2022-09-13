<div class="tabelem padded_top_bottom" title="{$c->__('visioconfig.title')}" data-mobileicon="video_settings" id="visioconfig_widget">
    <ul class="list fill thick">
        <li>
            <span class="primary icon gray">
                <i class="material-icons">info</i>
            </span>
            <div>
                <p class="line normal">{$c->__('visioconfig.title')}</p>
                <p class="all">{$c->__('visioconfig.help')}</p>
            </div>
        </li>
    </ul>
    <form>
        <div>
            <ul class="list fill">
                <li class="subheader">
                    <div>
                        <p>{$c->__('visioconfig.microphone')}</p>
                    </div>
                </li>
                <li>
                    <span class="primary icon gray">
                        <i class="material-icons">settings_voice</i>
                    </span>
                    <div>
                        <div class="select">
                            <select name="default_microphone"></select>
                        </div>
                        <label for="default_microphone">{$c->__('visioconfig.microphone_label')}</label>
                    </div>
                </li>
            </ul>
            <ul class="list fill thin">
                <li>
                    <span class="primary icon small"></span>
                    <div class="level">
                        <span class="color green"></span>
                        <span class="color green"></span>
                        <span class="color green"></span>
                        <span class="color green"></span>
                        <span class="color green"></span>
                        <span class="color yellow"></span>
                        <span class="color yellow"></span>
                        <span class="color yellow"></span>
                        <span class="color yellow"></span>
                        <span class="color red"></span>
                    </div>
                </li>
                <li id="no_mic_sound" class="disabled">
                    <span class="primary icon small red">
                        <i class="material-icons">mic_none</i>
                    </span>
                    <div>
                        <p>{$c->__('visioconfig.no_mic_sound')}</p>
                        <p>{$c->__('visioconfig.no_mic_sound2')}</p>
                    </div>
                </li>
            </ul>
            <ul class="list fill">
                <li class="subheader">
                    <div>
                        <p>{$c->__('visioconfig.camera')}</p>
                    </div>
                </li>
                <li>
                    <span class="primary icon gray">
                        <i class="material-icons">video_camera_back</i>
                    </span>
                    <div>
                        <div class="select">
                            <select name="default_camera"></select>
                        </div>
                        <label for="default_camera">{$c->__('visioconfig.camera_label')}</label>
                    </div>
                </li>
                <li>
                    <span class="primary"></span>
                    <div id="camera_preview">
                        <video></video>
                        <ul class="list">
                            <li>
                                <div>
                                    <p class="line center normal">
                                        <span class="button small flat gray" onclick="VisioConfig.testCamera();"><i class="material-icons">videocam</i> {$c->__('publish.preview')}</span>
                                    </p>
                                </div>
                            </li>
                        </ul>
                    </div>
                </li>
            </ul>
        </div>
    </form>
</div>
