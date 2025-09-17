<?php
function callLLM($prompt, $apiKey) {
    $url = "https://generativelanguage.googleapis.com/v1beta/models/gemini-2.0-flash:generateContent";
    $postData = ["contents"=>[["parts"=>[["text"=>$prompt]]]]];

    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        "Content-Type: application/json",
        "X-goog-api-key: $apiKey"
    ]);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($postData));
    curl_setopt($ch, CURLOPT_TIMEOUT, 8);

    $result = curl_exec($ch);
    curl_close($ch);

    $json = json_decode($result, true);
    if (!empty($json['candidates'][0]['content']['parts'])) {
        $text = "";
        foreach ($json['candidates'][0]['content']['parts'] as $p) $text .= $p['text'] ?? '';
        return trim($text);
    }
    return false;
}
