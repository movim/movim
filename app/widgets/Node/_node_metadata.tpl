<h1>{$title}</h1>
{if="isset($creation)"}
    <span class="key"><i class="fa fa-clock-o"></i> </span>
    <span>{$creation}</span>
{/if}
{if="isset($creator)"}
    <span class="key"><i class="fa fa-user"></i> </span>
    <span>{$creator}</span>
{/if}


{if="isset($description)"}
    <p>{$description}</p>
{/if}
