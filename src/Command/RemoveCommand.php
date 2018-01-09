<?php declare(strict_types=1);

/**
 * This file is part of pmg/cred-commands
 *
 * Copyright (c) PMG <https://www.pmg.com>
 *
 * For full copyright information see the LICENSE file distributed
 * with this source code.
 *
 * @license http://opensource.org/licenses/MIT MIT
 */

namespace PMG\CredCommands\Command;

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Remove a single parameter from the parameter store.
 *
 * @since 1.0
 */
final class RemoveCommand extends Command
{
    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this->setName('creds:remove');
        $this->setAliases(['creds:rm']);
        $this->setDescription('Remove a credential from the parameter store');
        $this->addArgument(
            'credential',
            InputArgument::REQUIRED,
            'The credential to remove'
        );
        $this->setHelp(<<<END
The <info>%command.name%</info> command removes a single paramter from the AWS SSM
Parameter store.

    %command.full_name% some_cred
END
        );
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $in, OutputInterface $out)
    {
        $cred = $this->client->remove($in->getArgument('credential'));

        $out->writeln(sprintf('removed %s', $in->getArgument('credential')));
    }
}
