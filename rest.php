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

  $hash = trim(COCKPIT_ADMIN_ROUTE . '/' . md5(serialize($_REQUEST)), '/');

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

  if (!in_array(COCKPIT_ADMIN_ROUTE, array_keys($settings['cache_paths']))) {
    return;
  }

  $ttl = ($settings['cache_paths'][COCKPIT_ADMIN_ROUTE] ?? 60);
  $hash = trim(COCKPIT_ADMIN_ROUTE . '/' . md5(serialize($_REQUEST)), '/');

  $app->module('rediscache')->set($hash, $this->response->body, $ttl);

}, -2000);
