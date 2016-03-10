# debby

Debby checks your project dependencies and tells you when to update.


## Installation

[Use Composer](http://getcomposer.org/). And use require to get the latest stable version:

```
composer require alsvanzelf/debby
```


## Getting started

``` php
require_once(__DIR__.'/trunk/vendor/autoload.php');

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
