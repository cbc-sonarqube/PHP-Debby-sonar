# Debby

Debby checks your project dependencies and tells you when to update.

1. Install her via composer.
2. Setup a cronjob to let her notify you regulary.
3. Sit back and relax. Take a :coffee: or :tea: or :beer:

Debby will tell you when you need to get working.
This lets you stay on top of your dependencies and deploy security releases quickly. :sunglasses:

By the way, Debby will tell when she needs an update herself. You don't need to do anything. :sparkles:


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

`0 8 * * * php /var/www/vendor/alsvanzelf/debby/notify.php example/project personal-access-token`

This will create issues on that repository for packages that need updates.

#### All options

If you want to email the updatable packages, or adjust the default options, provide the path of a options file.

`0 8 * * Mon php /var/www/vendor/alsvanzelf/debby/notify.php /var/www/debby-options.json`

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

$packages = $debby->check();
$debby->notify($packages);
```

See [example/custom.php](/example/custom.php) for a complete example.


## Options

Option | Type | Default | Explanation
------ | ---- | ------- | -----------
`root_dir` | `string` | one directory above `vendor/` | Root directory of the project.
`notify_github` | `array` | `null` | Supply to create issues for each package update. It should contain keys for: <ul><li>`repository`: i.e. `lode/debby`</li><li>`token`: a personal access token, [generate one in your settings](https://github.com/settings/tokens)</li></ul>
`notify_email` | `array` | `null` | Supply to send an email with the updatable packages. It should contain keys for: <ul><li>`recipient`: i.e. `devops@example.com`</li><li>`host`: smtp hostname</li><li>`port`: an int</li><li>`security`: i.e. `ssl`, `tls`</li><li>`user`: username to login to the smtp host, usually the same as the senders email address</li><li>`pass`: plain text password</li></ul>

See [example/options.json](/example/options.json) for a complete example.


## FAQ

#### Why does Debby tell me to update above the composer constraint?

Debby will tell you about an update i.e. `2.0` when you require `^1.5`. If you would run `composer update` yourself, that update won't show up. However, new releases might contain security updates also affecting your older version. For now, Debby defaults to telling you all these updates.
You're welcome to help making Debby smarter in this, i.e. checking for security updates.

### I don't want to run Debby in production

You don't trust her? She's open source you know. Anyway, Debby runs just fine in a testing environment. No hard feelings.
Just take into account that Debby will run just as fine while bisecting on old commits and notify you for updates since then.


## Contributing

Pull Requests or issues are welcome!


## Licence

[MIT](/LICENSE)


:girl:
