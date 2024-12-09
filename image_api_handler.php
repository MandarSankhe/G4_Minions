<?php
// IMGUR REST API Handler
class ImgurApiHandler {

    // client ID for IMGUR API
    private const CLIENT_ID = '546d2f4fd22194a';

    // POST (upload) image to Imgur
    public static function postImageImgur($fileTmpPath) {
        $imageData = file_get_contents($fileTmpPath);
        $base64Image = base64_encode($imageData);

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, "https://api.imgur.com/3/image");
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            "Authorization: Client-ID " . self::CLIENT_ID
        ]);
        curl_setopt($ch, CURLOPT_POSTFIELDS, [
            'image' => $base64Image,
            'type' => 'base64',
        ]);

        $response = curl_exec($ch);
        curl_close($ch);

        $responseJson = json_decode($response, true);

        if (isset($responseJson['success']) && $responseJson['success']) {
            return [
                'success' => true,
                'url' => $responseJson['data']['link']
            ];
        } else {
            return [
                'success' => false,
                'error' => $responseJson['data']['error']['message'] ?? 'Unknown error'
            ];
        }
    }

    // DELETE image from IMGUR
    public static function deleteImage($imageId) {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, "https://api.imgur.com/3/image/$imageId");
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "DELETE");
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            "Authorization: Client-ID " . self::CLIENT_ID
        ]);

        $response = curl_exec($ch);
        curl_close($ch);

        $responseJson = json_decode($response, true);

        return isset($responseJson['success']) && $responseJson['success'];
    }
}
?>
