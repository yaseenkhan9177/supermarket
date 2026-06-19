<?php
$url = "http://127.0.0.1:8000/refunds/search-invoice?q=CS-2026-0099";
$ch = curl_init($url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
// Add session cookie if needed, but for now let's see if it works without auth (it might redirect to login)
// Actually, these routes are protected. I need to simulate a logged-in request or use a route that bypasses auth for testing, 
// OR simpler: use `php artisan tinker` to call the controller method directly.

// Let's use tinker to call the controller method directly to avoid auth headers complexity in script.
echo "Testing via Controller Method directly...\n";
$request = \Illuminate\Http\Request::create('/refunds/search-invoice', 'GET', ['q' => 'CS-2026-0099']);
$controller = new \App\Http\Controllers\Store\RefundController();
$response = $controller->searchInvoice($request);
echo "Status: " . $response->getStatusCode() . "\n";
echo "Content: " . $response->getContent() . "\n";

echo "\nTesting Partial Search 'CS'...\n";
$request2 = \Illuminate\Http\Request::create('/refunds/search-invoice', 'GET', ['q' => 'CS']);
$response2 = $controller->searchInvoice($request2);
echo "Content (CS): " . substr($response2->getContent(), 0, 500) . "...\n";
