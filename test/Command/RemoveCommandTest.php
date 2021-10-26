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

class RemoveCommandTest extends CommandTestCase
{
    public function testCommandRemoveCredentialFromTheParameterStore()
    {
        $this->client->expects($this->once())
            ->method('remove')
            ->with(self::CREDENTIAL);

        [$tester, $status] = $this->executeCommand([
            'credential' => self::CREDENTIAL,
        ]);

        $this->assertSame(0, $status);
        $this->assertStringContainsStringIgnoringCase(
            sprintf('removed %s', self::CREDENTIAL),
            $tester->getDisplay()
        );
    }

    public function testMultipleCredentialsCanBeRemovedFromTheParameterStorage()
    {
        $this->client->expects($this->once())
            ->method('remove')
            ->with(self::CREDENTIAL, 'another_cred');

        [$tester, $status] = $this->executeCommand([
            'credential' => [self::CREDENTIAL, 'another_cred'],
        ]);

        $this->assertSame(0, $status);
        $this->assertStringContainsStringIgnoringCase(
            sprintf('removed %s', self::CREDENTIAL),
            $tester->getDisplay()
        );
        $this->assertStringContainsStringIgnoringCase(
            sprintf('removed %s', 'another_cred'),
            $tester->getDisplay()
        );
    }

    protected function getCommandName() : string
    {
        return RemoveCommand::getDefaultName();
    }
}
