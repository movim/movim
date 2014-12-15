<?php

/**
 * @package Widgets
 *
 * @file Post.php
 * This file is part of Movim.
 *
 * @brief The Post visualisation widget
 *
 * @author Jaussoin Timothée <edhelas_at_movim_dot_com>
 *
 * @version 1.0
 * @date 1 december 2014
 *
 * Copyright (C)2014 MOVIM project
 *
 * See COPYING for licensing information.
 */

class Post extends WidgetCommon
{
    function load()
    {
    }

    function ajaxGetPost($id)
    {
        $html = $this->preparePost($id);
        RPC::call('movim_fill', 'post_widget', $html);
    }

    function prepareEmpty()
    {
        $view = $this->tpl();
        return $view->draw('_post_empty', true);
    }

    function preparePost($id)
    {
        $pd = new \Modl\PostnDAO;
        $p  = $pd->getItem($id);

        $view = $this->tpl();

        if(isset($p)) {
            $view->assign('post', $p);
            $view->assign('attachements', $p->getAttachements());
            return $view->draw('_post', true);
        } else {
            return $this->prepareEmpty();
        }
    }

    function display()
    {
    }
}
