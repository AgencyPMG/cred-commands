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

namespace PMG\CredCommands;

use Symfony\Component\Console\Application as SymfonyApplication;

/**
 * A fully put together credential application to drop into other apps.
 *
 * @since 1.0
 */
final class Application extends SymfonyApplication
{
    public function __construct(CredentialClient $client, string $name='UNKNOWN', string $version='UNKNOWN')
    {
        parent::__construct($name, $version);
        $this->addCommands([
            new Command\GetCommand($client),
            new Command\MultiGetCommand($client),
            new Command\PutCommand($client),
            new Command\RemoveCommand($client),
        ]);
    }
}
