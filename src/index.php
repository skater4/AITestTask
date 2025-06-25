<?php

require __DIR__ . '/vendor/autoload.php';

use OpenAI\Factory;

$apiKey = 'secret_key';

$client = (new Factory())->withApiKey($apiKey)->make();

// Путь к файлу
$audioFile = __DIR__ . '/testVoice.mp3';

if (!file_exists($audioFile)) {
    fwrite(STDERR, "Файл не найден: $audioFile\n");
    exit(1);
}

// 1. Распознаём текст
$response = $client->audio()->transcribe([
    'model' => 'whisper-1',
    'file' => fopen($audioFile, 'r'),
]);

$text = trim($response['text']);
echo "Текст: $text\n";

// 2. Отправка в ChatGPT (роль интервьюера)
$chat = $client->chat()->create([
    'model' => 'gpt-3.5-turbo',
    'messages' => [
        ['role' => 'system', 'content' => 'Ты интервьюер, отвечай коротко, уточняющими вопросами.'],
        ['role' => 'user', 'content' => $text],
    ],
]);

$reply = $chat['choices'][0]['message']['content'];
echo "Ответ: $reply\n";

// 3. Озвучка ответа
$audio = $client->audio()->speech([
    'model' => 'tts-1',
    'voice' => 'nova',
    'input' => $reply,
]);

file_put_contents(__DIR__ . '/reply.mp3', $audio);
echo "\nФайл reply.mp3 сохранён.\n";
