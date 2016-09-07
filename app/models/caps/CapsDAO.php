<?php

namespace modl;

class CapsDAO extends SQL {
    function set(Caps $caps) {
        $this->_sql = '
            update caps
            set category = :category,
                type     = :type,
                name     = :name,
                features = :features
            where node = :node';

        $this->prepare(
            'Caps',
            [
                'node'      => $caps->node,
                'category'  => $caps->category,
                'type'      => $caps->type,
                'name'      => $caps->name,
                'features'  => $caps->features,
            ]
        );

        $this->run('Caps');

        if(!$this->_effective) {
            $this->_sql = '
                insert into caps
                (
                node,
                category,
                type,
                name,
                features
                )
                values(
                    :node,
                    :category,
                    :type,
                    :name,
                    :features
                    )';

            $this->prepare(
                'Caps',
                [
                    'node'      => $caps->node,
                    'category'  => $caps->category,
                    'type'      => $caps->type,
                    'name'      => $caps->name,
                    'features'  => $caps->features,
                ]
            );

            return $this->run('Caps');
        }
    }

    function get($node) {
        $this->_sql = '
            select * from caps
            where
                node = :node';

        $this->prepare(
            'Caps',
            [
                'node' => $node
            ]
        );

        return $this->run('Caps', 'item');
    }

    function getClients() {
        $this->_sql = '
            select * from caps
            where category = :category';

        $this->prepare(
            'Caps',
            [
                'category' => 'client'
            ]
        );

        return $this->run('Caps');
    }

    function getServers() {
        $this->_sql = '
            select * from caps
            where category = :category';

        $this->prepare(
            'Caps',
            [
                'category' => 'server'
            ]
        );

        return $this->run('Caps');
    }

    function getAll() {
        $this->_sql = '
            select * from caps';

        $this->prepare(
            'Caps'
        );

        return $this->run('Caps');
    }
}
