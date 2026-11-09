<div id="space_widget">
    <div class="dialog">
        <section>
            <ul class="list thick">
                <li>
                    <span class="primary icon bubble">
                        <img src="{$icon}">
                    </span>
                    <div>
                        <p>{autoescape="off"}{$space->name|addEmojis}{/autoescape}</p>
                        {if="$space->description"}
                            <p>{autoescape="off"}{$space->description|addEmojis}{/autoescape}</p>
                        {/if}
                    </div>
                </li>
                <li>
                    <span class="primary icon gray">
                        <i class="material-symbols">info</i>
                    </span>
                    <div>
                        <p></p>
                        <p>
                            {$c->__('communitydata.members', $space->occupants)}
                            <i class="material-symbols">people</i>

                            {if="$space->pubsubaccessmodel == 'authorize'"}
                                <br />{$c->__('spaceinfo.invite_text')}
                            {/if}
                        </p>
                    </div>
                </li>
            </ul>
            <hr />
            <form>
                <div>
                    <ul class="list">
                        <li>
                            <span class="primary icon gray">
                                <i class="material-symbols">group_add</i>
                            </span>
                            <span class="control icon active" onclick="MovimUtils.copyToClipboard('{$space->uri}'); Toast.send('{$c->__('chatactions.copied_text')}')">
                                <i class="material-symbols">content_copy</i>
                            </span>

                            <div>
                                <input name="title" value="{$space->uri}" readonly="">
                                <label for="title">{$c->__('spaceinfo.invite_key')}</label>
                                <span class="supporting"><i class="material-symbols">lightbulb</i> {$c->__('spaceinfo.join_text')}</span>
                            </div>
                        </li>
                    </ul>
                </div>
            </form>
            <br/>
            <hr />
            <ul class="list thick">
                <li>
                    <div>
                        <p class="center">
                            <a class="button flat color" href="{$loginUrl}">
                                {$c->__('spaceinfo.login_join')}
                            </a>
                        </p>
                    </div>
                </li>
            </ul>
        </section>
    </div>
</div>
