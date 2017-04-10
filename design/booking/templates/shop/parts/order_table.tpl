{def $currency = fetch( 'shop', 'currency', hash( 'code', $productcollection.currency_code ) )
     $locale = false()
     $symbol = false()}
{if $currency}
    {set locale = $currency.locale
    symbol = $currency.symbol}
{/if}

{if $items}
<table class="table" width="100%" cellspacing="0" cellpadding="0" border="0">
    <tr>
        <th>
            Descrizione
        </th>
        <th>
            {"VAT"|i18n("design/ocbootstrap/shop/basket")}
        </th>
        <th>
            {"Price inc. VAT"|i18n("design/ocbootstrap/shop/basket")}
        </th>
        {*<th>*}
            {*{"Discount"|i18n("design/ocbootstrap/shop/basket")}*}
        {*</th>*}
        <th>
            {"Total price ex. VAT"|i18n("design/ocbootstrap/shop/basket")}
        </th>
        <th>
            {"Total price inc. VAT"|i18n("design/ocbootstrap/shop/basket")}
        </th>
        {*<th>&nbsp;*}
        {*</th>*}
    </tr>
    {foreach $items as $item}
        <tr>
            <td>
                <input type="hidden" name="ProductItemIDList[]"  value="{$item.id}"/>
                {*{$item.id}-*}
                {*<a href={concat("/content/view/full/",$item.node_id,"/")|ezurl}>*}
                <ul class="list-unstyled">
                        <li>{$item.object_name}</li>
                        {if $item.item_object.contentobject}

                            {foreach $item.item_object.contentobject.data_map.stuff.content.relation_list as $stuff}
                                {if and(is_set($stuff.extra_fields.booking_status), $stuff.extra_fields.booking_status.identifier|eq('approved'))}
                                    <li>{fetch(content,object, hash(object_id, $stuff.contentobject_id)).name|wash()}</li>
                                {/if}
                            {/foreach}

                            {if $item.item_object.contentobject.main_node.children_count}
                                {foreach $item.item_object.contentobject.main_node.children as $child}
                                    <li>{$child.name|wash()}</li>
                                {/foreach}
                            {/if}

                        {/if}
                    </ul>

                {*</a>*}
            </td>
            <td>
                <input type="hidden" name="ProductItemCountList[]" value="{$item.item_count}"/>
                {if ne( $item.vat_value, -1 )}
                    {$item.vat_value} %
                {else}
                    {'Unknown'|i18n( 'design/ocbootstrap/shop/basket' )}
                {/if}
            </td>
            <td>
                {$item.price_inc_vat|l10n( 'currency', $locale, $symbol )}
            </td>
            {*<td>*}
                {*{$item.discount_percent}%*}
            {*</td>*}
            <td>
                {$item.total_price_ex_vat|l10n( 'currency', $locale, $symbol )}
            </td>
            <td>
                {$item.total_price_inc_vat|l10n( 'currency', $locale, $symbol )}
            </td>
            {*<td>*}
            {*<input type="checkbox" name="RemoveProductItemDeleteList[]"*}
            {*value="{$item.id}"/>*}
            {*</td>*}
        </tr>
        {*<tr>*}
        {*<td colspan="6"><input class="button" type="submit" name="StoreChangesButton"*}
        {*value="{'Update'|i18n('design/ocbootstrap/shop/basket')}"/></td>*}
        {*<td colspan="1"><input class="button" type="submit" name="RemoveProductItemButton"*}
        {*value="{'Remove'|i18n('design/ocbootstrap/shop/basket')}"/></td>*}
        {*</tr>*}
        {*{if $item.item_object.option_list}*}
            {*<tr>*}
                {*<td colspan="7" style="padding: 0;">*}
                    {*<table cellpadding="0" cellspacing="0">*}
                        {*<tr>*}
                            {*<td colspan="3">*}
                                {*{"Selected options"|i18n("design/ocbootstrap/shop/basket")}*}
                            {*</td>*}
                        {*</tr>*}
                        {*{foreach $item.item_object.option_list as $option}*}
                            {*<tr>*}
                                {*<td width="33%">{$option.name}</td>*}
                                {*<td width="33%">{$option.value}</td>*}
                                {*<td width="33%">{$optionitem.price|l10n( 'currency', $locale, $symbol )}</td>*}
                            {*</tr>*}
                        {*{/foreach}*}
                    {*</table>*}
                {*</td>*}
            {*</tr>*}
        {*{/if}*}
    {/foreach}
    <tr>
        <td colspan="3">
        </td>
        <td>
            <strong>{"Subtotal ex. VAT"|i18n("design/ocbootstrap/shop/basket")}</strong>:
        </td>
        <td>
            <strong>{"Subtotal inc. VAT"|i18n("design/ocbootstrap/shop/basket")}</strong>:
        </td>
    </tr>
    <tr>
        <td colspan="3">
        </td>
        <td>
            {$total_ex_vat|l10n( 'currency', $locale, $symbol )}
        </td>
        <td>
            {$total_inc_vat|l10n( 'currency', $locale, $symbol )}
        </td>
    </tr>
    {if is_set( $shipping_info )}
        {* Show shipping type/cost. *}
        <tr>
            <td colspan="5">
                <a href={$shipping_info.management_link|ezurl}>{'Shipping'|i18n( 'design/ocbootstrap/shop/basket' )}{if $shipping_info.description} ({$shipping_info.description}){/if}</a>:
            </td>
            <td>
                {$shipping_info.cost|l10n( 'currency', $locale, $symbol )}:
            </td>
            <td>
                {$shipping_info.cost|l10n( 'currency', $locale, $symbol )}:
            </td>
        </tr>
        {* Show order total *}
        <tr>
            <td colspan="5">
                <strong>{'Order total'|i18n( 'design/ocbootstrap/shop/basket' )}</strong>:
            </td>
            <td>
                <strong>{$total_inc_shipping_ex_vat|l10n( 'currency', $locale, $symbol )}</strong>
            </td>
            <td>
                <strong>{$total_inc_shipping_inc_vat|l10n( 'currency', $locale, $symbol )}</strong>
            </td>
        </tr>
    {/if}

</table>

{undef $currency $locale $symbol}

{else}
    <div class="feedback">
        <h2>{"You have no products in your basket."|i18n("design/ocbootstrap/shop/basket")}</h2>
    </div>
{/if}
