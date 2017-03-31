<section class="content-view-full class-{$node.class_identifier} row">

    <section class="hgroup">
        <h1>
            {$node.name|wash()}
            {include name=edit uri='design:parts/toolbar/node_edit.tpl' current_node=$node}
        </h1>
    </section>

    {include uri=$openpa.content_main.template}

    {include uri=$openpa.content_contacts.template}

    {include uri=$openpa.content_detail.template}


</section>
