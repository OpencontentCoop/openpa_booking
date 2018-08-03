{include uri='design:shop/parts/breadcrumb.tpl' selected=2}

<section class="shop-userregister">

    <section class="hgroup">
        <h1 class="long">{"Your account information"|i18n("design/ocbootstrap/shop/userregister")}</h1>
        <small>
            {"All fields marked with * must be filled in."|i18n("design/ocbootstrap/shop/userregister")}
        </small>
    </section>

    {if $input_error}
        <div class="alert alert-warning">
            <p>
                {"Input did not validate. All fields marked with * must be filled in."|i18n("design/ocbootstrap/shop/userregister")}
            </p>
        </div>
    {/if}

    <form class="form" method="post" action={"openpa_booking/shop_register"|ezurl}>

        <div class="row">
            <div class="col-md-8 form-group">
                <label>
                    Cognome Nome oppure Ragione sociale {if $settings['first_name']['is_required']}*{/if}
                </label>
                <input class="form-control" type="text" name="{$settings['first_name']['input_name']}" size="20" value="{$first_name|wash} {$last_name|wash}"/>
            </div>            
            <div class="col-md-4 form-group">
                <label>
                    {"Email"|i18n("design/ocbootstrap/shop/userregister")} {if $settings['email']['is_required']}*{/if}
                </label>
                <input class="form-control" type="text" name="{$settings['email']['input_name']}" size="20" value="{$email|wash}"/>
            </div>
        </div>


        <div class="row">
            <div class="col-md-4 form-group">
                <label>
                    {"Street"|i18n("design/ocbootstrap/shop/userregister")} {if $settings['street2']['is_required']}*{/if}
                </label>
                <input class="form-control" type="text" name="{$settings['street2']['input_name']}" size="20" value="{$street2|wash}"/>
            </div>
            <div class="col-md-4 form-group">
                <label>
                    {"Zip"|i18n("design/ocbootstrap/shop/userregister")} {if $settings['zip']['is_required']}*{/if}
                </label>
                <input class="form-control" type="text" name="{$settings['zip']['input_name']}" size="20" value="{$zip|wash}"/>
            </div>
            <div class="col-md-4 form-group">
                 <label>
                    Codice fiscale/Partita IVA {if $settings['vat_code']['is_required']}*{/if}
                </label>
                <input class="form-control" type="text" name="{$settings['vat_code']['input_name']}" size="20" value="{$vat_code|wash}"/>
            </div>
        </div>

        <div class="row">
            <div class="col-md-6 form-group">
                <label>
                    {"Place"|i18n("design/ocbootstrap/shop/userregister")} {if $settings['place']['is_required']}*{/if}
                </label>
                <input class="form-control" type="text" name="{$settings['place']['input_name']}" size="20" value="{$place|wash}"/>
            </div>
            <div class="col-md-6 form-group">
                <label>
                    {"Country"|i18n("design/ocbootstrap/shop/userregister")} {if $settings['country']['is_required']}*{/if}
                </label>
                {def $countries = fetch( 'content', 'country_list' )}
                <select name="{$settings['country']['input_name']}" class="form-control">
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
                {"Comment"|i18n("design/ocbootstrap/shop/userregister")} {if $settings['comment']['is_required']}*{/if}
            </label>
            <textarea name="{$settings['comment']['input_name']}" class="form-control" cols="80" rows="2">{$comment|wash}</textarea>
        </div>



        <div class="buttonblock">
            <input class="btn btn-default btn-lg pull-left" type="submit" name="CancelButton"
                   value="{"Cancel"|i18n('design/ocbootstrap/shop/userregister')}"/>
            <input class="btn btn-success btn-lg pull-right" type="submit" name="StoreButton"
                   value="{"Continue"|i18n( 'design/ocbootstrap/shop/userregister')}"/>
        </div>

    </form>

</section>
