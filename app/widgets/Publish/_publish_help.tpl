<section>
    <h3>{$c->__('post.help')}</h3>
    <ul class="list thick flex">
        <li class="block">
            <span class="primary icon">
                <i class="zmdi zmdi-format-size"></i>
            </span>
            <p># Title H1</p>
            <p>## Title H2…</p>
        </li>
        <li class="block">
            <span class="primary icon">
                <i class="zmdi zmdi-format-bold"></i>
            </span>
            <p>**bolded**</p>
            <p>__bolded__</p>
        </li>
        <li class="block">
            <span class="primary icon">
                <i class="zmdi zmdi-format-italic"></i>
            </span>
            <p>*emphasis*</p>
            <p>_emphasis_</p>
        </li>
        <li class="block">
            <span class="primary icon">
                <i class="zmdi zmdi-format-quote"></i>
            </span>
            <p>> Quoted line</p>
            <p>> Quoted line</p>
        </li>
        <li class="block">
            <span class="primary icon">
                <i class="zmdi zmdi-format-list-bulleted"></i>
            </span>
            <p>* Item 1</p>
            <p>* Item 2</p>
        </li>
        <li class="block">
            <span class="primary icon">
                <i class="zmdi zmdi-format-list-numbered"></i>
            </span>
            <p>1. Item 1</p>
            <p>2. Item 2</p>
        </li>
        <li class="block">
            <span class="primary icon">
                <i class="zmdi zmdi-functions"></i>
            </span>
            <p class="normal">`Sourcecode`</p>
        </li>
        <li class="block large">
            <span class="primary icon">
                <i class="zmdi zmdi-link"></i>
            </span>
            <p class="normal">[my text](http://my_url/)</p>
        </li>
        <li class="block large">
            <span class="primary icon">
                <i class="zmdi zmdi zmdi-image"></i>
            </span>
            <p class="normal">![Alt text](http://my_image_url/)</p>
        </li>
    </ul>
    <ul class="list">
        <li class="subheader">
            <p>{$c->__('post.help_more')}</p>
        </li>
            <li>
                <span class="primary icon color bubble blue">
                    <i class="zmdi zmdi-star"></i>
                </span>
                <span class="control icon gray">
                    <i class="zmdi zmdi-chevron-right"></i>
                </span>
                <p class="line">
                    {$c->__('post.help_manual')}
                </p>
                <p class="line">
                    <a href="http://daringfireball.net/projects/markdown/syntax" target="_blank">
                        http://daringfireball.net/projects/markdown/syntax
                    </a>
                </p>
            </li>
        </a>
    </ul>
</section>
<div>
    <a onclick="Dialog.clear()" class="button flat">
        {$c->__('button.cancel')}
    </a>
</div>
