<?php

return [
    // Common
    'email_salutation' => 'Ar cieņu, e-Shop komanda',

    // Order notifications
    'order_status_subject' => 'Pasūtījuma statuss atjaunināts - Pasūtījums #:order_number',
    'order_status_greeting' => 'Sveiki, :name!',
    'order_status_updated' => 'Jūsu pasūtījuma statuss ir atjaunināts.',
    'order_number_label' => 'Pasūtījuma numurs: :order_number',
    'new_status_label' => 'Jaunais statuss: :status',
    'track_order_action' => 'Izsekot pasūtījumu',
    'thank_you_order' => 'Paldies par jūsu pasūtījumu!',
    'order_status_message' => 'Pasūtījuma #:order_number statuss mainīts uz :status',

    // Order created notifications
    'order_created_subject' => 'Pasūtījums apstiprināts - Pasūtījums #:order_number',
    'order_confirmed_title' => 'Pasūtījums apstiprināts!',
    'order_created_greeting' => 'Sveiki, :name!',
    'order_created_thank_you' => 'Paldies par jūsu pasūtījumu! Mēs esam saņēmuši jūsu pasūtījumu un drīzumā sāksim to apstrādāt.',
    'order_items_count' => 'Preces pasūtījumā: :count',
    'order_total_label' => 'Pasūtījuma summa: :total',
    'order_created_processing' => 'Mēs nosūtīsim jums e-pastu, kad jūsu pasūtījums tiks nosūtīts.',
    'order_created_message' => 'Jūsu pasūtījums #:order_number ir veiksmīgi noformēts',
    'order_created_database_message' => 'Jūsu pasūtījums #:order_number ir veiksmīgi noformēts',
    'view_order_action' => 'Skatīt pasūtījumu',
    'order_number' => 'Pasūtījuma numurs',
    'order_date' => 'Pasūtījuma datums',
    'order_status' => 'Statuss',
    'order_total' => 'Summa',
    'status_confirmed' => 'Apstiprināts',
    'status_processing' => 'Apstrādē',
    'status_shipped' => 'Nosūtīts',
    'status_delivered' => 'Piegādāts',
    'status_cancelled' => 'Atcelts',

    // Order shipped notifications
    'order_shipped_subject' => 'Jūsu pasūtījums ir nosūtīts - Pasūtījums #:order_number',
    'order_shipped_greeting' => 'Lieliskas ziņas, :name!',
    'order_shipped_message' => 'Jūsu pasūtījums ir ceļā! Tas ir nosūtīts un drīz būs pie jums.',
    'tracking_number_label' => 'Izsekošanas numurs: :tracking_number',
    'order_shipped_delivery' => 'Drīzumā jūs saņemsiet savu sūtījumu. Izmantojiet iepriekš norādīto izsekošanas numuru, lai sekotu līdzi sūtījumam.',
    'order_shipped_database_message' => 'Pasūtījums #:order_number ir nosūtīts',

    // Order delivered notifications
    'order_delivered_subject' => 'Jūsu pasūtījums ir piegādāts - Pasūtījums #:order_number',
    'order_delivered_greeting' => 'Sveiki, :name!',
    'order_delivered_message' => 'Jūsu pasūtījums ir piegādāts! Ceram, ka jums patiks jūsu pirkums.',
    'order_delivered_enjoy' => 'Izbaudiet savus pirkumus! Ja jums ir brīdis, mēs labprāt uzklausītu jūsu atsauksmi.',
    'leave_review_action' => 'Atstāt atsauksmi',
    'order_delivered_thank_you' => 'Paldies, ka izvēlējāties mūs!',
    'order_delivered_database_message' => 'Pasūtījums #:order_number ir piegādāts',

    // Order cancelled notifications
    'order_cancelled_subject' => 'Pasūtījums atcelts - Pasūtījums #:order_number',
    'order_cancelled_greeting' => 'Sveiki, :name,',
    'order_cancelled_message' => 'Diemžēl jūsu pasūtījums ir atcelts.',
    'order_cancelled_reason' => 'Iemesls: :reason',
    'order_cancelled_refund_info' => 'Ja jums tika noņemta maksa, atmaksa tiks apstrādāta 5-10 darba dienu laikā.',
    'contact_support_action' => 'Sazināties ar atbalstu',
    'order_cancelled_apologies' => 'Atvainojamies par sagādātajām neērtībām.',
    'order_cancelled_database_message' => 'Pasūtījums #:order_number ir atcelts',

    // Ticket notifications
    'ticket_created_subject' => 'Izveidots jauns atbalsta pieteikums',
    'ticket_created_message' => 'Jauns atbalsta pieteikums: ":subject"',
    'ticket_status_subject' => 'Pieteikuma #:id statuss atjaunināts',
    'ticket_status_greeting' => 'Sveiki, :name!',
    'ticket_status_updated' => 'Jūsu atbalsta pieteikuma statuss ir atjaunināts.',
    'ticket_label' => 'Pieteikums: :subject',
    'new_status' => 'Jaunais statuss: :status',
    'view_ticket' => 'Skatīt pieteikumu',
    'thank_you_patience' => 'Paldies par jūsu pacietību!',
    'ticket_status_message' => 'Pieteikuma #:id statuss mainīts uz :status',

    // Ticket reply notifications
    'ticket_reply_support' => 'Atbalsts atbildēja uz jūsu pieteikumu: ":subject"',
    'ticket_reply_user' => 'Jauna atbilde pieteikumā: ":subject"',

    // Import notifications
    'import_subject' => 'Imports pabeigts: :status',
    'import_greeting' => 'Imports pabeigts',
    'import_status' => 'Statuss: :status',
    'import_processed' => 'Apstrādāts: :count',
    'import_failed' => 'Neizdevās: :count',
    'view_imports' => 'Skatīt importus',
    'import_failed_notice' => 'Ir neveiksmīgas rindas. Lejupielādējiet neveiksmīgo CSV no admin importu lapas.',

    // Notifications page UI
    'title' => 'Paziņojumi',
    'no_notifications' => 'Nav paziņojumu',
    'empty_message' => 'Jūs esat informēts! Atgriezieties vēlāk, lai apskatītu jaunus paziņojumus.',
    'empty_subtitle' => 'Nav jaunu atjauninājumu',
    'showing_count' => ':count paziņojumi',
    'mark_read' => 'Atzīmēt kā izlasītu',
    'mark_all_read' => 'Atzīmēt visus kā izlasītus',
    'delete' => 'Dzēst',
    'delete_all' => 'Dzēst visus',
    'view_order' => 'Skatīt pasūtījumu',
    'view' => 'Skatīt',
    'browse_products' => 'Pārlūkot produktus',
    'no_message' => 'Ziņojums nav pieejams',

    // Filters
    'filter_all' => 'Visi',
    'filter_tickets' => 'Pieteikumi',
    'filter_orders' => 'Pasūtījumi',

    // Types
    'type_notification' => 'Paziņojums',
    'type_ticket' => 'Pieteikums',
    'type_order' => 'Pasūtījums',
    'type_import' => 'Imports',
    'type_refund' => 'Atmaksa',

    // Confirmations
    'confirm_delete' => 'Vai tiešām vēlaties dzēst šo paziņojumu?',
    'confirm_delete_all' => 'Vai tiešām vēlaties dzēst visus paziņojumus? Šo darbību nevar atsaukt.',

    // Errors
    'error_mark_read' => 'Neizdevās atzīmēt paziņojumu kā izlasītu',
    'error_delete' => 'Neizdevās dzēst paziņojumu',
    'error_mark_all' => 'Neizdevās atzīmēt visus kā izlasītus',
    'error_delete_all' => 'Neizdevās dzēst visus paziņojumus',
];
