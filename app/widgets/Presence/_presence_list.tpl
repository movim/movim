<section>
    <ul class="list thick active">
        <li onclick="MovimUtils.reload('{$c->route('conf')}')">
            {$url = $contact->getPhoto('s')}
            {if="$url"}
                <span
                    class="primary icon bubble"
                    style="background-image: url({$contact->getPhoto('s')})">
                </span>
            {else}
                <span class="primary icon bubble color {$contact->jid|stringToColor}">
                    <i class="zmdi zmdi-account"></i>
                </span>
            {/if}
            <span class="control icon gray">
                <i class="zmdi zmdi-chevron-right"></i>
            </span>
            <span class="control icon gray">
                <i class="zmdi zmdi-edit"></i>
            </span>
            <p class="normal line">{$contact->getTrueName()}</p>
            <p class="line">
                {if="isset($p) && $p->status != ''"}
                    {$p->status}
                {else}
                    {$txt[$p->value]}
                {/if}
            </p>
        </li>
    </ul>
    <form name="presence">
        <div>
            <textarea
                spellcheck="false"
                name="status"
                placeholder="{$c->__('status.here')}"
            >{if="isset($p)"}{$p->status}{/if}</textarea>
            <label>{$c->__('status.here')}</label>
        </div>

        <div>
            <ul class="list active">
                <li>
                    <span class="primary icon bubble color small green"></span>
                    <span class="control">
                        <div class="radio">
                            <input type="radio" name="value" id="chat" value="chat" {if="isset($p) && $p->value == 1"}checked="checked"{/if}/>
                            <label for="chat"></label>
                        </div>
                    </span>
                    <p class="normal">{$txt[1]}</p>
                </li>
                <li>
                    <span class="primary icon bubble color small orange"></span>
                    <span class="control">
                        <div class="radio">
                            <input type="radio" name="value" id="away" value="away" {if="isset($p) && $p->value == 2"}checked="checked"{/if}/>
                            <label for="away"></label>
                        </div>
                    </span>
                    <p class="normal">{$txt[2]}</p>
                </li>
                <li>
                    <span class="primary icon bubble color small red"></span>
                    <span class="control">
                        <div class="radio">
                            <input type="radio" name="value" id="dnd" value="dnd" {if="isset($p) && $p->value == 3"}checked="checked"{/if}/>
                            <label for="dnd"></label>
                        </div>
                    </span>
                    <p class="normal">{$txt[3]}</p>
                </li>
                <li>
                    <span class="primary icon bubble color small purple"></span>
                    <span class="control">
                        <div class="radio">
                            <input type="radio" name="value" id="xa" value="xa" {if="isset($p) && $p->value == 4"}checked="checked"{/if}/>
                            <label for="xa"></label>
                        </div>
                    </span>
                    <p class="normal">{$txt[4]}</p>
                </li>
            </ul>
        </div>
    </form>
</section>
<div>
    <a onclick="Dialog_ajaxClear()" class="button flat">
        {$c->__('button.close')}
    </a>
    <a onclick="Presence_ajaxSet(MovimUtils.parseForm('presence')); Dialog_ajaxClear();" class="button flat">
        {$c->__('button.submit')}
    </a>
</div>
