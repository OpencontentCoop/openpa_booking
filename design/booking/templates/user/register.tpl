<div class="row">
<div class="col-md-8 col-md-offset-2">

{if booking_root_node()|has_attribute('user_register_alert')}
    <div class="alert alert-warning">
        {attribute_view_gui attribute=booking_root_node()|attribute('user_register_alert')}
    </div>
{/if}

<form enctype="multipart/form-data"  action={"/user/register/"|ezurl} method="post" name="Register" class="form-signin">

<h1 class="container-title">{"Register user"|i18n("design/ocbootstrap/user/register")}</h1>


{if and( and( is_set( $checkErrNodeId ), $checkErrNodeId ), eq( $checkErrNodeId, true() ) )}
<div class="alert alert-danger">
<h2><span class="time">[{currentdate()|l10n( shortdatetime )}]</span> {$errMsg}</h2>
</div>
{/if}

{if $validation.processed}

{if $validation.attributes|count|gt(0)}
    <div class="alert alert-danger">
    <p><strong>{"Input did not validate"|i18n("design/ocbootstrap/user/register")}</strong></p>
    <ul>
    {foreach $validation.attributes as $attribute}
        <li>{$attribute.name}: {$attribute.description}</li>
    {/foreach}
    </ul>
    </div>
{else}
    <div class="alert alert-success">
    <p><strong>{"Input was stored successfully"|i18n("design/ocbootstrap/user/register")}</strong></p>
    </div>
{/if}

{/if}

{if count($content_attributes)|gt(0)}
    <div class="row">
        <div class="col-md-6">
            {foreach $content_attributes as $attribute max 3}
            {if $attribute.contentclass_attribute.category|eq('hidden')}<div style="display:none">{/if}
                <input type="hidden" name="ContentObjectAttribute_id[]" value="{$attribute.id}" />
                <p>{attribute_edit_gui attribute=$attribute html_class="form-control input-lg" placeholder=$attribute.contentclass_attribute.name}</p>
            {if $attribute.contentclass_attribute.category|eq('hidden')}</div>{/if}
            {/foreach}
        </div>
            
        <div class="col-md-6">
            {foreach $content_attributes as $attribute offset 3}
            {if $attribute.contentclass_attribute.category|eq('hidden')}<div style="display:none">{/if}
                <input type="hidden" name="ContentObjectAttribute_id[]" value="{$attribute.id}" />
                <p>{attribute_edit_gui attribute=$attribute html_class="form-control input-lg" placeholder=$attribute.contentclass_attribute.name}</p>
            {if $attribute.contentclass_attribute.category|eq('hidden')}</div>{/if}
            {/foreach}
        </div>
    </div>

    <div class="buttonblock">
         <input type="hidden" name="UserID" value="{$content_attributes[0].contentobject_id}" />
        {if and( is_set( $checkErrNodeId ), $checkErrNodeId )|not()}
            <input class="btn btn-lg btn-primary pull-right" type="submit" id="PublishButton" name="PublishButton" value="{'Register'|i18n('design/ocbootstrap/user/register')}" onclick="window.setTimeout( disableButtons, 1 ); return true;" />
        {else}    
            <input class="btn btn-lg btn-info pull-right" type="submit" id="PublishButton" name="PublishButton" disabled="disabled" value="{'Register'|i18n('design/ocbootstrap/user/register')}" onclick="window.setTimeout( disableButtons, 1 ); return true;" />
        {/if}
    <input class="btn btn-lg btn-info pull-left" type="submit" id="CancelButton" name="CancelButton" value="{'Discard'|i18n('design/ocbootstrap/user/register')}" onclick="window.setTimeout( disableButtons, 1 ); return true;" />
    </div>
{else}
    <div class="alert alert-danger">
        <p>{"Unable to register new user"|i18n("design/ocbootstrap/user/register")}</p>
    </div>
    <input class="btn btn-primary" type="submit" id="CancelButton" name="CancelButton" value="{'Back'|i18n('design/ocbootstrap/user/register')}" onclick="window.setTimeout( disableButtons, 1 ); return true;" />
{/if}
</form>
</div>
</div>

{literal}
<script type="text/javascript">
    function disableButtons()
    {
        document.getElementById( 'PublishButton' ).disabled = true;
        document.getElementById( 'CancelButton' ).disabled = true;
    }
</script>
{/literal}
