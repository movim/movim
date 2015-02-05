<div id="caps_widget" class="tabelem paddedtop" title="Capabilities">
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
                <td>IoT</td>
                <td class="iot yes">0xxx</td>
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
                {loop="$nslist"}
                    <th><a href="{$c->route('about', $key, 'xep_widget')}">{$key}</a></th>
                {/loop}
            </tr>
        </thead>

        <tbody>
            {loop="$table"}
            <tr>
                <td>{$key}</td>
                {$client = $value}
                {loop="$nslist"}
                    {$c->isImplemented($client, $key)}
                {/loop}                
            </tr>
            {/loop}
        </tbody>
    </table>
</div>
