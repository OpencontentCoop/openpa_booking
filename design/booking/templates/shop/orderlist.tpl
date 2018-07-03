{def $sort_by = ezpreference( 'admin_orderlist_sortfield' )}
{def $order_by = ezpreference( 'admin_orderlist_sortorder' )}

<section class="shop-orderlist">

    <form name="orderlist" method="post" action={concat( '/shop/orderlist', $view_parameters.offset|gt(0)|choose( '', concat( '/(offset)/', $view_parameters.offset ) ) )|ezurl}>
        <section class="hgroup">
            <h1 class="long">{"Order list"|i18n("design/ezwebin/shop/orderlist")}</h1>
        </section>

        {section show=$order_list}
        <table class="table table-striped" width="100%" cellspacing="0" cellpadding="0" border="0">
            <tr>
                <th width="1">&nbsp;</th>
                <th>
                    {"ID"|i18n("design/ezwebin/shop/orderlist")}
                </th>
                <th>
                    {if $sort_by|eq('time')}
                        {if $order_by|eq('desc')}
                            <a class="pull-right" href={'/user/preferences/set/admin_orderlist_sortorder/asc/shop/orderlist/'|ezurl}><i class="fa fa-sort-desc"></i></a>
                        {else}
                            <a class="pull-right" href={'/user/preferences/set/admin_orderlist_sortorder/desc/shop/orderlist/'|ezurl}><i class="fa fa-sort-asc"></i></a>
                        {/if}
                    {else}
                        <a class="pull-right text-muted" href={'/user/preferences/set/admin_orderlist_sortfield/time/shop/orderlist/'|ezurl}><i class="fa fa-sort"></i></a>
                    {/if}
                    {"Date"|i18n("design/ezwebin/shop/orderlist")}                    
                </th>
                <th>
                    {if $sort_by|eq('user_name')}
                        {if $order_by|eq('desc')}
                            <a class="pull-right" href={'/user/preferences/set/admin_orderlist_sortorder/asc/shop/orderlist/'|ezurl}><i class="fa fa-sort-desc"></i></a>
                        {else}
                            <a class="pull-right" href={'/user/preferences/set/admin_orderlist_sortorder/desc/shop/orderlist/'|ezurl}><i class="fa fa-sort-asc"></i></a>
                        {/if}
                    {else}
                        <a class="pull-right text-muted" href={'/user/preferences/set/admin_orderlist_sortfield/user_name/shop/orderlist/'|ezurl}><i class="fa fa-sort"></i></a>
                    {/if}
                    {"Customer"|i18n("design/ezwebin/shop/orderlist")}
                </th>
                <th>
                    {"Total ex. VAT"|i18n("design/ezwebin/shop/orderlist")}
                </th>
                <th>
                    {"Total inc. VAT"|i18n("design/ezwebin/shop/orderlist")}
                </th>
                <th>{'Status'|i18n( 'design/admin/shop/orderlist' )}</th>
                <th>
                </th>
            </tr>
            {section name="Order" loop=$order_list sequence=array(bglight,bgdark)}
                <tr class="{$Order:sequence}">
                    <td>
                        <input type="checkbox" name="OrderIDArray[]" value="{$Order:item.id}"/>
                    </td>
                    <td>
                        {$Order:item.order_nr}
                    </td>
                    <td>
                        {$Order:item.created|l10n(shortdatetime)}
                    </td>
                    <td>
                        <a href={concat("/shop/customerorderview/",$Order:item.user_id,"/",$Order:item.account_email)|ezurl}>{$Order:item.account_name}</a>
                    </td>
                    <td>
                        {$Order:item.total_ex_vat|l10n(currency)}
                    </td>
                    <td>
                        {$Order:item.total_inc_vat|l10n(currency)}
                    </td>
                    <td>
                        {def $order_status_list=$Order:item.status_modification_list}

                        {if $order_status_list|count|gt( 0 )}
                            <select name="StatusList[{$Order:item.id}]">
                                {foreach $order_status_list as $item}
                                    <option value="{$item.status_id}"
                                            {if eq( $item.status_id, $Order:item.status_id )}selected="selected"{/if}>
                                        {$item.name|wash}</option>
                                {/foreach}
                            </select>
                        {else}
                            {* Lets just show the name if we don't have access to change the status *}
                            {$Order:item.status_name|wash}
                        {/if}

                        {undef $order_status_list}
                    </td>
                    <td>
                        <a class="btn btn-lg btn-xs btn-default" href={concat("/shop/orderview/",$Order:item.id,"/")|ezurl}>Dettaglio</a>
                    </td>
                </tr>
            {/section}
        </table>
        {section-else}
            <div class="feedback">
                <h2>{"The order list is empty"|i18n("design/ezwebin/shop/orderlist")}</h2>
            </div>
        {/section}


        <input type="submit" class="btn btn-xs btn-default" name="ArchiveButton" value="Archivia selezionati"/>
        <input class="btn btn-xs btn-success pull-right" type="submit" name="SaveOrderStatusButton" value="{'Apply changes'|i18n( 'design/admin/shop/orderlist' )}" />

        {include name=navigator
                uri='design:navigator/google.tpl'
                page_uri='/shop/orderlist'
                item_count=$order_list_count
                view_parameters=$view_parameters
                item_limit=$limit}
    </form>

</section>
