<?php

return [
    // Common
    'email_salutation' => 'С уважением, Команда e-Shop',

    // Order notifications
    'order_status_subject' => 'Статус заказа обновлён - Заказ #:order_number',
    'order_status_greeting' => 'Здравствуйте, :name!',
    'order_status_updated' => 'Статус вашего заказа был обновлён.',
    'order_number_label' => 'Номер заказа: :order_number',
    'new_status_label' => 'Новый статус: :status',
    'track_order_action' => 'Отследить заказ',
    'thank_you_order' => 'Спасибо за ваш заказ!',
    'order_status_message' => 'Статус заказа #:order_number изменён на :status',

    // Order created notifications
    'order_created_subject' => 'Заказ подтверждён - Заказ #:order_number',
    'order_confirmed_title' => 'Заказ подтверждён!',
    'order_created_greeting' => 'Здравствуйте, :name!',
    'order_created_thank_you' => 'Спасибо за ваш заказ! Мы получили ваш заказ и скоро начнём его обработку.',
    'order_items_count' => 'Товаров в заказе: :count',
    'order_total_label' => 'Сумма заказа: :total',
    'order_created_processing' => 'Мы отправим вам письмо, когда ваш заказ будет отправлен.',
    'order_created_message' => 'Ваш заказ #:order_number успешно оформлен',
    'order_created_database_message' => 'Ваш заказ #:order_number успешно оформлен',
    'view_order_action' => 'Посмотреть заказ',
    'order_number' => 'Номер заказа',
    'order_date' => 'Дата заказа',
    'order_status' => 'Статус',
    'order_total' => 'Сумма',
    'status_confirmed' => 'Подтверждён',
    'status_processing' => 'В обработке',
    'status_shipped' => 'Отправлен',
    'status_delivered' => 'Доставлен',
    'status_cancelled' => 'Отменён',

    // Order shipped notifications
    'order_shipped_subject' => 'Ваш заказ отправлен - Заказ #:order_number',
    'order_shipped_greeting' => 'Отличные новости, :name!',
    'order_shipped_message' => 'Ваш заказ уже в пути! Он был отправлен и скоро будет у вас.',
    'tracking_number_label' => 'Номер отслеживания: :tracking_number',
    'order_shipped_delivery' => 'Скоро вы получите свою посылку. Используйте номер отслеживания выше, чтобы следить за доставкой.',
    'order_shipped_database_message' => 'Заказ #:order_number был отправлен',

    // Order delivered notifications
    'order_delivered_subject' => 'Ваш заказ доставлен - Заказ #:order_number',
    'order_delivered_greeting' => 'Здравствуйте, :name!',
    'order_delivered_message' => 'Ваш заказ был доставлен! Надеемся, вам понравится ваша покупка.',
    'order_delivered_enjoy' => 'Наслаждайтесь вашими покупками! Если у вас есть минутка, мы будем рады услышать ваш отзыв.',
    'leave_review_action' => 'Оставить отзыв',
    'order_delivered_thank_you' => 'Спасибо, что выбрали нас!',
    'order_delivered_database_message' => 'Заказ #:order_number был доставлен',

    // Order cancelled notifications
    'order_cancelled_subject' => 'Заказ отменён - Заказ #:order_number',
    'order_cancelled_greeting' => 'Здравствуйте, :name,',
    'order_cancelled_message' => 'К сожалению, ваш заказ был отменён.',
    'order_cancelled_reason' => 'Причина: :reason',
    'order_cancelled_refund_info' => 'Если с вас была списана оплата, возврат будет обработан в течение 5-10 рабочих дней.',
    'contact_support_action' => 'Связаться с поддержкой',
    'order_cancelled_apologies' => 'Приносим извинения за доставленные неудобства.',
    'order_cancelled_database_message' => 'Заказ #:order_number был отменён',

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
