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


{foreach $content_attributes_grouped_data_map as $attribute_group => $content_attributes_grouped}
<div class="row" id="attribute-group-{$attribute_group}" {if $attribute_group|eq('hidden')}style="display: none" {/if}>
    <div class="col-md-4">
        
    </div>
    <div class="col-md-8 service_teaser vertical">
        <div class="service_details clearfix">
            {foreach $content_attributes_grouped as $attribute_identifier => $attribute}
                {def $contentclass_attribute = $attribute.contentclass_attribute}
                <div class="edit-row ezcca-edit-datatype-{$attribute.data_type_string} ezcca-edit-{$attribute_identifier}">
                    {if $is_translating_content|not()}
                        {if $attribute.display_info.edit.grouped_input}
                            <p{if $attribute.has_validation_error} class="message-error"{/if}>
                                <b>{first_set( $contentclass_attribute.nameList[$content_language], $contentclass_attribute.name )|wash}</b>
                                {if $attribute.is_required}
                                <span class="required" title="{'required'|i18n( 'design/admin/content/edit_attribute' )}">*</span>
                                {/if}
                                {if $attribute.is_information_collector}
                                    <span class="collector">({'information collector'|i18n( 'design/admin/content/edit_attribute' )})</span>
                                {/if}
                            </p>
                            {if $contentclass_attribute.description}
                                <span class="classattribute-description">{first_set( $contentclass_attribute.descriptionList[$content_language], $contentclass_attribute.description)|wash}</span>
                            {/if}
                            {attribute_edit_gui attribute_base=$attribute_base attribute=$attribute view_parameters=$view_parameters html_class='form-control'}
                            <input type="hidden" name="ContentObjectAttribute_id[]" value="{$attribute.id}"/>
                        {else}
                            <p{if $attribute.has_validation_error} class="message-error"{/if}>
                                <b>{first_set( $contentclass_attribute.nameList[$content_language], $contentclass_attribute.name )|wash}</b>
                                {if $attribute.is_required}
                                    <span class="required" title="{'required'|i18n( 'design/admin/content/edit_attribute' )}">*</span>
                                {/if}
                                {if $attribute.is_information_collector}
                                    <span class="collector">({'information collector'|i18n( 'design/admin/content/edit_attribute' )})</span>
                                {/if}
                            </p>
                            {if $contentclass_attribute.description}
                                <span class="classattribute-description">{first_set( $contentclass_attribute.descriptionList[$content_language], $contentclass_attribute.description)|wash}</span>
                            {/if}
                            {attribute_edit_gui attribute_base=$attribute_base attribute=$attribute view_parameters=$view_parameters html_class='form-control'}
                            <input type="hidden" name="ContentObjectAttribute_id[]" value="{$attribute.id}"/>
                        {/if}
                    {/if}
                </div>
                {undef $contentclass_attribute}
            {/foreach}
        </div>
    </div>
</div>
{/if}
{/foreach}
<div class="service_teaser vertical">
    <div class="service_details clearfix">
        <div class="clearfix attribute-edit" id="repeat">
            <div class="row edit-row">
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
