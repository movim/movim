<div class="tabelem" title="{$c->__('page.help')}" id="help_widget">
    <ul class="list middle">
        <li class="subheader">
            <p>{$c->__('faq.title')}</p>
        </li>
        <li class="block">
            <span class="primary icon gray">
                <i class="material-icons">wifi_tethering</i>
            </span>
            <p>{$c->__('faq.permission_title')}</p>
            <p class="all">{$c->__('faq.permission_text')}</p>
        </li>
        <li class="block">
            <span class="primary icon gray">
                <i class="material-icons">group_work</i>
            </span>
            <p>{$c->__('faq.permission_community_title')}</p>
            <p class="all">{$c->__('faq.permission_community_text')}</p>
        </li>
        <li class="block">
            <span class="primary icon gray">
                <i class="material-icons">forum</i>
            </span>
            <p>{$c->__('faq.chatrooms_title')}</p>
            <p class="all">
                <a href="https://search.jabber.network" target="_blank">search.jabber.network</a>
            </p>
        </li>
        <li class="block">
            <span class="primary icon gray">
                <i class="material-icons">search</i>
            </span>
            <p>{$c->__('faq.find_contacts_title')}</p>
            <p class="all">{$c->__('faq.find_contacts_text')}</p>
        </li>
    </ul>
    <br />
    <hr />
    <ul class="list divided middle">
        <li class="subheader">
            <p>{$c->__('page.help')}</p>
        </li>
        <li class="block">
            <span class="primary icon gray">
                <i class="material-icons">comment</i>
            </span>
            <p>{$c->__('chatroom.question')}</p>
            <p class="all">
                <a href="#" onclick="Help_ajaxAddChatroom()">
                    {$c->__('chatroom.button')} movim@conference.movim.eu
                </a>
            </p>
        </li>
    </ul>
    <br />
    <hr />
    <ul class="list thick block divided">
        <li class="subheader">
            <p>{$c->__('apps.question')}</p>
        </li>
        <li class="block">
            <span class="primary icon bubble color green">
                <i class="material-icons">android</i>
            </span>
            <p>{$c->__('apps.phone')}<p>
            <p class="all">
                {$c->__('apps.android')}
                <br />
                <!--<a class="button flat" href="https://play.google.com/store/apps/details?id=com.movim.movim" target="_blank">
                    <i class="material-icons">android</i> Play Store
                </a>-->
                <a class="button flat" href="https://f-droid.org/packages/com.movim.movim/" target="_blank">
                    <i class="material-icons">adb</i> F-Droid
                </a>
                <br />
                {$c->__('apps.recommend')} Conversations
                <br />
                <a class="button flat" href="https://play.google.com/store/apps/details?id=eu.siacs.conversations" target="_blank">
                    <i class="material-icons">android</i> Play Store
                </a>
                <a class="button flat" href="https://f-droid.org/packages/eu.siacs.conversations/" target="_blank">
                    <i class="material-icons">adb</i> F-Droid
                </a>
            </p>
        </li>
        <li class="block">
            <span class="primary icon bubble color purple">
                <i class="material-icons">desktop_windows</i>
            </span>
            <p>{$c->__('apps.computer')}<p>
            <p class="all">
                <a href="https://movim.eu/#apps" target="_blank">
                    {$c->__('apps.computer_text')}
                </a>
            </p>
        </li>
    </ul>
    {if="$info && (!empty($info->adminaddresses) || !empty($info->abuseaddresses) || !empty($info->supportaddresses)  || !empty($info->securityaddresses))"}
        <hr />
        <ul class="list thin flex">
            <li class="subheader block large">
                <p class="normal">{$c->__('contact.title')}</p>
            </li>
            <hr />
            {$addresses = array_unique(array_merge($info->adminaddresses, $info->abuseaddresses, $info->supportaddresses, $info->securityaddresses))}
            {loop="$addresses"}
                <li class="block">
                    {$parsed = parse_url($value)}
                    {if="$parsed['scheme'] == 'xmpp'"}
                        {if="isset($parsed['query']) && $parsed['query'] == 'join'"}
                        <span class="primary icon gray">
                            <i class="material-icons">mode_comment</i>
                        </span>
                        <p class="normal">
                            <a href="{$c->route('chat', [$parsed['path'], 'room'])}">
                                {$parsed['path']}
                            </a>
                        </p>
                        {else}
                        <span class="primary icon gray">
                            <i class="material-icons">comment</i>
                        </span>
                        <p class="normal">
                            <a href="{$c->route('chat', $parsed['path'])}">
                                {$parsed['path']}
                            </a>
                        </p>
                        {/if}
                    {else}
                        <span class="primary icon gray">
                            <i class="material-icons">email</i>
                        </span>
                        <p class="normal">
                            <a href="{$value}" target="_blank" rel="noopener noreferrer">
                                {$parsed['path']}
                            </a>
                        </p>
                    {/if}
                </li>
            {/loop}
        </ul>
    {/if}
</div>
