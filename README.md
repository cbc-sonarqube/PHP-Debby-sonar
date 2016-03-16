# Debby

Debby checks your project dependencies and tells you when to update.


## Installation

[Use Composer](http://getcomposer.org/). And use require to get the latest stable version:

``` sh
composer require alsvanzelf/debby
```


## Getting started

There are three ways to talk to Debby.

#### Out of the box

Set up a cron to run debby periodically.
You just provide an email address where to send results to.

`0 8 * * Mon export php /var/www/vendor/alsvanzelf/debby/notify.php devops@example.com`

This will send you a report 8 o'clock, every Monday morning.

#### All options

If you want to adjust the default options, provide the path of a options file.

`0 8 * * Mon export php /var/www/vendor/alsvanzelf/debby/notify.php /var/www/debby-options.json`

See [example/options.json](/example/options.json) for all possible options.

#### Custom

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


## Options

Option | Type | Default | Explanation
------ | ---- | ------- | -----------
`notify_all_ok` | `bool` | `true` | Notify also if no packages need an update.
`notify_address` | `string` | `''` | Email address where notification will be sent to. **Required** when using `->notify()`.
`root_dir` | `string` | one directory above `vendor/` | Root directory of the project.
`smtp_login` | `array` | `null` | **Required** when using `->notify()`.


## FAQ

#### Why does Debby send me emails when there is nothing to be updated?

This is an out-of-the-box option. It helps you know Debby actually works when you just installed it and your project is all up-to-date. You can disable these emails with the [`notify_all_ok`](/README.md#Options) option.

#### Why does Debby tell me to update above the composer constraint?

Debby will tell you about an update i.e. `2.0` when you require `^1.5`. If you would run `composer update` yourself, that update won't show up. However, new releases might contain security updates also affecting your older version. For now, Debby defaults to telling you all these updates.
You're welcome to help making Debby smarter in this, i.e. checking for security updates.


## Contributing

Pull Requests or issues are welcome!


## Licence

[MIT](/LICENSE)
