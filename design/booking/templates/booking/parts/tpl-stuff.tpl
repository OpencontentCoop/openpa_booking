{literal}
<script id="tpl-stuff" type="text/x-jsrender">
<div class="col-md-4">
    <div class="service_teaser vertical">
        <div class="service_photo">
          <figure style="background-image:url({{if ~mainImageUrl(data)}}{{:~mainImageUrl(data)}}){{else}}{/literal}{social_pagedata().logo_path|ezroot(no)}{literal});background-size:contain{{/if}}"></figure>
        </div>
        <div class="service_details clearfix">
            <h2 class="section_header skincolored noborder">
                <a href="{{:~settings('accessPath')}}/openpa_booking/locations/{{:metadata.mainNodeId}}">
                    <b>{{:~i18n(metadata.name)}}</b>
                </a>
            </h2>
            {{if ~i18n(data,'abstract')}}
              {{:~i18n(data,'abstract')}}
            {{/if}}
        </div>
    </div>
</div>
</script>
{/literal}
