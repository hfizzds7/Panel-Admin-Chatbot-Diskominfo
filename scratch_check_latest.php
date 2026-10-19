<?php

require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\KnowledgeBase;

echo "=== 15 DATA TERAKHIR DI KNOWLEDGE_BASES ===\n";
$items = KnowledgeBase::orderBy('id', 'desc')->take(15)->get();
foreach ($items as $it) {
    echo "ID: {$it->id} | Cluster: [{$it->cluster}] | Topic: {$it->topic}\n";
    echo "  URL: {$it->url}\n";
    echo "  Created: {$it->created_at}\n";
}

