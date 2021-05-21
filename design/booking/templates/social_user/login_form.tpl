{def $login_layout = ezini('LoginTemplate', 'Layout', 'app.ini')}
{def $login_modules = ezini('LoginTemplate', 'LoginModules', 'app.ini')}
{def $login_modules_count = count($login_modules)}

<section id="login">
    <div class="row">
        <div class="col-sm-12 col-md-12 text-center">
            <h1 style="margin-bottom: 1em">
                {'In order to partecipate, you need to be registered!'|i18n('social_user/signin')}
            </h1>
        </div>
    </div>

{if $login_modules_count|gt(1)}
    <div class="row">
    {foreach $login_modules as $login_module}
        <div class="col-sm-6 col-md-6">
            {def $login_module_parts = $login_module|explode('|')}
            {if $login_module_parts[0]|eq('default')}
                <div class="signin">
                    <h3>
                        {if ezini_hasvariable('LoginTemplate_default', 'Title', 'app.ini')}
                            {ezini('LoginTemplate_default', 'Title', 'app.ini')|wash()}
                        {else}
                            {'Are you already a member?'|i18n('social_user/signin')}
                        {/if}
                    </h3>
                    {if ezini_hasvariable('LoginTemplate_default', 'Text', 'app.ini')}
                        <p class="text-center">{ezini('LoginTemplate_default', 'Text', 'app.ini')|wash()}</p>
                    {else}
                        <p><strong>{'Log in now!'|i18n('social_user/signin')}</strong></p>
                    {/if}
                    <div class="text-center">
                        <a href="{'/user/login/'|ezurl(no)}" class="btn btn-success btn-lg">{'Login'|i18n('social_user/signin')}</a>
                    </div>
                </div>
            {else}
                <div class="signin">
                    {include uri=concat('design:user/login_templates/', $login_module_parts[0], '.tpl')
                             header_tag='h3'
                             login_module_setting=cond(is_set($login_module_parts[1]), $login_module_parts[1], $login_module_parts[0])}
                </div>
            {/if}
            {undef $login_module_parts}
        </div>
    {/foreach}
    </div>

{else}
    <div class="row">
        <div class="col-sm-6 col-md-6">
            <div class="signin">
                <div class="social_sign">
                    <h3>
                        {'Are you already a member?'|i18n('social_user/signin')}<br />
                        <strong>{'Log in now!'|i18n('social_user/signin')}</strong>
                        <a style="background:#5cb85c;font-size: 0.7em;padding: 18px 2px;" href="{'/user/login/'|ezurl(no)}" class="btn btn-success btn-lg">{'Login'|i18n('social_user/signin')}</a>
                    </h3>
                </div>
            </div>
        </div>
        <div class="col-sm-6 col-md-6">
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
{/if}

</section>