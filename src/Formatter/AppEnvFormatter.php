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

use PMG\CredCommands\CredentialNameFormatter;

/**
 * A formatter im plementation that produces creds in an
 * `/{appName}/{environment}/{cred}` format
 *
 * @since 1.0
 */
final class AppEnvFormatter implements CredentialNameFormatter
{
    /**
     * @var string
     */
    private $appName;

    /**
     * @var string
     */
    private $environment;

    public function __construct(string $appName, string $environment)
    {
        $this->appName = $appName;
        $this->environment = $environment;
    }

    /**
     * {@inheritdoc}
     */
    public function format(string $credential) : string
    {
        return sprintf(
            '/%s/%s/%s',
            $this->appName,
            $this->environment,
            $credential
        );
    }
}
