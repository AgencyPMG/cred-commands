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
use PMG\CredCommands\Formatter\AppEnvFormatter;

/**
 * @group network
 * @group integration
 */
class CredentialClientTest extends TestCase
{
    private const LOCALSTACK_ENDPOINT = 'http://localhost:4566';
    private const KEY_ALIAS = 'alias/ignored-by-localstack';

    private $ssm, $client;

    public function testParamtersCanBePutRetrievedAndRemoved()
    {
        $paramName = __FUNCTION__;
        $ver = $this->client->put($paramName, 'testing123');
        $this->assertGreaterThan(0, $ver);

        $cred = $this->client->get($paramName);
        $this->assertSame('testing123', $cred);

        $this->client->remove($paramName);

        $e = null;
        try {
            $this->client->get($paramName);
        } catch (SsmException $e) {
            $this->assertEquals('ParameterNotFound', $e->getAwsErrorCode());
        }
        $this->assertInstanceOf(SsmException::class, $e);
    }

    public function testMultiParametersCanBeRecieved()
    {
        $paramOne = __FUNCTION__;
        $paramTwo = __FUNCTION__.'_again';
        $this->client->put($paramOne, 'testing123');
        $this->client->put($paramTwo, 'testing234');

        $creds = $this->client->getMultiple($paramOne, $paramTwo);

        $this->assertCount(2, $creds);
        $this->assertEquals([
            $paramOne => 'testing123',
            $paramTwo => 'testing234',
        ], $creds);

        $this->client->remove($paramOne, $paramTwo);
    }

    /**
     * @group https://github.com/AgencyPMG/cred-commands/issues/3
     * @group slow
     */
    public function testMoreThanTenParametersCanBeRecievedAndRemoved()
    {
        $names = [];
        $value = bin2hex(random_bytes(4));
        foreach(range(1, 15) as $i) {
            $names[] = $name = __FUNCTION__.$i;
            $this->client->put($name, $value);
        }

        $creds = $this->client->getMultiple(...$names);

        $this->assertCount(15, $creds);
        $this->assertEquals(array_fill_keys($names, $value), $creds);

        $this->client->remove(...$names);
    }

    public function testGettingMultipleParametersWithInvalidParamsCausesError()
    {
        $this->expectException(InvalidParameters::class);
        $this->client->getMultiple(__FUNCTION__);
    }

    protected function setUp() : void
    {
        $this->ssm = SsmClient::factory([
            'version' => 'latest',
            'region' => 'us-east-1',
            'endpoint' => self::LOCALSTACK_ENDPOINT,
            'credentials' => [
                'key' => 'ignoredByLocalstack',
                'secret' => 'ignoredByLocalstack',
            ],
        ]);
        $this->client = new CredentialClient(
            $this->ssm,
            new AppEnvFormatter('credcommands', 'test'),
            self::KEY_ALIAS
        );
    }
}
