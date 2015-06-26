<article>
    <header>
        <ul class="thick">
            <li class="condensed">
                {if="(float)$ticker->change < 0"}
                    <span class="icon color bubble red">
                        <i class="zmdi zmdi-trending-down"></i>
                    </span>
                {elseif="(float)$ticker->change > 0"}
                    <span class="icon color bubble green">
                        <i class="zmdi zmdi-trending-up"></i>
                    </span>
                {else}
                    <span class="icon color bubble gray">
                        <i class="zmdi zmdi-arrow-forward"></i>
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
        <ul class="middle simple flex">
            <li class="condensed block background_fade">
                <span>
                    <h3>{$ticker->value}</h3>
                </span>
                <p>
                    {$ticker->change} ({$ticker->percent}%)
                </p>
            </li>
            <li class="condensed block">
                <span>Market Cap : {$ticker->capitalization}</span><br />
                <span>
                    Volume : {$ticker->volume|floatval|number_format:0,',',' '}
                </span>
            </li>
            <li class="block large">
                <img src="http://chart.finance.yahoo.com/t?s={$ticker->symbol}&lang=en-US&region=US&width=700&height=400"/>
            </li>
        </ul>
    </section>
</article>
