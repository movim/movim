<?php
/*
 * SPDX-FileCopyrightText: 2010 Jaussoin Timothée
 * SPDX-License-Identifier: AGPL-3.0-or-later
 */

namespace Movim\Console;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\TreeHelper;
use Symfony\Component\Console\Helper\TreeNode;
use Symfony\Component\Console\Helper\TreeStyle;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class GalenerManager extends Command
{
    protected function configure()
    {
        $this
            ->setName('galener')
            ->setDescription('Manage the Galener worker')
            ->addArgument(
                name: 'action',
                mode: InputArgument::REQUIRED,
                description: 'Action to perform',
                suggestedValues: ['start', 'stop', 'restart']
            );
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $message = match ($input->getArgument('action')) {
            'start' => requestAPI('galenerstart'),
            'stop' => requestAPI('galenerstop'),
            'restart' => requestAPI('galenerrestart'),
            'status' => 'status',
            default => 'status'
        };

        if ($message == 'status') {
            $message = requestAPI('galenerstatus', socket: API_SOCKET);

            $output->writeln('<info>' . $message . '</info>');

            if (file_exists(GALENER_API_SOCKET)) {
                $tree = requestAPI('conferences', socket: GALENER_API_SOCKET);

                if ($tree == false) {
                    $output->writeln('<error>Cannot get the information, did you tried with the daemon user?</error>');
                    return Command::FAILURE;
                }

                $tree = json_decode($tree, true);

                $root = new TreeNode('⚙️  Movim Galener');

                foreach ($tree as $jid => $conference) {
                    $conferenceNode = new TreeNode('📹 ' . $conference['sfu_jid']);

                    $statusNode = new TreeNode('➡️  <options=bold>In room</> ' . $jid);
                    $conferenceNode->addChild($statusNode);

                    if ($conference['started_at']) {
                        $startedAt = new TreeNode('🕒 <options=bold>Started since</> ' . $conference['started_at']);
                        $conferenceNode->addChild($startedAt);
                    }

                    $connectionsNode = new TreeNode('📑 <options=bold>Connected</> '. count($conference['connections']));
                    $conferenceNode->addChild($connectionsNode);

                    foreach ($conference['connections'] as $connection) {
                        $connectionNode = new TreeNode('👤 ' . $connection);
                        $connectionsNode->addChild($connectionNode);
                    }

                    $root->addChild($conferenceNode);
                }

                $tree = TreeHelper::createTree($output, $root, style: TreeStyle::rounded());
                $tree->render();
            }
        } else {
            $output->writeln('<info>' . $message . '</info>');
        }

        return Command::SUCCESS;
    }
}
