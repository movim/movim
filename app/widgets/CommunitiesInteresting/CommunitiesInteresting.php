<?php

use Movim\Widget\Base;

include_once WIDGETS_PATH . 'Post/Post.php';

class CommunitiesInteresting extends Base
{
    public function display()
    {
        $this->view->assign('communities', $this->user->session
            ->interestingCommunities(6)
            ->inRandomOrder()
            ->get()
        );
    }
}
