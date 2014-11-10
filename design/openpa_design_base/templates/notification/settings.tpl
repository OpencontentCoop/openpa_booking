{let handlers=fetch( notification, handler_list )}
<form name="notification" method="post" action={'/notification/settings/'|ezurl}>

<div class="global-view-full">

<h1 class="context-title">{'My notification settings'|i18n( 'design/admin/notification/settings' )}</h1>


<div class="context-attributes">
{section var=Handlers loop=$handlers}
    {section-exclude match=eq( $Handlers.item, $handlers.ezsubtree )}
    {include name=newspace uri=concat( 'design:notification/handler/', $Handlers.item.id_string, '/settings/edit.tpl' ) handler=$Handlers.item}
{/section}
</div>

<div class="block">
    <input class="button" type="submit" name="Store" value="{'Apply changes'|i18n( 'design/admin/notification/settings' )}" />
</div>

{include name=newspace uri=concat( 'design:notification/handler/', $handlers.ezsubtree.id_string, '/settings/edit.tpl' ) handler=$handlers.ezsubtree view_parameters=$view_parameters}

</div>

</form>
{/let}
