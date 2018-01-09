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

namespace PMG\CredCommands\Formatter;

class NullFormatterTest extends \PMG\CredCommands\TestCase
{
    public function testFormatterReturnsCredentialNameWithoutModifications()
    {
        $f = new NullFormatter();

        $this->assertSame('testcred', $f->format('testcred'));
    }
}
