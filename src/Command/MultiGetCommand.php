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

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use PMG\CredCommands\Exception\InvalidMultiFormat;

/**
 * Get a multiple parameters from the parameter store.
 *
 * @since 1.1
 */
final class MultiGetCommand extends Command
{
    const FORMAT_DEFAULT = 'default';
    const FORMAT_JSON = 'json';
    const FORMAT_SHELL = 'sh';
    const VALID_FORMATS = [
        self::FORMAT_DEFAULT,
        self::FORMAT_JSON,
        self::FORMAT_SHELL,
    ];

    protected static $defaultName = 'creds:multi-get';

    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this->setDescription('Get one or more credentials from the parameter store');
        $this->addArgument(
            'credential',
            InputArgument::REQUIRED | InputArgument::IS_ARRAY,
            'The credentials to retrieve'
        );
        $this->addOption(
            'format',
            'f',
            InputOption::VALUE_REQUIRED,
            'The format of the output: default, json, sh',
            'default'
        );
        $this->setHelp(<<<'END'
The <info>%command.name%</info> command fetches a multiple paramters from the
AWS SSM Parameter store. A common use case for this would be to fetch a
credentials into an environment variables before running the application.

The follow is an example of how that might work in something like a
docker entrypoint.

    #!/bin/sh

    creds="$(%command.full_name -f sh some_cred another_cred again_cred)" || exit $?
    eval "$creds"

    exec "$@"
END
        );
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $in, OutputInterface $out)
    {
        $format = self::ensureValidFormat($in->getOption('format'));
        $toGet = (array) $in->getArgument('credential');
        $creds = $this->client->getMultiple(...$toGet);

        $out->write(self::formatCredentials($format, $creds), true, OutputInterface::OUTPUT_RAW);
    }

    private static function ensureValidFormat(string $format) : string
    {
        $f = strtolower($format);
        if (!in_array($f, self::VALID_FORMATS, true)) {
            throw new InvalidMultiFormat(sprintf(
                '%s is not a valid format, valid formats: %s',
                $format,
                implode(', ', self::VALID_FORMATS)
            ));
        }

        return $f;
    }

    private static function formatCredentials(string $format, array $credentials) : string
    {
        switch ($format) {
            case self::FORMAT_JSON:
                return json_encode($credentials, JSON_PRETTY_PRINT);
            case self::FORMAT_SHELL:
                return implode("\n", self::formatCallback(function (string $key, string $val) {
                    return sprintf('export %s=%s', strtoupper($key), escapeshellarg($val));
                }, $credentials));
            case self::FORMAT_DEFAULT:
            default:
                return implode(PHP_EOL, self::formatCallback(function (string $key, string $val) {
                    return sprintf('%s=%s', $key, $val);
                }, $credentials));
        }
    }

    private static function formatCallback(callable $cb, array $credentials) : array
    {
        return array_map($cb, array_keys($credentials), $credentials);
    }
}
