<div class="placeholder">
    <i class="material-symbols">article_person</i>
    <h4>{$c->__('post.subscription_required', $contact->truename)}</h4>
</div>
<ul class="list thick card flex shadow active">
    <li class="block large" onclick="MovimUtils.reload('{$c->route('contact', $contact->id)}');">
        <span class="primary icon bubble">
            <img loading="lazy" src="{$contact->getPicture(\Movim\ImageSize::M)}">
        </span>
        <span class="control icon">
            <i class="material-symbols">chevron_right</i>
        </span>
        <div>
            <p>{$contact->truename}</p>
            <p>{$c->__('post.see_profile', $contact->truename)}</p>
        </div>
    </li>
</ul>
