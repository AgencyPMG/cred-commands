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

use Aws\Ssm\SsmClient;
use Symfony\Component\Console\Command\Command as SymfonyCommand;
use PMG\CredCommands\CredentialNameFormatter;
use PMG\CredCommands\Formatter\NullFormatter;

abstract class Command extends SymfonyCommand
{
    /**
     * @var SsmClient
     */
    protected $ssm;

    /**
     * @var CredentialNameFormatter
     */
    private $formatter;

    public function __construct(SsmClient $ssm, ?CredentialNameFormatter $formatter=null)
    {
        parent::__construct();
        $this->ssm = $ssm;
        $this->formatter = $formatter ?? new NullFormatter();
    }

    protected function format(string $credential) : string
    {
        return $this->formatter->format($credential);
    }
}
