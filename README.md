# Redis cache Cockpit CMS Addon

This addon extends Cockpit CMS core functionality by introducing the possibility to cache API requests using Redis memory storage. The main idea is based on the [SimpleResponseCache](https://github.com/agentejo/SimpleResponseCache) addon, but with some differences in terms of storage (Redis instead of the filesystem) and configuration logic.
Along with the caching of the API requests the addon provides a wrapper for programmatically write and read (set/get) values from the Redis server.

## Installation

1. Confirm that you have Cockpit CMS (Next branch) installed and working.
2. Download zip and extract to 'your-cockpit-docroot/addons' (e.g. cockpitcms/addons/RedisCache)

_Please note that the addon folder name must be RedisCache_

## Configuration

The Addon can be enabled/configured on the config/config.yaml file, e.g.:

```yaml
redis:
  host: <redis-hostname>
  port: <redis-port> # usually 6379
  bypass_token: 123456
  prefix: cockpit
  cache_request_ignore:
    - AWSALB
  cache_paths:
    /api/collections/get/page: 60
    /api/collections/get/post: 60
    /api/custom/*: 120

```

* The **host_** and port shall be the host:port of your Redis server.
* The **bypass_token** shall be a private token you generate to bypass the cache during requests (e.g. for performing tests).
* The **prefix** can be used when using multiple instances of cockpit in same redis server.
* The **cache_paths** are the API paths you want to cache and the ttl value (in seconds).
* The **cache_request_ignore** is optional and can be useful in situations the request is modified by the server with random/dynamic values invalidating the cache. It can be filled with the server specific parameters.
* Wildcards are supported in the paths

### Permissions

There is only one permission (manage) that can be used to access addon details on the Cockpit system page.

![Addon info details](https://monosnap.com/image/oy3KZCHAEoXFdZf9Htv9aISxYBlaF0.png)

## Usage

Addon doesn't provide any visible functionality, after enabling just confirm that your API responses are being handled by Redis. Depending on the number of entries you are fetching, filters and any backend logic (operations during collections.find.before or collections.find.after) you shall see a decrease in the response times.

To bypass Redis on any request, use the query argument ```&_bypass=xxxxxx``` where ```xxxxxx``` is the value configured
in the bypass_token attribute in your main configuration.

When cleaning up the cockpit caches the Redis caches are automatically removed.

The addon can be used by other addons for programmatically set and get values from Redis, the following methods are available:

* Set a value
```
$app->module('rediscache')->set('my_key', $value, $ttl);
```
* Get a value
```
$app->module('rediscache')->get('my_key');
```

## Demo

[![CockpitCMS Redis Cache Addon Screencast](http://img.youtube.com/vi/H0cKFWCAYa8/0.jpg)](http://www.youtube.com/watch?v=H0cKFWCAYa8 "CockpitCMS Redis Cache Addon Screencast")

https://youtu.be/H0cKFWCAYa8

## Copyright and license

Copyright 2018 pauloamgomes under the MIT license.
