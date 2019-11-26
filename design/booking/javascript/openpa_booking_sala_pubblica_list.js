$(document).ready(function () {
        var tools = $.opendataTools;
        var mainQuery = tools.settings('mainQuery');
        var columns, columnDefs, datatable;

        var renderStatus = function (data, row) {
            var state = $.map(data, function (val, i) {
                if (val.indexOf('booking.') > -1) {
                    return val;
                }
            });
            var stateClass = state[0].replace('booking.', '');
            var stateName = $('li.state_filter.' + stateClass + ' a').text();
            var mainStatus = '<li><span class="label label-' + stateClass + '">' + stateName + '</span></li>';

            var subStatuses = [];
            if (tools.settings('stuff_sub_workflow_is_enabled')) {
                if (row.data[tools.settings('language')].stuff && row.data[tools.settings('language')].stuff.length) {
                    $.each(row.data[tools.settings('language')].stuff, function () {
                        var bookingStatus = this.extra.in_context && this.extra.in_context.booking_status ? this.extra.in_context.booking_status : 'default';
                        subStatuses.push(
                            '<li class="stuff-list"><small><span class="label label-' + bookingStatus + '"></span>' + this.name[tools.settings('language')] + '</small></li>'
                        );
                    });
                }
            }

            return '<ul class="list list-unstyled">' + mainStatus + subStatuses.join('') + '</ul>';
        };

        if (tools.settings('showLinkColumn')) {
            columns = [
                {'data': 'metadata.id', 'name': 'id', 'title': 'ID'},
                {'data': 'data.' + tools.settings('language') + '.sala', 'name': 'sala', 'title': 'Richiesta'},
                {'data': 'metadata.stateIdentifiers', 'name': 'state', 'title': 'Stato', 'sortable': false},
                {'data': 'metadata.published', 'name': 'published', 'title': 'Creata il'},
                {'data': 'metadata.ownerName', 'name': 'raw[meta_owner_name_t]', 'title': 'Autore'},
                {'data': 'data.' + tools.settings('language') + '.from_time', 'name': 'from_time', 'title': 'Periodo'},
                {'data': 'metadata.mainNodeId', 'name': 'id', 'title': '', 'sortable': false}
            ];
            columnDefs = [
                {
                    'render': function (data, type, row) {
                        return '<a href="' + tools.settings('accessPath') + '/openpa_booking/view/sala_pubblica/' + row.metadata.id + '"><span class="label label-default">' + data + '</span></a>';
                    },
                    'targets': [0]
                },
                {
                    'render': function (data, type, row) {
                        var contentData = row.data;
                        var i18nData = typeof contentData[tools.settings('language')] !== 'undefined' ? contentData[tools.settings('language')] : contentData[Object.keys(contentData)[0]];
                        if (i18nData.sala.length > 0) {
                            var sala = i18nData.sala[0].name;
                            return typeof sala[tools.settings('language')] !== 'undefined' ? sala[tools.settings('language')] : sala[Object.keys(sala.name[0])];
                        }
                        return '?';
                    },
                    'targets': [1]
                },
                {
                    'render': function (data, type, row) {
                        return renderStatus(data, row);
                    },
                    'targets': [2]
                },
                {
                    'render': function (data) {
                        return moment(new Date(data)).format('DD/MM/YYYY HH:mm');
                    },
                    'targets': [3]
                },
                {
                    'render': function (data) {
                        if (data === null) {
                            return '?';
                        }
                        return typeof data[tools.settings('language')] !== 'undefined' ? data[tools.settings('language')] : data[Object.keys(data)[0]];
                    },
                    'targets': [4]
                },
                {
                    'render': function (data, type, row) {
                        var contentData = row.data;
                        var from = typeof contentData[tools.settings('language')] !== 'undefined' ? contentData[tools.settings('language')].from_time : contentData[Object.keys(contentData)[0]].from_time;
                        var to = typeof contentData[tools.settings('language')] !== 'undefined' ? contentData[tools.settings('language')].to_time : contentData[Object.keys(contentData)[0]].to_time;
                        return moment(new Date(from)).format('DD/MM/YYYY HH:mm') + '-' + moment(new Date(to)).format('HH:mm');
                    },
                    'targets': [5]
                },
                {
                    'render': function (data, type, row) {
                        return '<a href="' + tools.settings('accessPath') + '/openpa_booking/view/sala_pubblica/' + row.metadata.id + '" class="btn btn-xs btn-default">Entra</a>';
                    },
                    'targets': [6]
                }
            ];
        } else {
            columns = [
                {'data': 'metadata.id', 'name': 'id', 'title': 'ID'},
                {'data': 'data.' + tools.settings('language') + '.sala', 'name': 'sala', 'title': 'Richiesta'},
                {'data': 'metadata.stateIdentifiers', 'name': 'state', 'title': 'Stato', 'sortable': false},
                {'data': 'metadata.published', 'name': 'published', 'title': 'Creata il'},
                {'data': 'metadata.ownerName', 'name': 'raw[meta_owner_name_t]', 'title': 'Autore'},
                {'data': 'data.' + tools.settings('language') + '.from_time', 'name': 'from_time', 'title': 'Periodo'},
            ];
            columnDefs = [
                {
                    'render': function (data) {
                        return data;
                    },
                    'targets': [0]
                },
                {
                    'render': function (data, type, row) {
                        var contentData = row.data;
                        var i18nData = typeof contentData[tools.settings('language')] !== 'undefined' ? contentData[tools.settings('language')] : contentData[Object.keys(contentData)[0]];
                        if (i18nData.sala.length > 0) {
                            var sala = i18nData.sala[0].name;
                            return typeof sala[tools.settings('language')] !== 'undefined' ? sala[tools.settings('language')] : sala[Object.keys(sala.name[0])];
                        }
                        return '?';
                    },
                    'targets': [1]
                },
                {
                    'render': function (data, type, row) {
                        return renderStatus(data, row);
                    },
                    'targets': [2]
                },
                {
                    'render': function (data) {
                        return moment(new Date(data)).format('DD/MM/YYYY HH:mm');
                    },
                    'targets': [3]
                },
                {
                    'render': function (data) {
                        if (data === null) {
                            return '?';
                        }
                        return typeof data[tools.settings('language')] !== 'undefined' ? data[tools.settings('language')] : data[Object.keys(data)[0]];
                    },
                    'targets': [4]
                },
                {
                    'render': function (data, type, row) {
                        var contentData = row.data;
                        var from = typeof contentData[tools.settings('language')] !== 'undefined' ? contentData[tools.settings('language')].from_time : contentData[Object.keys(contentData)[0]].from_time;
                        var to = typeof contentData[tools.settings('language')] !== 'undefined' ? contentData[tools.settings('language')].to_time : contentData[Object.keys(contentData)[0]].to_time;
                        return moment(new Date(from)).format('DD/MM/YYYY HH:mm') + '-' + moment(new Date(to)).format('HH:mm');
                    },
                    'targets': [5]
                }
            ];
        }

        datatable = $('.content-data').opendataDataTable({
            'builder': {
                'query': mainQuery
            },
            'datatable': {
                'dom': tools.settings('datatableDom'),
                'language': {
                    sEmptyTable: 'Nessun dato presente nella tabella',
                    sInfo: 'Vista da _START_ a _END_ di _TOTAL_ elementi',
                    sInfoEmpty: 'Vista da 0 a 0 di 0 elementi',
                    sInfoFiltered: '(filtrati da _MAX_ elementi totali)',
                    sInfoPostFix: '',
                    sInfoThousands: '.',
                    sLengthMenu: 'Visualizza _MENU_ elementi',
                    sLoadingRecords: 'Caricamento...',
                    sProcessing: 'Elaborazione...',
                    sSearch: 'Ricerca libera:',
                    sZeroRecords: 'La ricerca non ha portato alcun risultato.',
                    oPaginate: {
                        sFirst: 'Inizio',
                        sPrevious: 'Precedente',
                        sNext: 'Successivo',
                        sLast: 'Fine'
                    },
                    oAria: {
                        sSortAscending: ': attiva per ordinare la colonna in ordine crescente',
                        sSortDescending: ': attiva per ordinare la colonna in ordine decrescente'
                    }
                },
                'ajax': {
                    url: tools.settings('accessPath') + '/opendata/api/datatable/search/'
                },
                'order': [[0, 'desc']],
                'columns': columns,
                'columnDefs': columnDefs
            }
        }).on('xhr.dt', function (e, settings, json) {
            var query = datatable.buildQuery();
            var exportButton = $('#exportButton');
            if (exportButton.length > 0){
                exportButton.attr('href', exportButton.data('base_href')+'/'+query+' sort [id=>asc]');
            }
        }).data('opendataDataTable');

        var loadFilteredDatatable = function () {
            var States = $('#stateFilter').val() || [];
            if (States.length > 0) {
                datatable.settings.builder.filters['state'] = {
                    'field': 'state',
                    'operator': 'in',
                    'value': States
                };
            } else {
                datatable.settings.builder.filters['state'] = null;
            }

            var Circoscrizione = $('#circoscrizioneFilter option:selected');
            if (Circoscrizione.length > 0 && Circoscrizione.val().length > 0) {
                datatable.settings.builder.filters['circoscrizione'] = {
                    'field': 'sala.id',
                    'operator': 'in',
                    'value': Circoscrizione.val().split(',')
                };
            } else {
                datatable.settings.builder.filters['circoscrizione'] = null;
            }

            var Sala = $('#salaFilter option:selected');
            if (Sala.length > 0 && Sala.val().length > 0) {
                datatable.settings.builder.filters['sala'] = {
                    'field': 'sala.id',
                    'operator': 'in',
                    'value': [Sala.val()]
                };
            } else {
                datatable.settings.builder.filters['sala'] = null;
            }

            var BookingId = $('#idFilter').val();
            if (BookingId.length > 0) {
                datatable.settings.builder.filters['booking_id'] = {
                    'field': 'id',
                    'operator': 'in',
                    'value': [BookingId]
                };
            } else {
                datatable.settings.builder.filters['booking_id'] = null;
            }

            var BookingRangeFrom = moment($('#bookingFilterFrom').datepicker('getDate'));
            var BookingRangeTo = moment($('#bookingFilterTo').datepicker('getDate'));
            if (!BookingRangeFrom.isValid()) {
                BookingRangeFrom = '*';
            } else {
                BookingRangeFrom = BookingRangeFrom.set('hour', 0).set('minute', 0).format('YYYY-MM-DD HH:mm');
            }
            if (!BookingRangeTo.isValid()) {
                BookingRangeTo = '*';
            } else {
                BookingRangeTo = BookingRangeTo.set('hour', 23).set('minute', 59).format('YYYY-MM-DD HH:mm');
            }
            var BookingRange = [BookingRangeFrom, BookingRangeTo];
            if (BookingRange.length > 0) {
                datatable.settings.builder.filters['booking_range'] = {
                    'field': 'calendar[]',
                    'operator': '=',
                    'value': BookingRange
                };
            } else {
                datatable.settings.builder.filters['booking_range'] = null;
            }

            var PublicationRangeFrom = moment($('#publishedFilterFrom').datepicker('getDate'));
            var PublicationRangeTo = moment($('#publishedFilterTo').datepicker('getDate'));
            if (!PublicationRangeFrom.isValid()) {
                PublicationRangeFrom = '*';
            } else {
                PublicationRangeFrom = PublicationRangeFrom.set('hour', 0).set('minute', 0).format('YYYY-MM-DD HH:mm');
            }
            if (!PublicationRangeTo.isValid()) {
                PublicationRangeTo = '*';
            } else {
                PublicationRangeTo = PublicationRangeTo.set('hour', 23).set('minute', 59).format('YYYY-MM-DD HH:mm');
            }
            var PublicationRange = [PublicationRangeFrom, PublicationRangeTo];
            if (PublicationRange.length > 0) {
                datatable.settings.builder.filters['published_range'] = {
                    'field': 'published',
                    'operator': 'range',
                    'value': PublicationRange
                };
            } else {
                datatable.settings.builder.filters['published_range'] = null;
            }

            var SubRequest = $('#subRequestFilter').is(':checked');
            if (SubRequest){
                datatable.settings.builder.filters['subrequest'] = null;
            } else {
                datatable.settings.builder.filters['subrequest'] = {
                    'field': 'subrequest',
                    'operator': 'in',
                    'value': [0]
                };
            }

            datatable.loadDataTable();
        };

        $('.input-daterange input').each(function () {
            $(this).datepicker({language: 'it-IT'}).datepicker('clearDates').on('changeDate clearDate', function (e) {
                loadFilteredDatatable();
                e.preventDefault();
            });
        });

        $('#stateFilter').chosen({'width': '100%'}).on('change', function (e) {
            loadFilteredDatatable();
            e.preventDefault();
        });

        $('#circoscrizioneFilter').prop('selectedIndex', 0).on('change', function (e) {
            loadFilteredDatatable();
            e.preventDefault();
        });

        $('#salaFilter').prop('selectedIndex', 0).on('change', function (e) {
            loadFilteredDatatable();
            e.preventDefault();
        });

        $('#idFilter').val('').on('keyup', function (e) {
            if (this.value.length > 0 && this.value.length < 3) return;
            loadFilteredDatatable();
        });

        $('#subRequestFilter').prop('checked', '').on('change', function () {
            loadFilteredDatatable();
        });

        $('.spinner').hide();
        $('.content-list').show();
        loadFilteredDatatable();
    }
);
