<?php
/**
 * Vortexa AI Backend Processor
 * Author: Moaz Sahaheen
 * Purpose: Handles AI requests and Web Search Integration
 */

header('Content-Type: application/json');

// --- إعدادات مفاتيح الـ API (ضع مفاتيحك هنا قبل الاستخدام) ---
$serper_api_key = "YOUR_SERPER_API_KEY_HERE";
$groq_api_key   = "YOUR_GROQ_API_KEY_HERE";

$user_query = $_POST['query'] ?? '';
$chat_history = json_decode($_POST['history'] ?? '[]', true);

if (empty($user_query)) {
    echo json_encode(["answer" => "الرجاء إدخال سؤال صالح."]);
    exit;
}

/**
 * دالة إرسال الطلبات الخارجية
 */
function fetchData($url, $headers, $payload) {
    $curl = curl_init($url);
    curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($curl, CURLOPT_POST, true);
    curl_setopt($curl, CURLOPT_POSTFIELDS, json_encode($payload));
    curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);
    curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($curl, CURLOPT_TIMEOUT, 20);
    $response = curl_exec($curl);
    curl_close($curl);
    return json_decode($response, true);
}

// 1. استرجاع بيانات البحث (اختياري لتحسين الدقة)
$search_context = "";
if ($serper_api_key !== "YOUR_SERPER_API_KEY_HERE") {
    $search_results = fetchData("https://google.serper.dev/search", 
        ["X-API-KEY: $serper_api_key", "Content-Type: application/json"], 
        ["q" => $user_query, "num" => 1]
    );
    $search_context = $search_results['organic'][0]['snippet'] ?? "";
}

// 2. معالجة الرد عبر Groq AI
$messages = [
    ["role" => "system", "content" => "أنت Vortexa AI، مساعد تقني ذكي. استخدم المعلومات التالية إذا كانت مفيدة: $search_context"]
];
foreach($chat_history as $msg) { $messages[] = $msg; }
$messages[] = ["role" => "user", "content" => $user_query];

$ai_output = fetchData("https://api.groq.com/openai/v1/chat/completions", 
    ["Authorization: Bearer $groq_api_key", "Content-Type: application/json"], 
    [
        "model" => "gemma2-9b-it", // نموذج سريع ومتوافق
        "messages" => $messages,
        "temperature" => 0.7
    ]
);

$final_text = $ai_output['choices'][0]['message']['content'] ?? "حدث خطأ في استجابة الموديل، يرجى التحقق من API Key.";

echo json_encode(["answer" => $final_text]);

