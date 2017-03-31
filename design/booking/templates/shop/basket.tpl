{include uri='design:shop/parts/breadcrumb.tpl' selected=1}

<div class="shop-basket">

    <form method="post" action={"/shop/basket/"|ezurl}>

        <section class="hgroup">
            <h1 class="long">{"Basket"|i18n("design/ocbootstrap/shop/basket")}</h1>
        </section>
        {section show=$removed_items}
            <div class="alert alert-warning">
                <h2>{"The following items were removed from your basket because the products were changed."|i18n("design/ocbootstrap/shop/basket",,)}</h2>
                <ul>
                    {section name=RemovedItem loop=$removed_items}
                        <li>
                            <a href={concat("/content/view/full/",$RemovedItem:item.contentobject.main_node_id,"/")|ezurl}>{$RemovedItem:item.contentobject.name|wash}</a>
                        </li>
                    {/section}
                </ul>
            </div>
        {/section}

        {if not( $vat_is_known )}
            <div class="alert alert-warning">
                <h2>{'VAT is unknown'|i18n( 'design/ocbootstrap/shop/basket' )}</h2>
                {'VAT percentage is not yet known for some of the items being purchased.'|i18n( 'design/ocbootstrap/shop/basket' )}
                <br/>
                {'This probably means that some information about you is not yet available and will be obtained during checkout.'|i18n( 'design/ocbootstrap/shop/basket' )}
            </div>
        {/if}

        {section show=$error}
            <div class="alert alert-danger">
                {section show=$error|eq(1)}
                    <h2>{"Attempted to add object without price to basket."|i18n("design/ocbootstrap/shop/basket",,)}</h2>
                {/section}
            </div>
        {/section}

        {section show=$error}
            <div class="alert alert-danger">
                {section show=eq($error, "aborted")}
                    <h2>{"Your payment was aborted."|i18n("design/ocbootstrap/shop/basket",,)}</h2>
                {/section}
            </div>
        {/section}

        {include uri='design:shop/parts/order_table.tpl' productcollection=$basket.productcollection items=$basket.items total_ex_vat=$basket.total_ex_vat total_inc_vat=$basket.total_inc_vat}

        <div class="clearfix">
            {*<input class="button" type="submit" name="ContinueShoppingButton"*}
            {*value="{'Continue shopping'|i18n('design/ocbootstrap/shop/basket')}"/>*}
            <input class="btn btn-success btn-lg pull-right" type="submit" name="CheckoutButton"
                   value="{'Checkout'|i18n('design/ocbootstrap/shop/basket')}"/> &nbsp;
        </div>

    </form>

</div>
