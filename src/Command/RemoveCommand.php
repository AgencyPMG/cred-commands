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
    protected static $defaultName = 'creds:remove';

    /**
     * {@inheritdoc}
     */
    protected function configure() : void
    {
        $this->setDescription('Remove a credential from the parameter store');
        $this->addArgument(
            'credential',
            InputArgument::REQUIRED | InputArgument::IS_ARRAY,
            'The credential to remove'
        );
        $this->setHelp(<<<END
The <info>%command.name%</info> command removes one or more paramters from the
AWS SSM Parameter store.

    %command.full_name% some_cred

Or with multiple creds.

    %command.full_name% one_more_cred another_cred
END
        );
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $in, OutputInterface $out) : int
    {
        $toRemove = (array) $in->getArgument('credential');
        $cred = $this->client->remove(...$toRemove);

        foreach ($toRemove as $c) {
            $out->writeln(sprintf('removed %s', $c));
        }

        return 0;
    }
}
