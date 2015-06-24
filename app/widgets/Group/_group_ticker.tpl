<article>
    <header>
        <ul class="thick">
            <li class="condensed">
                {if="(float)$ticker->change < 0"}
                    <span class="icon color bubble red">
                        <i class="md md-trending-down"></i>
                    </span>
                {elseif="(float)$ticker->change > 0"}
                    <span class="icon color bubble green">
                        <i class="md md-trending-up"></i>
                    </span>
                {else}
                    <span class="icon color bubble gray">
                        <i class="md md-trending"></i>
                    </span>
                {/if}
                </span>
                <h2>
                    {$ticker->name}
                </h2>
                <p>{$ticker->symbol} - {$ticker->time|strtotime|prepareDate}</p>
            </li>
        </ul>
    </header>
    <section>
        <ul class="middle simple">
            <li class="condensed">
                <span><h3>{$ticker->value}</h3></span>
                <p>
                    {$ticker->change}({$ticker->percent})
                </p>
            </li>
            <li>
                <img src="http://chart.finance.yahoo.com/t?s={$ticker->symbol}%3dX&lang=en-US&region=US&width=500&height=280"/>
            </li>
        </ul>
    </section>
</article>
