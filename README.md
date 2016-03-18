# Debby

Debby checks your project dependencies and tells you when to update.


## Installation

[Use Composer](http://getcomposer.org/). And use require to get the latest stable version:

``` sh
composer require alsvanzelf/debby
```


## Getting started

There are three ways to talk to Debby.

#### Out of the box: GitHub issues

Set up a cron to run debby periodically.
You just provide the repository and your personal access token.

`0 8 * * Mon export php /var/www/vendor/alsvanzelf/debby/notify.php example/project personal-access-token`

This will create issues on that repo for packages that need updates.

#### All options

If you want to email the results, or adjust the default options, provide the path of a options file.

`0 8 * * Mon export php /var/www/vendor/alsvanzelf/debby/notify.php /var/www/debby-options.json`

See [Options](/README.md#Options) for all possible options.

#### Custom

You can also call debby from php and do what every you want.

``` php
require_once(__DIR__.'/vendor/autoload.php');

use alsvanzelf\debby;

$options = [
	'root_dir'      => '/path/to/project/',
	'notify_github' => [
		'repository' => 'example/project',
		// ...
	],
	'notify_email'  => [
		'recipient' => 'devops@example.com',
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
`root_dir` | `string` | one directory above `vendor/` | Root directory of the project.
`notify_github` | `array` | `null` | Supply to create issues for each package update. It should contain keys for `repository` (i.e. `lode/debby`) and a `token` ([personal access token](https://github.com/settings/tokens)).
`notify_email` | `array` | `null` | Supply to send an email with the results. It should contain keys for `recipient`, `host`, `port`, `security`, `user`, `pass`.

See [example/options.json](/example/options.json) for a complete example.


## FAQ

#### Why does Debby tell me to update above the composer constraint?

Debby will tell you about an update i.e. `2.0` when you require `^1.5`. If you would run `composer update` yourself, that update won't show up. However, new releases might contain security updates also affecting your older version. For now, Debby defaults to telling you all these updates.
You're welcome to help making Debby smarter in this, i.e. checking for security updates.


## Contributing

Pull Requests or issues are welcome!


## Licence

[MIT](/LICENSE)
