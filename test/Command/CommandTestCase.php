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

use PMG\CredCommands\Application;
use PMG\CredCommands\CredentialClient;
use Symfony\Component\Console\Tester\CommandTester;

abstract class CommandTestCase extends \PMG\CredCommands\TestCase
{
    const CREDENTIAL = 'testcredname';

    protected $client, $app;

    protected function setUp()
    {
        $this->client = $this->createMock(CredentialClient::class);
        $this->app = new Application($this->client);
    }

    abstract protected function getCommandName() : string;

    protected function createTester() : CommandTester
    {
        return new CommandTester($this->app->find($this->getCommandName()));
    }

    protected function executeCommand(array $args, array $options=[])
    {
        $tester = $this->createTester();
        $statusCode = $tester->execute(array_replace([
            'command' => $this->getCommandName(),
        ], $args), $options);

        return [$tester, $statusCode];
    }
}
