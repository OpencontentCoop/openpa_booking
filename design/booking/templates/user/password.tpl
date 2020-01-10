{ezscript_require(array(
    "password-score/password-score.js",
    "password-score/password-score-options.js",
    "password-score/bootstrap-strength-meter.js",
    "password-score/password.js"
))}
{ezcss_require(array('password-score/password.css'))}
<div class="row">
<div class="col-md-8 col-md-offset-2">

    <form action={concat($module.functions.password.uri,"/",$userID)|ezurl} method="post" name="Password">

    <div class="attribute-header">
        <h1 class="long">{"Change password for user"|i18n("design/ocbootstrap/user/password")} {$userAccount.login}</h1>
    </div>

    {if $message}
    {if or( $oldPasswordNotValid, $newPasswordNotMatch, $newPasswordTooShort )}
        {if $oldPasswordNotValid}
            <div class="warning">
                <h2>{'Please retype your old password.'|i18n('design/ocbootstrap/user/password')}</h2>
            </div>
        {/if}
        {if $newPasswordNotMatch}
            <div class="warning">
                <h2>{"Password didn't match, please retype your new password."|i18n('design/ocbootstrap/user/password')}</h2>
            </div>
        {/if}
        {if $newPasswordTooShort}
            <div class="warning">
                <h2>{"The new password must be at least %1 characters long, please retype your new password."|i18n( 'design/ocbootstrap/user/password','',array( ezini('UserSettings','MinPasswordLength') ) )}</h2>
            </div>
        {/if}

    {else}
        <div class="feedback">
            <h2>{'Password successfully updated.'|i18n('design/ocbootstrap/user/password')}</h2>
        </div>
    {/if}

    {/if}

        <div style="margin-bottom: 10px">
            {if $oldPasswordNotValid}*{/if}
            <label>{"Old password"|i18n("design/ocbootstrap/user/password")}</label><div class="labelbreak"></div>
            <input class="form-control" type="password" name="oldPassword" size="11" value="{$oldPassword|wash}" />
        </div>

        <div style="margin-bottom: 10px">
            {if $newPasswordNotMatch}*{/if}
            <label>{"New password"|i18n("design/ocbootstrap/user/password")}</label><div class="labelbreak"></div>
            <input id="pwd-input" class="form-control" autocomplete="off" type="password" name="newPassword" size="11" value="{$newPassword|wash}" />
            {include uri='design:parts/password_meter.tpl'}
        </div>

        <div style="margin-bottom: 10px">
            {if $newPasswordNotMatch}*{/if}
            <label>{"Retype password"|i18n("design/ocbootstrap/user/password")}</label><div class="labelbreak"></div>
            <input class="form-control" autocomplete="off" type="password" name="confirmPassword" size="11" value="{$confirmPassword|wash}" />
        </div>

        <input class="defaultbutton" type="submit" name="OKButton" value="{'OK'|i18n('design/ocbootstrap/user/password')}" />
        <input class="button" type="submit" name="CancelButton" value="{'Cancel'|i18n('design/ocbootstrap/user/password')}" />

    </form>
</div>
</div>

{literal}
<script type="text/javascript">
    $(document).ready(function() {
        $('#pwd-input').password({
            minLength:{/literal}{ezini('UserSettings', 'MinPasswordLength')}{literal},
            message: "{/literal}{'Show/hide password'|i18n('ocbootstrap')}{literal}",
            hierarchy: {
                '0': ['text-danger', "{/literal}{'Evaluation of complexity: bad'|i18n('ocbootstrap')}{literal}"],
                '10': ['text-danger', "{/literal}{'Evaluation of complexity: very weak'|i18n('ocbootstrap')}{literal}"],
                '20': ['text-warning', "{/literal}{'Evaluation of complexity: weak'|i18n('ocbootstrap')}{literal}"],
                '30': ['text-info', "{/literal}{'Evaluation of complexity: good'|i18n('ocbootstrap')}{literal}"],
                '40': ['text-success', "{/literal}{'Evaluation of complexity: very good'|i18n('ocbootstrap')}{literal}"],
                '50': ['text-success', "{/literal}{'Evaluation of complexity: excellent'|i18n('ocbootstrap')}{literal}"]
            }
        });
        $('[name="confirmPassword"]').password({
            strengthMeter:false,
            message: "{/literal}{'Show/hide password'|i18n('ocbootstrap')}{literal}"
        });
    });
</script>
{/literal}


