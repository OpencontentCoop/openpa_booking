{if $message_list}
    <aside class="widget timeline" id="current-post-timeline">
        <ol class="list-unstyled">
            {foreach $message_list as $item sequence array(bglight,bgdark) as $_style}
                {collaboration_simple_message_view view=element sequence=$_style is_read=$current_participant.last_read|gt($item.modified) item_link=$item collaboration_message=$item.simple_message}
            {/foreach}
        </ol>
    </aside>
{/if}

<form method="post" action={"collaboration/action/"|ezurl} xmlns="http://www.w3.org/1999/html">
    <input type="hidden" name="Collaboration_OpenpaBookingActionParameters[]" value=""/>
    <textarea name="Collaboration_OpenpaBookingComment" cols="40" rows="5" class="form-control"></textarea>
    <input class="btn btn-block btn-xs btn-info" type="submit" name="CollaborationAction_Comment"
           value="Aggiungi un messaggio"/>

    <input type="hidden" name="CollaborationActionCustom" value="custom"/>
    <input type="hidden" name="CollaborationTypeIdentifier" value="openpabooking"/>
    <input type="hidden" name="CollaborationItemID" value="{$collab_item.id}"/>
</form>

