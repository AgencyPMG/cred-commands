# pmg/cred-commands

These are a set of symfony console commands that interact with the
[AWS SSM Paramter Store](https://docs.aws.amazon.com/systems-manager/latest/userguide/systems-manager-paramstore.html).

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

$ssm = SsmClient::factory([
  'version' => 'latest',
  'region' => 'us-east-1',
]);

$app = new Application($ssm, 'App Name', 'App Version');

$app->run();
```



### Add Commands to an Existing Console Application

```php
#!/usr/bin/env php
<?php

use Aws\Ssm\SsmClient;
use Symfony\Component\Console\Application;
use PMG\CredCommands\Command\GetCommand;
use PMG\CredCommands\Command\PutCommand;
use PMG\CredCommands\Command\RemoveCommand;

$app = new Application();

// other command added here or something...

$ssm = SsmClient::factory([
  'version' => 'latest',
  'region' => 'us-east-1',
]);

$app->add(new GetCommand($ssm));
$app->add(new PutCommand($ssm));
$app->add(new RemoveCommand($ssm));

$app->run();
```

### CLI Usage

```
./bin/console creds:{get,put,rm}
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

Formatters can be passed as the second argument to any of the console commands
or the fourth, final argument to the built in application.

```php
#!/usr/bin/env php
<?php

use Aws\Ssm\SsmClient;
use PMG\CredCommands\Application;
use PMG\CredCommands\Command\GetCommand;
use PMG\CredCommands\Formatter\AppEnvFormatter;

$ssm = SsmClient::factory([
  // ...
]);
$formatter = AppEnvFormatter('example', 'dev');

// second arg
new GetCommand($ssm, $formatter);

// last arg
new Application($ssm, 'name', 'version', $formatter);
```
