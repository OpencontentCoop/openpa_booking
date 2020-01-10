{def $sala = $content_object.data_map.sala.content}

<div class="global-view-full">
    <section class="hgroup">
        <h1 class="text-center">
            Cancellazione prenotazione <a href="{concat('openpa_booking/view/sala_pubblica/',$content_object.id)|ezurl(no,full)}" class="label label-default">{$content_object.id}</a>
            {if $parent_object}
                <br/><a href="{concat('openpa_booking/view/sala_pubblica/',$parent_object.id)|ezurl(no,full)}">
                <small>Prenotazione principale <span class="label label-default">{$parent_object.id}</span></small>
            </a>
            {/if}
        </h1>
    </section>

    <div class="row">
        <div class="col-md-12">
            <div class="panel panel-info" style="font-size: 1.3em;">
                <table class="table">
                    <tr>
                        <th>{'Richiesta'|i18n('booking')}</th>
                        <td colspan="2">
                            <a href="{concat('openpa_booking/locations/', $sala.main_node_id)|ezurl(no)}">{$sala.name|wash()}</a>
                        </td>
                    </tr>
                    <tr>
                        <th>{'Data'|i18n('booking')}</th>
                        <th>{'Inizio'|i18n('booking')}</th>
                        <th>{'Termine'|i18n('booking')}</th>
                    </tr>
                    <tr>
                        <td>{$content_object.data_map.from_time.content.timestamp|l10n(date)}</td>
                        <td>{$content_object.data_map.from_time.content.timestamp|l10n(shorttime)}</td>
                        <td>{$content_object.data_map.to_time.content.timestamp|l10n(shorttime)}</td>
                    </tr>
                </table>
            </div>

            <div class="text-center" style="font-size: 1.5em;margin: 40px 0">
                {if is_set($error)}
                    <div class="alert alert-warning">{$error|wash}</div>
                {/if}
                <form method="post" action="{concat('openpa_booking/trash/sala_pubblica/',$content_object.id)|ezurl(no,full)}">
                    <input type="submit" class="btn btn-lg btn-success" value="Conferma la cancellazione della prenotazione" name="ConfirmButton"/>
                    <a href="{concat('openpa_booking/view/sala_pubblica/',$content_object.id)|ezurl(no,full)}" class="btn btn-lg btn-danger">Annulla</a>
                </form>
            </div>
        </div>

    </div>

</div>