<div class="tabelem paddedtop" title="{$c->__('page.help')}" id="help">
<h1><i class="fa fa-life-ring"></i> {$c->__('page.help')}</h1>
    
<h2>{$c->__('what.title')}</h2>

<p>{$c->__('what.info1', '<a href="http://wiki.movim.eu/whoami" target="_blank">', '</a>')}</p>

<h2>{$c->__('banner.title')}</h2>
<center>    
<div title="{function="getFlagTitle("white")"}" style="width: 60px; height: 50px; display: inline-block;" class="protect white"></div>
<div title="{function="getFlagTitle("green")"}"  style="width: 60px; height: 50px; display: inline-block;" class="protect green"></div>
<div title="{function="getFlagTitle("orange")"}"  style="width: 60px; height: 50px; display: inline-block;" class="protect orange"></div>
<div title="{function="getFlagTitle("red")"}"  style="width: 60px; height: 50px; display: inline-block;" class="protect red"></div>
<div title="{function="getFlagTitle("black")"}" title="{$c->__('Help')}" style="width: 60px; height: 50px; display: inline-block;" class="protect black"></div>
</center>
    
<p>{$c->__('banner.info1')}</p>

<p>
    <ul class="clean">
        <li>{$c->__('banner.white')}</li>
        <li>{$c->__('banner.green')}</li>
        <li>{$c->__('banner.orange')}</li>
        <li>{$c->__('banner.red')}</li>
        <li>{$c->__('banner.black')}</li>
    </ul>
</p>

<h2>{$c->__('missing.title')}</h2>

<p>{$missing_info1}</p>

<p>{$missing_info2}</p>

<h2>{$c->__('faq.title')}</h2>

<p>{$faq_info1}</p>

</div>
