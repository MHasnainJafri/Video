<?php

require_once(ROOT .  DS.  'app' . DS. 'Vendor' . DS  . 'google' . DS  . 'vendor'. DS . 'autoload.php');
require_once(ROOT .  DS.  'app' . DS. 'Vendor' . DS  . 'aws' . DS  . 'vendor'. DS . 'autoload.php');

use Aws\S3\S3Client;



class Premium
{


    static function s3_video_upload($user_id, $param, $sound_details, $video_details, $duet)
    {


        $original_video_file_path = (new self)->uploadOriginalVideoFileIntoTemporaryFolder($param, $user_id);



        if (!$original_video_file_path) {

            $error['code'] = 201;
            $error['msg'] = "Something went wrong in uploading file into the folder. check your max upload size or check if fileupload is On in your php.ini file";

            echo json_encode($error);
            die();


        }

        $final_video = (new self)->addBlackBackgroundInTheVideo($original_video_file_path, $user_id);
        //$final_video = $original_video_file_path;
        if (count($video_details) > 0) {

            //duet feature

            $original_video_file_path = (new self)->duet($final_video, $video_details['Video']['video'], $duet);
            $final_video = $original_video_file_path;



        }

        $gif = (new self)->videoToGif($original_video_file_path, $user_id);
        $thumb = (new self)->videoToThumb($original_video_file_path, $user_id);


        // $final_video = (new self)->optimizeVideoSize($final_video);


        if (count($sound_details) < 1) {
            $mp3_file_name = "";
            $mp3_file = (new self)->convertVideoToAudio($final_video, $user_id);

            if ($mp3_file) {


                $mp3_file_name = explode('/', $mp3_file);
                $mp3_file_name = "audio/" . $mp3_file_name[4];


                $output['audio'] = $mp3_file_name;

            } else {


                $output['audio'] = "";
            }

            $final_video_file_path = $final_video;
        } else {


            $mp3_file_name = "";
            $video_path_with_audio = (new self)->mergeVideoWithSound($final_video, $sound_details['Sound']['audio']);
            $output['audio'] = "";
            $final_video_file_path = $video_path_with_audio;

        }


        $video_file_name = explode('/', $final_video_file_path);

        $video_file_name = "video/" . $video_file_name[4];

        $gif_file_name = explode('/', $gif);
        $gif_file_name = "gif/" . $gif_file_name[4];

        $thumb_file_name = explode('/', $thumb);
        $thumb_file_name = "thum/" . $thumb_file_name[4];


        $IAM_KEY = IAM_KEY;
        $IAM_SECRET = IAM_SECRET;

        // Set Amazon S3 Credentials
        $s3 = S3Client::factory(
            array(
                'credentials' => array(
                    'key' => $IAM_KEY,
                    'secret' => $IAM_SECRET
                ),
                'version' => 'latest',
                'region' => S3_REGION
            )
        );


        try {


            // Put on S3


            $upload_video = $s3->putObject(
                array(
                    'Bucket' => BUCKET_NAME,
                    'Key' => $video_file_name,
                    'ACL' => 'public-read',
                    'SourceFile' => $final_video_file_path,
                    'StorageClass' => 'REDUCED_REDUNDANCY'
                )
            );


            $upload_gif = $s3->putObject(
                array(
                    'Bucket' => BUCKET_NAME,
                    'Key' => $gif_file_name,
                    'ACL' => 'public-read',
                    'SourceFile' => $gif,
                    'StorageClass' => 'REDUCED_REDUNDANCY'
                )
            );

            $upload_thumb = $s3->putObject(
                array(
                    'Bucket' => BUCKET_NAME,
                    'Key' => $thumb_file_name,
                    'ACL' => 'public-read',
                    'SourceFile' => $thumb,
                    'StorageClass' => 'REDUCED_REDUNDANCY'
                )
            );

            if (strlen($mp3_file_name) > 2) {

                $upload_mp3 = $s3->putObject(
                    array(
                        'Bucket' => BUCKET_NAME,
                        'Key' => $mp3_file_name,
                        'ACL' => 'public-read',
                        'SourceFile' => $mp3_file,
                        'StorageClass' => 'REDUCED_REDUNDANCY'
                    )
                );

                $final_output['audio'] = $upload_mp3['ObjectURL'];
                unlink($mp3_file);

            } else {

                $final_output['audio'] = "";
            }
            $final_output['video'] = $upload_video['ObjectURL'];
            $final_output['gif'] = $upload_gif['ObjectURL'];
            $final_output['thum'] = $upload_thumb['ObjectURL'];

            

            (new self)->unlinkFile($final_video);
            (new self)->unlinkFile($gif);
            (new self)->unlinkFile($thumb);
            (new self)->unlinkFile($original_video_file_path);


            return $final_output;

        } catch (S3Exception $e) {
            //echo $e->getMessage();
            if (strpos($e->getMessage(), "NoSuchBucket") != "") {

                echo $e->getMessage();
                define("s3_Error", "NoSuchBucket");
            } else
                if (strpos($e->getMessage(), "AccessDenied") != "") {
                    define("s3_Error", "AccessDenied");
                    echo $e->getMessage();
                }
        } catch (Exception $e) {
            echo $e->getMessage();


        }

        die();


    }

    static function fileUploadToS3Multipart($file, $ext=null)
    {

        //$audio_data = base64_decode(end(explode(",", $base64)));
        $random_string = (new self)->random_string(5);
        if(is_null($ext)){
            $type = $_FILES[$file]['type'];

            $ext_details = explode("/",$type);
            $ext = $ext_details[1];
            if($ext =="mpeg"){
                $ext = "mp3";
            }

        }
        $file_name = uniqid() . $random_string . "." . $ext;
        $IAM_KEY = IAM_KEY;
        $IAM_SECRET = IAM_SECRET;

        // Set Amazon S3 Credentials
        $s3 = S3Client::factory(
            array(
                'credentials' => array(
                    'key' => $IAM_KEY,
                    'secret' => $IAM_SECRET
                ),
                'version' => 'latest',
                'region' => S3_REGION
            )
        );


        try {


            if ($ext == "mp4") {



                $folder = 'videos/';
            } else   if ($ext == "jpg" || $ext == "jpeg" ||  $ext == "png" ||  $ext == "gif") {



                $folder = 'images/';

            }else   if ($ext == "pdf") {



                $folder = 'pdf/';

            }else   if ($ext == "mp3") {



                $folder = 'audio/';

            }else   if ($ext == "mpeg") {



                $folder = 'audio/';

            }
            $content_type = $_FILES[$file]['type'];

            $upload_file = $s3->createMultipartUpload(
                array(
                    'Bucket' => BUCKET_NAME,
                    'Key' => $folder . $file_name,
                    'ACL' => 'public-read',
                    //'Body' => $file,
                    'StorageClass' => 'REDUCED_REDUNDANCY',

                    'ContentType' => $content_type,
                    'Metadata' => array(
                        'Content-Type' => $content_type,
                        'Cache-Control' => 'max-age=31536000',
                        'x-amz-meta-uuid' => '14365123651274',
                        'x-amz-meta-tag' => 'some-tag'
                    )
                )
            );
            $uploadId = $upload_file['UploadId'];
            $temp_filename = $_FILES[$file]['tmp_name'];

            $file = fopen($temp_filename, 'r');
            $partNumber = 1;
            $partSize = 5 * 1024 * 1024; // 5 MB
            $parts = [];
            while (!feof($file)) {
                $body = fread($file, $partSize);
                $result = $s3->uploadPart([
                    'Bucket' => BUCKET_NAME,
                    'Key' => $folder . $file_name,
                    'UploadId' => $uploadId,
                    'PartNumber' => $partNumber,
                    'StorageClass' => 'REDUCED_REDUNDANCY',
                    'Body' => $body,
                    'ContentType' => $content_type,
                    'ACL' => 'public-read',
                ]);
                $parts[] = [
                    'PartNumber' => $partNumber,
                    'ETag' => $result['ETag'],
                ];
                $partNumber++;
            }
            fclose($file);

            $result = $s3->completeMultipartUpload([
                'Bucket' => BUCKET_NAME,
                'Key' => $folder . $file_name,
                'UploadId' => $uploadId,
                'ACL' => 'public-read',
                'ContentType' => $content_type,
                'StorageClass' => 'REDUCED_REDUNDANCY',
                'MultipartUpload' => [
                    'Parts' => $parts,
                ],
                'Metadata' => array(
                    'Content-Type' => $content_type,
                    'Cache-Control' => 'max-age=31536000',
                    'x-amz-meta-uuid' => '14365123651274',
                    'x-amz-meta-tag' => 'some-tag'
                )
            ]);



            $code = 200;
            $msg = $upload_file['Key'];


        } catch (S3Exception $e) {
            //echo $e->getMessage();
            if (strpos($e->getMessage(), "NoSuchBucket") != "") {

                echo $e->getMessage();
                define("s3_Error", "NoSuchBucket");

                $code = 201;
                $msg = "No such Bucket exist";
            } else
                if (strpos($e->getMessage(), "AccessDenied") != "") {
                    define("s3_Error", "AccessDenied");
                    echo $e->getMessage();

                    $code = 201;
                    $msg = "Access Denied of aws bucket";
                }
        } catch (Exception $e) {
            // echo $e->getMessage();

            $code = 201;
            $msg = "some invalid error in aws";

        }


        $final_output['code'] = $code;
        $final_output['msg'] = $msg;
        return $final_output;

    }
    function random_string($length)
    {
        $key = '';
        $keys = array_merge(range(0, 9), range('a', 'z'));

        for ($i = 0; $i < $length; $i++) {
            $key .= $keys[array_rand($keys)];
        }

        return $key;
    }


    static function fileUploadToS3($file, $ext,$folderparam = false)
    {

        $ext = pathinfo($file, PATHINFO_EXTENSION);
        //$audio_data = base64_decode(end(explode(",", $base64)));
        $random_string = (new self)->random_string(5);
        $file_name = uniqid() . $random_string . "." . $ext;
        $IAM_KEY = IAM_KEY;
        $IAM_SECRET = IAM_SECRET;

        // Set Amazon S3 Credentials
        $s3 = S3Client::factory(
            array(
                'credentials' => array(
                    'key' => $IAM_KEY,
                    'secret' => $IAM_SECRET
                ),
                'version' => 'latest',
                'region' => S3_REGION
            )
        );


        try {



            if ($ext == "mp3") {


                $content_type = 'audio/mpeg';
                $folder = 'audio/';
            } else   if ($ext == "png") {

                $content_type = 'image/png';
                $folder = 'stickers/';
            }else if ($ext == "gif") {

                $content_type = 'image/gif';
                $folder = 'profile/';
            }else if ($ext == "mp4") {

                    $content_type = 'video/mp4';
                    $folder = 'video/';


            }else{


                $content_type = 'image/jpeg';
                $folder = 'thum/';

            }

            if($folderparam){

                $folder = "profile/";

            }



            if($ext == "gif"){

                $upload_file = $s3->putObject(
                    array(
                        'Bucket' => BUCKET_NAME,
                        'Key' => $folder.$file_name,
                        'ACL' => 'public-read',
                        'SourceFile' => $file,
                        'StorageClass' => 'REDUCED_REDUNDANCY'
                    )
                );

            }else {
                $upload_file = $s3->putObject(
                    array(
                        'Bucket' => BUCKET_NAME,
                        'Key' => $folder . $file_name,
                        'ACL' => 'public-read',
                        //'Body' => $file,
                        'SourceFile' => $file,
                        'StorageClass' => 'REDUCED_REDUNDANCY',
                        'ContentType' => $content_type
                    )
                );
            }

            //  $final_output['audio'] = $upload_file['ObjectURL'];
            $code = 200;
            $msg = $upload_file['ObjectURL'];


        } catch (S3Exception $e) {
            //echo $e->getMessage();
            if (strpos($e->getMessage(), "NoSuchBucket") != "") {

                echo $e->getMessage();
                define("s3_Error", "NoSuchBucket");

                $code = 201;
                $msg = "No such Bucket exist";
            } else
                if (strpos($e->getMessage(), "AccessDenied") != "") {
                    define("s3_Error", "AccessDenied");
                    echo $e->getMessage();

                    $code = 201;
                    $msg = "Access Denied of aws bucket";
                }
        } catch (Exception $e) {
            // echo $e->getMessage();

            $code = 201;
            $msg = "some invalid error in aws";

        }


        $final_output['code'] = $code;
        $final_output['msg'] = $msg;
        return $final_output;

    }
    static function fileUpload($file, $ext,$folderparam = false)
    {
        //$ext = pathinfo($file, PATHINFO_EXTENSION);
        //$audio_data = base64_decode(end(explode(",", $base64)));
        $random_string = (new self)->random_string(5);

        $file_name = uniqid() . $random_string . "." . $ext;
        $IAM_KEY = IAM_KEY;
        $IAM_SECRET = IAM_SECRET;

        // Set Amazon S3 Credentials
        $s3 = S3Client::factory(
            array(
                'credentials' => array(
                    'key' => $IAM_KEY,
                    'secret' => $IAM_SECRET
                ),
                'version' => 'latest',
                'region' => S3_REGION
            )
        );


        try {

            if($ext == "mp3"){
                $content_type = 'audio/mpeg';
                $folder = 'audio/';

            }else
                if($ext == "png"){
                    $content_type = 'image/png';
                    $folder = 'thum/';

                }else if($ext == "mp4"){
                    $content_type = 'video/mp4';
                    $folder = 'video/';

                }





            $upload_file = $s3->putObject(
                array(
                    'Bucket' => BUCKET_NAME,
                    'Key' => $folder . $file_name,
                    'ACL' => 'public-read',
                    'Body' => $file,
                    //'SourceFile' => $file,
                    'StorageClass' => 'REDUCED_REDUNDANCY',
                    'ContentType' => $content_type
                )
            );


            //  $final_output['audio'] = $upload_file['ObjectURL'];
            $code = 200;
            $msg = $upload_file['ObjectURL'];


        } catch (S3Exception $e) {
            //echo $e->getMessage();
            if (strpos($e->getMessage(), "NoSuchBucket") != "") {

                echo $e->getMessage();
                define("s3_Error", "NoSuchBucket");

                $code = 201;
                $msg = "No such Bucket exist";
            } else
                if (strpos($e->getMessage(), "AccessDenied") != "") {
                    define("s3_Error", "AccessDenied");
                    echo $e->getMessage();

                    $code = 201;
                    $msg = "Access Denied of aws bucket";
                }
        } catch (Exception $e) {
            // echo $e->getMessage();

            $code = 201;
            $msg = "some invalid error in aws";

        }


        $final_output['code'] = $code;
        $final_output['msg'] = $msg;
        return $final_output;

    }




    static function testFileUploadToS3($file)
    {


        $IAM_KEY = IAM_KEY;
        $IAM_SECRET = IAM_SECRET;

        // Set Amazon S3 Credentials
        $s3 = S3Client::factory(
            array(
                'credentials' => array(
                    'key' => $IAM_KEY,
                    'secret' => $IAM_SECRET
                ),
                'version' => 'latest',
                'region' => S3_REGION
            )
        );


        try {


            $upload_file = $s3->putObject(
                array(
                    'Bucket' => BUCKET_NAME,
                    'Key' => "thum/test.png",
                    'ACL' => 'public-read',
                    'SourceFile' => $file,
                    'StorageClass' => 'REDUCED_REDUNDANCY',
                    'ContentType' => 'image/jpeg'
                )
            );


            //  $final_output['audio'] = $upload_file['ObjectURL'];
            $code = 200;
            $msg = $upload_file['ObjectURL'];


        } catch (S3Exception $e) {
            //echo $e->getMessage();
            if (strpos($e->getMessage(), "NoSuchBucket") != "") {

                echo $e->getMessage();
                define("s3_Error", "NoSuchBucket");

                $code = 201;
                $msg = "No such Bucket exist";
            } else
                if (strpos($e->getMessage(), "AccessDenied") != "") {
                    define("s3_Error", "AccessDenied");
                    echo $e->getMessage();

                    $code = 201;
                    $msg = "Access Denied of aws bucket";
                }
        } catch (Exception $e) {
            // echo $e->getMessage();

            $code = 201;
            $msg = $e->getMessage();

        }


        $final_output['code'] = $code;
        $final_output['msg'] = $msg;
        return $final_output;

    }


    static function profileImageToS3($file,$extension)
    {

        $random_string = (new self)->random_string(5);
        $file_name = uniqid() . $random_string . "." . $extension;
        $IAM_KEY = IAM_KEY;
        $IAM_SECRET = IAM_SECRET;

        // Set Amazon S3 Credentials
        $s3 = S3Client::factory(
            array(
                'credentials' => array(
                    'key' => $IAM_KEY,
                    'secret' => $IAM_SECRET
                ),
                'version' => 'latest',
                'region' => S3_REGION
            )
        );


        try {

            if($extension == "png") {

                $upload_file = $s3->putObject(
                    array(
                        'Bucket' => BUCKET_NAME,
                        'Key' => "profile/$file_name",
                        'ACL' => 'public-read',
                        'SourceFile' => $file,
                        'StorageClass' => 'REDUCED_REDUNDANCY',
                        'ContentType' => 'image/png'
                    )
                );

            }else{

                $upload_file = $s3->putObject(
                    array(
                        'Bucket' => BUCKET_NAME,
                        'Key' => "profile/$file_name",
                        'ACL' => 'public-read',
                        'SourceFile' => $file,
                        'StorageClass' => 'REDUCED_REDUNDANCY',
                        //'ContentType' => 'image/png'
                    )
                );

            }
            //  $final_output['audio'] = $upload_file['ObjectURL'];
            $code = 200;
            $msg = $upload_file['ObjectURL'];


        } catch (S3Exception $e) {
            //echo $e->getMessage();
            if (strpos($e->getMessage(), "NoSuchBucket") != "") {

                echo $e->getMessage();
                define("s3_Error", "NoSuchBucket");

                $code = 201;
                $msg = "No such Bucket exist";
            } else
                if (strpos($e->getMessage(), "AccessDenied") != "") {
                    define("s3_Error", "AccessDenied");
                    echo $e->getMessage();

                    $code = 201;
                    $msg = "Access Denied of aws bucket";
                }
        } catch (Exception $e) {
            // echo $e->getMessage();

            $code = 201;
            $msg = $e->getMessage();

        }


        $final_output['code'] = $code;
        $final_output['msg'] = $msg;
        return $final_output;

    }

    static function addSticker($file,$extension)
    {

        $random_string = (new self)->random_string(5);
        $file_name = uniqid() . $random_string . "." . $extension;
        $IAM_KEY = IAM_KEY;
        $IAM_SECRET = IAM_SECRET;

        // Set Amazon S3 Credentials
        $s3 = S3Client::factory(
            array(
                'credentials' => array(
                    'key' => $IAM_KEY,
                    'secret' => $IAM_SECRET
                ),
                'version' => 'latest',
                'region' => S3_REGION
            )
        );


        try {

            if($extension == "png") {

                $upload_file = $s3->putObject(
                    array(
                        'Bucket' => BUCKET_NAME,
                        'Key' => "sticker/$file_name",
                        'ACL' => 'public-read',
                        'SourceFile' => $file,
                        'StorageClass' => 'REDUCED_REDUNDANCY',
                        'ContentType' => 'image/png'
                    )
                );

            }else{

                $upload_file = $s3->putObject(
                    array(
                        'Bucket' => BUCKET_NAME,
                        'Key' => "sticker/$file_name",
                        'ACL' => 'public-read',
                        'SourceFile' => $file,
                        'StorageClass' => 'REDUCED_REDUNDANCY',
                        //'ContentType' => 'image/png'
                    )
                );

            }
            //  $final_output['audio'] = $upload_file['ObjectURL'];
            $code = 200;
            $msg = $upload_file['ObjectURL'];


        } catch (S3Exception $e) {
            //echo $e->getMessage();
            if (strpos($e->getMessage(), "NoSuchBucket") != "") {

                echo $e->getMessage();
                define("s3_Error", "NoSuchBucket");

                $code = 201;
                $msg = "No such Bucket exist";
            } else
                if (strpos($e->getMessage(), "AccessDenied") != "") {
                    define("s3_Error", "AccessDenied");
                    echo $e->getMessage();

                    $code = 201;
                    $msg = "Access Denied of aws bucket";
                }
        } catch (Exception $e) {
            // echo $e->getMessage();

            $code = 201;
            $msg = $e->getMessage();

        }


        $final_output['code'] = $code;
        $final_output['msg'] = $msg;
        return $final_output;

    }


    static function getDurationofAudioFile($filepath)
    {

        $duration = shell_exec("ffmpeg -i \"" . $filepath . "\" 2>&1");


        preg_match("/Duration: (\d{2}:\d{2}:\d{2}\.\d{2})/", $duration, $matches);

        $time = explode(':', $matches[1]);
        $hour = $time[0];
        $minutes = $time[1];
        $seconds = round($time[2]);

        $total_seconds = 0;
        $total_seconds += 60 * 60 * $hour;
        $total_seconds += 60 * $minutes;

        return $minutes . ":" . $seconds;

    }

    static function unlinkFile($file_path)
    {
        if (file_exists($file_path)) {
            unlink($file_path);
        }

        return true;
    }


    static function deleteObjectS3($video_url)
    {


        $pieces = explode('/', $video_url);
        $count = count($pieces);

        if ($count > 5) {
            $key1 = $pieces['3'];
            $key2 = $pieces['4'];
            $key3 = $pieces['5'];
            $key_name = $key1 . "/" . $key2 . "/" . $key3;

        } else {

            $key1 = $pieces['3'];
            $key2 = $pieces['4'];

            $key_name = $key1 . "/" . $key2;

        }

        if (count($pieces) > 0) {

            $IAM_KEY = IAM_KEY;
            $IAM_SECRET = IAM_SECRET;


            // Set Amazon S3 Credentials
            $s3 = S3Client::factory(
                array(
                    'credentials' => array(
                        'key' => $IAM_KEY,
                        'secret' => $IAM_SECRET
                    ),
                    'version' => 'latest',
                    'region' => S3_REGION
                )
            );
            try {


                $result = $s3->deleteObject([
                    'Bucket' => BUCKET_NAME,
                    'Key' => $key_name
                ]);


                $deleteMarker = (bool)$result->get('DeleteMarker');

                if ($deleteMarker) {

                    return true;
                } else {

                    return false;
                }
            } catch (S3Exception $e) {
                return false;
            }
        } else {

            return false;
        }

    }


    static function google_cdn($data)
    {

        //You need to buy Premium license for it
        return false;


    }

    function uploadOriginalVideoFileIntoTemporaryFolder($param, $user_id)
    {

        $fileName = uniqid() . $user_id;
        $folder = TEMP_UPLOADS_FOLDER_URI . '/' . $user_id;
        if (!file_exists($folder)) {
            mkdir($folder, 0777, true);
        }

        if ($param == "image") {

            $ext = ".png";

        } else
            if ($param == "video") {

                $ext = ".mp4";

            } else
                if ($param == "audio") {

                    $ext = ".mp3";

                }
        $filePath = $folder . "/" . $fileName . $ext;


        if (move_uploaded_file($_FILES[$param]['tmp_name'], $filePath)) {


            return $filePath;

        } else {

            return false;
        }
    }


    function optimizeVideoSize($original_video_path)
    {

        $without_extension_file_name = pathinfo($original_video_path, PATHINFO_FILENAME);


        $pieces = explode('/', $original_video_path);

        $str = implode('/', array_slice($pieces, 0, -1));


        $optimizeResultFile = $str . '/' . $without_extension_file_name . "_optimize.mp4";

        $cmd_new = "ffmpeg -i $original_video_path -c:v libx264 -crf 38 $optimizeResultFile";
        exec($cmd_new);
        return $optimizeResultFile;


    }


    function addBlackBackgroundInTheVideo($optimizeResultFile, $user_id)
    {


        $without_extension_file_name = pathinfo($optimizeResultFile, PATHINFO_FILENAME);


        $pieces = explode('/', $optimizeResultFile);

        $str = implode('/', array_slice($pieces, 0, -1));


        $black_background = $str . '/' . $without_extension_file_name . "black.mp4";

        //720:1280

        $command_new = "ffmpeg -i   $optimizeResultFile -vf 'scale=540:960:force_original_aspect_ratio=decrease,pad=540:960:(ow-iw)/2:(oh-ih)/2,setsar=1' $black_background 2>&1";
        exec($command_new,$output,$returnCode);
        if ($returnCode !== 0) {
            return $optimizeResultFile;
        }else{
            return $black_background;
        }
    }




    function convertVideoToAudio($original_video_file_path)
    {

        $fileName = uniqid();
        $folder = UPLOADS_FOLDER_URI . '/audio/';
        $mp3_file = $folder . $fileName . ".mp3";
        if (!file_exists($folder)) {
            mkdir($folder, 0777, true);
        }

        $cmd = "ffprobe -i $original_video_file_path -show_streams -select_streams a -loglevel error";
        exec($cmd, $output);
        if (count($output) > 0) {

            $command_new = "ffmpeg -i $original_video_file_path -b:a 192K -vn $mp3_file";
            exec($command_new, $output);
            return $mp3_file;
        } else {

            return false;
        }


    }

    function videoToGif($original_video_file_path, $user_id)
    {
        $fileName = uniqid() . $user_id;
        $folder = UPLOADS_FOLDER_URI . '/gif/' . $user_id;
        $genrateGifPath = $folder . $fileName . ".gif";
        if (!file_exists($folder)) {
            mkdir($folder, 0777, true);
        }

        $gif = "ffmpeg -ss 3 -t 2 -i $original_video_file_path -vf 'fps=10,scale=160:-1:flags=lanczos,split[s0][s1];[s0]palettegen[p];[s1][p]paletteuse' -loop 0 $genrateGifPath";

        exec($gif, $output);

        return $genrateGifPath;

    }

    function videoToThumb($original_video_file_path, $user_id)
    {

        $without_extension_file_name = pathinfo($original_video_file_path, PATHINFO_FILENAME);


        $pieces = explode('/', $original_video_file_path);

        $str = implode('/', array_slice($pieces, 0, -1));


        $thumb_path = $str . '/' . $without_extension_file_name . "thumb.png";

        $thumb_cmd = "ffmpeg -i $original_video_file_path -vf fps=3 $thumb_path";

        exec($thumb_cmd, $output);


        return $thumb_path;
    }

    static function getDurationOfVideoFile($video_url)
    {

        $cmd = "ffprobe -i $video_url -show_format  -v quiet | sed -n 's/duration=//p'";
        exec($cmd, $output);
        $duration = number_format((float)$output[0], 1, '.', '');
        return $duration;
    }

    static function addUsernameWithWatermark($username,$user_id){
        $fileName = uniqid() . $user_id;
        $font_name = 'roboto-bold.ttf';
        $font_path = FONT_FOLDER_URI . '/' . $font_name;
        $watermark = WATERMARK_IMAGE_URI;
        $dir =  UPLOADS_FOLDER_URI."/watermark/";
        $watermark_with_username ="$dir.$fileName.png";
        if (!file_exists($dir)) {
            mkdir($dir, 0777, true);
        }
        $cmd_image = "ffmpeg -i $watermark -vf \"drawtext=text='@$username':fontsize=14:fontcolor=white:fontfile=$font_path:x=12:y=80\" $watermark_with_username";
        exec($cmd_image, $output);
        if(file_exists($watermark_with_username)){
            return $watermark_with_username;
        }else{
            return false;
        }



    }

    static function addWaterMarkInLastClip($username,$user_id){
        $fileName = uniqid() . $user_id;

        $dir =  UPLOADS_FOLDER_URI."/watermark/";
        $watermark_with_video ="$dir.$fileName.mp4";
        if (!file_exists($dir)) {
            mkdir($dir, 0777, true);
        }

        $watermark_video = WATERMARK_VIDEO_URI;
        $cmd_video_watermark = "ffmpeg -i $watermark_video -vf \"drawtext=text='@$username':x=400:y=1110:fontsize=30:fontcolor=white:enable='gte(t,1)'\" -c:a copy $watermark_with_video";


        exec($cmd_video_watermark, $output);

        if(file_exists($watermark_with_video)){
            return $watermark_with_video;
        }else{
            return false;
        }



    }

    static function addWaterMarkInTheUserUploadedVideo($video,$username,$user_id,$duration){
        $fileName = uniqid() . $user_id;
        $starting_time = $duration/2;
        $ending_time = $starting_time*2;
        $dir =  UPLOADS_FOLDER_URI."/watermark/";
        $watermark_with_video ="$dir.$fileName.mp4";
        if (!file_exists($dir)) {
            mkdir($dir, 0777, true);
        }
        $watermark_with_username = (new self)->addUsernameWithWatermark($username,$user_id);

        $cmd = "ffmpeg -i $video -i $watermark_with_username -filter_complex \"[0:v][1:v]overlay=x='if(between(t,1,$starting_time),10,(main_w-overlay_w))':y='if(between(t,1,$starting_time),(main_h-overlay_h)/2-10,(main_h-overlay_h))':enable='between(t,0,$ending_time)'\" $watermark_with_video";

        //$cmd = "ffmpeg -i $original_video_file_path -i $image_with_watermark -filter_complex \"[0:v][1:v] overlay=x=\$x1\$:y=\$y1\$:enable='between(t,0,3)'[first]; [first][1:v] overlay=x=\$x2\$:y=\$y2\$:enable='between(t,3,6)'[outv]; [outv] fade=out:st=6:d=1 [finalv]\" -map '[finalv]' -c:a copy $video_with_watermark 2>&1";

        exec($cmd, $output);

        if(file_exists($watermark_with_video)){
            return $watermark_with_video;
        }else{
            return false;
        }



    }

    static function combineVideoWithOriginal($user_video_with_watermark,$video_with_last_clip_watermark){
        $fileName = uniqid();

        $dir =  UPLOADS_FOLDER_URI."/watermark/";
        $video =$dir.$fileName.".mp4";
        if (!file_exists($dir)) {
            mkdir($dir, 0777, true);
        }


        $cmd_combine = "ffmpeg -i $user_video_with_watermark -i $video_with_last_clip_watermark -filter_complex '[0:v]scale=720:1280,setsar=1[v0]; [1:v]scale=720:1280,setsar=1[v1]; [v0][0:a][v1][1:a]concat=n=2:v=1:a=1[outv][outa]' -map '[outv]' -map '[outa]' $video";


        exec($cmd_combine, $output);

        if(file_exists($video)){
            return $video;
        }else{
            return false;
        }



    }

    function addWaterMarkAndText($original_video_file_path, $user_id, $username, $duration)
    {




        if ($duration < 1) {

            $duration = (new self)->getDurationOfVideoFile($original_video_file_path);
        }

        if(file_exists(WATERMARK_VIDEO_URI)) {


            $video_with_last_clip_watermark = (new self)->addWaterMarkInLastClip($username, $user_id);


            $user_video_with_watermark = (new self)->addWaterMarkInTheUserUploadedVideo($original_video_file_path, $username, $user_id, $duration);


            $final_video = (new self)->combineVideoWithOriginal($user_video_with_watermark, $video_with_last_clip_watermark);

            unlink($user_video_with_watermark);
            unlink($video_with_last_clip_watermark);

            return $final_video;

        }else{

            $fileName = uniqid() . $user_id;
            $font_name = 'roboto-bold.ttf';
            $font_path = FONT_FOLDER_URI . '/' . $font_name;
            $watermark = WATERMARK_IMAGE_URI;
            $folder = TEMP_UPLOADS_FOLDER_URI . '/video/' . $user_id . '/';
            $video_with_watermark = $folder . $fileName . ".mp4";
            if (!file_exists($folder)) {
                mkdir($folder, 0777, true);
            }

            $ext = pathinfo($watermark, PATHINFO_EXTENSION);

            if ($ext == "png") {


                $cmd = "ffmpeg -i $original_video_file_path -i $watermark -filter_complex \"[0:v][1:v]overlay=10:10,drawtext=fontfile='$font_path':text='@$username':fontcolor=#ffffff:fontsize=18:y=40:x=13\" -c:a copy -movflags +faststart $video_with_watermark";

            } else {




                $cmd = "ffmpeg -i $original_video_file_path -ignore_loop 0 -i $watermark -filter_complex \"[0:v][1:v]overlay=x=10:y=10:format=auto:enable='lte(t,$duration)':shortest=1[bg];[bg][1:v]overlay=x=main_w-overlay_w-10:y=main_h-overlay_h-20:format=auto:enable='gte(t,$duration)':shortest=1,drawtext=fontfile='$font_path':text='@$username':fontsize=18:fontcolor=white:x=13:y=40:enable='lte(t,$duration)',drawtext=fontfile='$font_path':text='@$username':fontsize=18:fontcolor=white:x=w-tw-10:y=h-th-10:enable='gte(t,$duration)',format=yuv420p[v]\" -map \"[v]\" -c:v libx264 -crf 18 -map 0:a? -c:a copy -movflags +faststart $video_with_watermark";
            }

            exec($cmd, $output);


            return $video_with_watermark;
        }





    }

    static function duet($video1_path, $video2_path, $duet)
    {


       
        $without_extension_file_name = pathinfo($video1_path, PATHINFO_FILENAME);


        $pieces = explode('/', $video1_path);

        $str = implode('/', array_slice($pieces, 0, -1));


        $duetMergePathOutput = $str . '/' . $without_extension_file_name . "duet.mp4";


        $command_new = "ffmpeg -i $video1_path   -i $video2_path   -filter_complex '[0:v]pad=iw*2:ih[int];[int][1:v]overlay=W/2:0[vid]'   -map [vid]   -c:v libx264   -crf 23   -preset veryfast $duetMergePathOutput";


        exec($command_new);


        return $duetMergePathOutput;
    }


    static function duetVertical($video1_path, $video2_path)
    {


        $without_extension_file_name = pathinfo($video1_path, PATHINFO_FILENAME);


        $pieces = explode('/', $video1_path);

        $str = implode('/', array_slice($pieces, 0, -1));


        $duetMergePathOutput_top = $str . '/' . $without_extension_file_name . "temptop.mp4";
        $duetMergePathOutput_bottom = $str . '/' . $without_extension_file_name . "tempbottom.mp4";
        $duetMergePathOutput_final = $str . '/' . $without_extension_file_name . "duet.mp4";


        $command_top = "ffmpeg -i $video1_path -s 720x640 -c:a copy $duetMergePathOutput_top";
        $command_bottom = "ffmpeg -i $video2_path -s 720x640 -c:a copy $duetMergePathOutput_bottom";
        exec($command_top);
        exec($command_bottom);

        $command_final = "ffmpeg -i $duetMergePathOutput_top -i $duetMergePathOutput_bottom -filter_complex '[0:v][1:v]vstack=inputs=2[v]' -map '[v]' -map 1:a $duetMergePathOutput_final";
        // $command_final = "ffmpeg -i $command_top -i $command_bottom -lavfi vstack $duetMergePathOutput_final";

        exec($command_final);


        return $duetMergePathOutput_final;
    }


    function mergeVideoWithSound($video_path, $audio)
    {

        $without_extension_file_name = pathinfo($video_path, PATHINFO_FILENAME);


        $pieces = explode('/', $video_path);

        $str = implode('/', array_slice($pieces, 0, -1));


        $with_new_audio = $str . '/' . $without_extension_file_name . "1.mp4";

        $cmd = "ffprobe -i $video_path -show_streams -select_streams a -loglevel error";
        exec($cmd, $if_audio_exist);

        if (count($if_audio_exist) > 0) {
            //replace audio

            $cmd_new = "ffmpeg -i $video_path -i $audio -c:v copy -c:a aac -shortest -map 0:v:0 -map 1:a:0 $with_new_audio";
            exec($cmd_new);


        } else {

            //add audio
            $cmd_new = "ffmpeg -i $video_path -i $audio -c:v copy -c:a aac -shortest $with_new_audio";
            exec($cmd_new);

        }

        return $with_new_audio;
    }



    function getToken($length)
    {
        $token        = "";
        $codeAlphabet = "ABCDEFGHIJKLMNOPQRSTUVWXYZ";
        $codeAlphabet .= "abcdefghijklmnopqrstuvwxyz";
        $codeAlphabet .= "0123456789";
        for ($i = 0; $i < $length; $i++) {
            $token .= $codeAlphabet[Utility::crypto_rand_secure(0, strlen($codeAlphabet))];
        }
        return $token;
    }





}