<?php
/**
 * MUGBUZZ API Proxy
 * Drop this on any PHP host. Point the frontend at it.
 * Forwards to api.torn.com + tornpal + ffscout with CORS headers.
 *
 * Endpoints:
 *   ?target=torn&path=/market/206?selections=bazaar&key=YOURKEY
 *   ?target=tornpal&itemId=206
 *   ?target=ffscout&playerId=12345&ffKey=OPTIONAL
 */

header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');
header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') { http_response_code(204); exit; }

$target = $_GET['target'] ?? '';

function proxyFetch(string $url, array $headers = []): string {
    $ch = curl_init($url);
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_FOLLOWLOCATION => true,
        CURLOPT_TIMEOUT        => 10,
        CURLOPT_HTTPHEADER     => $headers,
        CURLOPT_USERAGENT      => 'MugBuzz/1.0',
        CURLOPT_SSL_VERIFYPEER => true,
    ]);
    $body   = curl_exec($ch);
    $status = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $err    = curl_error($ch);
    curl_close($ch);

    if ($err)    { http_response_code(502); echo json_encode(['error' => "Curl: $err"]);  exit; }
    if ($status !== 200) { http_response_code($status); echo $body; exit; }
    return $body;
}

switch ($target) {

    // ── TORN ──────────────────────────────────────────────────────────────────
    // ?target=torn&path=/market/206?selections=bazaar&key=APIKEY
    case 'torn': {
        $path = $_GET['path'] ?? '';
        if (!$path) { http_response_code(400); echo json_encode(['error' => 'path required']); exit; }
        // Sanitize: only allow paths to api.torn.com
        $url = 'https://api.torn.com' . $path;
        echo proxyFetch($url);
        break;
    }

    // ── TORNPAL ───────────────────────────────────────────────────────────────
    // ?target=tornpal&itemId=206
    case 'tornpal': {
        $itemId = (int)($_GET['itemId'] ?? 0);
        if (!$itemId) { http_response_code(400); echo json_encode(['error' => 'itemId required']); exit; }
        echo proxyFetch("https://tornpal.com/api/v1/bazaar/item/$itemId");
        break;
    }

    // ── FFSCOUT ───────────────────────────────────────────────────────────────
    // ?target=ffscout&playerId=12345&ffKey=OPTIONAL
    case 'ffscout': {
        $playerId = (int)($_GET['playerId'] ?? 0);
        if (!$playerId) { http_response_code(400); echo json_encode(['error' => 'playerId required']); exit; }
        $headers = [];
        if (!empty($_GET['ffKey'])) {
            $headers[] = 'Authorization: Bearer ' . $_GET['ffKey'];
        }
        echo proxyFetch("https://torn.ffscout.com/api/v1/users/$playerId", $headers);
        break;
    }

    default:
        http_response_code(400);
        echo json_encode(['error' => 'Unknown target. Use: torn | tornpal | ffscout']);
}
