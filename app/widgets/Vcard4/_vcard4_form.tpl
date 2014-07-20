<form name="vcard4" id="vcard4form">
    <fieldset>
        <legend><i class="fa fa-user"></i> {$c->__('page.profile')}</legend>
        <div class="element">
            <label for="fn">{$c->__('vcard.name')}</label>
            <input type="text" name="fn" class="content" value="{$me->fn}">
        </div>
        <div class="element">
            <label for="fn">{$c->__('vcard.nickname')}</label>
            <input type="text" name="name" class="content" value="{$me->name}">
        </div>
        <div class="element">
            <label for="fn">{$c->__('vcard.email')}</label>
            <input type="email" name="email" class="content" value="{$me->email}">
        </div>

        <!-- The date picker -->

        <div class="element ">
            <label for="day">{$c->__('vcard.date_of_birth')}</label>
            <div class="select" style="width: 33%; float: left;">
                <select name="day" class="datepicker">
                    <option value="-1">{$c->__('Day')}</option>
                    {loop="$days"}
                        <option value="{$value}" 
                        {if="$key == substr($me->date, 8)"}
                            selected 
                        {/if}
                        >{$value}</option>
                    {/loop}
                </select>
            </div>
            
            <div class="select" style="width: 34%; float: left;">
                <select name="month" class="datepicker">
                    <option value="-1">{$c->__('Month')}</option>
                    {loop="$months"}
                        <option value="{$key}" 
                        {if="$key == substr($me->date,5,2)"}
                            selected 
                        {/if}
                        >{$value}</option>
                    {/loop}
                </select>
            </div>
                    
            <div class="select" style="width: 33%; float: left;">
                <select name="year" class="datepicker">
                    <option value="-1">{$c->__('Year')}</option>
                    {loop="$years"}
                        <option value="{$value}" 
                        {if="$value == substr($me->date,0,4)"}
                            selected 
                        {/if}
                        >{$value}</option>
                    {/loop}
                </select>
            </div>
        </div>

        <div class="element">
            <label for="gender">{$c->__('vcard.gender')}</label>
            <div class="select">
                <select name="gender">
                {loop="$gender"}
                    <option 
                    {if="$key == $me->gender"}
                        selected 
                    {/if}
                    value="{$key}">{$value}</option>
                {/loop}
                </select>
            </div>
        </div>

        <div class="element">
            <label for="marital">{$c->__('vcard.marital')}</label>
            <div class="select">
                <select name="marital">
                {loop="$marital"}
                    <option 
                    {if="$key == $me->marital"}
                        selected 
                    {/if}
                    value="{$key}">{$value}</option>
                {/loop}
                </select>
            </div>
        </div>

        <div class="element large">
            <label for="url">{$c->__('vcard.website')}</label>
            <input type="url" name ="url" class="content" value="{$me->url}">
        </div>

        <div class="element large">
            <label for="desc">{$c->__('vcard.about')}</label>
            <textarea name="desc" id="desctext" class="content" onkeyup="movim_textarea_autoheight(this);">{$desc}</textarea>
        </div>
    </fieldset>

    <fieldset>
        <legend><i class="fa fa-compass"></i> {$c->__('vcard.position_title')}</legend>
            
        <div class="element">
            <label for="url">{$c->__('vcard.locality')}</label>
            <input type="text" type="locality" name ="locality" class="content" value="{$me->adrlocality}" placeholder="{$c->__('Locality')}">
        </div>
                  
        <div class="element">
            <label for="country">{$c->__('vcard.country')}</label>
                    <div class="select">
                        <select name="country">
                            <option value=""></option>
                            {loop="$countries"}
                                <option 
                                {if="$value == $me->adrcountry"}
                                    selected 
                                {/if}
                                value="{$value}">{$value}</option>
                            {/loop}
                    </select>
            </div>
        </div>

    </fieldset>

    <fieldset>
        <legend><i class="fa fa-circle-thin"></i> {$c->__('vcard.accounts_title')}</legend>
        
        <div class="element">
            <label for="twitter"><i class="fa fa-twitter"></i> {$c->__('vcard.twitter')}</label>
            <input type="text" name="twitter" class="content" value="{$me->twitter}" placeholder="{$c->__('Nickname')}">
        </div>
        
        <div class="element">
            <label for="skype"><i class="fa fa-skype"></i> {$c->__('vcard.skype')}</label>
            <input type="text" name="skype" class="content" value="{$me->skype}" placeholder="{$c->__('Nickname')}">
        </div>
        
        <div class="element">
            <label for="skype"><i class="fa fa-yahoo"></i> {$c->__('vcard.yahoo')}</label>
            <input type="email" name="yahoo" class="content" value="{$me->yahoo}" placeholder="{$c->__('Yahoo Account')}">
        </div>
    </fieldset>

    <fieldset>
        <a
            onclick="
                {$submit}
                movim_button_save('#vcard4validate');
                this.value = '{$c->__('Submitting')}'; 
                this.className='button color orange icon loading merged right inactive';" 
            class="button merged right color green oppose" 
            id="vcard4validate"
            >
            <i class="fa fa-check"></i> {$c->__('Submit')}
        </a>
        <a
            onclick="document.querySelector('#vcard4form').reset();"
            class="button merged left color orange oppose">
            <i class="fa fa-eraser"></i> {$c->__('Reset')}
        </a>
    </fieldset> 

    <fieldset>
        <legend><i class="fa fa-lock"></i> {$c->__('vcard.privacy_title')}</legend>
        <div class="element">
            <label>{$c->__('vcard.privacy_question')}</label>
            <div class="checkbox">
                <input
                    type="checkbox"
                    id="privacy"
                    name="privacy"
                    {if="$me->privacy"}
                        checked
                    {/if}
                    onchange="{$privacy}"/>
                <label for="privacy"></label>
            </div>
        </div>
        <div class="element">
            <div class="message info">
                {$c->__('vcard.privacy_info')}
            </div>
        </div>
    </fieldset> 
</form>
