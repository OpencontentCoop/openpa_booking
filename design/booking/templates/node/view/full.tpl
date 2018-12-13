{def $openpa = object_handler($node)
     $current_user = fetch(user, current_user)}
{if location_class_identifiers()|contains($node.class_identifier)}
	{include uri="design:openpa/full/sala_pubblica.tpl"}
{elseif stuff_class_identifiers()|contains($node.class_identifier)}
	{include uri="design:openpa/full/attrezzatura_sala.tpl"}
{else}
	{include uri=$openpa.control_template.full}
{/if}

{include uri='design:parts/load_website_toolbar.tpl'}