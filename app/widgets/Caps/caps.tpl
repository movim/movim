<div id="caps_widget" class="tabelem paddedtop" title="Capabilities">
    <h1>Statistics</h1>

    <p>From {$total} users presences retrieved.</p>

    <table>
        <thead>
            <tr>
                <th style="width: 25rem;">Client</th>
                <th>Percentage</th>
            </tr>
        </thead>

        <tbody>
            {loop="$stats"}
                {if="$value/$total*100 > 0.2"}
                    <tr>
                        <td style="text-align: right;">{$c->getCapabilityName($key)}</td>
                        <td>{$value/$total*100|round:1}%</td>
                    </tr>
                {/if}
            {/loop}
        </tbody>
    </table>

    <h1>Capabilities</h1>

    <h2>Legend</h2>
    <table>
        <tbody>
            <tr>
                <td>Chat</td>
                <td class="chat yes">0xxx</td>
                <td>Jingle</td>
                <td class="jingle yes">0xxx</td>
                <td>Rayo</td>
                <td class="rayo yes">0xxx</td>
                <td>Profile</td>
                <td class="profile yes">0xxx</td>
                <td>Client</td>
                <td class="client yes">0xxx</td>
                <td>Social</td>
                <td class="social yes">0xxx</td>
            </tr>
        </tbody>
    </table>

    <h2>Table</h2>
    <table>
        <thead>
            <tr>
                <th>Client</th>
                <th>Count</th>
                {loop="$nslist"}
                    <th>
                        <a target="_blank" title="{$value.name}"
                           href="https://xmpp.org/extensions/xep-{$key}.html">
                            {$key}
                        </a>
                    </th>
                {/loop}
            </tr>
        </thead>

        <tbody>
            {loop="$table"}
            <tr>
                <td title="{$key}">{$key}</td>
                <td>{$value|count}</td>
                {$client = $value}
                {loop="$nslist"}
                    {autoescape="off"}
                        {$c->isImplemented($client, $key)}
                    {/autoescape}
                {/loop}
            </tr>
            {/loop}
        </tbody>
    </table>
</div>
