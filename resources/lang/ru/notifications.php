<?php

return [
    // Order notifications
    'order_status_subject' => 'Статус заказа обновлён - Заказ #:order_number',
    'order_status_greeting' => 'Здравствуйте, :name!',
    'order_status_updated' => 'Статус вашего заказа был обновлён.',
    'order_number_label' => 'Номер заказа: :order_number',
    'new_status_label' => 'Новый статус: :status',
    'track_order_action' => 'Отследить заказ',
    'thank_you_order' => 'Спасибо за ваш заказ!',
    'order_status_message' => 'Статус заказа #:order_number изменён на :status',

    // Ticket notifications
    'ticket_created_subject' => 'Создан новый тикет поддержки',
    'ticket_created_message' => 'Новый тикет поддержки: ":subject"',
    'ticket_status_subject' => 'Статус тикета #:id обновлён',
    'ticket_status_greeting' => 'Здравствуйте, :name!',
    'ticket_status_updated' => 'Статус вашего тикета поддержки был обновлён.',
    'ticket_label' => 'Тикет: :subject',
    'new_status' => 'Новый статус: :status',
    'view_ticket' => 'Посмотреть тикет',
    'thank_you_patience' => 'Благодарим за терпение!',
    'ticket_status_message' => 'Статус тикета #:id изменён на :status',

    // Ticket reply notifications
    'ticket_reply_support' => 'Поддержка ответила на ваш тикет: ":subject"',
    'ticket_reply_user' => 'Новый ответ в тикете: ":subject"',

    // Import notifications
    'import_subject' => 'Импорт завершён: :status',
    'import_greeting' => 'Импорт завершён',
    'import_status' => 'Статус: :status',
    'import_processed' => 'Обработано: :count',
    'import_failed' => 'Ошибок: :count',
    'view_imports' => 'Посмотреть импорты',
    'import_failed_notice' => 'Есть строки с ошибками. Скачайте CSV с ошибками на странице импортов в админке.',

    // Notifications page UI
    'title' => 'Уведомления',
    'no_notifications' => 'Нет уведомлений',
    'empty_message' => 'Вы в курсе всего! Проверьте позже, чтобы увидеть новые уведомления.',
    'empty_subtitle' => 'Нет новых обновлений',
    'showing_count' => ':count уведомлений',
    'mark_read' => 'Отметить как прочитанное',
    'mark_all_read' => 'Отметить все как прочитанные',
    'delete' => 'Удалить',
    'delete_all' => 'Удалить все',
    'view_order' => 'Посмотреть заказ',    'view' => 'Посмотреть',    'browse_products' => 'Смотреть товары',
    'no_message' => 'Сообщение отсутствует',

    // Filters
    'filter_all' => 'Все',
    'filter_tickets' => 'Тикеты',
    'filter_orders' => 'Заказы',

    // Types
    'type_notification' => 'Уведомление',
    'type_ticket' => 'Тикет',
    'type_order' => 'Заказ',
    'type_import' => 'Импорт',
    'type_refund' => 'Возврат',

    // Confirmations
    'confirm_delete' => 'Вы уверены, что хотите удалить это уведомление?',
    'confirm_delete_all' => 'Вы уверены, что хотите удалить все уведомления? Это действие нельзя отменить.',

    // Errors
    'error_mark_read' => 'Не удалось отметить уведомление как прочитанное',
    'error_delete' => 'Не удалось удалить уведомление',
    'error_mark_all' => 'Не удалось отметить все как прочитанные',
    'error_delete_all' => 'Не удалось удалить все уведомления',
];
