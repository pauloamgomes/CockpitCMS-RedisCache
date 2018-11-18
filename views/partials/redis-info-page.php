  <div id="redis-info">
    <p><strong><span class="uk-badge app-badge">Redis</span></strong></p>
    <table class="uk-table uk-table-striped">
      <tbody>
        <tr>
          <td width="30%">@lang('Host:')</td>
          <td>{settings.host}</td>
        </tr>
        <tr>
          <td width="30%">@lang('Port:')</td>
          <td>{settings.port}</td>
        </tr>
        <tr>
          <td width="30%">@lang('Bypass token:')</td>
          <td>{settings.bypass_token}</td>
        </tr>
        <tr>
          <td width="30%">@lang('Saved keys:')</td>
          <td>
            <div>{countKeys} @lang('saved key(s)')</div>
            <div if="{countKeys}">
              <pre class="uk-text-small" show="{ showKeys }">{keys}</pre>
              <a class="uk-button uk-button-small" onclick="{ () => toggleKeys(true) }" show="{ !showKeys }">
                <i class="uk-icon-cog"></i> @lang('Show saved keys')
              </a>
              <a class="uk-button uk-button-small" onclick="{ () => toggleKeys(false) }" show="{ showKeys }">
                <i class="uk-icon-cog"></i> @lang('Hide')
              </a>
            </div>
          </td>
        </tr>
        <tr>
          <td width="30%">@lang('Cache paths:')</td>
          <td class="uk-text-small">
            <table class="uk-table">
              <tr each="{cachePath, idx in settings.cache_paths}">
                <td>{cachePath.path}</td>
                <td>{cachePath.ttl} @lang('seconds')</td>
              </tr>
            </table>
            <div class="uk-panel uk-panel-box" if="{ !cleaningRedis && settings.cache_paths && countKeys }">
              <a title="@lang('Clear cache')" data-uk-tooltip="pos:'right'" onclick="{cleanRedisCaches}"><i class="uk-icon-trash-o"></i> @lang('Clear caches')</a>
            </div>
            <div class="uk-alert" if="{ cleaningRedis }">
                <i class="uk-icon-spinner uk-icon-spin"></i> @lang('Clearing Redis cache...')
            </div>
          </td>
        </tr>
      </tbody>
    </table>
    <p>
      <strong><span class="uk-badge app-badge">Redis server info</span></strong>
    </p>
    <table class="uk-table uk-table-striped">
      <tbody>
        <tr  each="{stat, idx in info}">
          <td width="30%">{stat.key}</td>
          <td>{stat.value}</td>
        </tr>
      </tbody>
    </table>

  </div>

  <script>
    var $this = this;

    $this.settings = {};
    $this.info = [];
    $this.countKeys = 0;
    $this.cleaningRedis = false;
    $this.showKeys = false;

    window.setTimeout(function() {
      document.getElementById('settings-info').append(document.getElementById('redis-info'));
    }, 50);

    this.on('mount', function() {
      this.loading = true;

      App.callmodule('rediscache:getInfo').then(function(data) {
        if (data.result) {
          $this.settings = data.result.settings;
          $this.info = data.result.info;
          $this.countKeys = data.result.count;
          $this.keys = data.result.keys.join("\n");
        }
        $this.loading = false;
        $this.update();
      });

      this.cleanRedisCaches = function() {
        $this.cleaningRedis = true;
        App.callmodule('rediscache:clearCache').then(function() {
          setTimeout(function(){
            App.ui.notify("Redis Caches cleared successful", "success");
            $this.cleaningRedis = false;
            $this.countKeys = 0;
            $this.update();
          }, 1000);
        });
      }

      this.toggleKeys = function(status) {
        $this.showKeys = status;
      }

    });

  </script>

