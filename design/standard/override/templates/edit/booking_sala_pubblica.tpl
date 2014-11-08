<form enctype="multipart/form-data" method="post" action={concat("/content/edit/",$object.id,"/",$edit_version,"/",$edit_language|not|choose(concat($edit_language,"/"),''))|ezurl}>


  <h1>{$class.name|wash()}</h1>

  {include uri="design:content/edit_validation.tpl"}

  {include uri="design:content/booking/edit_attribute.tpl"}

  <div class="buttonblock">
    <input class="defaultbutton" type="submit" name="PublishButton" value="Invia richiesta di prenotazione" />
    <input class="button" type="submit" name="DiscardButton" value="Annulla richiesta" />
    <input type="hidden" name="RedirectIfDiscarded" value="{concat( 'content/view/full/', $object.current.main_parent_node_id )}" />
    <input type="hidden" name="RedirectURIAfterPublish" value="{concat( 'openpa_booking/view/sala_pubblica/', $object.id )}" />
    <input type="hidden" name="DiscardConfirm" value="0" />
  </div>

</form>