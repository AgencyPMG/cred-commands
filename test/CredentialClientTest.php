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
    const PARAM = 'testparam';

    private $ssm, $client;

    public function testParamtersCanBePutRetrievedAndRemoved()
    {
        $ver = $this->client->put(self::PARAM, 'testing123');
        $this->assertGreaterThan(0, $ver);

        $cred = $this->client->get(self::PARAM);
        $this->assertSame('testing123', $cred);

        $this->client->remove(self::PARAM);

        $e = null;
        try {
            $this->client->get(self::PARAM);
        } catch (SsmException $e) {
            $this->assertEquals('ParameterNotFound', $e->getAwsErrorCode());
        }
        $this->assertInstanceOf(SsmException::class, $e);
    }

    public function testMultiParametersCanBeRecieved()
    {
        $this->client->put(self::PARAM, 'testing123');
        $this->client->put(self::PARAM.'_again', 'testing234');

        $creds = $this->client->getMultiple(self::PARAM, self::PARAM.'_again');

        $this->assertCount(2, $creds);
        $this->assertEquals([
            self::PARAM => 'testing123',
            self::PARAM.'_again' => 'testing234',
        ], $creds);

        $this->client->remove(self::PARAM, self::PARAM.'_again');
    }

    /**
     * @group https://github.com/AgencyPMG/cred-commands/issues/3
     */
    public function testMoreThanTenParametersCanBeRecievedAndRemoved()
    {
        $names = [];
        $value = bin2hex(random_bytes(4));
        foreach(range(1, 15) as $i) {
            $names[] = $name = self::PARAM.'_multi_'.$i;
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
        $this->client->getMultiple(self::PARAM.'_does_not_exist');
    }

    protected function setUp() : void
    {
        $key = getenv('CREDCOMMANDS_KEY_ID');
        if (false === $key) {
            $this->markTestSkipped('No `CREDCOMMANDS_KEY_ID` in the environment');
            return;
        }

        // assume we have creds available
        $this->ssm = SsmClient::factory([
            'version' => 'latest',
            'region' => getenv('AWS_DEFAULT_REGION') ?: 'us-east-1',
        ]);
        $this->client = new CredentialClient(
            $this->ssm,
            new AppEnvFormatter('credcommands', 'test'),
            $key
        );
    }
}
