{if $collab_item.is_creator}
    {include uri=concat( 'design:booking/', $collab_item.content.openpabooking_handler, '/full/author.tpl' )}
{else}
    {include uri=concat( 'design:booking/', $collab_item.content.openpabooking_handler, '/full/approver.tpl' )}
{/if}
