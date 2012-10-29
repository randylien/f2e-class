<?php
require_once "phpFlickr.php";
require_once "function.php";

$photoset_id = "72157631752849808";
$method   = getREQUEST("method");
$callback = getREQUEST("callback");
$redirect = getREQUEST("redirect");
$format   = getREQUEST("format");

$f = new phpFlickr("2070c8ac214487572681a57a0b7d3e40", "67794c1d44a9fc7e");
$f->setToken("72157631752899669-3729349dd1b76f08");

switch ($method)
{
    case "saveURL":
        $url = getREQUEST("url");
        $title = getREQUEST("title");
        $description = getREQUEST("description");
        $tags = getREQUEST("tags");
        $parts = pathinfo($url);
        $filename = $parts["basename"];
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        $fp = fopen("tmp/$filename", "w+");
        curl_setopt($ch, CURLOPT_FILE, $fp);
        curl_exec($ch);
        curl_close($ch);
        fclose($fp);
        $data = $f->sync_upload("tmp/$filename", $title, $description, $tags, 0, 0, 0);
        $f->photosets_addPhoto($photoset_id, $data);
        // $f->photosets_reoderPhotos($photoset_id, "$data");
        $result = json_encode(array("photo_id" => $data));
        break;
    case "upload":
        //if ($_SERVER["REQUEST_METHOD"] == "OPTIONS")
        //{
	    header("Access-Control-Allow-Origin: *");
        //}

        if ($_FILES["Filedata"]["error"] > 0)
        {
            $result = json_encode(array("error" => $_FILES["Filedata"]["error"]));
        }
        else
        {
            $title = getREQUEST("title");
            $description = getREQUEST("description");
            $tags = getREQUEST("tags");
            $title = ($title) ? $title : $_FILES["Filedata"]["name"];
            $data = $f->sync_upload($_FILES["Filedata"]["tmp_name"], $title, $description, $tags, 0, 0, 0);
            $photo_id = explode("-", $data);
            $photo_id = $photo_id[0];
            $f->photosets_addPhoto($photoset_id, $photo_id);
            $result = json_encode(array("photo_id" => $data));
        }
        break;
    case "getPhotoList":
    default:
        $data = $f->photosets_getPhotos($photoset_id, "url_m,url_o,tags,date_taken", NULL, 100, 1);
        $photos = $data["photoset"]["photo"];
        $result = json_encode($photos);
        break;
}

if ($redirect !== "")
{
    header("Location: $redirect");
    exit;
}

if ($format === "php")
{
    $result = json_decode($result);
    echo "儲存成功，以下是除錯訊息！<br>";
    echo "<pre>";
    print_r($result);
    exit;
}
else
{
    echo ($callback) ? "$callback($result);" : $result;
}

?>
