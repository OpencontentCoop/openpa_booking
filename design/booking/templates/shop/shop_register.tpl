{include uri='design:shop/parts/breadcrumb.tpl' selected=2}

<section class="shop-userregister">

    <section class="hgroup">
        <h1 class="long">{"Your account information"|i18n("design/ocbootstrap/shop/userregister")}</h1>
        <small>
            {"All fields marked with * must be filled in."|i18n("design/ocbootstrap/shop/userregister")}
        </small>
    </section>

    {section show=$input_error}
        <div class="alert alert-warning">
            <p>
                {"Input did not validate. All fields marked with * must be filled in."|i18n("design/ocbootstrap/shop/userregister")}
            </p>
        </div>
    {/section}

    <form class="form" method="post" action={"/shop/userregister/"|ezurl}>

        <div class="row">
            <div class="col-md-4 form-group">
                <label>
                    {"First name"|i18n("design/ocbootstrap/shop/userregister")}:*
                </label>
                <input class="form-control" type="text" name="FirstName" size="20" value="{$first_name|wash}"/>
            </div>
            <div class="col-md-4 form-group">
                <label>
                    {"Last name"|i18n("design/ocbootstrap/shop/userregister")}:*
                </label>
                <input class="form-control" type="text" name="LastName" size="20" value="{$last_name|wash}"/>
            </div>
            <div class="col-md-4 form-group">
                <label>
                    {"Email"|i18n("design/ocbootstrap/shop/userregister")}:*
                </label>
                <input class="form-control" type="text" name="EMail" size="20" value="{$email|wash}"/>
            </div>
        </div>


        <div class="row">
            <div class="col-md-6 form-group">
                <label>
                    {"Street"|i18n("design/ocbootstrap/shop/userregister")}:*
                </label>
                <input class="form-control" type="text" name="Street2" size="20" value="{$street2|wash}"/>
            </div>
            <div class="col-md-6 form-group">
                <label>
                    {"Zip"|i18n("design/ocbootstrap/shop/userregister")}:*
                </label>
                <input class="form-control" type="text" name="Zip" size="20" value="{$zip|wash}"/>
            </div>
        </div>

        <div class="row">

            <div class="col-md-6 form-group">
                <label>
                    {"Place"|i18n("design/ocbootstrap/shop/userregister")}:*
                </label>
                <input class="form-control" type="text" name="Place" size="20" value="{$place|wash}"/>
            </div>
            {*<div class="col-md-4 form-group">*}
            {*<label>*}
            {*{"State"|i18n("design/ocbootstrap/shop/userregister")}:*}
            {*</label>*}
            {*<input class="form-control" type="text" name="State" size="20" value="{$state|wash}"/>*}
            {*</div>*}
            <div class="col-md-6 form-group">
                <label>
                    {"Country"|i18n("design/ocbootstrap/shop/userregister")}:*
                </label>
                {def $countries = fetch( 'content', 'country_list' )}
                <select name="Country" class="form-control">
                    {foreach $countries as $_country}
                        <option {if eq( $_country['Name'], $country )} selected="selected" {/if}
                                value="{$_country['Name']}">
                            {$_country['Name']}
                        </option>
                    {/foreach}
                </select>


            </div>

        </div>


        <div class="form-group">
            <label>
                {"Comment"|i18n("design/ocbootstrap/shop/userregister")}:
            </label>
            <textarea name="Comment" class="form-control" cols="80" rows="5">{$comment|wash}</textarea>
        </div>


        <div class="buttonblock">
            <input class="btn btn-default btn-lg pull-left" type="submit" name="CancelButton"
                   value="{"Cancel"|i18n('design/ocbootstrap/shop/userregister')}"/>
            <input class="btn btn-success btn-lg pull-right" type="submit" name="StoreButton"
                   value="{"Continue"|i18n( 'design/ocbootstrap/shop/userregister')}"/>
        </div>

    </form>

</section>
