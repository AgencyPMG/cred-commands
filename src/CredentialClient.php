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

use Aws\Ssm\SsmClient;
use Aws\Ssm\Exception\SsmException;
use PMG\CredCommands\Exception\InvalidParameters;
use PMG\CredCommands\Formatter\NullFormatter;

/**
 * Wraps up the AWS SsmClient with some additional configuration for dealing with
 * parameters.
 *
 * @since 1.0
 */
class CredentialClient
{
    const MAX_NAMES = 10;

    /**
     * @var SsmClient
     */
    private $ssm;

    /**
     * @var CredentialNameFormatter
     */
    private $formatter;

    /**
     * @var string|null;
     */
    private $kmsKeyId;

    /**
     * Constructor.
     *
     * @param $ssm The SsmClient to use
     * @param $kmsKeyId If specified this key ID will be used to put paramters
     *        into the parameter store. Otherwise the default KMS key will get
     *        used.
     */
    public function __construct(SsmClient $ssm, ?CredentialNameFormatter $formatter=null, ?string $kmsKeyId=null)
    {
        $this->ssm = $ssm;
        $this->formatter = $formatter ?? new NullFormatter();
        $this->kmsKeyId = $kmsKeyId;
    }

    /**
     * Fetch a credential
     *
     * @param $credential the credential name
     * @return string|null a string if the credential is found `null` otherwise
     */
    public function get(string $credential) : string
    {
        $result = $this->ssm->getParameter([
            'Name' => $this->format($credential),
            'WithDecryption' => true,
        ]);

        return $result['Parameter']['Value'];
    }

    /**
     * Fetch multiple credentials.
     *
     * @param $credential At least one credential is required.
     * @param $credentials additional credentials to look up.
     * @throws InvalidParameters if any invalid parameters come back
     * @return An array of strings with the credentil names as key.
     */
    public function getMultiple(string $credential, string ...$credentials) : array
    {
        array_unshift($credentials, $credential);
        $keys = [];
        foreach ($credentials as $c) {
            $keys[$this->format($c)] = $c;
        }

        $params = [];
        foreach (array_chunk(array_keys($keys), self::MAX_NAMES) as $names) {
            $result = $this->ssm->getParameters([
                'Names' => $names,
                'WithDecryption' => true,
            ]);

            if (!empty($result['InvalidParameters'])) {
                throw new InvalidParameters(sprintf(
                    'Invalid Parameters: %s',
                    implode(', ', $result['InvalidParameters'])
                ));
            }

            $params[] = $result['Parameters'] ?: [];
        }

        $out = [];
        foreach (array_merge(...$params) as $param) {
            $origName = $keys[$param['Name']];
            $out[$origName] = $param['Value'];
        }

        return $out;
    }

    /**
     * Put the credential into the paramter.
     *
     * @param $credential the credential name
     * @param $value the value to put in the store
     * @return The credential version number
     */
    public function put(string $credential, string $value) : int
    {
        $req = [
            'Name' => $this->format($credential),
            'Value' => $value,
            'Overwrite' => true,
            'Type' => 'SecureString',
        ];
        if ($this->kmsKeyId) {
            $req['KeyId'] = $this->kmsKeyId;
        }

        $result = $this->ssm->putParameter($req);

        return intval($result['Version']);
    }

    /**
     * Remove a credential from the parameter store.
     *
     * @param $credential the credential name
     * @param $credentials additional credentials to remove
     */
    public function remove(string $credential, string ...$credentials) : void
    {
        array_unshift($credentials, $credential);
        foreach (array_chunk(array_map([$this, 'format'], $credentials), self::MAX_NAMES) as $names) {
            $this->ssm->deleteParameters([
                'Names' => $names,
            ]);
        }
    }

    private function format(string $credential) : string
    {
        return $this->formatter->format($credential);
    }
}
