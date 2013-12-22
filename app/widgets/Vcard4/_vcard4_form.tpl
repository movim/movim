<form name="vcard4" id="vcard4form">
    <fieldset>
        <legend>{$c->t('General Informations')}</legend>
        <div class="element">
            <label for="fn">{$c->t('Name')}</label>
            <input type="text" name="fn" class="content" value="{$me->fn}">
        </div>
        <div class="element">
            <label for="fn">{$c->t('Nickname')}</label>
            <input type="text" name="name" class="content" value="{$me->name}">
        </div>
        <div class="element">
            <label for="fn">{$c->t('Email')}</label>
            <input type="email" name="email" class="content" value="{$me->email}">
        </div>

        <!-- The date picker -->

        <div class="element ">
            <label for="day">{$c->t('Date of Birth')}</label>
            <div class="select" style="width: 33%; float: left;">
                <select name="day" class="datepicker">
                    <option value="-1">{$c->t('Day')}</option>
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
                    <option value="-1">{$c->t('Month')}</option>
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
                    <option value="-1">{$c->t('Year')}</option>
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
            <label for="gender">{$c->t('Gender')}</label>
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
            <label for="marital">{$c->t('Marital Status')}</label>
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
            <label for="url">{$c->t('Website')}</label>
            <input type="url" name ="url" class="content" value="{$me->url}">
        </div>

        <div class="element large">
            <label for="desc">{$c->t('About Me')}</label>
            <textarea name="desc" id="desctext" class="content" onkeyup="movim_textarea_autoheight(this);">{$desc}</textarea>
        </div>
    </fieldset>

    <fieldset>
        <legend>{$c->t('Geographic Position')}</legend>
            
        <div class="element">
            <label for="url">{$c->t('Locality')}</label>
            <input type="text" type="locality" name ="locality" class="content" value="{$me->adrlocality}">
        </div>
                  
        <div class="element">
            <label for="country">{$c->t('Country')}</label>
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
        <a
            onclick="
                {$submit}
                movim_button_save('#vcard4validate');
                this.value = '{$c->t('Submitting')}'; 
                this.className='button color orange icon loading merged right inactive';" 
            class="button icon merged right color green yes" 
            style="float: right;"
            id="vcard4validate"
            >{$c->t('Submit')}</a>
        <a
            onclick="document.querySelector('#vcard4form').reset();"
            class="button icon no merged left color orange"
            style="float: right;">{$c->t('Reset')}</a>
    </fieldset> 

    <fieldset>
        <legend>{$c->t('Privacy Level')}</legend>
        <div class="element">
            <label>{$c->t('Is this profile public ?')}</label>
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
                    {$c->t('Please pay attention ! By making your profile public, all the information listed above will be available for all the Movim users and on the whole Internet.')}
                </div>
            </div>
        <!--<div class="element">
            
            
            
        </div>-->
    </fieldset> 
</form>
