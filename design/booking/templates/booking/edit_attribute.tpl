{ezscript_require( array(
    'ezjsc::jquery',
    'ezjsc::jqueryUI',
    'jquery.opendataTools.js',
    'moment-with-locales.min.js',
    'moment-timezone-with-data.js',
    'jquery.opendataSearchView.js',
    'jquery.timepicker.js',
    'jsrender.js'
))}

{ezcss_require( array(
    'jquery.timepicker.css'
))}

<script>
    {def $current_language=ezini('RegionalSettings', 'Locale')}
    {def $moment_language = $current_language|explode('-')[1]|downcase()}

    moment.locale('{$moment_language}');
    moment.tz.setDefault("Europe/Rome");
    $.opendataTools.settings('endpoint',{ldelim}
        'search': '{'/openpa/data/booking_sala_pubblica/(availability)/search/'|ezurl(no,full)}/'
        {rdelim});
    $.opendataTools.settings('accessPath', "{''|ezurl(no,full)}");
    $.opendataTools.settings('language', "{$current_language}");
    $.opendataTools.settings('locale', "{$moment_language}");
</script>

{default $view_parameters            = array()
         $attribute_categorys        = ezini( 'ClassAttributeSettings', 'CategoryList', 'content.ini' )
         $attribute_default_category = ezini( 'ClassAttributeSettings', 'DefaultCategory', 'content.ini' )}

{def $count = 0}
{foreach $content_attributes_grouped_data_map as $attribute_group => $content_attributes_grouped}
  {if $attribute_group|ne('hidden')}
    {set $count = $count|inc()}
  {/if}
{/foreach}

{if $count|gt(1)}
  {set $count = 0}
  <ul class="nav nav-tabs">
  {set $count = 0}
  {foreach $content_attributes_grouped_data_map as $attribute_group => $content_attributes_grouped}
    {if $attribute_group|ne('hidden')}
      <li class="{if $count|eq(0)} active{/if}">
          <a data-toggle="tab" href="#attribute-group-{$attribute_group}">{$attribute_categorys[$attribute_group]}</a>
      </li>
      {set $count = $count|inc()}
    {/if}
  {/foreach}
      <li class="pull-right">
          <a data-toggle="tab" class="btn btn-danger" href="#repeat">Aggiungi date</a>
      </li>
  </ul>
{/if}

<div class="tab-content">
{set $count = 0}
{foreach $content_attributes_grouped_data_map as $attribute_group => $content_attributes_grouped}
  <div class="clearfix attribute-edit tab-pane{if $count|eq(0)} active{/if}" id="attribute-group-{$attribute_group}">
    {set $count = $count|inc()}

    {foreach $content_attributes_grouped as $attribute_identifier => $attribute}
      {def $contentclass_attribute = $attribute.contentclass_attribute}
      <div class="row edit-row ezcca-edit-datatype-{$attribute.data_type_string} ezcca-edit-{$attribute_identifier}">
        {if $is_translating_content|not()}
            {if $attribute.display_info.edit.grouped_input}
                <div class="col-md-3">
                    <p{if $attribute.has_validation_error} class="message-error"{/if}>
                        <b>{first_set( $contentclass_attribute.nameList[$content_language], $contentclass_attribute.name )|wash}</b>
                        {if $attribute.is_required} <span class="required" title="{'required'|i18n( 'design/admin/content/edit_attribute' )}">*</span>{/if}
                        {if $attribute.is_information_collector} <span class="collector">({'information collector'|i18n( 'design/admin/content/edit_attribute' )})</span>{/if}
                    </p>
                </div>
                <div class="col-md-9">
                    {if $contentclass_attribute.description} <span class="classattribute-description">{first_set( $contentclass_attribute.descriptionList[$content_language], $contentclass_attribute.description)|wash}</span>{/if}
                    {attribute_edit_gui attribute_base=$attribute_base attribute=$attribute view_parameters=$view_parameters html_class='form-control'}
                    <input type="hidden" name="ContentObjectAttribute_id[]" value="{$attribute.id}" />
                </div>
            {else}
                <div class="col-md-3">
                    <p{if $attribute.has_validation_error} class="message-error"{/if}>
                        <b>{first_set( $contentclass_attribute.nameList[$content_language], $contentclass_attribute.name )|wash}</b>
                        {if $attribute.is_required} <span class="required" title="{'required'|i18n( 'design/admin/content/edit_attribute' )}">*</span>{/if}
                        {if $attribute.is_information_collector} <span class="collector">({'information collector'|i18n( 'design/admin/content/edit_attribute' )})</span>{/if}
                    </p>
                </div>
                <div class="col-md-9">
                    {if $contentclass_attribute.description} <span class="classattribute-description">{first_set( $contentclass_attribute.descriptionList[$content_language], $contentclass_attribute.description)|wash}</span>{/if}
                    {attribute_edit_gui attribute_base=$attribute_base attribute=$attribute view_parameters=$view_parameters html_class='form-control'}
                    <input type="hidden" name="ContentObjectAttribute_id[]" value="{$attribute.id}" />
                </div>
            {/if}
        {/if}
      </div>
      {undef $contentclass_attribute}
    {/foreach}
  </div>
{/foreach}
    <div class="clearfix attribute-edit tab-pane" id="repeat">
        <div class="row edit-row">
            <div class="col-md-12">
                {include uri='design:booking/parts/scheduler.tpl'}
            </div>
        </div>
    </div>
</div>

<div class="buttonblock">
  <input class="btn btn-success btn-lg pull-right" type="submit" name="PublishButton" value="Invia richiesta di prenotazione" />
  <input class="btn btn-default btn-lg pull-left" type="submit" name="DiscardButton" value="Annulla richiesta" />
  <input type="hidden" name="RedirectIfDiscarded" value="/" />
  <input type="hidden" name="RedirectURIAfterPublish" value="{concat( 'openpa_booking/view/sala_pubblica/', $object.id )}" />
  <input type="hidden" name="DiscardConfirm" value="0" />
</div>
