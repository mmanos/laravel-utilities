# Custom Utilities for Laravel 4

Custom Laravel application utilities and installer to make initialization, configuration, and management of new Laravel 4 applications easier and less time consuming.

## Installation Via Composer

Download this installer using Composer.

```console
$ composer global require "mmanos/laravel-utilities=~1.0"
```

Make sure to place the `~/.composer/vendor/bin` directory in your PATH so the `mmanos-laravel` executable is found when you run the `mmanos-laravel` command in your terminal.

```console
$ export PATH=~/.composer/vendor/bin:$PATH
```

Once installed, the simple `mmanos-laravel` [global composer command](https://getcomposer.org/doc/03-cli.md#global) will be available to run the project commands listed below.

## Updates Via Composer

To keep this package up to date:

```console
$ composer global update mmanos/laravel-utilities
```

## Uninstalling

To remove this package, you edit `~/.composer/composer.json` and then run:

```console
$ composer global update
```

## Commands

Run the commands below to initialize and/or modify Laravel 4 applications.

### New

Create a fresh Laravel installation with dependencies in the directory you specify.
This command also make's the app/storage/* directories writable.

```console
$ mmanos-laravel new [directory]
```

For instance, `mmanos-laravel new blog` would create a directory named `blog` containing a fresh Laravel installation.

> **Note:** This runs the composer create-project command to check out the latest version of Laravel and install it's dependencies.

### Prepare

Prepare a fresh version of Laravel. This command will do the following:

* Create app/classes directory and autoload it
* Install helper functions file
* Configure event callbacks
* Configure the HTTP Exception handler
* Enable sending of custom headers in response
* Set app timezone to America/Chicago
* Configure a common company name for use throughout the app
* Create assets directory structure
* Install package [laravel-casset](https://github.com/mmanos/laravel-casset)
* Install package [bootstrap](https://github.com/twbs/bootstrap)
* Install package [jquery](https://github.com/jquery/jquery)
* Enable IE8 responsive css support (via html5shiv.js and respond.js)
* Create a default layout
* Configure default controllers and views

```console
$ mmanos-laravel prepare
```

> **Note:** Run from the base directory of the Laravel application.
