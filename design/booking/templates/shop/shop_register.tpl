{include uri='design:shop/parts/breadcrumb.tpl' selected=2}

<section class="shop-userregister">

    <section class="hgroup">
        <h1 class="long">{"Dati per la fatturazione"|i18n("design/ocbootstrap/shop/userregister")}</h1>
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
    <ul class="nav nav-pills" role="tablist">
        {foreach $settings['type']['enum'] as $type => $label}
        <li{if $type|eq($current_type)} class="active"{/if}>
            <a href="#{$type}" class="" aria-controls="home" role="tab" data-toggle="tab">
                {$label|wash()}
            </a>
        </li>
        {/foreach}
    </ul>

    <div class="tab-content" style="padding: 15px 0px">
    {foreach $settings['type']['enum'] as $type => $label}
        <div role="tabpanel" class="tab-pane clearfix{if $type|eq($current_type)} active{/if}" id="{$type}">
            <form class="form" method="post" action={"openpa_booking/shop_register"|ezurl}>
                <input type="{$settings['type']['type']}" name="{$settings['type']['input_name']}" value="{$type|wash()}">
                {include uri=concat('design:shop/shop_register/', $type|wash(), '.tpl')}
                <div class="buttonblock">
                    <input class="btn btn-default btn-lg pull-left" type="submit" name="CancelButton"
                           value="{"Cancel"|i18n('design/ocbootstrap/shop/userregister')}"/>
                    <input class="btn btn-success btn-lg pull-right" type="submit" name="StoreButton"
                           value="{"Continue"|i18n( 'design/ocbootstrap/shop/userregister')}"/>
                </div>
            </form>
        </div>
    {/foreach}
    </div>

</section>
