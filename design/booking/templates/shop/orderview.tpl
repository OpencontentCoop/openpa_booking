<div class="shop-orderview">

<section class="hgroup">
    <h1>
    {'Order %order_id [%order_status]'|i18n( 'design/ezwebin/shop/orderview',,hash( '%order_id', $order.order_nr,'%order_status', $order.status_name ) )}
    </h1>
</section>

{shop_account_view_gui view=html order=$order}

{def $invoiceData = booking_request_invoice($order)}
{if $invoiceData}
    <h3>Fattura</h3>
    {if $invoiceData._status|eq('pending')}
        <p><em>La fattura è in elaborazione e sarà disponibile a breve</em></p>
    {elseif $invoiceData._status|eq('ready')}
        <a class="btn btn-xl btn-info" href="{concat('openpa_booking/invoice/',$order.id)|ezurl(no)}">Scarica il pdf della fattura</a>
    {/if}
{/if}


{def $currency = fetch( 'shop', 'currency', hash( 'code', $order.productcollection.currency_code ) )
         $locale = false()
         $symbol = false()}

{if $currency}
    {set locale = $currency.locale
         symbol = $currency.symbol}
{/if}

<br />

<h3>{'Product items'|i18n( 'design/ezwebin/shop/orderview' )}</h3>
<table class="table table-striped" width="100%" cellspacing="0" cellpadding="0" border="0">
<tr>
    <th>
    {'Product'|i18n( 'design/ezwebin/shop/orderview' )}
    </th>
    <th>
    {'Count'|i18n( 'design/ezwebin/shop/orderview' )}
    </th>
    <th>
    {'VAT'|i18n( 'design/ezwebin/shop/orderview' )}
    </th>
    <th>
    {'Price inc. VAT'|i18n( 'design/ezwebin/shop/orderview' )}
    </th>
    {*<th>*}
    {*{'Discount'|i18n( 'design/ezwebin/shop/orderview' )}*}
    {*</th>*}
    <th>
    {'Total price ex. VAT'|i18n( 'design/ezwebin/shop/orderview' )}
    </th>
    <th>
    {'Total price inc. VAT'|i18n( 'design/ezwebin/shop/orderview' )}
    </th>
</tr>
{if $order.product_items|count()}
{foreach $order.product_items as $product_item sequence array( 'bglight', 'bgdark' ) as $style}
<tr>
    <td style="vertical-align: middle;">
        <p>
            <a href="{concat('openpa_booking/view/sala_pubblica/',$product_item.item_object.contentobject_id)|ezurl(no)}">
                <span class="label label-primary">
                    Prenotazione {$product_item.item_object.contentobject_id}                     
                </span> 
            </a>        
            &nbsp;
            <a href="{concat('openpa_booking/locations/',$product_item.item_object.contentobject.data_map.sala.content.main_node_id)|ezurl(no)}">
                <span class="label label-primary">
                    {$product_item.item_object.contentobject.data_map.sala.content.name|wash()}
                </span>
            </a>
        </p>
        <ul class="list list-unstyled">            
            <li>{$product_item.object_name}</li>
            {if $product_item.item_object.contentobject.main_node.children_count}
                {foreach $product_item.item_object.contentobject.main_node.children as $child}
                    <li>{$child.name|wash()}</li>
                {/foreach}
            {/if}
            {if $product_item.item_object.contentobject.data_map.stuff.has_content}
            {foreach $product_item.item_object.contentobject.data_map.stuff.content.relation_list as $stuff}
                {if and(is_set($stuff.extra_fields.booking_status), $stuff.extra_fields.booking_status.identifier|eq('approved'))}
                    <li>{fetch(content,object, hash(object_id, $stuff.contentobject_id)).name|wash()}</li>
                {/if}
            {/foreach}
            {/if}
        </ul>
    </td>
    <td style="vertical-align: middle;text-align: center;">
    {$product_item.item_count}
    </td>
    <td style="vertical-align: middle;text-align: center;">
    {$product_item.vat_value} %
    </td>
    <td style="vertical-align: middle;text-align: center;">
    {$product_item.price_inc_vat|l10n( 'currency', $locale, $symbol )}
    </td>
    {*<td style="vertical-align: middle;text-align: center;">*}
    {*{$product_item.discount_percent}%*}
    {*</td>*}
    <td style="vertical-align: middle;text-align: center;">
    {$product_item.total_price_ex_vat|l10n( 'currency', $locale, $symbol )}
    </td>
    <td style="vertical-align: middle;text-align: center;">
    {$product_item.total_price_inc_vat|l10n( 'currency', $locale, $symbol )}
    </td>
</tr>
{/foreach}
{/if}
</table>

<h3>{'Order summary'|i18n( 'design/ezwebin/shop/orderview' )}:</h3>
<table class="table table-striped" cellspacing="0" cellpadding="0" border="0">
<tr>
    <th>
    {'Summary'|i18n( 'design/ezwebin/shop/orderview' )}:
    </th>
    <th>
    {'Total price ex. VAT'|i18n( 'design/ezwebin/shop/orderview' )}
    </th>
    <th>
    {'Total price inc. VAT'|i18n( 'design/ezwebin/shop/orderview' )}
    </th>
</tr>
<tr class="bglight">
    <td>
    {'Subtotal of items'|i18n( 'design/ezwebin/shop/orderview' )}:
    </td>
    <td>
    {$order.product_total_ex_vat|l10n( 'currency', $locale, $symbol )}
    </td>
    <td>
    {$order.product_total_inc_vat|l10n( 'currency', $locale, $symbol )}
    </td>
</tr>
{if $order.order_items|count()}
{foreach $order.order_items as $order_item sequence array( 'bglight', 'bgdark' ) as $style}
<tr class="{$style}">
    <td>
    {$order_item.description}:
    </td>
    <td>
    {$order_item.price_ex_vat|l10n( 'currency', $locale, $symbol )}
    </td>
    <td>
    {$order_item.price_inc_vat|l10n( 'currency', $locale, $symbol )}
    </td>
</tr>
{/foreach}
{/if}
<tr class="bgdark">
    <td>
        {'Order total'|i18n( 'design/ezwebin/shop/orderview' )}
    </td>
    <td>
        {$order.total_ex_vat|l10n( 'currency', $locale, $symbol )}
    </td>
    <td>
        {$order.total_inc_vat|l10n( 'currency', $locale, $symbol )}
    </td>
</tr>
</table>


<h3>{'Order history'|i18n( 'design/ezwebin/shop/orderview' )}:</h3>
<table class="table table-striped" cellspacing="0" cellpadding="0" border="0">
<tr>
    <th>{'Date'|i18n( 'design/ezwebin/shop/orderview' )}</th>
    <th>{'Order status'|i18n( 'design/ezwebin/shop/orderview' )}</th>
</tr>
{def $order_status_history=fetch( 'shop', 'order_status_history', hash( 'order_id', $order.order_nr ) )}
{if $order_status_history|count()}
{foreach $order_status_history as $history sequence array( 'bglight', 'bgdark' ) as $style}
<tr class="{$style} ">
    <td class="date">{$history.modified|l10n( 'shortdatetime' )}</td>
    <td>{$history.status_name|wash}</td>
</tr>
{/foreach}
{/if}
</table>

</div>
