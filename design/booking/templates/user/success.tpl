<section class="hgroup">
<div class="row">
    <div class="col-md-8 col-md-offset-2">

        <div class="alert alert-info text-center">
            <i class="fa fa-envelope-o fa-5x"></i>
            {if $verify_user_email}
                <h3>{'Your account was successfully created. An email will be sent to the specified address. Follow the instructions in that email to activate your account.'|i18n('design/ocbootstrap/user/success')}</h3>
            {else}
                <h3>{"Your account was successfully created."|i18n("design/ocbootstrap/user/success")}</h3>
                <a class="btn btn-success pull-right" href={'/'|ezurl()}>Ok</a>
            {/if}
        </div>


    </div>
</div>
</section>