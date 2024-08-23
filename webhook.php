<?php
// Line Bot API Token
$access_token = '#############';

// Google Drive API Settings
$folder_id = '###############';
$api_key = '##################';

// Function to get a specific image from Google Drive folder by name
function getImageFromDriveByName($folder_id, $api_key, $image_name) {
    $url = "https://www.googleapis.com/drive/v3/files?q='" . $folder_id . "'+in+parents&key=" . $api_key . "&fields=files(id,name,webContentLink)";
    $response = file_get_contents($url);
    $data = json_decode($response, true);
    if (isset($data['files']) && count($data['files']) > 0) {
        foreach ($data['files'] as $file) {
            if ($file['name'] == $image_name) {
                $file_url = $file['webContentLink'];
                $file_url = str_replace("&export=download", "", $file_url); // Remove export=download to directly access the image
                return $file_url;
            }
        }
    }
    return null;
}

// Function to reply message to Line Bot
function replyMessage($replyToken, $message) {
    global $access_token;
    $url = 'https://api.line.me/v2/bot/message/reply';
    $data = [
        'replyToken' => $replyToken,
        'messages' => [$message]
    ];
    $post = json_encode($data);
    $headers = [
        'Content-Type: application/json',
        'Authorization: Bearer ' . $access_token
    ];

    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $post);
    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
    $result = curl_exec($ch);
    curl_close($ch);

    return $result;
}

// Function to start loading animation
function startLoadingAnimation($userId, $seconds) {
    global $access_token;
    $url = 'https://api.line.me/v2/bot/chat/loading/start';
    $data = [
        'chatId' => $userId,
        'loadingSeconds' => $seconds
    ];
    $post = json_encode($data);
    $headers = [
        'Content-Type: application/json',
        'Authorization: Bearer ' . $access_token
    ];

    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $post);
    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
    $result = curl_exec($ch);
    curl_close($ch);

    return $result;
}

// Get POST body content
$content = file_get_contents('php://input');
$events = json_decode($content, true);

// Process each event
if (!is_null($events['events'])) {
    foreach ($events['events'] as $event) {
        // Reply only when message sent is in 'text' format
        if ($event['type'] == 'message' && $event['message']['type'] == 'text') {
            $text = $event['message']['text'];
            $replyToken = $event['replyToken'];
            $userId = $event['source']['userId']; // Get the user ID

            // Start loading animation
            startLoadingAnimation($userId, 5);

            // Try to find image with the same name as the text
            $image_url = getImageFromDriveByName($folder_id, $api_key, $text);
            if ($image_url) {
                $message = [
                    'type' => 'image',
                    'originalContentUrl' => $image_url,
                    'previewImageUrl' => $image_url
                ];
            } else {
                $message = [
                    
                    'type' => 'text',
                    'text' => 'ไม่พบรูปภาพที่ชื่อ "' . $text . '" ใน Google Drive'
                ];
            }

            // Reply to the user
            replyMessage($replyToken, $message);
        }
    }
}
?>
