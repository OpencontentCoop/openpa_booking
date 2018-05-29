{let matrix=$attribute.content}
{foreach $matrix.rows.sequential as $row}
    <p>
        <b>{$row.columns[0]|wash()}: 
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
        </b>
        {if and(is_set($row.columns[5]), $row.columns[5]|ne(''))}
    		<br /><small class="text-muted">Valido solo per prenotazioni negli orari compresi tra le {$row.columns[5]|explode('-')|implode(' e le ')}</small>
    	{/if}
        <br />
        <small>{$row.columns[1]|wash()}</small>        
    </p>
{/foreach}
{/let}
