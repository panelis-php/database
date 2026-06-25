# Panelis Database

Manage database backups directly from the Panelis admin panel.

## Features

* Create database backups
* Download backup files
* Delete backup files
* Local storage support
* Automatic Panelis plugin discovery

## Requirements

* PHP 8.3+
* Laravel 13+
* Filament 5+

## Installation

Install the package via Composer:

```bash
composer require panelis-php/database
```

## Usage

After installation, a **Database** menu will be available in the Panelis admin panel.

The Database module allows administrators to create and manage database backups without accessing the server or running command-line tools.

Available actions include:

* Create a backup
* Download a backup
* Delete a backup

## Storage

Backup files can be stored using one of the supported storage drivers:

* Local
* Storage

The available storage locations can be configured through the Panelis settings panel.

## License

The MIT License (MIT).
