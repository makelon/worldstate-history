# Worldstate History

A collection of PHP applications that processes data recorded by the Warframe [worldstate server](https://github.com/makelon/worldstate-server):

* `reader` - Reader that stores the processed data in a PostgreSQL database.
* `client` - Queryable client that returns the processed data in the JSON format.
* `items` - Client that returns a list of all known Warframe items.

### Installation and use

**Requirements**: PHP >= 5.5 with Composer, PostgreSQL

1. Install dependencies.

```sh
composer install
```

2. Create PostgreSQL user and database.

```sql
CREATE USER new-db-user PASSWORD 'pick-a-password';
CREATE DATABASE new-db-name;
\c new-db-name
ALTER DEFAULT PRIVILEGES GRANT SELECT, INSERT, UPDATE ON TABLES TO new-db-user;
ALTER DEFAULT PRIVILEGES GRANT SELECT, UPDATE ON SEQUENCES TO new-db-user;
```

```sh
psql -d new-db-name -f reader/postgres.sql
```

3. Copy `common/Config.dist.php` to `common/Config.php` and edit the values.

4. Generate autoload data.

```sh
./vendor/bin/php-generate-autoload client/autoload.php client common
./vendor/bin/php-generate-autoload items/autoload.php items common
./vendor/bin/php-generate-autoload reader/autoload.php reader common
```

5. Add records to the database.

```sh
php reader/index.php
```

6. Point your favorite web server to client/index.php and/or items/index.php.
