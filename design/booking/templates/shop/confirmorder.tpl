{include uri='design:shop/parts/breadcrumb.tpl' selected=3}
<section class="shop-confirmorder">

    <form method="post" action={"/shop/confirmorder/"|ezurl}>

        <section class="hgroup">
            <h1 class="long">{"Confirm order"|i18n("design/ocbootstrap/shop/confirmorder")}</h1>
        </section>

        {shop_account_view_gui view=html order=$order}

        {include uri='design:shop/parts/order_table.tpl' productcollection=$order.productcollection items=$order.product_items total_ex_vat=$order.total_ex_vat total_inc_vat=$order.total_inc_vat}

        <div class="buttonblock">
            <input class="btn btn-default btn-lg pull-left" type="submit" name="CancelButton" value="{'Cancel'|i18n('design/ocbootstrap/shop/confirmorder')}" /> &nbsp;
            <input class="btn btn-success btn-lg pull-right" type="submit" name="ConfirmOrderButton" value="{'Confirm'|i18n('design/ocbootstrap/shop/confirmorder')}" /> &nbsp;
        </div>

    </form>

</section>
