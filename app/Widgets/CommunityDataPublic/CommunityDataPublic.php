<?php

namespace App\Widgets\CommunityDataPublic;

use App\Info;
use App\Subscription;
use App\Widgets\CommunityAffiliations\CommunityAffiliations;
use App\Widgets\CommunityData\CommunityData;
use Movim\Widget\Base;

class CommunityDataPublic extends Base
{
    public function prepareCard($info)
    {
        return (new CommunityData($this->me, sessionId: $this->sessionId))->prepareCard($info);
    }

    public function preparePublicSubscriptions($subscriptions)
    {
        return (new CommunityAffiliations($this->me, sessionId: $this->sessionId))->preparePublicSubscriptionsList($subscriptions);
    }

    public function display()
    {
        $server = $this->get('s');
        $node = $this->get('n');

        $info = Info::where('server', $server)
            ->where('node', $node)
            ->first();

        $subscriptions = Subscription::where('server', $server)
            ->where('node', $node)
            ->where('public', true)
            ->get();

        $this->view->assign('subscriptions', $subscriptions);
        $this->view->assign('info', $info);
    }
}
