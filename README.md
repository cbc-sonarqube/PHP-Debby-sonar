# Debby - stay on top of your dependencies

Debby checks your project dependencies and tells you when to update.

- always run the latest versions :shipit:
- choose whether you find an update interesting
- be able to fix security issues promptly

In the end, you'll be more comfortable upgrading because updates can be often and small.


## Super fast setup :rocket:

``` sh
composer require alsvanzelf/debby
crontab -l | { cat; echo "0 8 * * * php /www/vendor/alsvanzelf/debby/notify.php repo token"; } | crontab -
```

Replace `repo` with the path of your repository on GitHub (`organization/project`) and `token` with a [personal access token](https://github.com/settings/tokens).
This runs Debby every day at 8 o'clock and create issues whenever updates are found.


## Setup in a normal pace, with a bit more explanation

1. [Install Debby](/README.md#1-installation) via Composer
2. [Configure notifications](/README.md#2-configure-notifications) to GitHub, Trello, Slack, email
3. [Setup a cronjob](/README.md#3-fire-up) (there's other ways as well, see below)

Also check out [the questions Debby gets asked frequently](/README.md#faq).
Or checkout :blue_book: [the documentation](https://github.com/lode/debby/wiki).


#### 1. Installation

[Use Composer](http://getcomposer.org/). And use require to get the latest stable version:

```
composer require alsvanzelf/debby
```


#### 2. Configure notifications

Debby can talk to
![GitHub issues](/channels/github.png) GitHub issues,
![Trello](/channels/trello.png) Trello,
![Slack](/channels/slack.png) Slack,
and ![Email](/channels/email.png) email.

Pick the channel(s) that you want notifications on,
make a `debby.json` configuration file with the access details for these channels,
and place it in the root of your project.

I.e. for notifying to GitHub issues, use:

``` json
{
	"notify_github": {
		"repository": "example/project",
		"token": "personal access token"
	}
}
```

See the :blue_book: [wiki](https://github.com/lode/debby/wiki/Pick-your-channels) on the specific configuration.


#### 3. Fire up

Setup a cronjob to run the built-in notify script passing it your configuration file.

Run `crontab -e` and add:

``` sh
0 8 * * * php /var/www/vendor/alsvanzelf/debby/notify.php /var/www/debby.json
```

:thumbsup:

Sit back and relax. Take a :coffee: or :tea: or :beer:


## FAQ

#### Can I run it without a cronjob?

You can call Debby from php and do what every you want.

``` php
$options = [
	'notify_github' => [
		'repository' => 'example/project',
		'token'      => 'personal access token',
	],
];
$debby = new debby\debby($options);

$packages = $debby->check();
$debby->notify($packages);
```

See [example/custom.php](/example/custom.php) for a complete example.


#### I don't want to run Debby in production

You don't trust her? She's open source you know. Anyway, Debby runs just fine in a testing environment. No hard feelings. :heart:
Just take into account that Debby will run just as fine while bisecting on old commits and notify you for updates since then.
Also be-aware that Debby caches earlier notified packages, which might cause trouble when switching branches backwards.


#### Debby can not determine manage paths?
_or_
#### Can I have composer.json outside the project root?
_or_
#### I don't want to check [Composer|npm|...]

By default, Debby checks all package managers it can find. In some situations this doesn't work. I.e.:

- Debby is not installed via Composer herself.
- The package manager json files are at custom locations.
- You have a package manager which you *don't* want to have checked.
- You use files which look like they are from package managers (i.e. a package.json when you don't use npm).

Then you'll need to adjust configuration to specify which managers you want to check, and where to find them.

I.e. for notifying Composer, add the following to your `debby.json`:

``` json
{
	"check_composer": {
		"path": "/path/to/composerjson/"
	}
}
```

See the :blue_book: [wiki](https://github.com/lode/debby/wiki/Point-at-your-managers) on the specific configuration.



#### Why does Debby tell me to update above the composer constraint?

Debby will tell you about an update i.e. `2.0` when you require `^1.5`. If you would run `composer update` yourself, that update won't show up.
However, new releases might contain security updates also affecting your older version. For now, Debby defaults to telling you all these updates.
You're welcome to help making Debby smarter in this, i.e. checking for security updates.


## Contribute

Pull Requests or issues are welcome!


## License

[MIT](/LICENSE)


# :girl:

By the way, Debby will tell when she needs an update herself. You don't need to do anything. :sparkles:
