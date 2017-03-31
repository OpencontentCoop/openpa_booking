<form enctype="multipart/form-data" method="post" class="edit-booking"
      action={concat("/content/edit/",$object.id,"/",$edit_version,"/",$edit_language|not|choose(concat($edit_language,"/"),''))|ezurl}>
    <section class="hgroup">
        <h1 class="text-center">
            {$class.name|wash()} {$object.data_map.sala.content.name|wash()}
        </h1>
        <h1 class="text-center">
            per {$object.data_map.from_time.content.timestamp|l10n(date)}
            <br />
            dalle ore {$object.data_map.from_time.content.timestamp|l10n(shorttime)}
            alle ore {$object.data_map.to_time.content.timestamp|l10n(shorttime)}
        </h1>
    </section>
    <div class="row">
        <div class="col-md-8 col-md-offset-4">
            {include uri="design:content/edit_validation.tpl"}
        </div>
    </div>
    {include uri="design:booking/edit_attribute.tpl"}
</form>

