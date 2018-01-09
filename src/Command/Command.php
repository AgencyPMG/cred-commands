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

use Symfony\Component\Console\Command\Command as SymfonyCommand;
use PMG\CredCommands\CredentialClient;

abstract class Command extends SymfonyCommand
{
    /**
     * @var CredentialClient
     */
    protected $client;

    public function __construct(CredentialClient $client)
    {
        parent::__construct();
        $this->client = $client;
    }
}
