<div class="row">
    <div class="col-md-8">
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
                        <th>
                            {switch match=$item.name}

                            {case match="Autore"}
                                Richiedente
                            {/case}

                            {case match="Osservatore"}
                                Responsabile dell'attrezzatura
                            {/case}

                            {case match="Approvato da"}
                                Responsabile
                            {/case}

                            {case}
                            {$item.name|wash}
                            {/case}

                            {/switch}
                        </th>
                        <td>
                            <ul class="list-inline">
                            {foreach $item.items as $partecipant}
                                {if is_set($partecipant.participant.contentobject_id)}
                                    <li>
                                        {$partecipant.participant.contentobject.name|wash()}
                                        (<a href="mailto:{$partecipant.participant.email|wash()}">{$partecipant.participant.email|wash()}</a>)
                                    </li>
                                {/if}                                
                            {/foreach}
                            </ul>
                        </td>
                    </tr>
                {/foreach}

            </table>
        </div>

        <div class="panel panel-default">
            <table class="table">

                <tr>
                    {if $openpa_object.control_booking_sala_pubblica.current_state_code|eq(0)}
                    <th>{'Costo previsto'|i18n('booking')}</th>
                    {else}
                    <th>{'Costo'|i18n('booking')}</th>
                    {/if}
                    <td>
                        {def $price = $content_object.data_map.price.content.inc_vat_price}
                        {$price|l10n( currency )}
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

                    {def $invoiceData = booking_request_invoice($order)}
                    {if $invoiceData}
                        <tr>
                            <th>Fattura</th>
                            <td>
                                {if $invoiceData._status|eq('pending')}
                                    <p><em>La fattura è in elaborazione e sarà disponibile a breve</em></p>
                                {elseif $invoiceData._status|eq('ready')}
                                    <a class="btn btn-xl btn-info" href="{concat('openpa_booking/invoice/',$order.id)|ezurl(no)}">Scarica il pdf della fattura</a>
                                {/if}
                            </td>
                        </tr>
                    {/if}
                {/if}
            </table>

        </div>

        <div class="panel panel-default">
            <table class="table">
                {foreach $content_object.data_map as $identifier => $attribute}
                    {if array('from_time', 'to_time', 'sala', 'stuff', 'scheduler', 'subrequest', 'price', 'order_id')|contains($attribute.contentclass_attribute_identifier)|not()}
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
