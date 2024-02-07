# Install and configure

Use `Kingoft/Utils` to include make a global `SETTINGS` available

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
