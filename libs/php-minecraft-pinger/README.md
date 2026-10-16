# PHP Minecraft Pinger

Ping minecraft PC servers from PHP.
Support Minecraft 1.7+ (works with Spigot, Bukkit, ...).

## Installation

This library is published on packagist.org, so you can add it to your composer.json file for an easy installation:
```
composer require nathan818/php-minecraft-pinger
```

## Example
Ping a server:
```php
<?php
include(__DIR__ . '/vendor/autoload.php');

use MinecraftPinger\MinecraftPinger;
use MinecraftPinger\MinecraftPingException;

$pinger = new MinecraftPinger('mc.hypixel.net', 25565); // Port is optional
try
{
    $pingResponse = $pinger->ping();
    echo 'There are ' . $pingResponse->players->online . ' players online on Hypixel!' . "\n";
}
catch (MinecraftPingException $e)
{
    // An error has occurred
    echo $e->getMessage();
}
```
