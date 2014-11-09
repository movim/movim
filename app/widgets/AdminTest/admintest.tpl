<div id="admincomp" class="tabelem paddedtop" title="{$c->__('admin.compatibility')}">
    <figure class="block">
        <div id="webserver">

        </div>
        <div id="movim-daemon" class="link vertical disabled"><i class="fa fa-cog"></i></div>
        <div id="movim-browser" class="link horizontal success"><i class="fa fa-globe"></i></div>
        <div id="browser-daemon" class="link horizontal error"><i class="fa fa-plug"></i></div>
        <div id="daemon-xmpp" class="link horizontal success"><i class="fa fa-code"></i></div>
        <div id="movim-database" class="link vertical {if="$dbconnected"}success {if="$dbinfos > 0"}warning{/if} {else}error{/if}">
            <i class="fa fa-database"></i>
        </div>
        <div id="movim-api" class="link horizontal error"><i class="fa fa-puzzle-piece"></i></div>
        <div id="browser">
            Browser
        </div>
        <div id="movim">
            Movim Core
        </div>
        <div id="daemon">
            Movim Daemon
        </div>
        <div id="database" class="{if="$dbconnected"}success {if="$dbinfos > 0"}warning{/if} {else}error{/if}">
            Database
        </div>
        <div id="api">
            API
        </div>
        <div id="xmpp">
            XMPP
        </div>
    </figure>
    <div class="block">

    </div>
    
    <fieldset>
        <legend>{$c->__('admin.compatibility')}</legend>
        <div class="clear"></div>
        <p>
            {$c->__('compatibility.info')}
        </p><br />
           
        <div class="{$c->valid($c->version())}">
            {$c->__('compatibility.php', PHP_VERSION)}
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
