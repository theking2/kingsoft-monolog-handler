# Rotating and Crontab StreamingFileHandler for Monolog

This handler combines rotating (`AbstractRotatingFileHandler`) with an crontab based FileHandler (`CronRotatingFileHandler`) to offer a cron based rotating file handler. It is based on `Cesargb\php-log-rotation` for advanced rotation and `dragonmantank\cron-expression`  for cron interpretation. The abstract class can be used for different ways of rotation and requires an implementation of `mustRotate()` to estblish to know if a rotation is needed. 

Future versions might split the two and who knows `AbstractRotatingFileHandler` might end up in `Monolog`...

# Install and configure

Use `Kingoft/Utils` to include make a global `SETTINGS` available. Or look at the `PHPUNIT` test file to see what is needed to configure.

```php
require $_SERVER['DOCUMENT_ROOT'] . '/vendor/kingsoft/utils/settings.inc.php';
```

## CronRotatingFileHandler
Example usage
```php
$log = new Monolog\Logger( SETTINGS['log']['name'] );

$log->pushHandler(
	new \Kingsoft\MonologHandler\CronRotatingFileHandler(
		SETTINGS['log']['location'] . '/' . SETTINGS['log']['name'] . '_info.log',
		Monolog\Level::fromName( SETTINGS['log']['level'],
		SETTINGS['logrotate'] )
	)
);
```
With this in the ini-file
```ini
[log]
name = "app"
location = "D:/Projekte/logs"
level = Info

[logrotate]
cronExpression = '* */1 * * *'
maxFiles = 2
minSize = 120
compress = false
```

