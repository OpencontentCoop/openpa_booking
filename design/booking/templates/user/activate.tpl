<div class="row">
<div class="col-md-8 col-md-offset-2">

{if $account_activated}
<h1 class="container-title">{"Activate account"|i18n("design/ocbootstrap/user/activate")}</h1>
<div class="alert alert-success">
    <a class="btn btn-success pull-right" href="/">{'OK'|i18n( 'design/ocbootstrap/user/activate' )}</a>
    {if $is_pending}
        {'Your email address has been confirmed. An administrator needs to approve your sign up request, before your login becomes valid.'|i18n('design/standard/user')}
    {else}
        {'Your account is now activated.'|i18n('design/standard/user')}
    {/if}
</div>    
{elseif $already_active}
<h1 class="container-title">{"Activate account"|i18n("design/ocbootstrap/user/activate")}</h1>
<div class="alert alert-success">
    <a class="btn btn-success pull-right" href="/">{'OK'|i18n( 'design/ocbootstrap/user/activate' )}</a>
    {'Your account is already active.'|i18n('design/standard/user')}
</div>    
{else}
<div class="alert alert-danger">
    <form action={"/user/login"|ezurl} method="post" class="form-inline"><input class="btn btn-danger pull-right" type="submit" value="{'OK'|i18n( 'design/ocbootstrap/user/activate' )}" /></form>
    {'Sorry, the key submitted was not a valid key. Account was not activated.'|i18n('design/standard/user')}
</div>    
{/if}


</div>
</div>

