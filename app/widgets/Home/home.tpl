<header>
    <ul class="list">
        <li>
            <a class="button color oppose" href="{$c->route('login')}">{$c->__('page.login')}</a>
            <a class="button flat" href="{$c->route('account')}">{$c->__('button.register')}</a>
        </li>
    </ul>
</header>

<div class="card shadow clear">
<br />
    {loop="$posts"}
        {$c->preparePost($value)}
    {/loop}
    {if="isset($more)"}
        <article class="block">
            <ul class="list active thick">
                <a href="{$c->route('home', $more)}">
                    <li id="history" class="block large">
                        <span class="primary icon gray"><i class="zmdi zmdi-time-restore"></i></span>
                        <p class="normal line center">{$c->__('post.older')}</p>
                    </li>
                </a>
            </ul>
        </article>
    {/if}
    {if="$posts == null"}
        <ul class="list simple thick block">
            <li>
                <span class="primary icon gray">
                    <i class="zmdi zmdi-comment-outline"></i>
                </span>
                <p class="normal">{$c->__('blog.empty')}</p>
            </li>
        </ul>
    {/if}

    <ul class="list block">
        <li>
            <span class="primary icon gray">
                <i class="zmdi zmdi-cloud-outline"></i>
            </span>
            <p class="center normal"><a target="_blank" href="https://movim.eu">Powered by Movim</a></p>
        </li>
    </ul>
</div>
