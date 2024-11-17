<form id="config_blog" onchange="Config_ajaxBlogSetConfig(MovimUtils.formToJson('config_blog'))">
    <div>
        <ul class="list middle">
            <li class="subheader">
                <div>
                    <p>{$c->__('config.blog_text')}</p>
                </div>
            </li>
            <li>
                <span class="primary icon gray">
                    <i class="material-symbols">public</i>
                </span>
                <span class="control">
                    <div class="radio">
                        <input name="pubsub#access_model" value="open"
                            id="access_model_open" type="radio"
                            {if="$default == 'open'"}checked{/if}>
                        <label for="access_model_open"></label>
                    </div>
                </span>
                <div>
                    <p>{$c->__('config.blog_open_title')}</p>
                    <p>{$c->__('config.blog_open_text')}</p>
                </div>
            </li>
            <li>
                <span class="primary icon gray">
                    <i class="material-symbols">admin_panel_settings</i>
                </span>
                <span class="control">
                    <div class="radio">
                        <input name="pubsub#access_model" value="presence"
                            id="access_model_presence" type="radio"
                            {if="$default == 'presence'"}checked{/if}>
                        <label for="access_model_presence"></label>
                    </div>
                </span>
                <div>
                    <p>{$c->__('config.blog_presence_title')}</p>
                    <p>{$c->__('config.blog_presence_text')}</p>
                </div>
            </li>
        </ul>
    </div>
</form>
