<div class="tabelem" title="{$c->__('page.help')}" id="help_widget">
    <ul class="list middle">
        <li class="subheader">
            <div>
                <p>{$c->__('faq.title')}</p>
            </div>
        </li>
        <li class="block">
            <span class="primary icon gray">
                <i class="material-symbols">wifi_tethering</i>
            </span>
            <div>
                <p>{$c->__('faq.permission_title')}</p>
                <p class="all">{$c->__('faq.permission_text')}</p>
            </div>
        </li>
        <li class="block">
            <span class="primary icon gray">
                <i class="material-symbols">group_work</i>
            </span>
            <div>
                <p>{$c->__('faq.permission_community_title')}</p>
                <p class="all">{$c->__('faq.permission_community_text')}</p>
            </div>
        </li>
        <li class="block">
            <span class="primary icon gray">
                <i class="material-symbols">forum</i>
            </span>
            <div>
                <p>{$c->__('faq.chatrooms_title')}</p>
                <p class="all">
                    <a href="https://search.jabber.network" target="_blank">search.jabber.network</a>
                </p>
            </div>
        </li>
        <li class="block">
            <span class="primary icon gray">
                <i class="material-symbols">search</i>
            </span>
            <div>
                <p>{$c->__('faq.find_contacts_title')}</p>
                <p class="all">{$c->__('faq.find_contacts_text')}</p>
            </div>
        </li>
    </ul>
    <br />
    <hr />
    <ul class="list divided middle">
        <li class="subheader">
            <div>
                <p>{$c->__('page.help')}</p>
            </div>
        </li>
        <li class="block">
            <span class="primary icon gray">
                <i class="material-symbols">comment</i>
            </span>
            <div>
                <p>{$c->__('chatroom.question')}</p>
                <p class="all">
                    <a href="#" onclick="Help_ajaxAddChatroom()">
                        {$c->__('chatroom.button')} movim@conference.movim.eu
                    </a>
                </p>
            </div>
        </li>
    </ul>
    <br />
    <hr />
    <ul class="list thick block hide" id="pwa">
        <li class="subheader">
            <div>
                <p>{$c->__('apps.title')}</p>
            </div>
        </li>
        <li class="block active">
            <span class="primary icon gray">
                <i class="material-symbols on_desktop">install_desktop</i>
                <i class="material-symbols on_mobile">install_mobile</i>
            </span>
            <span class="control icon gray">
                <i class="material-symbols">chevron_right</i>
            </span>
            <div>
                <p class="line">{$c->__('apps.install')}<p>
                <p class="all">
                    {$c->__('apps.install_text')}
                </p>
            </div>
        </li>
    </ul>
    <ul class="list">
        <li class="subheader">
            <div>
                <p>{$c->__('apps.recommend')}</p>
            </div>
        </li>
        <li class="block">
            <span class="primary icon green">
                <i class="material-symbols">android</i>
            </span>
            <div>
                <p class="normal line">
                    Conversations
                    <a class="button flat" href="https://play.google.com/store/apps/details?id=eu.siacs.conversations" target="_blank">
                        <i class="material-symbols">android</i> Play Store
                    </a>
                    <a class="button flat" href="https://f-droid.org/packages/eu.siacs.conversations/" target="_blank">
                        <i class="material-symbols">adb</i> F-Droid
                    </a>
                </p>
            </div>
        </li>
        <li class="block">
            <span class="primary icon purple">
                <i class="material-symbols">computer</i>
            </span>
            <div>
                <p class="normal line">
                    Dino
                    <a class="button flat" href="https://dino.im/" target="_blank">
                        <i class="material-symbols">open_in_new</i> Website
                    </a>
                </p>
            </div>
        </li>
        <li class="block">
            <span class="primary icon blue">
                <i class="material-symbols">computer</i>
            </span>
            <div>
                <p class="normal line">
                    Gajim
                    <a class="button flat" href="https://gajim.org/" target="_blank">
                        <i class="material-symbols">open_in_new</i> Website
                    </a>
                </p>
            </div>
        </li>
    </ul>
    {if="$info && (!empty($info->adminaddresses) || !empty($info->abuseaddresses) || !empty($info->supportaddresses)  || !empty($info->securityaddresses))"}
        <hr />
        <ul class="list flex">
            <li class="subheader">
                <div>
                    <p class="normal">{$c->__('contact.title')}</p>
                </div>
            </li>
            {$addresses = array_unique(array_merge($info->adminaddresses, $info->abuseaddresses, $info->supportaddresses, $info->securityaddresses))}
            {loop="$addresses"}
                <li class="block">
                    {$parsed = parse_url($value)}
                    {if="$parsed['scheme'] == 'xmpp'"}
                        {if="isset($parsed['query']) && $parsed['query'] == 'join'"}
                        <span class="primary icon gray">
                            <i class="material-symbols">mode_comment</i>
                        </span>
                        <div>
                            <p class="normal">
                                <a href="{$c->route('chat', [$parsed['path'], 'room'])}">
                                    {$parsed['path']}
                                </a>
                            </p>
                        </div>
                        {else}
                        <span class="primary icon gray">
                            <i class="material-symbols">comment</i>
                        </span>
                        <div>
                            <p class="normal">
                                <a href="{$c->route('chat', $parsed['path'])}">
                                    {$parsed['path']}
                                </a>
                            </p>
                        </div>
                        {/if}
                    {else}
                        <span class="primary icon gray">
                            <i class="material-symbols">email</i>
                        </span>
                        <div>
                            <p class="normal">
                                <a href="{$value}" target="_blank" rel="noopener noreferrer">
                                    {$parsed['path']}
                                </a>
                            </p>
                        </div>
                    {/if}
                </li>
            {/loop}
        </ul>
    {/if}
</div>
