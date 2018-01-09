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

class GetCommandTest extends CommandTestCase
{
    public function testCommandLooksUpCredentialFromTheClientAndOutputs()
    {
        $this->client->expects($this->once())
            ->method('get')
            ->with(self::CREDENTIAL)
            ->willReturn('testing123');

        [$tester, $status] = $this->executeCommand([
            'credential' => self::CREDENTIAL,
        ]);

        $this->assertSame(0, $status);
        $this->assertEquals('testing123', $tester->getDisplay());
    }

    protected function createCommand() : Command
    {
        return new GetCommand($this->client);
    }
}
