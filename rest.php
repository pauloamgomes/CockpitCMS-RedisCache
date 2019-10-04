<?php

/**
 * @file
 * Implements RedisCache REST features.
 */

$this->on('before', function () use ($app) {
  // Check if we have bypass token
  if ($app->module('rediscache')->bypass()) {
    return;
  }

  $settings = $this->config['redis'];
  $ignore_params = $settings['cache_request_ignore'] ?? [];
  $request = array_diff_key($_REQUEST, array_flip($ignore_params));

  $hash = trim(COCKPIT_ADMIN_ROUTE . '/' . md5(serialize($request)), '/');

  if ($data = $app->module('rediscache')->get($hash)) {
    $this->response->body = $data;
    $this->response->flush();
    // Stop the response now and therefore all "after" events.
    $this->stop();
  }

}, 2000);

$this->on('after', function() use ($app) {

  // Do not cache other reponses than 200.
  if ($this->response->status != 200) {
    return;
  }

  // Check if we have bypass token
  if ($app->module('rediscache')->bypass()) {
    return;
  }

  // Check from config what we should to cache.
  $settings = $this->config['redis'];

  $ttl = 0;
  if (in_array(COCKPIT_ADMIN_ROUTE, array_keys($settings['cache_paths']))) {
    $ttl = $settings['cache_paths'][COCKPIT_ADMIN_ROUTE] ?? 60;
  }
  else {
    foreach ($settings['cache_paths'] as $path => $_ttl) {
      if (preg_match("#^" . strtr(preg_quote($path, '#'), ['\*' => '.*', '\?' => '.']) . "$#i", COCKPIT_ADMIN_ROUTE)) {
        $ttl = $_ttl;
        break;
      }
    }
    if (!$ttl) {
      return;
    }
  }

  $ignore_params = $settings['cache_request_ignore'] ?? [];
  $request = array_diff_key($_REQUEST, array_flip($ignore_params));
  $hash = trim(COCKPIT_ADMIN_ROUTE . '/' . md5(serialize($request)), '/');

  $app->module('rediscache')->set($hash, $this->response->body, $ttl);

}, -2000);
