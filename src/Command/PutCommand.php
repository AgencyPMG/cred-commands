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
 * Put a single parameter from the parameter store.
 *
 * @since 1.0
 */
final class PutCommand extends Command
{
    protected static $defaultName = 'creds:put';

    /**
     * {@inheritdoc}
     */
    protected function configure() : void
    {
        $this->setDescription('Put a credential into the parameter store');
        $this->addArgument(
            'credential',
            InputArgument::REQUIRED,
            'The credential to put'
        );
        $this->addArgument(
            'value',
            InputArgument::REQUIRED,
            'The credential value'
        );
        $this->setHelp(<<<END
The <info>%command.name%</info> command puts a new credential value into the parameter
store.

    %command.full_name% some_cred aValue

If the value contains dashes or other character that may be construed as options,
use the double dash (end of arguments) to avoid parsing the value.

    %command.full_name% some_cred -- -aValue-with-dashes
END
        );
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $in, OutputInterface $out) : int
    {
        $ver = $this->client->put(
            $in->getArgument('credential'),
            $in->getArgument('value')
        );

        $out->writeln(sprintf(
            'put %s: version %d',
            $in->getArgument('credential'),
            $ver
        ));

        return 0;
    }
}
