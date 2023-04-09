<?php

class FirebaseStorageUploader
{
    static public function uploadImage($image)
    {
        try {
            $imageType = $image['type'];
            if (
                !($imageType == 'image/jpeg' || $imageType == 'image/png' || $imageType == 'image/bmp')
            ) {
                throw new Exception("Image type is not accepted");
            }
            $imageSize = $image['size'];
            if ($imageSize > 5 * 1024 * 1024) {
                throw new Exception("Image size is too large");
            }
            $imageName = "" . uniqid() . "_" . $image['name'];
            $imageData = file_get_contents($image['tmp_name']);
            $url = "https://firebasestorage.googleapis.com/v0/b/vanlam-clothesshop.appspot.com/o/images%2F" . $imageName . "?uploadType=media";
            $headers = array(
                "Content-Type: " . $imageType,
            );
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $url);
            curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
            curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
            curl_setopt($ch, CURLOPT_POSTFIELDS, $imageData);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            $response = curl_exec($ch);
            curl_close($ch);
            // Parse the response to get the download URL for the image
            $responseObj = json_decode($response);
            $downloadUrl = $responseObj->downloadTokens;
            $imageUrl  = "https://firebasestorage.googleapis.com/v0/b/vanlam-clothesshop.appspot.com/o/images%2F" . $imageName . "?alt=media&token=" . $downloadUrl;
            return $imageUrl;
        } catch (Exception $e) {
            throw $e;
        }
    }
    static function uploadImages($images)
    {
        if ($images == null) {
            return null;
        }
        $imgUrls = [];
        for ($i = 0; $i < count($images['name']); $i++) {
            $img = [];
            $img['name'] = $images['name'][$i];
            $img['full_path'] = $images['full_path'][$i];
            $img['type'] = $images['type'][$i];
            $img['tmp_name'] = $images['tmp_name'][$i];
            $img['error'] = $images['error'][$i];
            $img['size'] = $images['size'][$i];
            try {
                array_push($imgUrls, self::uploadImage($img));
            } catch (Exception $e) {
                throw $e;
            }
        }
        return $imgUrls;
    }
}
