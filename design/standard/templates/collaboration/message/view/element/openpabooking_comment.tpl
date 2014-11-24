<tr class="{$sequence}">
  <td>
    {if $is_read|not}<b>{/if}{"Posted: %1"|i18n('design/admin/collaboration/view/element/ezapprove_comment',,array($item.created|l10n(shortdatetime)))}{if $is_read|not}</b>{/if}
  </td>
  <td>
    {if $is_read|not}<b>[new]</b>{/if}
  </td>

  <td rowspan="2" valign="top">
    {$item_link.participant.participant.contentobject.name|wash()}
  </td>
</tr>
<tr class="{$sequence}">
  <td colspan="2">
    {$item.data_text1|wash}
  </td>
</tr>
