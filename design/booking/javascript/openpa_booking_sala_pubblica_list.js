$(document).ready(function () {
    var tools = $.opendataTools;

    var mainQuery = 'classes [prenotazione_sala] and subrequest = 0 and facets [sala.name,stuff.name]';
    //var facets = [
    //    {field: 'categoria', 'limit': 300, 'sort': 'alpha', name: 'Categoria'},
    //    {field: 'argomento.name', 'limit': 300, 'sort': 'alpha', name: 'Argomento'}
    //];

    //mainQuery += ' facets ['+tools.buildFacetsString(facets)+']';
    var facets = [];

    var datatable;

    /**
     * Inizialiazzaione di OpendataDataTable (wrapper di jquery datatable)
     */
    datatable = $('.content-data').opendataDataTable({
            "builder": {
                "query": mainQuery
            },
            "datatable": {
                "language": {
                    sEmptyTable: "Nessun dato presente nella tabella",
                    sInfo: "Vista da _START_ a _END_ di _TOTAL_ elementi",
                    sInfoEmpty: "Vista da 0 a 0 di 0 elementi",
                    sInfoFiltered: "(filtrati da _MAX_ elementi totali)",
                    sInfoPostFix: "",
                    sInfoThousands: ".",
                    sLengthMenu: "Visualizza _MENU_ elementi",
                    sLoadingRecords: "Caricamento...",
                    sProcessing: "Elaborazione...",
                    sSearch: "Cerca:",
                    sZeroRecords: "La ricerca non ha portato alcun risultato.",
                    oPaginate: {
                        sFirst: "Inizio",
                        sPrevious: "Precedente",
                        sNext: "Successivo",
                        sLast: "Fine"
                    },
                    oAria: {
                        sSortAscending: ": attiva per ordinare la colonna in ordine crescente",
                        sSortDescending: ": attiva per ordinare la colonna in ordine decrescente"
                    }
                },
                "ajax": {
                    url: tools.settings('accessPath') + "/opendata/api/datatable/search/"
                },
                "order": [[2, "desc"]],
                "columns": [
                    {"data": "metadata.id", "name": 'id', "title": 'ID'},
                    {"data": "metadata.stateIdentifiers", "name": 'state', "title": 'Stato', "sortable": false},
                    {"data": "metadata.published", "name": 'published', "title": 'Creata il'},
                    {"data": "metadata.ownerName", "name": 'raw[meta_owner_name_t]', "title": 'Autore'},
                    {"data": "data." + tools.settings('language') + ".from_time", "name": 'from_time', "title": 'Periodo'},
                    {"data": "data." + tools.settings('language') + ".sala", "name": 'sala', "title": 'Luogo'}
                ],
                "columnDefs": [
                    {
                        "render": function (data, type, row) {
                            return '<a href="' + tools.settings('accessPath') + '/openpa_booking/view/sala_pubblica/' + row.metadata.id + '"><span class="label label-primary">' + data + '</span></a>';
                        },
                        "targets": [0]
                    },
                    {
                        "render": function (data, type, row) {
                            var state = $.map(data, function (val, i) {
                                if (val.indexOf("booking.") > -1) {
                                    return val;
                                }
                            });
                            var stateClass = state[0].replace("booking.", "");
                            var stateName = $('.nav-pills li.' + stateClass + ' a').text();
                            return '<span class="label label-' + stateClass + '">' + stateName + '</span>';
                        },
                        "targets": [1]
                    },
                    {
                        "render": function (data, type, row) {
                            return moment(new Date(data)).format('DD/MM/YYYY HH:mm');
                        },
                        "targets": [2]
                    },
                    {
                        "render": function (data, type, row) {
                            return typeof data[tools.settings('language')] != 'undefined' ? data[tools.settings('language')] : data[Object.keys(data)[0]];
                        },
                        "targets": [3]
                    },
                    {
                        "render": function (data, type, row) {
                            var contentData = row.data;
                            var from = typeof contentData[tools.settings('language')] != 'undefined' ? contentData[tools.settings('language')].from_time : contentData[Object.keys(contentData)[0]].from_time;
                            var to = typeof contentData[tools.settings('language')] != 'undefined' ? contentData[tools.settings('language')].to_time : contentData[Object.keys(contentData)[0]].to_time;
                            return moment(new Date(from)).format('DD/MM/YYYY HH:mm') + '-' + moment(new Date(to)).format('HH:mm');
                        },
                        "targets": [4]
                    },
                    {
                        "render": function (data, type, row) {
                            var contentData = row.data;
                            var i18nData = typeof contentData[tools.settings('language')] != 'undefined' ? contentData[tools.settings('language')] : contentData[Object.keys(contentData)[0]];
                            if (i18nData.sala.length > 0) {
                                var sala = i18nData.sala[0].name;
                                return typeof sala[tools.settings('language')] != 'undefined' ? sala[tools.settings('language')] : sala[Object.keys(sala.name[0])];
                            }
                            return '?';
                        },
                        "targets": [5]
                    }
                ]
            },
            "loadDatatableCallback": function (self) {
                var input = $('.dataTables_filter input');
                input.unbind().attr('placeholder', 'Premi invio per cercare');
                input.bind('keyup', function (e) {
                    if (e.keyCode == 13) {
                        self.datatable.search(this.value).draw();
                    }
                });
            }
        })
        .on('xhr.dt', function (e, settings, json, xhr) {

            $.each(json.facets, function (index, val) {
                // aggiorna le select delle faccette in base al risultato (json)
                var facet = this;
                tools.refreshFilterInput(facet, function (select) {
                    select.trigger("chosen:updated");
                });
            });
        })
        .data('opendataDataTable');


    var loadFilteredDatatable = function () {
        var States = [];
        $('li.state_filter.active').each(function () {
            States.push($(this).data('state'));
        });
        if (States.length > 0) {
            datatable.settings.builder.filters['state'] = {
                'field': 'state',
                'operator': 'in',
                'value': States
            };
        } else {
            $('li.state_filter').addClass('active');
            datatable.settings.builder.filters['state'] = null;
        }
        datatable.loadDataTable();
    };

    $('li.state_filter a').on('click', function (e) {
        if (!e.shiftKey) {
            $('li.state_filter').removeClass('active');
        }
        $(this).parent().toggleClass('active');
        loadFilteredDatatable();
        e.preventDefault();
    });

    tools.find(mainQuery + ' limit 1', function (response) {
        $('.spinner').hide();
        $('.content-main').show();

        loadFilteredDatatable();

        var form = $('<form class="form-inline">');
        $.each(response.facets, function () {
            tools.buildFilterInput(facets, this, datatable, function (selectContainer) {
                form.append(selectContainer);
            });
        });

        $('.nav-section').append(form).show();
    });

});
