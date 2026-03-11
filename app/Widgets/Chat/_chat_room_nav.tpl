<ul class="list thin active" id="room_nav_members">
    {autoescape="off"}{$contactsHtml}{/autoescape}
</ul>
<ul class="list thin" id="room_nav_members_search">
    <li class="search">
        <form name="search" onsubmit="return false;">
            <div>
                <input name="keyword" autocomplete="off" oninput="Chat.searchMembers(this.value)" type="text">
            </div>
        </form>
    </li>
</ul>