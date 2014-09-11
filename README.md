Vtiger API Wrapper
========================================

This is a simple PHP wrapper for the Vtiger API. It is currently incomplete.

You can find the Vtiger website [here](http://www.vtiger.com)


1. Features
----------------------------------------

The features we support are incomplete. It should be easy to add new ones, and if you do, please send a pull request!

- We're using Buzz as http client, but due to our OOP architecture, you can easily change that.
- If your session times out, a new one is created automatically (no worries anymore!)


2. Setup
----------------------------------------

### 2.1 Composer

Please use composer to autoload the Vtiger api wrapper! Other methods are not encouraged by me.

*composer.json*

```json
{
	"repositories": [
		{
			"type": "git",
			"url": "https://github.com/sumocoders/vtiger-api.git"
		}
	],
	"require": {
		"sumocoders/vtiger-api": "master"
	}
}
```


### 2.2 Authentication


```php
<?php

require_once __DIR__ . '/vendor/autoload.php';

// Create an adapter, the Buzz adapter is the default but you can create one yourself if you'd like
$adapter = new SumoCoders\Vtiger\Adapter\BuzzAdapter();

// Create a Vtiger instance, using the adapter, and your vtiger data
$vtiger = new SumoCoders\Vtiger\Vtiger(
    $adapter,
    'http://your-vtiger-host-name.com',
    '<your-user-here>',
    '<your-secret-here>'
);
```



3. Usage
----------------------------------------

```php
<?php

require_once __DIR__ . '/vendor/autoload.php';

// Create an adapter, the Buzz adapter is the default but you can create one yourself if you'd like
$adapter = new SumoCoders\Vtiger\Adapter\BuzzAdapter();

// Create a Vtiger instance, using the adapter, and your vtiger data
$vtiger = new SumoCoders\Vtiger\Vtiger(
    $adapter,
    'http://your-vtiger-host-name.com',
    '<your-user-here>',
    '<your-secret-here>'
);

// Create a user on the Vtiger CRM
$response = $vtiger->create(
    'Contacts',
    array(
        'firstname' => 'Sumo',
        'lastname' => 'Coders',
        'email' => 'sumocoders@example.com',
    )
);

var_dump($response);
```

