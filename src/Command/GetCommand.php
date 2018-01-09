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
 * Get a single parameter from the parameter store.
 *
 * @since 1.0
 */
final class GetCommand extends Command
{
    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this->setName(self::prefixName('get'));
        $this->setDescription('Get a credential from the parameter store');
        $this->addArgument(
            'credential',
            InputArgument::REQUIRED,
            'The credential to retrieve'
        );
        $this->setHelp(<<<END
The <info>%command.name%</info> command fetches a single paramter from the AWS SSM
Parameter store. A common use case for this would be to fetch a
credential into an environment variable before running the application.

    #!/bin/bash
    export SOME_CRED="$(%command.full_name% some_cred)"
    # other creds here probably

    exec /path/to/the/app
END
        );
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $in, OutputInterface $out)
    {
        $cred = $this->client->get($in->getArgument('credential'));

        $out->write($cred, false, OutputInterface::OUTPUT_RAW);
    }
}
