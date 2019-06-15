<?php

/**
 * @file
 * Addon bootstrap.
 */

// Check if Redis is configured.
if (!isset($app->config['redis']) || empty($app->config['redis']['host'])) {
  return;
}

// Main module functions.
$this->module('rediscache')->extend([

  'connect' => function() {
    $settings = $this->app->config['redis'];

    if (!$settings || !isset($settings['host'], $settings['port'])) {
      return;
    }

    $redis = new Redis();
    if ($redis->connect($settings['host'], $settings['port'])) {
      $redis->setOption(Redis::OPT_PREFIX, $settings['prefix']);
      return $redis;
    }
  },

  'set' => function($key, $value, $ttl = 30) {
    if ($redis = $this->connect()) {
      $redis->set(trim($key), json_encode($value), ['px' => ($ttl * 1000)]);
    }
  },

  'get' => function($key) {
    if ($redis = $this->connect()) {
      if ($data = $redis->get($key)) {
        return json_decode($data);
      }
    }
  },

  'bypass' => function() {
    $settings = $this->app->config['redis'];

    if (empty($_REQUEST['_bypass'])) {
      return FALSE;
    }

    if (empty($settings['bypass_token'])) {
      return FALSE;
    }

    return ($_REQUEST['_bypass'] == $settings['bypass_token']);
  },

  'clearCache' => function() {
    if ($redis = $this->connect()) {
      $redis->flushDb();
    }
  },

]);

// Include admin.
if (COCKPIT_ADMIN && !COCKPIT_API_REQUEST) {
  include_once __DIR__ . '/admin.php';

  $this->module('rediscache')->extend([
    'getInfo' => function() {
      $config = $this->app->config['redis'];
      $info = [];
      $keys = [];
      $count = 0;

      $settings = $config;
      $settings['cache_paths'] = [];
      foreach ($config['cache_paths'] as $path => $ttl) {
        $settings['cache_paths'][] = ['path' => $path, 'ttl' => $ttl];
      }
      if ($redis = $this->connect()) {
        $keys = $redis->keys('*');
        $count = $redis->dbSize();
        foreach ($redis->info() as $key => $value) {
          $info[] = ['key' => $key, 'value' => $value];
        }
      }
      return compact('settings', 'info', 'count', 'keys');
    },
  ]);
}

// Is REST?
if (COCKPIT_API_REQUEST) {
  include_once __DIR__ . '/rest.php';;
}

