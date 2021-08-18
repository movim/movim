<?php

use Movim\Widget\Base;

include_once WIDGETS_PATH.'CommunityData/CommunityData.php';
include_once WIDGETS_PATH.'CommunityAffiliations/CommunityAffiliations.php';

class CommunityDataPublic extends Base
{
    public function prepareCard($info)
    {
        return (new CommunityData)->prepareCard($info);
    }

    public function preparePublicSubscriptions($subscriptions)
    {
        return (new CommunityAffiliations)->preparePublicSubscriptionsList($subscriptions);
    }

    public function display()
    {
        $server = $this->get('s');
        $node = $this->get('n');

        $info = \App\Info::where('server', $server)
                         ->where('node', $node)
                         ->first();

        $subscriptions = \App\Subscription::where('server', $server)
                                          ->where('node', $node)
                                          ->where('public', true)
                                          ->get();

        $this->view->assign('subscriptions', $subscriptions);
        $this->view->assign('info', $info);
    }
}
