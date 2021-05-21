{def $login_layout = ezini('LoginTemplate', 'Layout', 'app.ini')}
{def $login_modules = ezini('LoginTemplate', 'LoginModules', 'app.ini')}
{def $login_modules_count = count($login_modules)}

{if $login_layout|eq('column')}

  {foreach $login_modules as $login_module}
    <div class="row">
      <div class="col-sm-4 col-sm-offset-4">
        {def $login_module_parts = $login_module|explode('|')}
        {include uri=concat('design:user/login_templates/', $login_module_parts[0], '.tpl') login_module_setting=cond(is_set($login_module_parts[1]), $login_module_parts[1], $login_module_parts[0])}
        {undef $login_module_parts}
      </div>
    </div>
    {delimiter}
      <hr/>
    {/delimiter}
  {/foreach}

{elseif $login_layout|eq('row')}
  {def $row_span = 12|div($login_modules_count)|ceil()}
  <div class="row">
    {foreach $login_modules as $login_module}
      {def $login_module_parts = $login_module|explode('|')}
      <div class="col-sm-{$row_span}">
        {include uri=concat('design:user/login_templates/', $login_module_parts[0], '.tpl') login_module_setting=cond(is_set($login_module_parts[1]), $login_module_parts[1], $login_module_parts[0])}
      </div>
      {undef $login_module_parts}
    {/foreach}
  </div>
  {undef $row_span}
{/if}

<hr />

{if $login_modules_count|le(1)}
  <section id="login">
    <div class="row">
      <div class="col-sm-6 col-md-6 col-sm-offset-3 col-md-offset-3">
        <div class="signup">
          <div class="social_sign">
            <h3>
              <strong>{'Are you not registered yet?'|i18n('social_user/signup')}</strong><br />
              {'It takes just 5 seconds to register!'|i18n('social_user/signup')}
              <a style="background:#f0ad4e;font-size: 0.7em;padding: 18px 2px;" href="{'/user/register/'|ezurl(no)}" class="btn btn-success btn-lg">{'Subscribe'|i18n('social_user/signup')}</a>
            </h3>
          </div>
        </div>
      </div>
    </div>
  </section>
{/if}