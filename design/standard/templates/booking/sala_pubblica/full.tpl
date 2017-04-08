{if $collab_item.content.is_author}
  {include uri=concat( 'design:booking/', $collab_item.content.openpabooking_handler, '/full/author.tpl' )}
{elseif $collab_item.content.is_approver}
  {include uri=concat( 'design:booking/', $collab_item.content.openpabooking_handler, '/full/approver.tpl' )}
{elseif $collab_item.content.is_observer}
  {include uri=concat( 'design:booking/', $collab_item.content.openpabooking_handler, '/full/observer.tpl' )}
{/if}
