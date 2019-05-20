<?php

$eZTemplateOperatorArray[] = array(
    'script' => 'extension/openpa_booking/autoloads/OpenPABookingOperators.php',
    'class' => 'OpenPABookingOperators',
    'operator_names' => array(
        'booking_states',
        'booking_state_colors',
        'booking_root_node',
        'location_node_id',
        'location_class_identifiers',
        'stuff_node_id',
        'stuff_class_identifiers',
        'stuff_sub_workflow_is_enabled',
        'openpa_agenda_link',
        'booking_vat_type_list', // deprecated
        'booking_is_in_range', // deprecated
        'booking_calc_price', // deprecated
        'booking_range_list',
        'booking_request_invoice',
        'booking_view_list',
        'booking_default_view',
        'booking_stuff_is_enabled',
    )
);
