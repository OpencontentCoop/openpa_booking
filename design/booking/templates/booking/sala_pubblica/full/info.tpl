<div class="row">
    <div class="col-md-8">
        <div class="panel panel-info">

            <div class="panel-heading"><b>{'Prenotazioni richieste'|i18n('booking')}</b></div>
            <table class="table">
                <tr>
                    <th>{'Luogo'|i18n('booking')}</th>
                    <td colspan="2">
                        <a href="{concat('openpa_booking/locations/', $sala.main_node_id)|ezurl(no)}">{$sala.name|wash()}</a>
                    </td>
                </tr>
                <tr>
                    <th>{'Data'|i18n('booking')}</th>
                    <th>{'Inizio'|i18n('booking')}</th>
                    <th>{'Termine'|i18n('booking')}</th>
                </tr>
                <tr>
                    <td>{$content_object.data_map.from_time.content.timestamp|l10n(date)}</td>
                    <td>{$content_object.data_map.from_time.content.timestamp|l10n(shorttime)}</td>
                    <td>{$content_object.data_map.to_time.content.timestamp|l10n(shorttime)}</td>
                </tr>
                {if $content_object.main_node.children_count}
                    {foreach $content_object.main_node.children as $child}
                        <tr>
                            <td>{$child.data_map.from_time.content.timestamp|l10n(date)}</td>
                            <td>{$child.data_map.from_time.content.timestamp|l10n(shorttime)}</td>
                            <td>{$child.data_map.to_time.content.timestamp|l10n(shorttime)}</td>
                        </tr>
                    {/foreach}
                {/if}
            </table>
            <div class="panel-heading"><b>{'Attrezzatura richiesta'|i18n('booking')}</b></div>
            <table class="table">
                {if $content_object.data_map.stuff.has_content}
                    {foreach $content_object.data_map.stuff.content.relation_list as $item}
                        <tr>
                            <td>
                                {fetch(content, object, hash(object_id, $item.contentobject_id)).name|wash()}
                            </td>
                        </tr>
                    {/foreach}
                {else}
                    <tr>
                        <td><em>Nessuna</em></td>
                    </tr>
                {/if}
            </table>
        </div>

        <div class="panel panel-default">
            <div class="panel-heading"><b>{'Informazioni sulla richiesta'|i18n('booking')}</b></div>
            <table class="table">
                <tr>
                    <th>{'Data di invio della richiesta'|i18n('booking')}</th>
                    <td>
                        {$content_object.published|l10n(shortdatetime)}
                        {if $content_object.published|ne($content_object.modified)}
                            <br/>
                            <small>{'Ultima modifica'|i18n('booking')} {$content_object.modified|l10n(shortdatetime)}</small>
                        {/if}
                    </td>
                </tr>
                <tr>
                    <th>{'Stato della richiesta'|i18n('booking')}</th>
                    <td>
                        {$openpa_object.control_booking_sala_pubblica.current_state.current_translation.name|wash()}
                    </td>
                </tr>
                {foreach $participant_list as $item}
                    <tr>
                        <th>{$item.name|wash}</th>
                        <td>{foreach $item.items as $partecipant}{$partecipant.participant.contentobject.name|wash()}{delimiter}, {/delimiter}{/foreach}</td>
                    </tr>
                {/foreach}

            </table>
        </div>

        <div class="panel panel-default">
            <table class="table">

                <tr>
                    <th>{'Costo previsto'|i18n('booking')}</th>
                    <td>
                        {attribute_view_gui attribute=$content_object.data_map.price}
                    </td>
                </tr>

                {if $content_object.data_map.order_id.content|gt(0)}
                    <tr>

                        {def $order = fetch('shop', 'order', hash('order_id', $content_object.data_map.order_id.content))}
                        {if $collab_item.is_creator}
                            <th>{'Ordine'|i18n('booking')}</th>
                            <td><a href="{concat('shop/orderview/', $order.id)|ezurl(no)}">{$order.status.name|wash()}</a></td>
                        {else}

                        <th><a href="{concat('shop/orderview/', $order.id)|ezurl(no)}">{'Ordine'|i18n('booking')}</a></th>
                        <td>

                            {def $order_status_list=$order.status_modification_list}
                            {if $order_status_list|count|gt( 0 )}
                            <form name="orderlist" method="post" action={'/shop/orderlist'|ezurl}>
                                <select name="StatusList[{$order.id}]">
                                    {foreach $order_status_list as $item}
                                        <option value="{$item.status_id}"
                                                {if eq( $item.status_id, $order.status_id )}selected="selected"{/if}>
                                            {$item.name|wash}</option>
                                    {/foreach}
                                </select>
                                <input class="button" type="submit" name="SaveOrderStatusButton" value="{'Apply changes'|i18n( 'design/admin/shop/orderlist' )}" />
                                </form>
                            {else}
                                <a href="{concat('shop/orderview/', $order.id)|ezurl(no)}">{$order.status.name|wash()}</a>
                            {/if}
                            {undef $order_status_list}
                        </td>
                        {/if}

                    </tr>
                {/if}
            </table>

        </div>

        <div class="panel panel-default">
            <table class="table">
                {foreach $content_object.data_map as $identifier => $attribute}
                    {if array('text', 'destinatari', 'patrocinio', 'comunicazione')|contains($attribute.contentclass_attribute_identifier)}
                        <tr>
                        <th>{$attribute.contentclass_attribute_name|wash()}</th>
                        <td>
                            {attribute_view_gui attribute=$attribute}
                        </td>
                        </tr>
                    {/if}
                {/foreach}

            </table>

        </div>
    </div>


    <div class="col-md-4">
        <div class="panel panel-default">
            <div class="panel-heading"><b>{'Iter della richiesta'|i18n('booking')}</b></div>
            <div class="panel-body">
                {include uri='design:booking/sala_pubblica/full/messages.tpl'}
            </div>
        </div>
    </div>
</div>
