<?php

return [
    // Order notifications
    'order_status_subject' => 'Order Status Updated - Order #:order_number',
    'order_status_greeting' => 'Hello :name!',
    'order_status_updated' => 'Your order status has been updated.',
    'order_number_label' => 'Order Number: :order_number',
    'new_status_label' => 'New Status: :status',
    'track_order_action' => 'Track Your Order',
    'thank_you_order' => 'Thank you for your order!',

    // Ticket notifications
    'ticket_created_subject' => 'New Support Ticket Created',
    'ticket_created_message' => 'New support ticket: ":subject"',
    'ticket_status_subject' => 'Ticket #:id Status Updated',
    'ticket_status_greeting' => 'Hello :name!',
    'ticket_status_updated' => 'The status of your support ticket has been updated.',
    'ticket_label' => 'Ticket: :subject',
    'new_status' => 'New Status: :status',
    'view_ticket' => 'View Ticket',
    'thank_you_patience' => 'Thank you for your patience!',
    'ticket_status_message' => 'Ticket #:id status changed to :status',

    // Ticket reply notifications
    'ticket_reply_support' => 'Support replied to your ticket: ":subject"',
    'ticket_reply_user' => 'New reply in ticket: ":subject"',

    // Import notifications
    'import_subject' => 'Import finished: :status',
    'import_greeting' => 'Import finished',
    'import_status' => 'Status: :status',
    'import_processed' => 'Processed: :count',
    'import_failed' => 'Failed: :count',
    'view_imports' => 'View imports',
    'import_failed_notice' => 'There are failed rows. Download the failed CSV from the admin imports page.',

    // Notifications page UI
    'title' => 'Notifications',
    'no_notifications' => 'No Notifications',
    'empty_message' => 'You\'re all caught up! Check back later for new notifications.',
    'empty_subtitle' => 'No new updates',
    'showing_count' => ':count notifications',
    'mark_read' => 'Mark as read',
    'mark_all_read' => 'Mark all as read',
    'delete' => 'Delete',
    'delete_all' => 'Delete all',
    'view_order' => 'View Order',
    'browse_products' => 'Browse Products',
    'no_message' => 'No message available',

    // Filters
    'filter_all' => 'All',
    'filter_tickets' => 'Tickets',
    'filter_orders' => 'Orders',

    // Types
    'type_notification' => 'Notification',
    'type_ticket' => 'Ticket',
    'type_order' => 'Order',
    'type_import' => 'Import',
    'type_refund' => 'Refund',

    // Confirmations
    'confirm_delete' => 'Are you sure you want to delete this notification?',
    'confirm_delete_all' => 'Are you sure you want to delete all notifications? This action cannot be undone.',

    // Errors
    'error_mark_read' => 'Failed to mark notification as read',
    'error_delete' => 'Failed to delete notification',
    'error_mark_all' => 'Failed to mark all as read',
    'error_delete_all' => 'Failed to delete all notifications',
];
