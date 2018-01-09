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

/**
 * Defines how a credential name is to be formatted before fetching the
 * parameter from the SSM.
 *
 * @since 1.0
 */
interface CredentialNameFormatter
{
    public function format(string $credential) : string;
}
