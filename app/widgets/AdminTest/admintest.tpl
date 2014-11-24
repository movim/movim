<div id="admincomp" class="tabelem" title="{$c->__('admin.compatibility')}">
    <div class="block">
        <figure>
            <div id="webserver">

            </div>
            <div id="movim-daemon" class="link vertical disabled"><i class="fa fa-cog"></i></div>
            <div id="movim-browser" class="link horizontal success"><i class="fa fa-globe"></i></div>
            <div id="browser-daemon" class="link horizontal error"><i class="fa fa-plug"></i></div>
            <div id="daemon-xmpp" class="link horizontal error"><i class="fa fa-code"></i></div>
            <div id="movim-database" class="link vertical {if="$dbconnected"}success {if="$dbinfos > 0"}warning{/if} {else}error{/if}">
                <i class="fa fa-database"></i>
            </div>
            <div id="movim-api" class="link horizontal disabled"><i class="fa fa-puzzle-piece"></i></div>
            <div id="browser_block">
                Browser
            </div>
            <div id="movim_block">
                Movim Core
            </div>
            <div id="daemon_block">
                Movim Daemon
            </div>
            <div id="database_block" class="{if="$dbconnected"}success {if="$dbinfos > 0"}warning{/if} {else}error{/if}">
                Database
            </div>
            <div id="api_block">
                API
            </div>
            <div id="xmpp_block">
                XMPP
            </div>
        </figure>
    </div>

    <div class="block">
        <p>
            {$c->__('compatibility.info')}
        </p>
        
        {if="$dbconnected"}
            {if="$dbinfos > 0"}
                <div class="message warning">
                    <i class="fa fa-refresh"></i> The database need to be updated, go to the database panel to fix this
                </div>
            {else}
                <script type="text/javascript">AdminTest.databaseOK = true</script>
            {/if}
        {else}
            <div class="message error">
                <i class="fa fa-database"></i> Database connection error, check if database configuration exist in the <code>config/</code> folder and fill it with proper values
            </div>
        {/if}

        <div id="websocket_error" class="message error">
            <i class="fa fa-plug"></i> WebSocket connection error, check if the Movim Daemon is running and is reacheable 
        </div>
        
        <div id="xmpp_websocket_error" class="message error">
            <i class="fa fa-plug"></i> XMPP Websocket connection error, please check the validity of the URL given in the General Configuration. <code>{$websocketurl}</code>
        </div>

        {if="!$c->version()"}
            <div class="message error">
                <i class="fa fa-code"></i> {$c->__('compatibility.php', PHP_VERSION)}
            </div>
            <script type="text/javascript">AdminTest.disableMovim()</script>
        {/if}

        {if="!extension_loaded('gd')"}
            <div class="message error">
                <i class="fa fa-file-image-o"></i> {$c->__('compatibility.gd')}
            </div>
            <script type="text/javascript">AdminTest.disableMovim()</script>
        {/if}

        {if="!$c->testDir(DOCUMENT_ROOT)"}
            <div class="message error">
                <i class="fa fa-folder"></i> {$c->__('compatibility.rights')}
            </div>
            <script type="text/javascript">AdminTest.disableMovim()</script>
        {/if}

        {if="!$_SERVER['HTTP_MOD_REWRITE']"}
            <div class="message info">
                <i class="fa fa-pencil"></i> {$c->__('compatibility.rewrite')}
            </div>
        {/if}
    </div>
    <script type="text/javascript">AdminTest.testXMPPWebsocket('{$websocketurl}');</script>
</div>
