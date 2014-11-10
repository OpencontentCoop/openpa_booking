<div id="path">
  <p>
    <span class="path-text">1. {"Shopping basket"|i18n("design/ezwebin/shop/basket")}</span>
    <span class="path-separator">»</span>
    <span class="path-text">2. {"Account information"|i18n("design/ezwebin/shop/basket")}</span>
    <span class="path-separator">»</span>
    <span class="path-text"><strong>3. {"Confirm order"|i18n("design/ezwebin/shop/basket")}</strong></span>
  </p>
</div>

<div class="global-view-full block">

<form method="post" action={"/shop/confirmorder/"|ezurl}>

    <h1>{"Confirm order"|i18n("design/ezwebin/shop/confirmorder")}</h1>

    {shop_account_view_gui view=html order=$order}

    {def $currency = fetch( 'shop', 'currency', hash( 'code', $order.productcollection.currency_code ) )
         $locale = false()
         $symbol = false()}

    {if $currency}
        {set locale = $currency.locale
             symbol = $currency.symbol}
    {/if}


    <h3>{"Product items"|i18n("design/ezwebin/shop/confirmorder")}</h3>
    <table class="list" width="100%" cellspacing="0" cellpadding="0" border="0">
    <tr>
        <th>{"Count"|i18n("design/ezwebin/shop/confirmorder")}</th>
        <th>{"VAT"|i18n("design/ezwebin/shop/confirmorder")}</th>
        <th>{"Price inc. VAT"|i18n("design/ezwebin/shop/confirmorder")}</th>
        <th>{"Discount"|i18n("design/ezwebin/shop/confirmorder")}</th>
        <th>{"Total price ex. VAT"|i18n("design/ezwebin/shop/confirmorder")}</th>
        <th>{"Total price inc. VAT"|i18n("design/ezwebin/shop/confirmorder")}</th>
    </tr>
    {section name=ProductItem loop=$order.product_items show=$order.product_items sequence=array(bglight,bgdark)}
    <tr class="bglight">
        <td colspan="6">    <input type="hidden" name="ProductItemIDList[]" value="{$ProductItem:item.id}" />
        <a href={concat("/content/view/full/",$ProductItem:item.node_id,"/")|ezurl}>{$ProductItem:item.object_name}</a></td>
    </tr>
    <tr class="bgdark">
        <td>{$ProductItem:item.item_count}</td>
        <td>{$ProductItem:item.vat_value} %</td>
        <td>{$ProductItem:item.price_inc_vat|l10n( 'currency', $locale, $symbol )}</td>
        <td>{$ProductItem:item.discount_percent}%</td>
        <td>{$ProductItem:item.total_price_ex_vat|l10n( 'currency', $locale, $symbol )}</td>
        <td>{$ProductItem:item.total_price_inc_vat|l10n( 'currency', $locale, $symbol )}</td>
    </tr>
    {section show=$ProductItem:item.item_object.option_list}
    <tr>
      <td colspan="6" style="padding: 0;">
         <table width="100%" cellpadding="0" cellspacing="0">
    <tr>
    <td colspan="3">
    {"Selected options"|i18n("design/ezwebin/shop/confirmorder")}
    </td>
    </tr>
         {section name=Options loop=$ProductItem:item.item_object.option_list}
          <tr>
            <td width="33%">{$ProductItem:Options:item.name}</td>
            <td width="33%">{$ProductItem:Options:item.value}</td>
            <td width="33%">{$ProductItem:Options:item.price|l10n( 'currency', $locale, $symbol )}</td>
          </tr>
        {/section}
         </table>
       </td>
    </tr>
    {/section}

    {/section}
    </table>

    <h3>{"Order summary"|i18n("design/ezwebin/shop/confirmorder")}:</h3>
    <table class="list" cellspacing="0" cellpadding="0" border="0">
    <tr>
        <th>{"Summary"|i18n("design/ezwebin/shop/confirmorder")}</th>
        <th>{"Total ex. VAT"|i18n("design/ezwebin/shop/confirmorder")}</th>
        <th>{"Total inc. VAT"|i18n("design/ezwebin/shop/confirmorder")}</th>
    </tr>
    <tr class="bglight">
        <td>{"Subtotal of items"|i18n("design/ezwebin/shop/confirmorder")}:</td>
        <td>{$order.product_total_ex_vat|l10n( 'currency', $locale, $symbol )}</td>
        <td>{$order.product_total_inc_vat|l10n( 'currency', $locale, $symbol )}</td>
    </tr>

    {section name=OrderItem loop=$order.order_items show=$order.order_items sequence=array(bgdark,bglight)}
    <tr class="{$OrderItem:sequence}">
        <td>{$OrderItem:item.description}:</td>
        <td>{$OrderItem:item.price_ex_vat|l10n( 'currency', $locale, $symbol )}</td>
        <td>{$OrderItem:item.price_inc_vat|l10n( 'currency', $locale, $symbol )}</td>
    </tr>
    {/section}
    <tr class="bgdark">
        <td>{"Order total"|i18n("design/ezwebin/shop/confirmorder")}:</td>
        <td>{$order.total_ex_vat|l10n( 'currency', $locale, $symbol )}</td>
        <td>{$order.total_inc_vat|l10n( 'currency', $locale, $symbol )}</td>
    </tr>
    </table>

    <div class="buttonblock">
    <input class="button" type="submit" name="CancelButton" value="{'Cancel'|i18n('design/ezwebin/shop/confirmorder')}" />
    <input class="defaultbutton" type="submit" name="ConfirmOrderButton" value="{'Confirm'|i18n('design/ezwebin/shop/confirmorder')}"  style="float:right"  />
    </div>

</form>

</div>
