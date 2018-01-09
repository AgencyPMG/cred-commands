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
 * A name formatter implementation that uses a template and replace `{cred}` with
 * the credential name.
 *
 * @since 1.0
 */
final class TemplateFormatter implements CredentialNameFormatter
{
    /**
     * @var string
     */
    private $template;

    public function __construct(string $template)
    {
        $this->template = $template;
    }

    /**
     * {@inheritdoc}
     */
    public function format(string $credential) : string
    {
        return strtr($this->template, [
            '{cred}' => $credential,
        ]);
    }
}
