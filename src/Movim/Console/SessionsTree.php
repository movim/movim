<?php
/*
 * SPDX-FileCopyrightText: 2023 Jaussoin Timothée
 * SPDX-License-Identifier: AGPL-3.0-or-later
 */

namespace Movim\Console;

use App\Session;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\TreeHelper;
use Symfony\Component\Console\Helper\TreeNode;
use Symfony\Component\Console\Helper\TreeStyle;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class SessionsTree extends Command
{
    protected function configure()
    {
        $this->setName('sessionsTree')
            ->setDescription('Display the current sessions tree')
            ->setHelp(
                '<options=bold>Structure</> ⚙️  - 🔧 worker-id - 👤⚪ session-id (<connected sockets>)' . "\n".
                '<options=bold>Session status</> 🔴 Launched | 🟠 XMPP Socket connected | 🟢 XMPP Session started' . "\n");
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $tree = requestAPI('sessionstree');

        if ($tree == false) {
            $output->writeln('<error>Cannot get the information, did you tried with the daemon user?</error>');
            return Command::FAILURE;
        }

        $tree = json_decode($tree, true);
        $sessions = 0;
        array_walk($tree, function ($w) use (&$sessions) {$sessions += count($w);});

        $root = new TreeNode('⚙️  Movim Daemon 🔧 ' . count($tree) . ' 👤 ' . $sessions);

        $dbSessions = Session::all()->pluck('user_id', 'id');

        foreach ($tree as $wid => $worker) {
            $workerNode = new TreeNode('🔧 ' . $wid . ' (' . count($worker) . ')');

            foreach ($worker as $sid => $session) {
                $state = '🔴';

                if ($session['registered']) $state = '🟠';
                if ($session['started']) $state = '🟢';

                $jid = '';
                if ($resolvedJid = $dbSessions->get($sid)) {
                    $jid = ' <fg=green>' . $resolvedJid . '</>';
                }

                $sessionNode = new TreeNode('👤 ' . $state . ' ' . $sid .  $jid . ' (' . $session['clients'] . ')');
                $workerNode->addChild($sessionNode);
            }

            $root->addChild($workerNode);
        }

        $tree = TreeHelper::createTree($output, $root, style: TreeStyle::rounded());
        $tree->render();

        return Command::SUCCESS;
    }
}
