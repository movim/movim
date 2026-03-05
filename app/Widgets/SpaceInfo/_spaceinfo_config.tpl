<section class="scroll">
    <form name="spaceinfo_config">
        <div>
            <ul class="list">
                <li>
                    <div>
                        <input id="pubsub#title" name="pubsub#title" label="{$c->__('general.name')}"
                            placeholder="{$c->__('general.name')}" type="text-single"
                            value="{$config['pubsub#title']}">
                        <label for="pubsub#title">{$c->__('general.name')}</label>
                    </div>
                </li>
                <li>
                    <div>
                        <textarea id="pubsub#description" name="pubsub#description"
                                data-autoheight="true"
                                placeholder="{$c->__('communityconfig.description')}">{if="isset($config['pubsub#description'])"}{$config['pubsub#description']}{/if}</textarea>
                        <label for="pubsub#description">{$c->__('communityconfig.description')}</label>
                    </div>
                </li>
            </ul>
        </div>
    </form>
    <div id="spaceinfo_affiliations"></div>
    <br />
</section>
<hr />
<footer>
    <button onclick="SpaceInfo_ajaxAskDestroy('{$server}', '{$node}'); Drawer.clear('spaceinfo_config');"
        class="button flat">
        {$c->__('button.destroy')}
    </button>
    <button onclick="SpaceInfo_ajaxSetConfig('{$server}', '{$node}', MovimUtils.formToJson('spaceinfo_config'));
        SpaceInfo_ajaxSetAffiliations('{$server}', '{$node}', MovimUtils.formToJson('spaceinfo_affiliations'));
        Drawer.clear('spaceinfo_config');"
       class="button flat oppose green color">
        {$c->__('button.save')}
    </button>
    <button onclick="Drawer.clear('spaceinfo_config');" class="button flat oppose">
        {$c->__('button.close')}
    </button>
</footer>
