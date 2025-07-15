<ul class="list divided active spaced actions">
    {if="!empty($list)"}
    <li class="subheader">
        <div>
            <p>{$c->__('adhoc.title')}</p>
        </div>
    </li>
    {/if}
    {loop="$list"}
        {if="isset($value->attributes()->name)"}
            <li data-node="{$value->attributes()->node}" data-jid="{$value->attributes()->jid}">
                <span class="primary icon gray">
                    <i class="material-symbols">{$c->getIcon((string)$value->attributes()->node)}</i>
                </span>
                <span class="control icon gray">
                    <i class="material-symbols">chevron_right</i>
                </span>
                <div>
                    <p class="normal line" title="{$value->attributes()->name}">
                        {$value->attributes()->name}
                    </p>
                </div>
            </li>
        {/if}
    {/loop}
</ul>

<ul class="list divided active spaced">
    <li class="subheader">
        <div>
            <p>{$c->__('tools.title')}</p>
        </div>
    </li>

    <li onclick="AdHoc_ajaxSDPToJingle()">
        <span class="primary icon gray">
            <i class="material-symbols">modeling</i>
        </span>
        <span class="control icon gray">
            <i class="material-symbols">chevron_right</i>
        </span>
        <div>
            <p class="normal line">{$c->__('tools.sdp_to_jingle')}</p>
        </div>
    </li>
    <li onclick="AdHoc_ajaxJingleToSDP()">
        <span class="primary icon gray">
            <i class="material-symbols">modeling</i>
        </span>
        <span class="control icon gray">
            <i class="material-symbols">chevron_right</i>
        </span>
        <div>
            <p class="normal line">{$c->__('tools.jingle_to_sdp')}</p>
        </div>
    </li>
</ul>