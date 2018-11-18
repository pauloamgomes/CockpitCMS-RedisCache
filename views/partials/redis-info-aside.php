<?php

/**
 * @file
 * RedisCache info settings page aside view.
 */
?>

<script>
  window.setTimeout(function() {
    sidebar = document.querySelector('.uk-width-medium-1-4 .uk-nav-side');
    li = document.querySelector('li.redis-info');
    if (!li) {
      li = document.createElement('li');
      li.className = 'redis-info';
      li.innerHTML ='<a href="#REDIS">Redis</a>';
      sidebar.append(li);
    }
  }, 50);
</script>
