<section class="content-view-full class-{$node.class_identifier} row">

    <section class="hgroup">
        <h1>{$node.name|wash()}</h1>
    </section>

    {if $show_left}
        {include uri='design:openpa/full/parts/section_left.tpl'}
    {/if}

    {include uri=$openpa.content_main.template}

    {include uri=$openpa.content_contacts.template}


</section>
