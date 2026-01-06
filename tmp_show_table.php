<?php

require __DIR__.'/vendor/autoload.php';
$app = require __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();
$results = DB::select('SHOW CREATE TABLE cart_items');
foreach ($results as $row) {
    print_r((array) $row);
}
