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

class MultiGetCommandTest extends CommandTestCase
{
    const CRED2 = 'testcred2';

    public static function formats()
    {
        return [
            [MultiGetCommand::FORMAT_DEFAULT],
            [MultiGetCommand::FORMAT_JSON],
            [MultiGetCommand::FORMAT_SHELL],
        ];
    }

    /**
     * @dataProvider formats
     */
    public function testCommandLooksUpCredentialsFromTheClientAndOutputs(string $format)
    {
        $creds = [
            self::CREDENTIAL => 'abc123',
            self::CRED2 => 'xyz234',
        ];
        $this->client->expects($this->once())
            ->method('getMultiple')
            ->with(self::CREDENTIAL, self::CRED2)
            ->willReturn($creds);

        [$tester, $status] = $this->executeCommand([
            'credential' => [self::CREDENTIAL, self::CRED2],
        ]);

        $this->assertSame(0, $status);
        foreach ($creds as $key => $cred) {
            $this->assertStringContainsStringIgnoringCase($key, $tester->getDisplay());
            $this->assertStringContainsStringIgnoringCase($cred, $tester->getDisplay());
        }
    }

    protected function getCommandName() : string
    {
        return MultiGetCommand::getDefaultName();
    }
}
