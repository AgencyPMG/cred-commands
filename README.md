# pmg/cred-commands

These are a set of symfony console commands that interact with the
[AWS SSM Parameter Store](https://docs.aws.amazon.com/systems-manager/latest/userguide/systems-manager-paramstore.html).

The goal here is to provide an easy way to fetch credentials into memory
(environment variabls) on application boot. See [this blog post](https://chrisguitarguy.com/2017/12/23/secrets-in-dockerized-applications/)
for some details on why one might want to do this.

## Installation

```
composer require pmg/cred-commands
```

## Usage

### Using the Built In Application

```php
#!/usr/bin/env php
<?php

use Aws\Ssm\SsmClient;
use PMG\CredCommands\Application;
use PMG\CredCommands\CredentialClient;

$ssm = SsmClient::factory([
  'version' => 'latest',
  'region' => 'us-east-1',
]);
$client = new CredentialClient($ssm);

$app = new Application($client, 'App Name', 'App Version');

$app->run();
```



### Add Commands to an Existing Console Application

```php
#!/usr/bin/env php
<?php

use Aws\Ssm\SsmClient;
use Symfony\Component\Console\Application;
use PMG\CredCommands\CredentialClient;
use PMG\CredCommands\Command\GetCommand;
use PMG\CredCommands\Command\PutCommand;
use PMG\CredCommands\Command\RemoveCommand;

$app = new Application();

// other command added here or something...

$ssm = SsmClient::factory([
  'version' => 'latest',
  'region' => 'us-east-1',
]);
$client = new CredentialClient($ssm);

$app->add(new GetCommand($client));
$app->add(new PutCommand($client));
$app->add(new RemoveCommand($client));

$app->run();
```

### CLI Usage

```
./bin/console creds:{get,put,remove}
```

## Custom Credential Name Formatting

By default all credential names passed to the CLI are used directly, but that
can be changed with a `CredentialNameFormatter` implementation.

There a few provided by default, all in the `PMG\CredCommands\Formatter`
namespace.

### `NullFormatter`

This is the default, just returns the credential name directly.

```php
use PMG\CredCommands\Formatter\NullFormatter;

$formatter = new NullFormatter();

$formater->format('someCredential'); // 'someCredential'
```

### `TemplateFormatter`

Takes a `$template` in its constructor and replaces a `{cred}` in that template
with the cred name.

```php
use PMG\CredCommands\Formatter\TemplateFormatter;

$formatter = new TemplateFormater('prefix_{cred}');

$formater->format('someCredential'); // 'prefix_someCredential'
```

### `AppEnvFormatter`

Builds a path-like credential name in the format `/{appName}/{environment}/{cred}`.

```php
use PMG\CredCommands\Formatter\AppEnvFormatter;

$formatter = new AppEnvFormater('appName', 'prod');

$formater->format('someCredential'); // '/appName/prod/someCredential'
```

### Why Format at All?

Because it prefixed parameter names can be used to [restrict credential access](https://docs.aws.amazon.com/systems-manager/latest/userguide/sysman-paramstore-access.html)
by configuring IAM permissions that use the actual parameter names.

For instance, an IAM role might only include permissions to access params named
`/appName/prod/*`.

### Using Formatters

Formatters can be passed as the second argument to the `CredentialClient`.

```php
<?php

use Aws\Ssm\SsmClient;
use PMG\CredCommands\Application;
use PMG\CredCommands\CredentialClient;
use PMG\CredCommands\Command\GetCommand;
use PMG\CredCommands\Formatter\AppEnvFormatter;

$ssm = SsmClient::factory([
  // ...
]);
$client = new CredentialClient(
    $ssm,
    new AppEnvFormatter('example', 'dev')
);

// new GetCommand($client);
// new Application($client, 'name', 'version');
// etc.
```

## Using Custom KMS Keys for Parameter Encryption

By default AWS (and by extension this library) uses an AWS account's default KMS
key to encrypt parameters when their types are set to `SecureString` as they
are in this library.

Pass a third argument into the `CredentialClient` to specify a KMS key ID. This
can be the actual key ID (a UUID) or a key alias (in the format `alias/{alias-name}`).

```php
<?php

use Aws\Ssm\SsmClient;
use PMG\CredCommands\CredentialClient;
use PMG\CredCommands\Formatter\AppEnvFormatter;

$ssm = SsmClient::factory([
  // ...
]);

// with a key ID (example, not a real key ID)
$client = new CredentialClient(
    $ssm,
    new AppEnvFormatter('example', 'dev'),
    'df502ce0-49e1-4579-a682-395274de6eb4',
);

// with a key alias (example, not a real key alias)
$client = new CredentialClient(
    $ssm,
    new AppEnvFormatter('example', 'dev'),
    'alias/some-alias-here'
);
```
