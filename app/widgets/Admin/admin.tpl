<form name="admin" id="adminform" action="#" method="post">
    <div id="admincomp" class="tabelem padded" title="{$c->__('admin.compatibility')}">
        {$c->prepareAdminComp()}
    </div>
    <div id="admingen" class="tabelem padded" title="{$c->__('admin.general')}">
        {$c->prepareAdminGen()}
    </div>
    <div id="admindb" class="tabelem padded" title="{$c->__('db.legend')}">
        {$c->prepareAdminDB()}
    </div>
</form>
