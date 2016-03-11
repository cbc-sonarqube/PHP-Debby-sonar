# debby

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

`0 8 * * Mon export php /var/www/vendor/alsvanzelf/debby/src/cron.php "devops@example.com"`

This will send you a report 8 o'clock, every Monday morning.

### All options

If you want to adjust the default options, provide the path of a options file.

`0 8 * * Mon export php /var/www/vendor/alsvanzelf/debby/src/cron.php /var/www/debby-options.json`

### Custom

You can also call debby from php and do what every you want.

``` php
require_once(__DIR__.'/vendor/autoload.php');

use alsvanzelf\debby;

$options = [
	'notify_address' => 'devops@example.com',
];
$debby = new debby\debby($options);

$results = $debby->check();
$debby->notify($results);
```


## To Do

...


## Contributing

Pull Requests or issues are welcome!


## Licence

[MIT](/LICENSE)
