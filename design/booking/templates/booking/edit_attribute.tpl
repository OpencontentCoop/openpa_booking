{ezscript_require( array(
    'ezjsc::jquery',
    'ezjsc::jqueryUI',
    'jquery.opendataTools.js',
    'moment-with-locales.min.js',
    'moment-timezone-with-data.js',
    'jquery.opendataSearchView.js',
    'jquery.timepicker.js',
    'datepicker-it.js',
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

{def $no_stuff = cond( $object.data_map.stuff.has_content, false(), true())}

{def $count = 2}
{foreach $content_attributes_grouped_data_map as $attribute_group => $content_attributes_grouped}
<div class="row edit-group-row" id="attribute-group-{$attribute_group}" {if or( $attribute_group|eq('hidden'), and($no_stuff, $attribute_group|eq('booking_stuff')))}style="display: none" {/if}>
    <div class="col-md-4">
        <div class="edit-group-label">
            <span class="group-number">&#931{$count};</span>
            <span class="group-label">
                {$attribute_categorys[$attribute_group]}
            </span>
        </div>
    </div>
    <div class="col-md-8 service_teaser vertical">
        {foreach $content_attributes_grouped as $attribute_identifier => $attribute}

            {def $contentclass_attribute = $attribute.contentclass_attribute}
            <div class="edit-row ezcca-edit-datatype-{$attribute.data_type_string} ezcca-edit-{$attribute_identifier}"
                    {if and($attribute_identifier|eq('range_user'), $object.data_map.sala.content.data_map.price_range.has_content|not())}style="display: none"{/if}>

                {if $is_translating_content|not()}
                    <p{if $attribute.has_validation_error} class="message-error"{/if}>
                        <b>
                            {first_set( $contentclass_attribute.nameList[$content_language], $contentclass_attribute.name )|wash}
                            {if $attribute.is_required}
                                <span class="required" title="{'required'|i18n( 'design/admin/content/edit_attribute' )}">*</span>
                            {/if}
                            {if $contentclass_attribute.description}
                                <small class="attribute-description">{first_set( $contentclass_attribute.descriptionList[$content_language], $contentclass_attribute.description)|wash}</small>
                            {/if}
                        </b>
                    </p>
                    {attribute_edit_gui attribute_base=$attribute_base attribute=$attribute view_parameters=$view_parameters html_class='form-control'}
                {/if}

            </div>
            {undef $contentclass_attribute}
        {/foreach}
    </div>
</div>
    {if or( $attribute_group|eq('hidden'), and($no_stuff, $attribute_group|eq('booking_stuff')))|not()}{set $count = $count|inc()}{/if}
{/foreach}

<div class="row edit-group-row" id="repeat">
    <div class="col-md-4">
        <div class="edit-group-label">
            <span class="group-number">&#931{$count};</span>
            <span class="group-label">
                {'Aggiungi date'|i18n('booking')}
            </span>
        </div>
    </div>
    <div class="col-md-8 service_teaser vertical">
        <div class="edit-row">
            <p>
                <b>
                    {"Vuoi aggiungere alla prenotazione altre date, allo stesso orario?"|i18n('booking')}
                </b>
            </p>
            {include uri='design:booking/parts/scheduler.tpl'}
        </div>
    </div>
</div>

<div class="row">
    <div class="col-md-8 col-md-offset-4">
        <div class="buttonblock">
            <input class="btn btn-success btn-lg pull-right" type="submit" name="PublishButton" value="{"Invia richiesta di prenotazione"|i18n('booking')}" />
            <input class="btn btn-default btn-lg pull-left" type="submit" name="DiscardButton" value="{"Annulla richiesta"|i18n('booking')}" />
            <input type="hidden" name="RedirectIfDiscarded" value="/" />
            <input type="hidden" name="RedirectURIAfterPublish" value="{concat( 'openpa_booking/view/sala_pubblica/', $object.id )}" />
            <input type="hidden" name="DiscardConfirm" value="0" />
        </div>
    </div>
</div>
