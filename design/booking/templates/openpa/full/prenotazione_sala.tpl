{if fetch(user, has_access_to, hash('module', 'openpa_booking', 'function', 'book') )}
    {def $message_limit=2
       $message_offset=0
       $collab_item = $openpa.control_booking_sala_pubblica.collaboration_item
       $content_object=fetch("content","object",hash("object_id",$collab_item.content.content_object_id))
       $openpa_object = object_handler( $content_object )
       $current_participant=fetch("collaboration","participant",hash("item_id",$collab_item.id))
       $participant_list=fetch("collaboration","participant_map",hash("item_id",$collab_item.id))
       $message_list=fetch("collaboration","message_list",hash("item_id",$collab_item.id,"limit",$message_limit,"offset",$message_offset))}

    {if or( $content_object|not(), $content_object.can_read|not() )}

    <div class="warning message-warning">
      <h2>La prenotazione {$collab_item.content.content_object_id} non &egrave; accessibile o &egrave; stata rimossa.</h2>
    </div>

    {else}

    {include uri=concat( 'design:booking/', $collab_item.content.openpabooking_handler, '/full.tpl' )}

    {/if}
{/if}

