<section id="login">
    <div class="row">
        <div class="col-sm-12 col-md-12 text-center">
            <h1 style="margin-bottom: 1em">
                {'In order to partecipate, you need to be registered!'|i18n('social_user/signin')}
            </h1>
        </div>
    </div>
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
</section>
