{def $terms_url = 'sensor/info/terms'|ezurl()}
{def $privacy_url = 'sensor/info/privacy'|ezurl()}
{if module_params()['module_name']|ne( 'user' )}
{include uri='design:social_user/login_form.tpl'}
{/if}