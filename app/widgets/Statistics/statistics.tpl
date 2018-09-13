<div id="statistics" class="tabelem paddedtop" title="{$c->__("statistics.title")}">
    <br />
    <p>From {$total} users presences retrieved.</p>
    <br />

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
</div>
