<section>
    <form name="presence">
        <h3>{$c->__('status.status')}</h3>

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
    <a onclick="Dialog.clear()" class="button flat">
        {$c->__('button.close')}
    </a>
    <a onclick="Presence_ajaxSet(movim_parse_form('presence')); Dialog.clear();" class="button flat">
        {$c->__('button.submit')}
    </a>
</div>
