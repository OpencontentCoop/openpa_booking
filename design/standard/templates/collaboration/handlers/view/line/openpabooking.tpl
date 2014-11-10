{def $content_object=fetch("content","object",hash("object_id",$item.content.content_object_id))
     $openpa_object = object_handler( $content_object )}


{if $content_object}

  {include uri=concat( 'design:booking/', $item.content.openpabooking_handler, '/line.tpl' )}

{else}

  <p>La prenotazione {$item.content.content_object_id} non esiste o &egrave; stata rimossa.</p>

{/if}