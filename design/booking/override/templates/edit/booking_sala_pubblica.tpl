<form enctype="multipart/form-data" method="post" action={concat("/content/edit/",$object.id,"/",$edit_version,"/",$edit_language|not|choose(concat($edit_language,"/"),''))|ezurl}>
      <h1 class="text-center">
          {$class.name|wash()} {$object.data_map.sala.content.name|wash()}
          <br />
          per il {$object.data_map.from_time.content.timestamp|l10n(shortdate)}
          dalle {$object.data_map.from_time.content.timestamp|l10n(shorttime)}
          alle {$object.data_map.to_time.content.timestamp|l10n(shorttime)}
      </h1>
      {include uri="design:content/edit_validation.tpl"}
      {include uri="design:booking/edit_attribute.tpl"}
</form>

