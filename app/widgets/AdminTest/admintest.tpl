<div id="admincomp" class="tabelem" title="{$c->__('admin.compatibility')}">
    <div>
        <figure>
            <div id="webserver">

            </div>
            <div id="movim-daemon" class="link vertical disabled"><i class="zmdi zmdi-settings"></i></div>
            <div id="movim-browser" class="link horizontal success"><i class="zmdi zmdi-open-in-browser"></i></div>
            <div id="browser-daemon" class="link horizontal error"><i class="zmdi zmdi-code-setting"></i></div>
            <div id="xmpp-daemon" class="link horizontal"><i class="zmdi zmdi-import-export"></i></div>
            <div id="movim-database" class="link vertical {if="$dbconnected"}success {if="$dbinfos > 0"}warning{/if} {else}error{/if}">
                <i class="zmdi zmdi-swap"></i>
            </div>
            <div id="movim-api" class="link horizontal disabled"><i class="zmdi zmdi-cloud"></i></div>
            <div id="browser_block">
                {$c->__('schema.browser')}
            </div>
            <div id="movim_block">
                {$c->__('schema.movim')}
            </div>
            <div id="daemon_block">
                {$c->__('schema.daemon')}
            </div>
            <div id="database_block" class="{if="$dbconnected"}success {if="$dbinfos > 0"}warning{/if} {else}error{/if}">
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
        <!--
        <li class="subheader">
            {$c->__('compatibility.info')}
        </li>
        -->
        {if="$dbconnected"}
            {if="$dbinfos > 0"}
                <li>
                    <span class="primary icon bubble color orange">
                        <i class="zmdi zmdi-refresh"></i>
                    </span>
                    <p class="normal line">{$c->__('compatibility.db')}</p>
                </li>
            {else}
                <script type="text/javascript">AdminTest.databaseOK = true</script>
            {/if}
        {else}
            <li>
                <span class="primary icon bubble color red">
                    <i class="zmdi zmdi-data-usage"></i>
                </span>
                <p>Database connection error</p>
                <p>Check if database configuration exist in the <code>config/</code> folder and fill it with proper values</p>
            </li>
        {/if}

        <li id="websocket_error">
            <span class="primary icon bubble color red">
                <i class="zmdi zmdi-code-setting"></i>
            </span>
            <p class="normal line">
                {$c->__('compatibility.websocket')}
            </p>
        </li>

        {if="!$c->version()"}
            <li>
                <span class="primary icon color bubble red">
                    <i class="zmdi zmdi-sync-problem"></i>
                </span>
                <p>{$c->__('compatibility.php1', PHP_VERSION)}</p>
                <p>{$c->__('compatibility.php2')}</p>
            </li>
            <script type="text/javascript">AdminTest.disableMovim()</script>
        {/if}

        {if="!extension_loaded('imagick')"}
            <li>
                <span class="primary icon color bubble red">
                    <i class="zmdi zmdi-image"></i>
                </span>
                <p class="normal line">
                    {$c->__('compatibility.imagick')}
                </p>
            </div>
            <script type="text/javascript">AdminTest.disableMovim()</script>
        {/if}

        {if="!extension_loaded('gd')"}
            <li>
                <span class="primary icon color bubble red">
                    <i class="zmdi zmdi-image"></i>
                </span>
                <p class="normal line">
                    {$c->__('compatibility.gd')}
                </p>
            </div>
            <script type="text/javascript">AdminTest.disableMovim()</script>
        {/if}

        {if="!$c->testDir(DOCUMENT_ROOT)"}
            <li>
                <span class="primary icon color bubble red">
                    <i class="zmdi zmdi-folder"></i>
                </span>
                <p class="normal line">{$c->__('compatibility.rights')}</p>
            </li>
            <script type="text/javascript">AdminTest.disableMovim()</script>
        {/if}
    </ul>
</div>
