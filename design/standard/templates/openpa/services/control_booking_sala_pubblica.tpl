<div class="oggetti-correlati" {if is_set( $view_parameters.error )}id="error"{/if}>
  <div class="border-header border-box box-trans-blue box-allegati-header">
    <div class="border-tl"><div class="border-tr"><div class="border-tc"></div></div></div>
    <div class="border-ml"><div class="border-mr"><div class="border-mc">
          <div class="border-content">
            <h2>Calendario prenotazioni sala</h2>
          </div>
        </div></div></div>
  </div>
  <div class="border-body border-box box-violet box-allegati-content">
    <div class="border-ml"><div class="border-mr"><div class="border-mc">
          <div class="border-content col">
            <div class="col-content"><div class="col-content-design">

                {if fetch( 'user', 'current_user' ).is_logged_in}
                
                  {def $colors = object_handler($node.object).control_booking_sala_pubblica.state_colors}
                  <div class="square-box-soft-gray float-break block">
                    <span style="display: inline-block; width: 10px; height: 10px;background: {$colors[3]}"></span> <small>Confermato</small>      
                    <span style="display: inline-block; width: 10px; height: 10px;background: {$colors[0]}"></span> <small>In attesa di approvazione</small>
                    <span style="display: inline-block; width: 10px; height: 10px;background: {$colors[1]}"></span> <small>In attesa di pagamento</small>
                    <span style="display: inline-block; width: 10px; height: 10px;background: {$colors[2]}"></span> <small>In attesa di verifica pagamento</small>
                    <span style="display: inline-block; width: 10px; height: 10px;background: {$colors[4]}"></span> <small>Rifiutato</small>
                    <span style="display: inline-block; width: 10px; height: 10px;background: {$colors['none']}"></span> <small>Non accessibile</small>
                  </div>
                  
                  {if is_set( $view_parameters.error )}
                  <div class="alert message-warning">
                    {$view_parameters.error|urldecode}
                  </div>
                  {/if}

                  {ezscript_require( array( 'fullcalendar/moment.min.js', 'jquery-1.7.1.js', 'fullcalendar/fullcalendar.js', 'fullcalendar/lang/it.js' ) )}
                  {ezcss_require( array( 'fullcalendar/fullcalendar.css' ) )}

                  {def $min_time = "00:00:00"
                       $max_time = "24:00:00"
                       $booking_url = concat( "openpa_booking/add/sala_pubblica/", $node.contentobject_id )}

                  {literal}
                    <script>
                      $(document).ready(function() {
                        $('#calendar').fullCalendar({
                          timezone: "local",
                          defaultView: "agendaWeek",
                          allDaySlot: false,
                          slotDuration: '00:60:00',
                          minTime: "{/literal}{$min_time}{literal}",
                          maxTime: "{/literal}{$max_time}{literal}",
                          header: {
                            left: 'prev,next today',
                            center: 'title',
                            right: 'month,agendaWeek,agendaDay'
                          },
                          events: { url: {/literal}{concat('openpa/data/booking_sala_pubblica?sala=', $node.contentobject_id)|ezurl()}{literal} },
                          selectable: true,
                          selectHelper: true,
                          select: function(start, end) {
                            window.location.href = "{/literal}{$booking_url|ezurl(no)}{literal}?start="+start.format( 'X' )+"&end="+end.format( 'X' );
                          },
                        });
                      });
                    </script>
                  {/literal}

                  <div id='calendar'></div>

                {else}

                  <p>Per prenotare l'utilizzo della sala pubblica {$node.name|wash()} devi essere autenticato nel sistema.</p>

                  <form method="post" action={"/user/login/"|ezurl} name="loginform">
                      <label for="id-{$block.id}-login">{"Username"|i18n("design/ezwebin/user/login",'User name')}</label><div class="labelbreak"></div>
                      <input class="halfbox" type="text" size="10" name="Login" id="id-{$block.id}-login" value="" tabindex="1" />

                      <label for="id-{$block.id}-password">{"Password"|i18n("design/ezwebin/user/login")}</label><div class="labelbreak"></div>
                      <input class="halfbox" type="password" size="10" name="Password" id="id-{$block.id}-password" value="" tabindex="2" />

                      <input class="defaultbutton" type="submit" name="LoginButton" value="{'Login'|i18n('design/ezwebin/user/login','Button')}" tabindex="3" />
                      <input class="button" type="submit" name="RegisterButton" id="RegisterButton" value="{'Sign up'|i18n('design/ezwebin/user/login','Button')}" tabindex="1" />

                      <input type="hidden" name="RedirectURI" value="{$node.url_alias}" />
                  </form>

                {/if}




              </div></div>
          </div>
        </div></div></div>
    <div class="border-bl"><div class="border-br"><div class="border-bc"></div></div></div>
  </div>
</div>