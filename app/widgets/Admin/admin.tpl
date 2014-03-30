<form name="admin" id="adminform" action="#" method="post">
    <div id="admincomp" class="tabelem padded" title="{$c->t("Compatibility Check")}">
        {$c->prepareAdminComp()}
    </div>
    <div id="admingen" class="tabelem padded" title="{$c->t('General Settings')}">
        {$c->prepareAdminGen()}
    </div>
    <div id="admindb" class="tabelem padded" title="{$c->t("Database Settings")}">
        {$c->prepareAdminDB()}
    </div>
</form>
