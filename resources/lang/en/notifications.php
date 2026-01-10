<?php

return [
    // Common
    'email_salutation' => 'Best regards, The e-Shop Team',

    // Order notifications
    'order_status_subject' => 'Order Status Updated - Order #:order_number',
    'order_status_greeting' => 'Hello :name!',
    'order_status_updated' => 'Your order status has been updated.',
    'order_number_label' => 'Order Number: :order_number',
    'new_status_label' => 'New Status: :status',
    'track_order_action' => 'Track Your Order',
    'thank_you_order' => 'Thank you for your order!',
    'order_status_message' => 'Order #:order_number status changed to :status',

    // Order created notifications
    'order_created_subject' => 'Order Confirmed - Order #:order_number',
    'order_confirmed_title' => 'Order Confirmed!',
    'order_created_greeting' => 'Hello :name!',
    'order_created_thank_you' => 'Thank you for your order! We have received your order and will begin processing it shortly.',
    'order_items_count' => 'Items in order: :count',
    'order_total_label' => 'Order Total: :total',
    'order_created_processing' => 'We will send you an email when your order ships.',
    'order_created_message' => 'Your order #:order_number has been placed successfully',
    'order_created_database_message' => 'Your order #:order_number has been placed successfully',
    'view_order_action' => 'View Order Details',
    'order_number' => 'Order Number',
    'order_date' => 'Order Date',
    'order_status' => 'Status',
    'order_total' => 'Total',
    'status_confirmed' => 'Confirmed',
    'status_processing' => 'Processing',
    'status_shipped' => 'Shipped',
    'status_delivered' => 'Delivered',
    'status_cancelled' => 'Cancelled',

    // Order shipped notifications
    'order_shipped_subject' => 'Your Order Has Shipped - Order #:order_number',
    'order_shipped_greeting' => 'Great news, :name!',
    'order_shipped_message' => 'Your order is on its way! It has been shipped and is heading to you.',
    'tracking_number_label' => 'Tracking Number: :tracking_number',
    'order_shipped_delivery' => 'You will receive your package soon. Use the tracking number above to follow your shipment.',
    'order_shipped_database_message' => 'Order #:order_number has been shipped',

    // Order delivered notifications
    'order_delivered_subject' => 'Your Order Has Been Delivered - Order #:order_number',
    'order_delivered_greeting' => 'Hello :name!',
    'order_delivered_message' => 'Your order has been delivered! We hope you love your purchase.',
    'order_delivered_enjoy' => 'Enjoy your items! If you have a moment, we\'d love to hear your feedback.',
    'leave_review_action' => 'Leave a Review',
    'order_delivered_thank_you' => 'Thank you for shopping with us!',
    'order_delivered_database_message' => 'Order #:order_number has been delivered',

    // Order cancelled notifications
    'order_cancelled_subject' => 'Order Cancelled - Order #:order_number',
    'order_cancelled_greeting' => 'Hello :name,',
    'order_cancelled_message' => 'We\'re sorry to inform you that your order has been cancelled.',
    'order_cancelled_reason' => 'Reason: :reason',
    'order_cancelled_refund_info' => 'If you were charged, a refund will be processed within 5-10 business days.',
    'contact_support_action' => 'Contact Support',
    'order_cancelled_apologies' => 'We apologize for any inconvenience this may have caused.',
    'order_cancelled_database_message' => 'Order #:order_number has been cancelled',

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
    'view' => 'View',
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
