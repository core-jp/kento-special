<?php

declare(strict_types=1);

const SERVER_NAME = 'kento-special';
const FALLBACK_PROTOCOL_VERSION = '2025-06-18';
const MAX_REQUEST_BYTES = 65536;

function send_json(array $payload, int $status = 200): never
{
    http_response_code($status);
    header('Content-Type: application/json; charset=utf-8');
    header('Cache-Control: no-store');
    echo json_encode($payload, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR);
    exit;
}

function send_empty(int $status): never
{
    http_response_code($status);
    header('Cache-Control: no-store');
    exit;
}

function json_rpc_error(mixed $id, int $code, string $message, int $httpStatus = 200): never
{
    send_json([
        'jsonrpc' => '2.0',
        'id' => $id,
        'error' => [
            'code' => $code,
            'message' => $message,
        ],
    ], $httpStatus);
}

function is_allowed_origin(string $origin): bool
{
    if ($origin === '') {
        return true;
    }

    $host = parse_url($origin, PHP_URL_HOST);
    if (!is_string($host)) {
        return false;
    }

    $host = strtolower($host);
    return $host === 'chatgpt.com'
        || str_ends_with($host, '.chatgpt.com')
        || $host === 'openai.com'
        || str_ends_with($host, '.openai.com');
}

function load_rules(): array
{
    $rulesPath = __DIR__ . '/rules/kento-special.md';
    $versionPath = __DIR__ . '/rules/version.txt';
    $instructions = file_get_contents($rulesPath);
    $version = file_get_contents($versionPath);

    if ($instructions === false || $version === false) {
        throw new RuntimeException('検討手順を読み込めません。');
    }

    return [
        'version' => trim($version),
        'instructions' => trim($instructions),
        'updatedAt' => gmdate('c', (int) filemtime($rulesPath)),
    ];
}

function result_response(mixed $id, array $result): never
{
    send_json([
        'jsonrpc' => '2.0',
        'id' => $id,
        'result' => $result,
    ]);
}

$origin = $_SERVER['HTTP_ORIGIN'] ?? '';
if (!is_allowed_origin($origin)) {
    send_json(['error' => 'Origin not allowed'], 403);
}

if ($origin !== '') {
    header('Access-Control-Allow-Origin: ' . $origin);
    header('Vary: Origin');
}
header('Access-Control-Allow-Methods: POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Accept, MCP-Protocol-Version, Mcp-Method, Mcp-Name');

$httpMethod = $_SERVER['REQUEST_METHOD'] ?? 'GET';
if ($httpMethod === 'OPTIONS') {
    send_empty(204);
}

if ($httpMethod !== 'POST') {
    header('Allow: POST, OPTIONS');
    send_json([
        'service' => SERVER_NAME,
        'message' => 'MCP requests must use HTTP POST.',
    ], 405);
}

$contentLength = (int) ($_SERVER['CONTENT_LENGTH'] ?? 0);
if ($contentLength > MAX_REQUEST_BYTES) {
    send_json(['error' => 'Request body too large'], 413);
}

$rawBody = file_get_contents('php://input', false, null, 0, MAX_REQUEST_BYTES + 1);
if ($rawBody === false || strlen($rawBody) > MAX_REQUEST_BYTES) {
    send_json(['error' => 'Request body too large'], 413);
}

try {
    $request = json_decode($rawBody, true, 32, JSON_THROW_ON_ERROR);
} catch (JsonException) {
    json_rpc_error(null, -32700, 'Parse error', 400);
}

if (!is_array($request) || array_is_list($request)) {
    json_rpc_error(null, -32600, 'Invalid Request', 400);
}

$id = $request['id'] ?? null;
$method = $request['method'] ?? null;
$params = $request['params'] ?? [];

if (!is_string($method) || !is_array($params)) {
    json_rpc_error($id, -32600, 'Invalid Request', 400);
}

if ($method === 'notifications/initialized') {
    send_empty(202);
}

try {
    switch ($method) {
        case 'initialize':
            $requestedVersion = $params['protocolVersion'] ?? FALLBACK_PROTOCOL_VERSION;
            $protocolVersion = is_string($requestedVersion) && $requestedVersion !== ''
                ? $requestedVersion
                : FALLBACK_PROTOCOL_VERSION;
            result_response($id, [
                'protocolVersion' => $protocolVersion,
                'capabilities' => [
                    'tools' => ['listChanged' => false],
                ],
                'serverInfo' => [
                    'name' => SERVER_NAME,
                    'version' => trim((string) file_get_contents(__DIR__ . '/rules/version.txt')),
                ],
                'instructions' => '「検討スペシャル」が検討方法として指定された場合に、最新版の検討手順を取得してください。',
            ]);

        case 'ping':
            result_response($id, []);

        case 'tools/list':
            result_response($id, [
                'tools' => [[
                    'name' => 'get_kento_instructions',
                    'title' => '検討スペシャルの最新版を取得',
                    'description' => '依頼文で「検討スペシャル」が現在の検討方法として指定されたとき、回答を始める前に最新版の検討手順を取得します。',
                    'inputSchema' => [
                        'type' => 'object',
                        'properties' => (object) [],
                        'additionalProperties' => false,
                    ],
                    'outputSchema' => [
                        'type' => 'object',
                        'properties' => [
                            'version' => ['type' => 'string'],
                            'instructions' => ['type' => 'string'],
                            'updatedAt' => ['type' => 'string'],
                        ],
                        'required' => ['version', 'instructions', 'updatedAt'],
                        'additionalProperties' => false,
                    ],
                    'annotations' => [
                        'readOnlyHint' => true,
                        'destructiveHint' => false,
                        'idempotentHint' => true,
                        'openWorldHint' => false,
                    ],
                ]],
            ]);

        case 'tools/call':
            if (($params['name'] ?? null) !== 'get_kento_instructions') {
                json_rpc_error($id, -32602, 'Unknown tool');
            }
            $rules = load_rules();
            result_response($id, [
                'content' => [[
                    'type' => 'text',
                    'text' => $rules['instructions'],
                ]],
                'structuredContent' => $rules,
                'isError' => false,
            ]);

        case 'resources/list':
            result_response($id, ['resources' => []]);

        case 'prompts/list':
            result_response($id, ['prompts' => []]);

        default:
            json_rpc_error($id, -32601, 'Method not found');
    }
} catch (Throwable) {
    json_rpc_error($id, -32603, 'Internal error', 500);
}
