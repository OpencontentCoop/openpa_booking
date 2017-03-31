<section class="hgroup">
    <h1>
        {$node.name|wash()}
        {include name=edit uri='design:parts/toolbar/node_edit.tpl' current_node=$node redirect_if_discarded='/openpa_booking/locations' redirect_after_publish=concat('/openpa_booking/locations/',$node.node_id)  }
    </h1>
</section>

{if fetch(user, has_access_to, hash('module', 'openpa_booking', 'function', 'book') )}
<section class="hgroup">
    {include uri=$openpa.control_booking_sala_pubblica.template}
</section>
{/if}

<div class="lead">
    {include uri=$openpa.content_main.template}
</div>





