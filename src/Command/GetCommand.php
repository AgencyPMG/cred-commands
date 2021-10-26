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
    protected static $defaultName = 'creds:get';

    /**
     * {@inheritdoc}
     */
    protected function configure() : void
    {
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

The follow is an example of how that might work in something like a
docker entrypoint.

    #!/bin/bash
    export SOME_CRED="$(%command.full_name% some_cred)"
    # other creds here probably

    exec "$@"
END
        );
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $in, OutputInterface $out) : int
    {
        $cred = $this->client->get($in->getArgument('credential'));

        $out->write($cred, false, OutputInterface::OUTPUT_RAW);

        return 0;
    }
}
