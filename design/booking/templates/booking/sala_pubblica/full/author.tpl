{def $sala = $content_object.data_map.sala.content}
{include uri='design:booking/sala_pubblica/full/header.tpl'}

{if $openpa_object.control_booking_sala_pubblica.current_state_code|eq(1)}
    <div class="text-center lead">
    {def $basket = fetch('shop', 'basket')
    $go_to_chekout = cond($content_object.data_map.order_id.content|gt(0), true(), false())}
    {foreach $basket.items as $item}
        {if $item.node_id|eq($content_object.main_node_id)}
            {set $go_to_chekout = true()}
            {break}
        {/if}
    {/foreach}
    {if $go_to_chekout}
        <a class="btn btn-lg btn-success" href="{'shop/basket/'|ezurl(no)}">Procedi con il pagamento</a>
    {else}
        <form class="form-inline" method="post" action={"content/action"|ezurl}>
            <button type="submit" class="btn btn-lg btn-success" name="ActionAddToBasket">
                Procedi con il pagamento di {attribute_view_gui attribute=$content_object.data_map.price}
            </button>
            <input type="hidden" name="ContentNodeID" value="{$content_object.main_node_id}"/>
            <input type="hidden" name="ContentObjectID" value="{$content_object.id}"/>
            <input type="hidden" name="ViewMode" value="full"/>
        </form>
    {/if}
    </div>
{/if}

{include uri='design:booking/sala_pubblica/full/info.tpl'}

{include uri='design:booking/sala_pubblica/full/detail_and_messages.tpl'}
