<?php
require __DIR__ . '/vendor/autoload.php';

use OpenAI\Factory;

$apiKey = 'enter_key';

header('Content-Type: application/json');

if (!isset($_FILES['audio'])) {
    http_response_code(400);
    echo json_encode(['error' => 'no-file']);
    exit;
}

$srcPath = $_FILES['audio']['tmp_name'];
$tmpFile = sys_get_temp_dir() . '/' . uniqid('rec_', true) . '.webm';
move_uploaded_file($srcPath, $tmpFile);

try {
    $client = new Factory()->withApiKey($apiKey)->make();

    // 1. Распознаём
    $whisper = $client->audio()->transcribe([
        'model' => 'whisper-1',
        'file'  => fopen($tmpFile, 'r'),
    ]);
    $question = trim($whisper['text']);

    // 2. ChatGPT
    $chat = $client->chat()->create([
        'model'    => 'gpt-4o',
        'messages' => [
            ['role' => 'system', 'content' => 'Ты интервьюер, отвечай коротко, уточняющими вопросами.'],
            ['role' => 'user',   'content' => $question],
        ],
    ]);
    $answer = $chat['choices'][0]['message']['content'];

    foreach (glob(__DIR__ . '/reply_*.mp3') as $oldFile) {
        @unlink($oldFile);
    }

    // 3. TTS
    $voice = $client->audio()->speech([
        'model' => 'tts-1',
        'voice' => 'nova',
        'input' => $answer,
    ]);

    // 4. Сохраняем рядом (в src/)
    $replyFile = __DIR__ . '/' . uniqid('reply_', true) . '.mp3';
    file_put_contents($replyFile, $voice);

    // 5. Ответ
    echo json_encode([
        'text'     => $answer,
        'audioUrl' => basename($replyFile),
    ]);

} catch (Throwable $e) {
    http_response_code(500);
    echo json_encode(['error' => $e->getMessage()]);
} finally {
    @unlink($tmpFile);
}
