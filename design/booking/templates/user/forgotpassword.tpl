<div class="row">
<div class="col-md-6 col-md-offset-3">

{if $link}
<div class="alert alert-success">
{"An email has been sent to the following address: %1. It contains a link you need to click so that we can confirm that the correct user has received the new password."|i18n('design/ocbootstrap/user/forgotpassword',,array($email))}
</div>
{else}
   {if $wrong_email}
   <div class="alert alert-danger">
    <strong>{"There is no registered user with that email address."|i18n('design/ocbootstrap/user/forgotpassword')}</strong>
   </div>
   {/if}
   
   {if $generated}
   <div class="alert alert-success clearfix">
    <strong>{"Password was successfully generated and sent to: %1"|i18n('design/ocbootstrap/user/forgotpassword',,array($email))}</strong>
     <a class="btn btn-success pull-right" href={"/"|ezurl(no)}>OK</a>
   </div>
   {else}
      {if $wrong_key}
      <div class="alert alert-danger clearfix">
        <p>{"The key is invalid or has been used. "|i18n('design/ocbootstrap/user/forgotpassword')}</p>
        <a class="btn btn-danger pull-right" href={"/"|ezurl(no)}>Indietro</a>
      </div>
      {else}
      <form method="post" name="forgotpassword" action={"/user/forgotpassword/"|ezurl}>
      
      <h1 class="long">{"Have you forgotten your password?"|i18n('design/ocbootstrap/user/forgotpassword')}</h1>
      <p>{"If you have forgotten your password, enter your email address and we will create a new password and send it to you."|i18n('design/ocbootstrap/user/forgotpassword')}</p>
      
      <div class="row">

      <div class="col-md-8">        
        <input autocomplete="off" placeholde="{"Email"|i18n('design/ocbootstrap/user/forgotpassword')}" class="form-control" type="text" name="UserEmail" size="40" value="{$wrong_email|wash}" />
      </div>

      <div class="col-md-4">
       <input class="btn btn-primary btn-block" type="submit" name="GenerateButton" value="{'Generate new password'|i18n('design/ocbootstrap/user/forgotpassword')}" />
      </div>
      
      </div>
      
      </form>
      {/if}
   {/if}
{/if}


</div>
</div>

