<?php

/**
 * @file
 * RedisCache admin features.
 */

// Module ACL definitions.
$this("acl")->addResource('rediscache', [
  'manage',
]);

// Hook into global cockpit cache clear.
$this->on('cockpit.clearcache', function ()  {
  $this->app->module('rediscache')->clearCache();
});

// Add Redis entry to settings aside.
$this->on('cockpit.settings.infopage.aside.menu', function() {
  $this->renderView("rediscache:views/partials/redis-info-aside.php");
});

// Add Redis entry to settings main.
$this->on('cockpit.settings.infopage.main.menu', function() {
  $this->renderView("rediscache:views/partials/redis-info-page.php");
});
