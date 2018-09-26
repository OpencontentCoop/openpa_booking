{literal}
<script id="tpl-location" type="text/x-jsrender">
<div class="col-md-4">
    <div class="service_teaser vertical">
        <div class="service_photo">
          <figure style="background-image:url({{if ~mainImageUrl(data)}}{{:~mainImageUrl(data)}}){{else}}{/literal}{social_pagedata().logo_path|ezroot(no)}{literal});background-size:contain{{/if}}"></figure>
        </div>
        <div class="service_details clearfix">
            <h2 class="section_header skincolored noborder">
                <a href="{{:~settings('accessPath')}}/openpa_booking/locations/{{:metadata.mainNodeId}}">
                    <b>{{:~i18n(data,'titolo')}}</b>
                </a>
            </h2>
            <!--<p>
                <a class="btn btn-xs btn-default" href="{{:~settings('accessPath')}}/openpa_booking/locations/{{:metadata.mainNodeId}}">
                    <i class="fa fa-calendar"></i> Vai al calendario
                </a>
            </p>-->            
            {{if ~i18n(data,'abstract')}}
              {{:~i18n(data,'abstract')}}
            {{/if}}
            <ul class="list list-unstyled">
            {{if ~i18n(data,'telefono')}}
              <li><small><i class="fa fa-phone-square"></i> <span>{{:~i18n(data,'telefono')}}</span></small></li>
            {{/if}}
            {{if ~i18n(data,'fax')}}
              <li><small><i class="fa fa-fax"></i> <span>{{:~i18n(data,'fax')}}</span></small></li>
            {{/if}}
            {{if ~i18n(data,'email')}}
              <li><small><i class="fa fa-envelope-o"></i> <span>{{:~i18n(data,'email')}}</span></small></li>
            {{/if}}
            {{if ~i18n(data,'numero_posti')}}
              <li><small><i class="fa fa-group"></i> <span>{{:~i18n(data,'numero_posti')}}</span></small></li>
            {{/if}}
            {{if ~i18n(data,'dimensione')}}
              <li><small><i class="fa fa-cube"></i> <span>{{:~i18n(data,'dimensione')}}</span></small></li>
            {{/if}}
            {{if ~i18n(data,'dotazioni_tecniche')}}
              <li><small><i class="fa fa-gears"></i> <span>{{:~i18n(data,'dotazioni_tecniche')}}</span></small></li>
            {{/if}}
            </ul>
        </div>
    </div>
</div>
</script>
{/literal}
