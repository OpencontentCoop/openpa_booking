{if fetch(user, has_access_to, hash('module', 'openpa_booking', 'function', 'book') )}
{literal}
    <script id="tpl-prenotazione" type="text/x-jsrender">
    <div class="service_teaser vertical booking {{if !location_available || !stuff_available}}inverted{{/if}}">
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
            <ul class="list list-unstyled">
            {{if ~i18n(data,'abstract')}}
              {{:~i18n(data,'abstract')}}
            {{/if}}
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
    {{if location_available && stuff_available}}
        <div class="service_teaser vertical booking book-now">
            <div class="service_details clearfix">
                <a href="{{:~settings('accessPath')}}/openpa_booking/add/sala_pubblica/{{:metadata.id}}?start={{:currentRequest.from}}&end={{:currentRequest.to}}&stuff={{:currentRequest.stuff_id_list}}">
                    Prenota per <span class="booking_date">{{:currentRequest.date_formatted}}</span> <span class="booking_hours">dalle {{:currentRequest.from_hours_formatted}} alle {{:currentRequest.to_hours_formatted}}</span>
                    {{if currentRequest.has_stuff}}
                    <br /> <small>con
                    {{for currentRequest.stuff}}
                        {{:~i18n(metadata.name)}}
                    {{/for}}
                    </small>
                    {{/if}}
                </a>
            </div>
        </div>
    {{else location_busy_level == 0 || stuff_global_busy_level > -1}}
        <div class="service_teaser vertical booking book-try">
            <div class="service_details clearfix">
            {{if location_busy_level == 0}}
                <p>{{if location_bookings == 1}}C'è una prenotazione{{else}}Ci sono {{: location_bookings}} prenotazioni{{/if}} in attesa, puoi metterti in coda di prenotazione</p>
            {{/if}}
            {{if !stuff_available && stuff_global_busy_level > -1}}
                <p>L'attrezzatura è già stata richiesta in altre prenotazioni in attesa</p>
            {{/if}}

                <a class="btn btn-{{if location_busy_level == 0}}warning{{else}}success{{/if}} btn-block" href="{{:~settings('accessPath')}}/openpa_booking/add/sala_pubblica/{{:metadata.id}}?start={{:currentRequest.from}}&end={{:currentRequest.to}}&stuff={{if stuff_available}}{{:currentRequest.stuff_id_list}}{{/if}}">
                    {{if location_busy_level == 0}}Metti in coda una prenotazione {{else}} Prenota {{/if}}per <br /><span class="booking_date">{{:currentRequest.date_formatted}}</span> <span class="booking_hours">dalle {{:currentRequest.from_hours_formatted}} alle {{:currentRequest.to_hours_formatted}}</span>
                    {{if currentRequest.has_stuff && stuff_available}}
                    <br /> <small>con
                    {{for currentRequest.stuff}}
                        {{:~i18n(metadata.name)}}
                    {{/for}}
                    </small>
                    {{else currentRequest.has_stuff && !stuff_available}}
                    <br /><b>senza attrezzatura</b>
                    {{/if}}
                </a>
            </div>
        </div>
    {{else (!location_available && location_busy_level > 0) || (!stuff_available && stuff_global_busy_level > 0)}}
        <div class="service_teaser vertical booking book-none">
            <div class="service_details clearfix">
                {{if !location_available && location_busy_level > 0 }}
                    <p>Sala non disponibile per il giorno selezionato</p>
                {{/if}}
                {{if !stuff_available && stuff_global_busy_level > 0 }}
                    <p>Attrezzatura non disponibile per il giorno selezionato</p>
                {{/if}}
            </div>
        </div>
    {{/if}}
    </script>
{/literal}
{else}
{literal}
    <script id="tpl-prenotazione" type="text/x-jsrender">
    <div class="service_teaser vertical booking {{if !location_available || !stuff_available}}inverted{{/if}}">
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
            <ul class="list list-unstyled">
            {{if ~i18n(data,'abstract')}}
              {{:~i18n(data,'abstract')}}
            {{/if}}
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
    <div class="service_teaser vertical booking book-now">
        <div class="service_details clearfix">
            <a href="#login">
                Accedi per prenotare
            </a>
        </div>
    </div>
    </script>
{/literal}
{/if}
