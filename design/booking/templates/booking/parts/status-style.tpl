{def $state_colors = booking_state_colors()}

<style>
    {foreach $state_colors as $identifier => $color}
    .nav-pills>li.{$identifier}>a, .nav-pills>li.{$identifier}>a:hover, .nav-pills>li.{$identifier}>a:focus,.label-{$identifier}{ldelim}background-color:{$color};color:#fff{rdelim}
    {/foreach}
    .label-pending{ldelim}background-color:{$state_colors['in_attesa_di_approvazione']};color:#fff{rdelim}
    .label-approved{ldelim}background-color:{$state_colors['confermato']};color:#fff{rdelim}
    .label-denied{ldelim}background-color:{$state_colors['rifiutato']};color:#fff{rdelim}
    .label-expired{ldelim}background-color:{$state_colors['scaduto']};color:#fff{rdelim}
    .stuff-list span.label{ldelim}width: 10px;  display: inline-block;  height: 10px;  padding: 0;  margin-right: 4px;{rdelim}
</style>

{undef $state_colors}
