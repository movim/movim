<h1>{$title}</h1>
{if="isset($creation)"}
    <span class="key">{$c->t('created')}</span>
    <span>{$creation}</span>
{/if}
{if="isset($creator)"}
    <span class="key">{$c->t('by')}</span>
    <span>{$creator}</span>
{/if}


{if="isset($description)"}
    <p>{$description}</p>
{/if}
