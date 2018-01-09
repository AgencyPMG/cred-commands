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

class PutCommandTest extends CommandTestCase
{
    public function testCommandAddsANewValueToTheParamterStore()
    {
        $this->client->expects($this->once())
            ->method('put')
            ->with(self::CREDENTIAL, 'testing123')
            ->willReturn(1);

        [$tester, $status] = $this->executeCommand([
            'credential' => self::CREDENTIAL,
            'value' => 'testing123',
        ]);

        $this->assertSame(0, $status);
        $this->assertContains(
            sprintf('put %s: version 1', self::CREDENTIAL),
            $tester->getDisplay()
        );
    }

    protected function getCommandName() : string
    {
        return 'put';
    }
}
