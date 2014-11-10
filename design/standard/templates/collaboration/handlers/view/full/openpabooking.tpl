<form method="post" action={"collaboration/action/"|ezurl} xmlns="http://www.w3.org/1999/html">
  {def $message_limit=2
       $message_offset=0
       $content_object=fetch("content","object",hash("object_id",$collab_item.content.content_object_id))
       $openpa_object = object_handler( $content_object )
       $current_participant=fetch("collaboration","participant",hash("item_id",$collab_item.id))
       $participant_list=fetch("collaboration","participant_map",hash("item_id",$collab_item.id))
       $message_list=fetch("collaboration","message_list",hash("item_id",$collab_item.id,"limit",$message_limit,"offset",$message_offset))
       $sala = $content_object.data_map.sala.content}

  {if $content_object|not()}

    <div class="warning message-warning">
      <h2>La prenotazione non esiste o &egrave; stata rimossa.</h2>
    </div>

  {else}

  {if $collab_item.is_creator}
     {include uri='design:collaboration/handlers/view/full/author.tpl'}
  {else}
     {include uri='design:collaboration/handlers/view/full/approver.tpl'}
  {/if}

  {/if}

</form>
