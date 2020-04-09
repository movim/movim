<div id="admincomp" class="tabelem" title="{$c->__('admin.compatibility')}">
    <div>
        <figure>
            <div id="webserver"></div>
            <div id="movim-daemon" class="link vertical disabled"><i class="material-icons">settings</i></div>
            <div id="movim-browser" class="link horizontal success"><i class="material-icons">open_in_browser</i></div>
            <div id="browser-daemon" class="link horizontal error"><i class="material-icons">code</i></div>
            <div id="xmpp-daemon" class="link horizontal"><i class="material-icons">import_export</i></div>
            <div id="movim-database" class="link vertical {if="$dbconnected"}success{else}error{/if}">
                <i class="material-icons">swap_horiz</i>
            </div>
            <div id="movim-api" class="link horizontal disabled"><i class="material-icons">cloud</i></div>
            <div id="browser_block">
                {$c->__('schema.browser')}
            </div>
            <div id="movim_block">
                {$c->__('schema.movim')}
            </div>
            <div id="daemon_block">
                {$c->__('schema.daemon')}
            </div>
            <div id="database_block" class="{if="$dbconnected"}success{else}error{/if}">
                {$c->__('schema.database')}
            </div>
            <div id="api_block">
                {$c->__('schema.api')}
            </div>
            <div id="xmpp_block">
                {$c->__('schema.xmpp')}
            </div>
        </figure>
    </div>

    <ul class="list">
        {if="$dbconnected"}
            <script type="text/javascript">AdminTest.databaseOK = true</script>
        {else}
            <li>
                <span class="primary icon bubble color red">
                    <i class="material-icons">data_usage</i>
                </span>
                <div>
                    <p>Database connection error</p>
                    <p>Check if database configuration exist in the <code>config/</code> folder and fill it with proper values</p>
                </div>
            </li>
        {/if}

        <li id="websocket_error">
            <span class="primary icon bubble color red">
                <i class="material-icons">code</i>
            </span>
            <div>
                <p class="normal line">
                    {$c->__('compatibility.websocket')}
                </p>
            </div>
        </li>

        {if="!$c->testDir(CACHE_PATH)"}
            <li>
                <span class="primary icon color bubble red">
                    <i class="material-icons">folder</i>
                </span>
                <div>
                    <p class="normal line">{$c->__('compatibility.rights', 'cache')}</p>
                </div>
            </li>
            <script type="text/javascript">AdminTest.disableMovim()</script>
        {/if}

        {if="!$c->testDir(LOG_PATH)"}
            <li>
                <span class="primary icon color bubble red">
                    <i class="material-icons">folder</i>
                </span>
                <div>
                    <p class="normal line">{$c->__('compatibility.rights', 'log')}</p>
                </div>
            </li>
            <script type="text/javascript">AdminTest.disableMovim()</script>
        {/if}
    </ul>
</div>
