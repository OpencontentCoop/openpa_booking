{def $object=fetch("content","object",hash("object_id",$collaboration_item.content.content_object_id))}
{include uri=concat( 'design:booking/', $collaboration_item.content.openpabooking_handler, '/notification/observer.tpl' )}
