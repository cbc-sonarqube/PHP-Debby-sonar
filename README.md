# Debby

Debby checks your project dependencies and tells you when to update.

1. [Set the package manager path](/README.md#point-at-your-managers).
2. [Configure channels](/README.md#pick-your-channels) where to notify you.
3. [Install via composer and setup a cronjob*](/README.md#get-debby-running) to let her notify you regularly.
4. Sit back and relax. Take a :coffee: or :tea: or :beer:

Debby will tell you when you need to get working.
This lets you stay on top of your dependencies and deploy security releases quickly. :sunglasses:

By the way, Debby will tell when she needs an update herself. You don't need to do anything. :sparkles:

\* You can use Debby [without cronjobs](/README.md#without-cronjob-all-options) as well.

---

- [Point at your managers](/README.md#point-at-your-managers)
  - [Composer](/README.md#composer)
- [Pick your channels](/README.md#pick-your-channels)
  - [GitHub](/README.md#github)
  - [Trello](/README.md#trello)
  - [Slack](/README.md#slack)
  - [Email](/README.md#email)
- [Get Debby running](/README.md#get-debby-running)
  - [Without `options.json`: GitHub channel only](/README.md#without-optionsjson-github-channel-only)
  - [Without cronjob: all options](/README.md#without-cronjob-all-options)
- [FAQ](/README.md#faq)
- [Contribute](/README.md#contribute)
- [License](/README.md#license)

## Point at your managers

Debby can check multiple package manager. Although at the moment, she just knows about one. Composer.
If you know want to add others, you're welcome to [contribute](/README.md#contribute).

#### Composer

To check Composer nothing special is needed.

If you don't run Debby from her vendor directory, you'll need to add an `root_dir` option.
This tells Debby where to find the `composer.json`/`lock` files.

``` json
{
	"root_dir": "/path/to/project/"
}
```

## Pick your channels

Debby can communicate via multiple channels. Mostly, you'll only want to setup a single one. But multiple at the same time is not a problem.

#### GitHub

Debby creates an issue at your GitHub repository for each updatable package.

Getting started:

1. [Generate a personal access token](https://github.com/settings/tokens) in your GitHub settings.
2. Add an `notify_github` option:

  ``` json
  {
  	"notify_github": {
  		"repository": "example/project",
  		"token": "personal access token"
  	}
  }
  ```

#### Trello

Debby adds a card in your Trello board for each updatable package.

Getting started:

1. [Authenticate at Trello](https://trello.com/1/authorize?name=Debby&expiration=never&scope=read,write&response_type=token&key=9b174ff1ccf5ca94f1c181bc3d802d4b) and copy the token.
2. Decide which list in your board should get the cards.
3. Open a card currently in that list (or create one temporarly), add `.json` to your browser address bar and go.
4. Look for `"idList":` and copy the id behind it.
5. Add an `notify_trello` option:

  ``` json
  {
  	"notify_trello": {
  		"list": "list id",
  		"token": "personal token"
  	}
  }
  ```

#### Slack

Debby sends a message to a Slack channel for each updatable package.
If multiple packages are found updatable in one run, it adds a single message for all.

Getting started:

1. [Install an incoming webhook](https://slack.com/apps/A0F7XDUAZ-incoming-webhooks) on your team's channel.
2. Copy the "Webhook URL", it looks like `https://hooks.slack.com/services/T00000000/B00000000/XXXXXXXXXXXXXXXXXXXXXXXX`.
3. You don't need to adjust any other setting on the webhook.
4. Add an `notify_slack` option:

  ``` json
  {
  	"notify_slack": {
  		"webhook": "https://hooks.slack.com/services/T00000000/B00000000/XXXXXXXXXXXXXXXXXXXXXXXX"
  	}
  }
  ```

#### Email

Debby sends an email to your inbox with a list of currently updatable packages.

Getting started:

1. Decide on the recipient of the messages.
2. Collect login details for your SMTP server.
3. Add an `notify_email` option:

  ``` json
  {
  	"notify_email": {
  		"recipient": "devops@example.com",
  		"host": "smtp.example.com",
  		"port": 587,
  		"security": "ssl",
  		"user": "devops@example.com",
  		"pass": "password"
  	}
  }
  ```

## Get Debby running

0. Go to your project: `cd /var/www/`
1. Install Debby: `composer require alsvanzelf/debby`
2. Define your options: `nano debby.json`

  ``` json
  {
  	"root_dir": "/path/to/project/",
  	"notify_github": {
  		"repository": "example/project",
  		"token": "user token"
  	},
  	"notify_slack": {
  		"webhook": "https://hooks.slack.com/services/T00000000/B00000000/XXXXXXXXXXXXXXXXXXXXXXXX"
  	}
  }
  ```

3. Run regularly: `crontab -e`

  ``` sh
  0 8 * * * php /var/www/vendor/alsvanzelf/debby/notify.php /var/www/debby.json
  ```

See [example/options.json](/example/options.json) for a complete example.

#### Without `options.json`: GitHub channel only

If you only want to notify via [GitHub issues](/README.md#github), you can setup Debby without `options.json`.
Just provide the repository and your personal access token directly to the crontab:

``` sh
0 8 * * * php /var/www/vendor/alsvanzelf/debby/notify.php example/project personal-access-token
```

#### Without cronjob: all options

You can also call Debby from php and do what every you want.

``` php
$options = [
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


## FAQ

#### Why does Debby tell me to update above the composer constraint?

Debby will tell you about an update i.e. `2.0` when you require `^1.5`. If you would run `composer update` yourself, that update won't show up. However, new releases might contain security updates also affecting your older version. For now, Debby defaults to telling you all these updates.
You're welcome to help making Debby smarter in this, i.e. checking for security updates.

### I don't want to run Debby in production

You don't trust her? She's open source you know. Anyway, Debby runs just fine in a testing environment. No hard feelings.
Just take into account that Debby will run just as fine while bisecting on old commits and notify you for updates since then.


## Contribute

Pull Requests or issues are welcome!


## License

[MIT](/LICENSE)


:girl:
