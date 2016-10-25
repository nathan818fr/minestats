# MINESTATS

MineStats is a realtime Minecraft PC/PE servers list.
You can see the current status of servers and filter them according to your criteria.
You can also view server statistics over several days (or months) and compare servers to each other.

## Try it!

A demo of the latest release is available on [https://minestats.info/](https://minestats.info/).

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


## Use

### Install

- Clone this repository or download a release (https://github.com/nathan818fr/minestats/releases).

- Install composer dependencies
  ```
  composer install
  ```
  
- Allow write on `storage/` and `bootstrap/cache/`
  ```
  chmod -R 770 storage bootstrap/cache
  ```
  
- Copy `.env.example` to `.env` then update the configuration
  (you can generate an APP_KEY with `php artisan key:generate`)

- Setup and populate the database
  ```
  php artisan migrate
  php artisan db:seed
  ```
  
- Setup the tasks scheduler (used for ping and stats garbage collection)

  You must run `php artisan schedule:run` every minutes, by example with crontab add:
  ```
  * * * * * php /path/to/minstats/artisan schedule:run
  ```
  
- You can access the site. The default credentials are admin / password!

### Configure

You can change configuration in `.env` file.

## Why PHP ?

I know that PHP is not the ideal language for this kind of site in real time.
But I like PHP, I like Laravel and I especially wanted to make a site that can run on a shared hosting.

Currently pings are performed by a PHP script and the client performs regular ajax calls to retrieve the data.

Subsequently, I will probably add alternative options, like:
- A node.js server to perform pings
- The use of websockets
