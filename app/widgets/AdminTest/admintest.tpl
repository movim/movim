<div id="admincomp" class="tabelem paddedtop" title="{$c->__('admin.compatibility')}">
    <fieldset>
        <legend>{$c->__('admin.compatibility')}</legend>
        <div class="clear"></div>
        <p>
            {$c->__('compatibility.info')}
        </p><br />
           
        <div class="{$c->valid($c->version())}">
            {$c->__('compatibility.php', PHP_VERSION)}
        </div>
        <div class="{$c->valid(extension_loaded('curl'))}">
            {$c->__('compatibility.curl')}
        </div>
        <div class="{$c->valid(extension_loaded('gd'))}">
            {$c->__('compatibility.gd')}
        </div>
        <div class="{$c->valid(extension_loaded('SimpleXml'))}">
            {$c->__('compatibility.simplexml')}
        </div>
        <div class="{$c->valid($c->testDir(DOCUMENT_ROOT))}">
            {$c->__('compatibility.rights')}
        </div>
        <div class="{$c->valid(extension_loaded('OpenSSL'))}">
            {$c->__('compatibility.openssl')}
        </div>
    </fieldset>

    <fieldset>
        <legend>{$c->__('compatibility.rewrite')}</legend>
            <div class="clear"></div>
            <div class="{$c->valid($_SERVER['HTTP_MOD_REWRITE'])}">
                {$c->__('compatibility.rewrite')}
            </div>
    </fieldset>
</div>
