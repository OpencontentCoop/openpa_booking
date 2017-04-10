{let matrix=$attribute.content}
{foreach $matrix.rows.sequential as $row}
    <p>
        <b>{$row.columns[0]|wash()}: {if $row.columns[2]|eq(0)}gratuito{else}{$row.columns[2]|l10n( currency )}{/if}</b>
        <br />
        <small>{$row.columns[1]|wash()}</small>
    </p>
{/foreach}
{/let}
