phpCore - PHP Framwork
----------------------

phpCore is an MVC web framework that makes heavy use of a very fast and efficient row level database caching engine.
This allows for websites with lots of varying dynamic content to generate uncached pages very quickly with minimal load
on the database. In fact, once the cache has warmed up, entire pages can be loaded without any connection being made to
the database, since database connections are lazy-loaded.

The framework takes lazy loads just about everything. Connections to the database, model data, collections, everything.

Installation:
-------------

```
chown -R www-data:www-data .
chmod -R 777 ./application/cache
chmod -R 777 ./logs
```

The `www` directory should be your website docroot.

The Basics
----------

Accessing HTTP variables and data:

```
echo core::http()->server('ip'); // returns 10.0.1.1
```

Accessing authentication:

```
echo core::auth()->user_id; // returns 837
```

Accessing a database directly:

```
echo core::sql(); // returns a PDO object (master/default connection)
echo core::sql('slave.b'); // returns a PDO object of a named slave connection
```

Loading a user model

```
$user = new user_model(28); // returns user model 28
```

[More info can be found in the wiki.](https://github.com/neoform/phpCore/wiki/Index)

