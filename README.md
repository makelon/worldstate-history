# Worldstate History

Reader and viewer for data recorded by the Warframe [worldstate server](https://github.com/makelon/worldstate-server).

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
psql -d new-db-name -f res/postgres.sql
```

3. Copy `src/Common/Config.php.dist` to `src/Common/Config.php` and edit the values.

4. Add records to the database.

```sh
php cli/reader.php
```

5. Point your favorite web server to public/index.php.
