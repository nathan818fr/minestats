<?php
include(__DIR__ . '/../vendor/autoload.php');

use MinecraftPinger\MinecraftPinger;
use MinecraftPinger\MinecraftPingException;

$pinger = new MinecraftPinger('mc.hypixel.net'/* , 25565 - port is optional */);
try {
    $pingResponse = $pinger->ping();

    // var_dump($pingResponse); // Display all ping data

    echo 'There are ' . $pingResponse->players->online . ' players online on Hypixel!' . "\n";
} catch (MinecraftPingException $e) {
    echo 'Unable to ping Hypixel! Is it down?' . "\n";
    throw $e;
}
