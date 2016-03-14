# Debby

Debby checks your project dependencies and tells you when to update.


## Installation

[Use Composer](http://getcomposer.org/). And use require to get the latest stable version:

``` sh
composer require alsvanzelf/debby
```


## Getting started

There are three ways to talk to Debby.

### Out of the box

Set up a cron to run debby periodically.
You just provide an email address where to send results to.

`0 8 * * Mon export php /var/www/vendor/alsvanzelf/debby/notify.php devops@example.com`

This will send you a report 8 o'clock, every Monday morning.

### All options

If you want to adjust the default options, provide the path of a options file.

`0 8 * * Mon export php /var/www/vendor/alsvanzelf/debby/notify.php /var/www/debby-options.json`

See [example/options.json](/example/options.json) for all possible options.

### Custom

You can also call debby from php and do what every you want.

``` php
require_once(__DIR__.'/vendor/autoload.php');

use alsvanzelf\debby;

$options = [
	'notify_address' => 'devops@example.com',
	'root_dir'       => '/path/to/project/',
	'smtp_login'     => [
		// ...
	],
];
$debby = new debby\debby($options);

$results = $debby->check();
$debby->notify($results);
```

See [example/custom.php](/example/custom.php) for a complete example.


## Contributing

Pull Requests or issues are welcome!


## Licence

[MIT](/LICENSE)
