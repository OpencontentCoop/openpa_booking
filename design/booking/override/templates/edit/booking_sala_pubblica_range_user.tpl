{default attribute_base='ContentObjectAttribute' html_class='full'}

{if $attribute.object.data_map.sala.content.data_map.price_range.has_content}
    {def $range_user =  $attribute.object.data_map.sala.content.data_map.price_range.content}
    {foreach $range_user.rows.sequential as $row}
        <div class="radio">
            <input type="radio" value="{$row.columns[0]}" name="{$attribute_base}_ezstring_data_text_{$attribute.id}" {if $attribute.data_text|eq($row.columns[0])}checked="checked"{/if}/>
                {$row.columns[1]}
        </div>
    {/foreach}
    {undef $range_user}
{else}
    <input type="hidden" value="nessuno" name="{$attribute_base}_ezstring_data_text_{$attribute.id}"/>
{/if}

{/default}
