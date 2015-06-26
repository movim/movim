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
            <ul class="active">
                <li class="action">
                    <span class="icon bubble color small green"></span>
                    <div class="action">
                        <div class="radio">
                            <input type="radio" name="value" id="chat" value="chat" {if="isset($p) && $p->value == 1"}checked="checked"{/if}/>
                            <label for="chat"></label>
                        </div>
                    </div>
                    <span>{$txt[1]}</span>
                </li>
                <li class="action">
                    <span class="icon bubble color small orange"></span>
                    <div class="action">
                        <div class="radio">
                            <input type="radio" name="value" id="away" value="away" {if="isset($p) && $p->value == 2"}checked="checked"{/if}/>
                            <label for="away"></label>
                        </div>
                    </div>
                    <span>{$txt[2]}</span>
                </li>
                <li class="action">
                    <span class="icon bubble color small red"></span>
                    <div class="action">
                        <div class="radio">
                            <input type="radio" name="value" id="dnd" value="dnd" {if="isset($p) && $p->value == 3"}checked="checked"{/if}/>
                            <label for="dnd"></label>
                        </div>
                    </div>
                    <span>{$txt[3]}</span>
                </li>
                <li class="action">
                    <span class="icon bubble color small purple"></span>
                    <div class="action">
                        <div class="radio">
                            <input type="radio" name="value" id="xa" value="xa" {if="isset($p) && $p->value == 4"}checked="checked"{/if}/>
                            <label for="xa"></label>
                        </div>
                    </div>
                    <span>{$txt[4]}</span>
                </li>
            </ul>
        </div>
    </form>
    <ul class="active">
        <li class="subheader">{$c->__('status.disconnect')}</li>
        <li onclick="{$calllogout}">
            <span class="icon"><i class="zmdi zmdi-sign-in"></i></span>
            <span>{$c->__('status.disconnect')}</span>
        </li>
    </ul>
                <!--
        <div class="element large mini">
            <label>{$c->__('chatroom.autojoin_label')}</label>
            <div class="checkbox">
                <input type="checkbox" id="autojoin" name="autojoin"/>
                <label for="autojoin"></label>
            </div>
        </div>
        -->
</section>
<div>
    <a onclick="Dialog.clear()" class="button flat">
        {$c->__('button.close')}
    </a>
    <a onclick="Presence_ajaxSet(movim_parse_form('presence')); Dialog.clear();" class="button flat">
        {$c->__('button.submit')}
    </a>
</div>
