# MINESTATS

MineStats is a realtime Minecraft PC/PE servers list.
You can see the current status of servers and filter them according to your criteria.
You can also view server statistics over several days (or months) and compare servers to each other.

## Try it!

A demo of the latest release is available on [TODO].

If you like, you can install the same system to analyze your own network (Bungee, Spigot, ...).

## Features

- Servers list
  - Realtime status
  - Display favicon and supported versions
  - Filters (by languages, versions, ...)
  - Fully responsive
- Admin UI
  - Manage users (with simple role system)
  - Manage servers


## Installation

TODO

## Why PHP ?

I know that PHP is not the ideal language for this kind of site in real time.
But I like PHP, I like Laravel and I especially wanted to make a site that can run on a shared hosting.

Currently pings are performed by a PHP script and the client performs regular ajax calls to retrieve the data.

Subsequently, I will probably add alternative options, like:
- A node.js server to perform pings
- The use of websockets
