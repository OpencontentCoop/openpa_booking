{default attribute_base='ContentObjectAttribute' html_class='full'}

{if $attribute.object.data_map.sala.content.data_map.price_range.has_content}
    {def $range_user = $attribute.object.data_map.sala.content.data_map.price_range.content
    	 $valid_time = true()}
    {foreach $range_user.rows.sequential as $row}
        {set $valid_time = true()}
        {if and(is_set($row.columns[5]), $row.columns[5]|ne(''))}
		   	{set $valid_time = booking_is_in_range(
		   		$attribute.object.data_map.from_time.content.timestamp,
		   		$attribute.object.data_map.to_time.content.timestamp,
		   		$row.columns[5]
	   		)}
	   {/if}
        <div class="radio {if $valid_time|not()}text-muted{/if}">
            <input type="radio" 
        		   value="{$row.columns[0]}" 
        		   name="{$attribute_base}_ezstring_data_text_{$attribute.id}" 
        		   {if $attribute.data_text|eq($row.columns[0])}checked="checked"{/if}
        		   {if $valid_time|not()}disabled="disabled"{/if}
        		   />
                
                {$row.columns[1]|wash( xhtml )|nl2br}
                
                <br />
                <strong>Prezzo: 
                {if $row.columns[2]|eq(0)}
                	gratuito
            	{else}                	
                	{if and(is_set($row.columns[3]), is_set($row.columns[4]))}
                		{foreach booking_vat_type_list() as $vat_type}
                			{if eq( $vat_type.id, $row.columns[4] )}
                				{if $row.columns[3]|ne('1')}
                					{booking_calc_price($row)|l10n( currency )} ({$row.columns[2]|l10n( currency )} + IVA {$vat_type.percentage}%)                			
                				{else}
                					{$row.columns[2]|l10n( currency )} ({booking_calc_price($row)|l10n( currency )} + IVA {$vat_type.percentage}%)
                				{/if}
                			{/if}
                		{/foreach}
                	{else}
                		{$row.columns[2]|l10n( currency )}
                	{/if}
            	{/if}
            	</strong>
            	{if and(is_set($row.columns[5]), $row.columns[5]|ne(''))}
            		<p class="text-muted">Valido solo per prenotazioni negli orari compresi tra le {$row.columns[5]|explode('-')|implode(' e le ')}</p>
            	{/if}
        </div>
    {/foreach}
    {undef $range_user}
{else}
    <input type="hidden" value="nessuno" name="{$attribute_base}_ezstring_data_text_{$attribute.id}"/>
{/if}

{/default}
