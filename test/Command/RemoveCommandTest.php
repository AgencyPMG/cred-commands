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
        $this->assertContains(
            sprintf('removed %s', self::CREDENTIAL),
            $tester->getDisplay()
        );
    }

    protected function getCommandName() : string
    {
        return 'remove';
    }
}
