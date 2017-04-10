{def $openpa_prenotazione = object_handler( $prenotazione )
     $colors = $openpa_prenotazione.control_booking_sala_pubblica.state_colors}

<tr class="{$style}">
    <td><span class="label label-primary">{$prenotazione.object.id}</span></td>
    <td>
        <span class="label label-{$openpa_prenotazione.control_booking_sala_pubblica.current_state.identifier}">
            {$openpa_prenotazione.control_booking_sala_pubblica.current_state.current_translation.name}
        </span>

        {if $prenotazione.object.data_map.stuff.has_content}
            {def $stuff_list = $prenotazione.object.data_map.stuff.content.relation_list}
            <ul class="list list-unstyled">
            {foreach $stuff_list as $item}
                <li class="stuff-list">
                {def $stuff = fetch(content, object, hash(object_id, $item.contentobject_id))}
                {if is_set($item.extra_fields.booking_status)}
                    <span class="label label-{$item.extra_fields.booking_status.identifier}">
                        <span class="hide">{$item.extra_fields.booking_status.value|wash()}</span>
                    </span>
                {/if}
                    {$stuff.name|wash()}
                </li>
                {undef $stuff}
            {/foreach}
            </ul>
        {/if}

    </td>
    <td>{$prenotazione.object.published|l10n(datetime)}</td>
    <td>
        {$prenotazione.object.owner.name|wash()}
    </td>
    <td>
        <p>
        {$openpa_prenotazione.control_booking_sala_pubblica.start_timestamp|l10n(date)}
        {$openpa_prenotazione.control_booking_sala_pubblica.start_timestamp|l10n(shorttime)}
        {$openpa_prenotazione.control_booking_sala_pubblica.end_timestamp|l10n(shorttime)}
        </p>
        {if $prenotazione.object.main_node.children_count}
            {foreach $prenotazione.object.main_node.children as $child}
                <p>
                {$child.data_map.from_time.content.timestamp|l10n(date)}
                {$child.data_map.from_time.content.timestamp|l10n(shorttime)}
                {$child.data_map.to_time.content.timestamp|l10n(shorttime)}
                {if fetch('user', 'current_user').contentobject_id|eq(14)}
                    <a href="{$child.url_alias|ezurl(no)}">
                        <span class="label label-default"><i class="fa fa-link"></i> {$child.contentobject_id}</span>
                    </a>
                {/if}
                </p>
            {/foreach}
        {/if}
    </td>
    <td>{$openpa_prenotazione.sala.contentobject_attribute.content.name|wash()}</td>
<td>
    <a class="btn btn-default btn-xs" href="{concat( "/openpa_booking/view/sala_pubblica/", $prenotazione.contentobject_id )|ezurl(no)}">
        Entra
    </a>
</td>



</tr>
{undef $openpa_prenotazione}
