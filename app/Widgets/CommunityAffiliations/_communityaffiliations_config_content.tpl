<h3>{$c->__('communityaffiliation.roles')}</h3>
<br />
<ul class="list thin">
        {loop="$affiliations"}
            {$contact = $c->getContact($value->jid)}
            <li title="{$contact->jid}">
                <span class="primary icon bubble">
                    <img src="{$contact->getPicture(\Movim\ImageSize::M)}">
                </span>
                <form name="{$contact->jid}">
                    <input type="hidden" name="jid" value="{$contact->jid}"/>
                    <div>
                        {if="$value->affiliation == 'owner' && $contact->jid == $me"}
                            <input type="text" disabled value="{$c->__('affiliation.owner')}"/>
                        {else}
                        <div class="select">
                            <select name="role" id="role" onchange="CommunityAffiliations.update('{$contact->jid}')">
                                {$affiliation = $value->affiliation}
                                {loop="$roles"}
                                    <option
                                        value="{$key}"
                                        {if="$key == $affiliation"}selected="selected"{/if}
                                    >
                                        {$value}
                                    </option>
                                {/loop}
                            </select>
                        </div>
                        {/if}
                        <label for="role">{$contact->truename} role</label>
                    </div>
                </form>
            </li>
        {/loop}

    <br />
    <hr />
    <li class="subheader">
        <div>
            <p>{$c->__('button.add')}</p>
        </div>
    </li>
    <li>
        <form name="addaffiliation">
            <div>
                <datalist id="jid_list" style="display: none;">
                    {if="is_array($subscriptions)"}
                        {loop="$subscriptions"}
                            <option value="{$value->jid}"/>
                        {/loop}
                    {/if}
                </datalist>
                <input type="text" list="jid_list" name="jid" placeholder="user@server.tld"/>
                <label for="jid">Jabber ID</label>
            </div>
            <div>
                <div class="select">
                    <select name="role" id="role" onchange="">
                        {loop="$roles"}
                            <option
                                value="{$key}"
                                {if="$key == 'none'"}
                                    selected="selected"
                                {/if}
                            >
                                {$value}
                            </option>
                        {/loop}
                    </select>
                </div>
                <label for="role">Role</label>
            </div>
            <div>
                <p>
                    <a href="#" onclick="CommunityAffiliations.update('addaffiliation')"
                       class="button green color oppose">
                        <i class="material-symbols">add</i>
                        {$c->__('button.add')}
                    </a>
                </p>
            </div>
        </form>
    </li>
</ul>
