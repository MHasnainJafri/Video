<?php

App::uses('Utility', 'Lib');
App::uses('Message', 'Lib');
App::uses('Premium', 'Lib');
App::uses('Regular', 'Lib');
class AdminController extends AppController
{

    //public $components = array('Email');

    public $autoRender = false;
    public $layout = false;


    public function beforeFilter()
    {


        $json = file_get_contents('php://input');
        $json_error = Utility::isJsonError($json);

        if ($json_error == "false") {

            if( !function_exists('apache_request_headers') ) {
                $headers =  Utility::apache_request_headers();
            }else {
                $headers = apache_request_headers();
            }

            $client_api_key = 0;
            if (array_key_exists("Api-Key", $headers) ) {
                $client_api_key = $headers['Api-Key'];

            }else if (array_key_exists("API-KEY", $headers)){

                $client_api_key = $headers['API-KEY'];
            }else if (array_key_exists("api-key", $headers)){

                $client_api_key = $headers['api-key'];
            }


            if (strlen($client_api_key) > 0) {


                if ($client_api_key != ADMIN_API_KEY) {

                    Message::ACCESSRESTRICTED();
                    die();

                }
            }else {
                $output['code'] = 201;
                $output['msg'] = "API KEY is missing";

                echo json_encode($output);
                die();

            }
            return true;


        } else {
            return true;
            $output['code'] = 201;
            $output['msg'] = $json_error;

            echo json_encode($output);
            die();


        }

    }

    public function index(){


        echo "Congratulations!. You have configured your mobile api correctly";

    }



    public function login() //changes done by irfan
    {
        $this->loadModel('Admin');


        if ($this->request->isPost()) {


            $json = file_get_contents('php://input');
            // $json = file_get_contents('php://input');
            $data = json_decode($json, TRUE);

            $email = strtolower($data['email']);
            $password = $data['password'];


            if ($email != null && $password != null) {
                $userData = $this->Admin->loginAllUsers($email, $password);

                if ($userData) {
                    $user_id = $userData[0]['Admin']['id'];

                    // $this->UserInfo->id = $user_id;
                    // $savedField = $this->UserInfo->saveField('device_token', $device_token);

                    $output = array();
                    $userDetails = $this->Admin->getUserDetailsFromID($user_id);

                    //CustomEmail::welcomeStudentEmail($email);
                    $output['code'] = 200;
                    $output['msg'] = $userDetails;
                    echo json_encode($output);


                } else {
                    echo Message::INVALIDDETAILS();
                    die();

                }


            } else {
                echo Message::ERROR();
                die();
            }
        }
    }




    public function showUsers(){

        $this->loadModel('User');




        if ($this->request->isPost()) {
            $json = file_get_contents('php://input');
            $data = json_decode($json, TRUE);

            $role="user";
            if(isset($data['role'])) {
                $role = $data['role'];

            }

            $starting_point = 0;
            if(isset($data['starting_point'])){
                $starting_point = $data['starting_point'];
            }

            if(APP_STATUS == "demo"){

                $users = $this->User->getUsers(0);
            }else{

                $users = $this->User->getUsers($starting_point);
            }


            if(count($users) > 0) {

                foreach($users as $key=>$user){

                    $user_id =  $user['User']['id'];
                    $wallet_total =  $this->walletTotal($user_id);
                    $wallet_total = $wallet_total['total'];
                    $users[$key]['User']['wallet'] = $wallet_total;

                }

                $output['code'] = 200;

                $output['msg'] = $users;


                echo json_encode($output);


                die();

            }else{


                Message::EMPTYDATA();
                die();
            }
            $total_count_records = $this->User->total_count_getUsers($role);







            $output['code'] = 200;
            $output['total_pages'] = ceil($total_count_records/ADMIN_RECORDS_PER_PAGE);

            $output['msg'] = $users;


            echo json_encode($output);


            die();


        }


    }

    public function postVideo(){

        $this->loadModel('Video');
        $this->loadModel('Sound');
        $this->loadModel('Hashtag');
        $this->loadModel('HashtagVideo');
        $this->loadModel('User');
        $this->loadModel('Notification');
        $this->loadModel('Follower');
        $this->loadModel('PushNotification');


        if ($this->request->isPost()) {


            $created = date('Y-m-d H:i:s', time());
            $user_id = $this->request->data('user_id');

            $description = $this->request->data('description');
            $privacy_type = $this->request->data('privacy_type');
            $allow_comments = $this->request->data('allow_comments');
            $allow_duet = $this->request->data('allow_duet');
            $video_id = $this->request->data('video_id');
            $sound_id = $this->request->data('sound_id');
            $hashtags_json = $this->request->data('hashtags_json');
            $users_json = $this->request->data('users_json');
            $duet = $this->request->data('duet');
            $lang_id = $this->request->data('lang_id');
            $interest_id = $this->request->data('interest_id');
            $product_id = $this->request->data('product_id');
            //$schedule = $this->request->data('schedule');

            $story = $this->request->data('story');

            $privacy_type = strtolower($privacy_type);



            $data_hashtag = json_decode($hashtags_json, TRUE);
            $data_users = json_decode($users_json, TRUE);


            $video_userDetails = $this->User->getUserDetailsFromID($user_id);


            if(count($video_userDetails) > 0) {

                $type = "video";


                $sound_details = $this->Sound->getDetails($sound_id);



                if ($video_id > 0) {

                    //duet
                    $video_details = $this->Video->getDetails($video_id);
                    $video_save['duet_video_id'] = $video_details['Video']['id'];
                    $sound_details = $this->Sound->getDetails($video_details['Video']['sound_id']);

                } else {

                    $video_details = array();


                }


                if (MEDIA_STORAGE == "s3") {
                    if (method_exists('Premium', 's3_video_upload')) {

                        $result_video = Premium::s3_video_upload($user_id, $type, $sound_details, $video_details, $duet);
                        $video_url_nudity_check = $result_video['video'];
                        if(strlen(CLOUDFRONT_URL) > 5) {
                            $video_url = Utility::getCloudFrontUrl($result_video['video'], "/video");
                            $gif_url = Utility::getCloudFrontUrl($result_video['gif'], "/gif");
                            $thum_url = Utility::getCloudFrontUrl($result_video['thum'], "/thum");
                            $audio_url = Utility::getCloudFrontUrl($result_video['audio'], "/audio");
                        }else{


                            $video_url = $result_video['video'];
                            $gif_url = $result_video['gif'];
                            $thum_url = $result_video['thum'];
                            $audio_url = $result_video['audio'];
                        }


                    } else {


                        $output['code'] = 201;

                        $output['msg'] = "It seems like you do not have premium files. submit ticket on yeahhelp.com for support";


                        echo json_encode($output);


                        die();
                    }



                } else {


                    $result_video = Regular::local_video_upload($user_id, $type, $sound_details, $video_details, $duet);

                    $video_url = $result_video['video'];
                    $gif_url = $result_video['gif'];
                    $thum_url = $result_video['thum'];
                    $audio_url = $result_video['audio'];
                    $video_url_nudity_check = BASE_URL.$video_url;


                }





                $video_save['sound_id'] = $sound_id;
                if (count($result_video) > 0) {
                    $video_duration = Utility::getDurationOfVideoFile($result_video['video']);
                    if (strlen($result_video['audio']) > 2) {

                        //$audio_url = Utility::getCloudFrontUrl($result_video['audio'], "/audio");

                        $duration = Utility::getDurationofAudioFile($result_video['audio']);


                        $sound_date['audio'] = $audio_url;
                        $sound_date['duration'] = $duration;
                        $sound_date['thum'] = $video_userDetails['User']['profile_pic'];
                        $sound_date['name'] = "original sound - " . $video_userDetails['User']['username'];
                        $sound_date['uploaded_by'] = "user";

                        $this->Sound->save($sound_date);
                        $sound_id = $this->Sound->getInsertID();
                        $video_save['sound_id'] = $sound_id;
                    }


                    //$filepath_thumb = Utility::multipartFileUpload($user_id, 'thumb',$type);


                    $video_save['gif'] = $gif_url;
                    $video_save['duration'] = $video_duration;
                    $video_save['video'] = $video_url;
                    $video_save['lang_id'] = $lang_id;

                    $video_save['thum'] = $thum_url;
                    $video_save['description'] = $description;
                    $video_save['privacy_type'] = $privacy_type;
                    $video_save['allow_comments'] = $allow_comments;
                    $video_save['allow_duet'] = $allow_duet;
                    $video_save['user_id'] = $user_id;
                    $video_save['interest_id'] = $interest_id;
                    $video_save['story'] = $story;
                    $video_save['created'] = $created;
                    $video_save['product_id'] = $product_id;
                    //$video_save['schedule'] = $schedule;

                    if($user_id < 1){

                        Message::EMPTYDATA();
                        die();


                    }

                    if (!$this->Video->save($video_save)) {
                        echo Message::DATASAVEERROR();
                        die();
                    }

                    $video_id = $this->Video->getInsertID();

                    if(strlen(DEEPENGIN_KEY) > 5) {
                        $nudity_result = Utility::checkNudity($video_url_nudity_check, $video_id);
                    }
                    /**************hashtag save******************/

                    /*if (count($data_hashtag) < 1) {

                         preg_match_all('/#\w+/', $description, $matches);

                         $data_hashtag = array_map(function($match) {
                             return str_replace('#', '', $match);
                         }, $matches[0]);
                     }*/

                    if (count($data_hashtag) > 0) {
                        foreach ($data_hashtag as $key => $value) {

                            $name = strtolower($value['name']);

                            $if_hashtag_exist = $this->Hashtag->ifExist($name);

                            if (count($if_hashtag_exist) < 1) {

                                $hashtag['name'] = $name;
                                $hashtag['lang_id'] = $lang_id;
                                $this->Hashtag->save($hashtag);
                                $hashtag_id = $this->Hashtag->getInsertID();
                                $this->Hashtag->clear();

                                $hashtag_video[$key]['hashtag_id'] = $hashtag_id;
                                $hashtag_video[$key]['video_id'] = $video_id;


                            } else {

                                $hashtag_id = $if_hashtag_exist['Hashtag']['id'];
                                $hashtag_video[$key]['hashtag_id'] = $hashtag_id;
                                $hashtag_video[$key]['video_id'] = $video_id;

                            }


                        }

                        if (count($hashtag_video) > 0) {


                            $this->HashtagVideo->saveAll($hashtag_video);
                        }
                    }



                    if (count($data_users) > 0) {

                        foreach ($data_users as $key => $value) {

                            $user_id = $value['user_id'];

                            $tagged_userDetails = $this->User->getUserDetailsFromID($user_id);

                            $msg = $video_userDetails['User']['username'] . " has tagged you in a video";

                            if (strlen($tagged_userDetails['User']['device_token']) > 8) {
                                $notification['to'] = $tagged_userDetails['User']['device_token'];

                                $notification['notification']['title'] = $msg;
                                $notification['notification']['body'] = "";
                                $notification['notification']['badge'] = "1";
                                $notification['notification']['sound'] = "default";
                                $notification['notification']['icon'] = "";
                                $notification['notification']['type'] = "video_tag";
                                $notification['data']['title'] = $msg;
                                $notification['data']['body'] = '';
                                $notification['data']['icon'] = "";
                                $notification['data']['badge'] = "1";
                                $notification['data']['sound'] = "default";
                                $notification['data']['type'] = "video_tag";
                                $notification['notification']['receiver_id'] =  $tagged_userDetails['User']['id'];
                                $notification['data']['receiver_id'] = $tagged_userDetails['User']['id'];




                                $if_exist = $this->PushNotification->getDetails($tagged_userDetails['User']['id']);

                                if (count($if_exist) > 0) {

                                    $video_updates = $if_exist['PushNotification']['video_updates'];
                                    if ($video_updates > 0) {
                                        Utility::sendPushNotificationToMobileDevice(json_encode($notification));
                                    }
                                }


                                $notification_data['sender_id'] = $video_userDetails['User']['id'];
                                $notification_data['receiver_id'] = $tagged_userDetails['User']['id'];
                                $notification_data['type'] = "video_tag";
                                $notification_data['video_id'] = $video_id;

                                $notification_data['string'] = $msg;
                                $notification_data['created'] = $created;

                                $this->Notification->save($notification_data);

                            }


                        }
                    }
                    /*************************end hashtag save ********************/


                    /**************pushnotification to tagged users******************/
                    $all_followers = $this->Follower->getUserFollowersWithoutLimit($user_id);
                    if (count($all_followers) > 0) {
                        foreach ($all_followers as $key => $value) {

                            $user_id = $value['FollowerList']['id'];
                            $device_token = $value['FollowerList']['device_token'];


                            $msg = $video_userDetails['User']['username'] . " has posted a a video";

                            if (strlen($device_token) > 8) {
                                $notification['to'] = $device_token;

                                $notification['notification']['title'] = $msg;
                                $notification['notification']['body'] = "";
                                $notification['notification']['badge'] = "1";
                                $notification['notification']['sound'] = "default";
                                $notification['notification']['icon'] = "";
                                $notification['notification']['type'] = "video_new_post";
                                $notification['notification']['video_id'] = $video_id;
                                $notification['data']['title'] = $msg;
                                $notification['data']['body'] = '';
                                $notification['data']['icon'] = "";
                                $notification['data']['badge'] = "1";
                                $notification['data']['sound'] = "default";
                                $notification['data']['video_id'] = $video_id;
                                $notification['data']['type'] = "video_new_post";
                                $notification['notification']['receiver_id'] =  $value['FollowerList']['id'];
                                $notification['data']['receiver_id'] = $value['FollowerList']['id'];



                                $if_exist = $this->PushNotification->getDetails($user_id);

                                if (count($if_exist) > 0) {

                                    $video_updates = $if_exist['PushNotification']['video_updates'];
                                    if ($video_updates > 0) {
                                        Utility::sendPushNotificationToMobileDevice(json_encode($notification));
                                    }
                                }


                                $notification_data['sender_id'] = $video_userDetails['User']['id'];
                                $notification_data['receiver_id'] = $user_id;
                                $notification_data['type'] = "video_updates";
                                $notification_data['video_id'] = $video_id;

                                $notification_data['string'] = $msg;
                                $notification_data['created'] = $created;

                                $this->Notification->save($notification_data);

                            }


                        }
                    }
                    /*************************end hashtag save ********************/


                    $output = array();

                    $album_details = $this->Video->getDetails($video_id);


                    $output['code'] = 200;
                    $output['msg'] = $album_details;
                    echo json_encode($output);

                }
            }else{
                Message::EMPTYDATA();
                die();

            }


        }

    }
    public function purchaseCoin()
    {


        $this->loadModel('User');


        if ($this->request->isPost()) {


            $json = file_get_contents('php://input');
            $data = json_decode($json, TRUE);


            if (in_array("PurchaseCoin", App::objects('Model'))) {
                $this->loadModel('PurchaseCoin');


            }else{

                $output['code'] = 201;

                $output['msg'] = "Contact hello@qboxus.com to get these premium features";


                echo json_encode($output);


                die();
            }
            $coin_data['user_id'] = $data['user_id'];

            $coin_data['title'] = $data['title'];
            $coin_data['coin'] = $data['coin'];

            $coin_data['transaction_id'] = $data['transaction_id'];
            $coin_data['device'] = $data['device'];
            $coin_data['created'] = date('Y-m-d H:i:s', time());







            $userDetails = $this->User->getUserDetailsFromID( $data['user_id']);
            if(count($userDetails) > 0) {


                $this->PurchaseCoin->save($coin_data);

                $id = $this->PurchaseCoin->getInsertID();

                $output = array();


                // $this->User->id = $userDetails['User']['id'];
                // $this->User->saveField('wallet',$total_coins_in_db + $data['coin']);
                $details = $this->PurchaseCoin->getDetails($id);
                $wallet_total =  $this->walletTotal($userDetails['User']['id']);

                $details['User']['wallet'] = $wallet_total['total'];

                $output['code'] = 200;
                $output['msg'] = $details;
                echo json_encode($output);
                die();

            }



        }
    }

    function walletTotal($user_id){
        $this->loadModel('WithdrawRequest');
        $this->loadModel('Promotion');
        $this->loadModel('LiveStreaming');
        $this->loadModel('LiveStreamingWatch');
        $this->loadModel('ReferralUsed');
        $this->loadModel('Setting');
        $purchase_amount_total = 0;
        $gift_receive[0]['total_amount'] = 0;
        $gift_send[0]['total_amount'] = 0;
        $withdraw_request_detail = $this->WithdrawRequest->getTotalCoins($user_id);
        if(strlen($withdraw_request_detail[0]['total_coin']) < 1){

            $withdraw_request_detail[0]['total_coin'] = 0;

        }
        if (in_array("Gift", App::objects('Model'))) {



            $this->loadModel('PurchaseCoin');
            $this->loadModel('GiftSend');



            $purchase_amount_total = $this->PurchaseCoin->totalAmountPurchase($user_id);
            $gift_send = $this->GiftSend->countGiftSendByUser($user_id);
            $gift_receive = $this->GiftSend->countGiftReceiveByUser($user_id);
        }



        $referral_count = $this->ReferralUsed->countReferralUsedByOthers($user_id);
        $referal_value = 0;
        $setting_details = $this->Setting->checkDuplicate("referral_coin");

        if(count($setting_details) > 0){


            $referal_value =  $setting_details['Setting']['value'];

        }
        $referral_earn = $referral_count * $referal_value;



        $promotion_coin = $this->Promotion->countPromotionCoin($user_id);
        $live_stream_earned_coin = $this->LiveStreaming->countCoinsEarnedByUser($user_id);
        $live_stream_watch_coin = $this->LiveStreamingWatch->countCoinsSpendByUser($user_id);
        //$gifts_total = $this->Gift->countGifts($user_id,$datetime);



        if(strlen($gift_receive[0]['total_amount']) < 1){

            $gift_receive[0]['total_amount'] = "0";

        }

        if(strlen($gift_send[0]['total_amount']) < 1){

            $gift_send[0]['total_amount'] = "0";

        }

        if(strlen($promotion_coin[0]['total_amount']) < 1){

            $promotion_coin[0]['total_amount'] = "0";

        }

        if(strlen($live_stream_earned_coin[0]['total_amount']) < 1){

            $live_stream_earned_coin[0]['total_amount'] = "0";

        }

        if(strlen($live_stream_watch_coin[0]['total_amount']) < 1){

            $live_stream_watch_coin[0]['total_amount'] = "0";

        }







        $earned_money =  $gift_receive[0]['total_amount'] + $purchase_amount_total[0]['total_amount']+  $live_stream_earned_coin[0]['total_amount'] + $referral_earn;





        $total_money = $earned_money -  $gift_send[0]['total_amount'] - $promotion_coin[0]['total_amount'] - $live_stream_watch_coin[0]['total_amount'] -  $withdraw_request_detail[0]['total_coin'];



        $output['gifts_receive'] =  (string)$gift_receive[0]['total_amount'];
        $output['gifts_send'] =  (string)$gift_send[0]['total_amount'];

        $output['total'] =  (string)$total_money;
        return $output;


    }
    public function showUsersInfo(){

        $this->loadModel('User');




        if ($this->request->isPost()) {
            $json = file_get_contents('php://input');
            $data = json_decode($json, TRUE);

            $users = $data['users'];
            foreach ($users as $key=>$user) {

                $array[$key] = $user['user_id'];

            }

            $user_infos = $this->User->getMultipleUsersData($array);





            $output['code'] = 200;

            $output['msg'] = $user_infos;


            echo json_encode($output);


            die();


        }


    }

    public function sendPushNotificationToAllUsers()
    {

        $this->loadModel("User");


        if ($this->request->isPost()) {


            $json = file_get_contents('php://input');
            $data = json_decode($json, TRUE);



            $txt = $data['text'];



            $users =  $this->User->getAllUsersNotification();








            if(count($users) > 0){


                foreach ($users as $user){


                    $device_token = $user['User']['device_token'];

                    if(strlen($device_token) > 15){


                        $notification['to'] = $device_token;
                        $notification['notification']['title'] = "";
                        $notification['notification']['body'] = $txt;
                        $notification['notification']['badge'] = "1";
                        $notification['notification']['sound'] = "default";
                        $notification['notification']['icon'] = "";



                        $notification['data']['title']="";
                        $notification['data']['body'] = $txt;

                        $notification['data']['icon'] = "";
                        $notification['data']['badge'] = "1";
                        $notification['data']['sound'] = "default";


                        Utility::sendPushNotificationToMobileDevice(json_encode($notification));




                    }
                }
            }

            $output['code'] = 200;

            $output['msg'] = "sucessfully sent";
            echo json_encode($output);


            die();

        }


    }

    public function deleteVideoComment(){



        $this->loadModel('VideoComment');

        if ($this->request->isPost()) {
            $json = file_get_contents('php://input');
            $data = json_decode($json, TRUE);

            //$trip['trip_id'] =

            $comment_id =  $data['comment_id'];

            $details =  $this->VideoComment->getDetails($comment_id);


            if(count($details) > 0) {




                $this->VideoComment->id = $comment_id;
                $this->VideoComment->delete();




                $output['code'] = 200;

                $output['msg'] = "deleted";


                echo json_encode($output);


                die();




            }else{

                Message::EMPTYDATA();
                die();
            }

        }




    }

    public function showUserLikedVideos(){

        $this->loadModel('VideoLike');




        if ($this->request->isPost()) {
            $json = file_get_contents('php://input');
            $data = json_decode($json, TRUE);

            $user_id = $data['user_id'];

            $videos = $this->VideoLike->getUserAllVideoLikes($user_id);

            if(count($videos) > 0) {
                foreach ($videos as $key => $video) {


                    $video_likes_count = $this->VideoLike->countLikes($video['Video']['id']);
                    $videos[$key]['Video']['like_count'] = $video_likes_count;

                }


                $output['code'] = 200;

                $output['msg'] = $videos;


                echo json_encode($output);


                die();

            }else{


                Message::EMPTYDATA();
                die();

            }
        }


    }



    public function showVideos()
    {


        $this->loadModel("Video");
        $this->loadModel("VideoComment");
        $this->loadModel("VideoLike");



        if ($this->request->isPost()) {
            $json = file_get_contents('php://input');
            $data = json_decode($json, TRUE);

            if(isset($data['user_id'])){

                $videos = $this->Video->getUserVideos($data['user_id']);


            }else{

                // $videos = $this->Video->getAllVideos();
                $starting_point = $data['starting_point'];
                $videos = $this->Video->getAllVideos($starting_point);
            }

            foreach($videos as $key=>$video) {

                $comment_count = $this->VideoComment->countComments($video['Video']['id']);
                $video_likes_count = $this->VideoLike->countLikes($video['Video']['id']);


                $videos[$key]['Video']['comment_count'] = $comment_count;
                $videos[$key]['Video']['like_count'] = $video_likes_count;


            }




            $output['code'] = 200;

            $output['msg'] = $videos;


            echo json_encode($output);


            die();


        }
    }

    public function showVideoDetail(){

        $this->loadModel('Video');
        $this->loadModel('VideoLike');
        $this->loadModel('VideoFavourite');
        $this->loadModel('VideoComment');




        if ($this->request->isPost()) {
            $json = file_get_contents('php://input');
            $data = json_decode($json, TRUE);

            $video_id = $data['video_id'];


            $video_detail = $this->Video->getDetails($video_id);


            if(count($video_detail) > 0) {

                $video_like_count = $this->VideoLike->countLikes($video_detail['Video']['id']);
                $video_comment_count = $this->VideoComment->countComments($video_detail['Video']['id']);
                $video_detail['Video']['like_count'] = $video_like_count;
                $video_detail['Video']['comment_count'] = $video_comment_count;


                $output['code'] = 200;

                $output['msg'] = $video_detail;


                echo json_encode($output);


                die();

            }else{

                Message::EMPTYDATA();
                die();

            }
        }


    }

    public function showAllVerificationRequests(){

        $this->loadModel('VerificationRequest');





        if ($this->request->isPost()) {


            $json = file_get_contents('php://input');
            $data = json_decode($json, TRUE);



            $details = $this->VerificationRequest->getAll();



            if(count($details) > 0) {


                $output['code'] = 200;

                $output['msg'] = $details;


                echo json_encode($output);


                die();
            }else{


                Message::EMPTYDATA();
                die();
            }

        }


    }

    public function showVideosAgainstHashtag(){

        $this->loadModel('HashtagVideo');
        $this->loadModel('Hashtag');
        $this->loadModel('Video');
        $this->loadModel('VideoLike');
        $this->loadModel('VideoFavourite');
        $this->loadModel('VideoComment');




        if ($this->request->isPost()) {
            $json = file_get_contents('php://input');
            $data = json_decode($json, TRUE);

            $hashtag_id = $data['hashtag_id'];




            $videos = $this->HashtagVideo->getHashtagVideos($hashtag_id);

            //$hashtag_views = $this->HashtagVideo->countHashtagViews($hashtag_details['Hashtag']['id']);



            if(count($videos) > 0) {


                foreach($videos as $key=>$video){




                    $comment_count = $this->VideoComment->countComments($video['Video']['id']);
                    $video_likes_count = $this->VideoLike->countLikes($video['Video']['id']);


                    $videos[$key]['Video']['comment_count'] = $comment_count;
                    $videos[$key]['Video']['like_count'] = $video_likes_count;

                }


                $output['code'] = 200;

                $output['msg'] = $videos;
                // $output['views'] = $hashtag_views[0]['total_sum'];


                echo json_encode($output);


                die();
            }else{


                Message::EMPTYDATA();
                die();

            }

        }


    }




    public function showVideosAgainstSound(){

        $this->loadModel('Sound');
        $this->loadModel('Video');
        $this->loadModel('VideoLike');
        $this->loadModel('VideoFavourite');
        $this->loadModel('VideoComment');




        if ($this->request->isPost()) {
            $json = file_get_contents('php://input');
            $data = json_decode($json, TRUE);
            $user_id = 0;
            $device_id = $data['device_id'];
            $starting_point = $data['starting_point'];
            $sound_id = $data['sound_id'];

            if(isset($data['user_id'])){

                $user_id = $data['user_id'];

            }





            $videos = $this->Video->getVideosAgainstSoundID($user_id,$device_id,$starting_point,$sound_id);



            if(count($videos) > 0) {


                foreach($videos as $key=>$video){





                    $comment_count = $this->VideoComment->countComments($video['Video']['id']);
                    $video_likes_count = $this->VideoLike->countLikes($video['Video']['id']);


                    $videos[$key]['Video']['comment_count'] = $comment_count;
                    $videos[$key]['Video']['like_count'] = $video_likes_count;

                }

                $output['code'] = 200;

                $output['msg'] = $videos;


                echo json_encode($output);


                die();
            }else{


                Message::EMPTYDATA();
                die();

            }

        }


    }




    public function updateVerificationRequest(){

        $this->loadModel('VerificationRequest');
        $this->loadModel('User');

        if ($this->request->isPost()) {
            $json = file_get_contents('php://input');
            $data = json_decode($json, TRUE);


            $verified = $data['verified'];
            $verification_request_id = $data['verification_request_id'];


            $details = $this->VerificationRequest->getDetails($verification_request_id);
            if(count($details) > 0 ) {

                $this->User->id = $details['VerificationRequest']['user_id'];
                $this->User->saveField('verified',$verified);


                $this->VerificationRequest->id = $verification_request_id;
                $this->VerificationRequest->saveField('verified',$verified);

                $details = $this->User->getUserDetailsFromID($details['VerificationRequest']['user_id']);

                $output['code'] = 200;

                $output['msg'] = $details;


                echo json_encode($output);


                die();

            }else{

                Message::EMPTYDATA();
                die();

            }

        }




    }

    public function deleteSoundSection(){



        $this->loadModel('SoundSection');

        if ($this->request->isPost()) {
            $json = file_get_contents('php://input');
            $data = json_decode($json, TRUE);

            //$trip['trip_id'] =

            $id =  $data['id'];

            $details =  $this->SoundSection->getDetails($id);


            if(count($details) > 0) {





                $this->SoundSection->delete($id,true);




                $output['code'] = 200;

                $output['msg'] = "deleted";


                echo json_encode($output);


                die();




            }else{

                Message::EMPTYDATA();
                die();
            }

        }




    }

    public function showLicense(){


        $if_method_exist = method_exists('Premium', 'deleteObjectS3');

        if($if_method_exist){


            $output['code'] = 200;

            $output['msg'] = "Premium";


            echo json_encode($output);

            die();
        }else{

            $output['code'] = 201;

            $output['msg'] = "Regular";


            echo json_encode($output);

            die();
        }
    }


    public function addSoundSection()
    {


        $this->loadModel('SoundSection');


        if ($this->request->isPost()) {


            $json = file_get_contents('php://input');
            $data = json_decode($json, TRUE);

            $section['name'] = $data['name'];


            $section['created'] = date('Y-m-d H:i:s', time());
            $details = $this->SoundSection->ifExist($data['name']);

            if(isset($data['id'])){

                $this->SoundSection->id = $data['id'];
                $this->SoundSection->save($section);

                $details = $this->SoundSection->getDetails($data['id']);



                $output['code'] = 200;
                $output['msg'] = $details;
                echo json_encode($output);
                die();
            }
            if(count($details) < 1) {




                $this->SoundSection->save($section);

                $id = $this->SoundSection->getInsertID();

                $output = array();
                $details = $this->SoundSection->getDetails($id);


                $output['code'] = 200;
                $output['msg'] = $details;
                echo json_encode($output);
                die();



            }else{


                echo Message::DUPLICATEDATE();
                die();

            }

        }
    }

    public function sendVideoPushNotificationToAllUsers()
    {

        $this->loadModel("User");
        $this->loadModel("Video");


        if ($this->request->isPost()) {


            $json = file_get_contents('php://input');
            $data = json_decode($json, TRUE);



            $txt = $data['text'];
            $video_id = $data['video_id'];
            $title = $data['title'];



            $users =  $this->User->getAllUsersNotification();


            $video_details = $this->Video->getDetails($video_id);







            if(count($users) > 0 && count($video_details) > 0){


                foreach ($users as $user){


                    $device_token = $user['User']['device_token'];

                    if(strlen($device_token) > 15){


                        $notification['to'] = $device_token;

                        $notification['notification']['title'] =$title;
                        $notification['notification']['body'] = $txt;
                        $notification['notification']['badge'] = "1";
                        $notification['notification']['sound'] = "default";
                        $notification['notification']['icon'] = "";
                        $notification['notification']['type'] = "video_posted";
                        $notification['notification']['video_id'] = $video_id;
                        $notification['data']['title'] = $title;
                        $notification['data']['body'] = $txt;
                        $notification['data']['icon'] = "";
                        $notification['data']['badge'] = "1";
                        $notification['data']['sound'] = "default";
                        $notification['data']['type'] = "video_posted";
                        $notification['data']['video_id'] = $video_id;
                        Utility::sendPushNotificationToMobileDevice(json_encode($notification));





                    }
                }
            }

            $output['code'] = 200;

            $output['msg'] = "sucessfully sent";
            echo json_encode($output);


            die();

        }


    }


    public function sendUserAccountNotificationToAllUsers()
    {

        $this->loadModel("User");
        $this->loadModel("Video");


        if ($this->request->isPost()) {


            $json = file_get_contents('php://input');
            $data = json_decode($json, TRUE);



            $txt = $data['text'];
            $title = $data['title'];
            $user_id = $data['user_id'];



            $users =  $this->User->getAllUsersNotification();


            $userDetails = $this->User->getUserDetailsFromID($user_id);







            if(count($users) > 0 && count($userDetails) > 0){


                foreach ($users as $user){


                    $device_token = $user['User']['device_token'];

                    if(strlen($device_token) > 15){


                        $notification['to'] = $device_token;

                        $notification['notification']['title'] = $title;
                        $notification['notification']['body'] = $txt;
                        $notification['notification']['badge'] = "1";
                        $notification['notification']['sound'] = "default";
                        $notification['notification']['icon'] = "";
                        $notification['notification']['type'] = "follow";
                        $notification['notification']['user_id'] = $userDetails['User']['id'];
                        $notification['data']['title'] =$title;
                        $notification['data']['body'] = $txt;
                        $notification['data']['icon'] = "";
                        $notification['data']['badge'] = "1";
                        $notification['data']['sound'] = "default";
                        $notification['data']['type'] = "follow";
                        $notification['data']['user_id'] = $user_id;
                        Utility::sendPushNotificationToMobileDevice(json_encode($notification));





                    }
                }
            }

            $output['code'] = 200;

            $output['msg'] = "sucessfully sent";
            echo json_encode($output);


            die();

        }


    }

    public function promoteVideo()
    {


        $this->loadModel('Video');


        if ($this->request->isPost()) {


            $json = file_get_contents('php://input');
            $data = json_decode($json, TRUE);

            $video_id = $data['video_id'];
            $promote = $data['promote'];

            $details = $this->Video->getDetails($video_id);



            if(count($details) > 0){





                $this->Video->id = $details['Video']['id'];
                $this->Video->saveField('promote',$promote);

                $details = $this->Video->getDetails($video_id);


                $output['code'] = 200;
                $output['msg'] = $details;
                echo json_encode($output);
                die();
            }else{



                Message::EMPTYDATA();
                die();


            }



        }
    }

    public function addCategory()
    {


        if(APP_STATUS == "live"){



            $output['code'] = 201;

            $output['msg'] = "Sorry this feature is disabled in demo mode";


            echo json_encode($output);


            die();
        }
        $this->loadModel('Category');

        if ($this->request->isPost()) {
            $created = date('Y-m-d H:i:s', time());

            $json = file_get_contents('php://input');
            $data = json_decode($json, TRUE);



            $post_data['title'] = $this->request->data('title');
            $post_data['parent_id'] = $this->request->data('parent_id');

            $id= $this->request->data('id');



            if($id > 0){







                $details = $this->Category->getDetails($id);
                $image_db = $details['Category']['image'];
                //$icon_db = $details['Category']['icon'];
                if (isset($_FILES['file'])) {
                    if (strpos($details['Category']['image'], "http") !== false) {

                        Utility::deleteObjectS3($image_db);
                        //Utility::deleteObjectS3($icon_db);


                    } else {

                        if (strlen($image_db) > 5) {
                            @unlink($image_db);
                            // @unlink($icon_db);

                        }
                    }


                    if (MEDIA_STORAGE == "s3") {


                        $image_details = Premium::fileUploadToS3Multipart("file");
                        //$icon_details = Utility::fileUploadToS3Multipart("icon");

                        if ($image_details['code'] == 200) {


                            $file_key_image = $image_details['msg'];
                            // $file_icon_key = $icon_details['msg'];


                            $file_image_url = CLOUDFRONT_URL . "/" . $file_key_image;

                            // $file_icon_url = CLOUDFRONT_URL . "/" . $file_icon_key;

                            $post_data['image'] = $file_image_url;
                            //$post_data['icon'] = $file_icon_url;


                        }
                    } else {

                        $image_path = Utility::uploadAMultipartFileIntoFolder("file");
                        //$icon_path =  Utility::uploadAMultipartFileIntoFolder("icon");

                        $post_data['image'] = $image_path;
                        //$post_data['icon'] = $icon_path;
                    }

                }





                $this->Category->id = $id;
                $this->Category->save($post_data);
                $details =  $this->Category->getDetails($id);


                $output['code'] = 200;
                $output['msg'] = $details;
                echo json_encode($output);
                die();

            }
            if (isset($_FILES['file'])) {

                if (MEDIA_STORAGE == "s3") {


                    $result = Premium::fileUploadToS3Multipart("file");
                    // $icon_details = Utility::fileUploadToS3Multipart("icon");


                    if ($result['code'] == 200) {


                        if (strpos($result['msg'], "tictic-video") !== false) {

                            $key = str_replace("tictic-video", "", $result['msg']);
                            $file_url = CLOUDFRONT_URL . $key;
                        } else {

                            $file_url = CLOUDFRONT_URL . "/" . $result['msg'];
                        }

                        $post_data['image'] = $file_url;


                    }
                } else {

                    $image_path = Utility::uploadAMultipartFileIntoFolder("file");
                    // $icon_path =  Utility::uploadAMultipartFileIntoFolder("icon");

                    $post_data['image'] = $image_path;
                    // $post_data['icon'] = $icon_path;
                }


            }





            $post_data['created'] =  $created;
            $this->Category->save($post_data);
            $id = $this->Category->getInsertID();
            $details =  $this->Category->getDetails($id);


            $output['code'] = 200;
            $output['msg'] = $details;
            echo json_encode($output);
            die();




        }



    }

    public function addCoinWorth()
    {





        if ($this->request->isPost()) {


            $json = file_get_contents('php://input');
            $data = json_decode($json, TRUE);


            if (in_array("CoinWorth", App::objects('Model'))) {

                $this->loadModel('CoinWorth');
            }else{

                $output['code'] = 201;

                $output['msg'] = "Contact hello@qboxus.com to get these premium features";


                echo json_encode($output);


                die();
            }
            $price = $data['price'];

            $worth_data['price'] = $price;
            $details = $this->CoinWorth->getAll();



            if(count($details) > 0){





                $this->CoinWorth->id = $details['CoinWorth']['id'];
                $this->CoinWorth->saveField('price',$price);

                $details = $this->CoinWorth->getDetails($details['CoinWorth']['id']);



                $output['code'] = 200;
                $output['msg'] = $details;
                echo json_encode($output);
                die();
            }else{




                $this->CoinWorth->save($worth_data);

                $id = $this->CoinWorth->getInsertID();

                $output = array();
                $details = $this->CoinWorth->getDetails($id);


                $output['code'] = 200;
                $output['msg'] = $details;
                echo json_encode($output);
                die();

            }



        }
    }

    public function showCoinWorth()
    {





        if ($this->request->isPost()) {
            $json = file_get_contents('php://input');
            $data = json_decode($json, TRUE);


            if (in_array("CoinWorth", App::objects('Model'))) {

                $this->loadModel('CoinWorth');
            }else{

                $output['code'] = 201;

                $output['msg'] = "Contact hello@qboxus.com to get these premium features";


                echo json_encode($output);


                die();
            }

            $details = $this->CoinWorth->getAll();



            if(count($details) > 0) {


                $output['code'] = 200;

                $output['msg'] = $details;


                echo json_encode($output);


                die();
            }else{

                Message::EMPTYDATA();
                die();
            }

        }
    }

    public function featuredGift()
    {


        $this->loadModel('Gift');

        if ($this->request->isPost()) {


            $json = file_get_contents('php://input');
            $data = json_decode($json, TRUE);


            //$user['password'] = $data['password'];
            $featured = $data['featured'];

            $id = $data['id'];



            $gift_details = $this->Gift->getDetails($id);
            if(count($gift_details)) {
                $this->Gift->id = $id;
                $this->Gift->saveField("featured",$featured);


                $output = array();
                $gift_details = $this->Gift->getDetails($id);


                $output['code'] = 200;
                $output['msg'] = $gift_details;
                echo json_encode($output);

            }else{
                Message::EMPTYDATA();
                die();


            }
        }
    }

    public function addGift()
    {





        if ($this->request->isPost()) {


            $json = file_get_contents('php://input');
            $data = json_decode($json, TRUE);


            $title = $this->request->data('title');
            $coin = $this->request->data('coin');
            $time = $this->request->data('time');
            $id = $this->request->data('id');

            $gift_data['title'] = $title;
            $gift_data['coin'] = $coin;
            $gift_data['time'] = $time;





            $gift_data['created'] = date('Y-m-d H:i:s', time());

            if (in_array("Gift", App::objects('Model'))) {
                $this->loadModel("Gift");
            }else{

                $output['code'] = 201;

                $output['msg'] = "Contact hello@qboxus.com to get these premium features";


                echo json_encode($output);


                die();
            }
            if($id > 0){




                $this->Gift->id = $id;
                $this->Gift->save($gift_data);

                $details = $this->Gift->getDetails($data['id']);



                $output['code'] = 200;
                $output['msg'] = $details;
                echo json_encode($output);
                die();
            }

            $gift_data['type'] = "image";
            $file_sound_url = "";
            $file_image_url = "";
            $file_icon_url = "";
            $details = $this->Gift->ifExist($data['title']);
            if(count($details) < 1) {
                if (MEDIA_STORAGE == "s3") {
                    if (isset($_FILES['image'])) {


                        $image_details = Premium::fileUploadToS3Multipart("image");

                        if ($image_details['code'] == 200) {


                            $file_key = $image_details['msg'];




                            $cloudfront_url = str_replace('tictic-video', '', CLOUDFRONT_URL);
                            $file_image_url = $cloudfront_url."/". $file_key;
                            $extension = pathinfo($file_image_url, PATHINFO_EXTENSION);
                            if($extension == "gif"){

                                $gift_data['type'] = "gif";
                            }

                            //$file_image_url = Utility::getCloudFrontUrl($image_details['msg'], "/images");



                        }
                    }


                    if (isset($_FILES['icon'])) {


                        $icon_details = Premium::fileUploadToS3Multipart("icon");



                        if ($icon_details['code'] == 200) {


                            $file_key = $icon_details['msg'];




                            $cloudfront_url = str_replace('tictic-video', '', CLOUDFRONT_URL);


                            $file_icon_url = $cloudfront_url."/". $file_key;
                            echo $file_icon_url;

                        }
                    }

                    if (isset($_FILES['sound'])) {


                        $sound_details = Premium::fileUploadToS3Multipart("sound", "mp3");

                        if ($sound_details['code'] == 200) {

                            $file_key = $sound_details['msg'];




                            $cloudfront_url = str_replace('tictic-video', '', CLOUDFRONT_URL);
                            $file_sound_url = $cloudfront_url. $file_key;

                        }
                    }


                }else{

                    if (isset($_FILES['image'])) {
                        $file_image_url = Utility::uploadAMultipartFileIntoFolder("image",null, UPLOADS_FOLDER_URI);

                        $extension = pathinfo($file_image_url, PATHINFO_EXTENSION);
                        if($extension == "gif"){

                            $gift_data['type'] = "gif";
                        }

                    }

                    if (isset($_FILES['icon'])) {
                        $file_icon_url = Utility::uploadAMultipartFileIntoFolder("icon",null, UPLOADS_FOLDER_URI);

                    }

                    if (isset($_FILES['sound'])) {
                        $file_sound_url = Utility::uploadAMultipartFileIntoFolder("sound",null, UPLOADS_FOLDER_URI);

                    }




                }



                /*if (isset($data['image']) && $data['image'] != " ") {


                    $image = $data['image'];
                    $folder_url = UPLOADS_FOLDER_URI;

                    $filePath = Utility::uploadFileintoFolderDir($image, $folder_url);
                    $gift_data['image'] = $filePath;
                }*/

                $gift_data['image'] = $file_image_url;
                $gift_data['icon'] = $file_icon_url;
                $gift_data['sound'] = $file_sound_url;
                $this->Gift->save($gift_data);

                $id = $this->Gift->getInsertID();

                $output = array();
                $details = $this->Gift->getDetails($id);


                $output['code'] = 200;
                $output['msg'] = $details;
                echo json_encode($output);
                die();



            }else{


                echo Message::DUPLICATEDATE();
                die();

            }

        }
    }

    public function deleteGift(){



        if ($this->request->isPost()) {
            $json = file_get_contents('php://input');
            $data = json_decode($json, TRUE);

            $id = $data['id'];

            if (in_array("Gift", App::objects('Model'))) {
                $this->loadModel("Gift");
            }else{

                $output['code'] = 201;

                $output['msg'] = "Contact hello@qboxus.com to get these premium features";


                echo json_encode($output);


                die();
            }
            $details = $this->Gift->getDetails($id);
            if(count($details) > 0 ) {

                $this->Gift->id = $id;
                $this->Gift->delete();

                $output['code'] = 200;

                $output['msg'] = "deleted";


                echo json_encode($output);


                die();

            }else{

                $output['code'] = 201;

                $output['msg'] = "Invalid id: Do not exist";


                echo json_encode($output);


                die();


            }

        }




    }

    public function showGifts()
    {





        if ($this->request->isPost()) {
            $json = file_get_contents('php://input');
            $data = json_decode($json, TRUE);


            $if_method_exist = method_exists('Premium', 'deleteObjectS3');

            if($if_method_exist) {

                if (in_array("Gift", App::objects('Model'))) {
                    $this->loadModel("Gift");
                }else{

                    $output['code'] = 201;

                    $output['msg'] = "Contact hello@qboxus.com to get these premium features";


                    echo json_encode($output);


                    die();
                }


                if (isset($data['id'])) {

                    $gifts = $this->Gift->getDetails($data['id']);

                } else {
                    $gifts = $this->Gift->getAll();

                }


                $output['code'] = 200;

                $output['msg'] = $gifts;


                echo json_encode($output);


                die();
            }else{


                $output['code'] = 201;

                $output['msg'] = "Contact hello@qboxus.com to get these premium features";


                echo json_encode($output);


                die();
            }

        }
    }

    public function showWithdrawRequest()
    {




        if ($this->request->isPost()) {
            $json = file_get_contents('php://input');
            $data = json_decode($json, TRUE);
            if (in_array("WithdrawRequest", App::objects('Model'))) {

                $this->loadModel("WithdrawRequest");

            }else{

                $output['code'] = 201;

                $output['msg'] = "Contact hello@qboxus.com to get these premium features";


                echo json_encode($output);


                die();
            }

            if(isset($data['user_id'])) {
                $user_id = $data['user_id'];


                $requests = $this->WithdrawRequest->getUserPendingWithdrawRequest($user_id);

            }else if(isset($data['id'])) {
                $requests = $this->WithdrawRequest->getDetails($data['id']);

            }else{
                $requests = $this->WithdrawRequest->getAllPendingRequests(0);
            }






            $output['code'] = 200;

            $output['msg'] = $requests;


            echo json_encode($output);


            die();


        }
    }
    public function showSettings(){

        $this->loadModel('Setting');




        if ($this->request->isPost()) {
            $json = file_get_contents('php://input');
            $data = json_decode($json, TRUE);


            if(isset($data['id'])){

                $details = $this->Setting->getDetails($data['id']);

            }else{

                $details = $this->Setting->getAll();
            }




            if(count($details) > 0) {


                $output['code'] = 200;

                $output['msg'] = $details;


                echo json_encode($output);


                die();
            }else{


                Message::EMPTYDATA();
                die();
            }

        }


    }

    public function deleteSetting(){

        $this->loadModel('Setting');


        if ($this->request->isPost()) {
            $json = file_get_contents('php://input');
            $data = json_decode($json, TRUE);

            $id = $data['id'];

            $details = $this->Setting->getDetails($id);

            if(count($details) > 0 ) {
                $this->Setting->id = $id;
                $this->Setting->delete();


                $output['code'] = 200;

                $output['msg'] = "deleted";


                echo json_encode($output);


                die();

            }else{

                $output['code'] = 201;

                $output['msg'] = "Invalid id: Do not exist";


                echo json_encode($output);


                die();


            }

        }




    }

    public function addSetting()
    {


        $this->loadModel('Setting');


        if ($this->request->isPost()) {


            $json = file_get_contents('php://input');
            $data = json_decode($json, TRUE);

            $report['type'] = $data['type'];
            $report['value'] = $data['value'];


            $report['created'] = date('Y-m-d H:i:s', time());
            $details = $this->Setting->checkDuplicate($data['type']);

            if(isset($data['id'])){

                $this->Setting->id = $data['id'];
                $this->Setting->save($report);

                $details = $this->Setting->getDetails($data['id']);



                $output['code'] = 200;
                $output['msg'] = $details;
                echo json_encode($output);
                die();
            }
            if(count($details) < 1) {




                $this->Setting->save($report);

                $id = $this->Setting->getInsertID();

                $output = array();
                $details = $this->Setting->getDetails($id);


                $output['code'] = 200;
                $output['msg'] = $details;
                echo json_encode($output);
                die();



            }else{


                echo Message::DUPLICATEDATE();
                die();

            }

        }
    }
    public function withdrawRequestApproval()
    {


        $this->loadModel("User");

        if ($this->request->isPost()) {


            $json = file_get_contents('php://input');
            $data = json_decode($json, TRUE);
            if (in_array("WithdrawRequest", App::objects('Model'))) {

                $this->loadModel("WithdrawRequest");

            }else{

                $output['code'] = 201;

                $output['msg'] = "Contact hello@qboxus.com to get these premium features";


                echo json_encode($output);


                die();
            }
            //  $withdraw_data['user_id'] = $data['user_id'];
            $id = $data['id'];
            $withdraw_data['status'] = $data['status'];

            $withdraw_data['updated'] = date('Y-m-d H:i:s', time());


            $details = $this->WithdrawRequest->getDetails($id);

            if(count($details) > 0) {

                if($data['status'] == 1){
                    $this->User->id = $details['WithdrawRequest']['user_id'];
                    $user_wallet['wallet'] = 0;
                    $user_wallet['reset_wallet_datetime'] = date('Y-m-d H:i:s', time());
                    $this->User->save($user_wallet);



                }
                $this->WithdrawRequest->id = $id;
                $this->WithdrawRequest->save($withdraw_data);


                $output = array();
                $details = $this->WithdrawRequest->getDetails($id);


                $output['code'] = 200;
                $output['msg'] = $details;
                echo json_encode($output);
                die();

            }else{


                Message::EMPTYDATA();
                die();
            }



        }
    }



    public function showSounds()
    {


        $this->loadModel("Sound");



        if ($this->request->isPost()) {
            $json = file_get_contents('php://input');
            $data = json_decode($json, TRUE);



            if(isset($data['publish'])){

                $sounds = $this->Sound->getSoundsAccordingToStatus($data['publish']);

            }else {
                $sounds = $this->Sound->getSounds();

            }






            $output['code'] = 200;

            $output['msg'] = $sounds;


            echo json_encode($output);


            die();


        }
    }


    public function showSoundSections()
    {


        $this->loadModel("SoundSection");
        $this->loadModel("Sound");



        if ($this->request->isPost()) {
            $json = file_get_contents('php://input');
            $data = json_decode($json, TRUE);



            if(isset($data['id'])){

                $sounds = $this->SoundSection->getDetails($data['id']);

                $output['code'] = 200;

                $output['msg'] = $sounds;


                echo json_encode($output);


                die();

            }else {
                $sounds = $this->SoundSection->getAll();

            }






            if(count($sounds) > 1) {


                foreach ($sounds as $key => $sound) {


                    $total_sounds = $this->Sound->getSoundsCount($sound['SoundSection']['id']);
                    $sounds[$key]['SoundSection']['total_sounds'] = $total_sounds;
                }
            }else if(count($sounds) == 1) {


                $total_sounds = $this->Sound->getSoundsCount($sounds['SoundSection']['id']);
                $sounds['SoundSection']['total_sounds'] = $total_sounds;

            }else {

                Message::EMPTYDATA();
                die();


            }
            $output['code'] = 200;

            $output['msg'] = $sounds;


            echo json_encode($output);


            die();


        }
    }

    public function deleteCategory(){

        $this->loadModel('Category');





        if ($this->request->isPost()) {
            $json = file_get_contents('php://input');
            $data = json_decode($json, TRUE);

            $id = $data['id'];




            $this->Category->delete($id,true);





            $output['code'] = 200;

            $output['msg'] = "deleted";


            echo json_encode($output);


            die();


        }


    }
    public function deletePromotion(){

        $this->loadModel('Promotion');

        if ($this->request->isPost()) {
            $json = file_get_contents('php://input');
            $data = json_decode($json, TRUE);

            $id = $data['id'];

            $details = $this->Promotion->getDetails($id);
            if(count($details) > 0 ) {

                $this->Promotion->id = $id;
                $this->Promotion->delete();

                $output['code'] = 200;

                $output['msg'] = "deleted";


                echo json_encode($output);


                die();

            }else{

                $output['code'] = 201;

                $output['msg'] = "Invalid id: Do not exist";


                echo json_encode($output);


                die();


            }

        }




    }

    public function showAllPromotions(){

        $this->loadModel('Promotion');
        $this->loadModel('VideoWatch');
        $this->loadModel('VideoLike');




        if ($this->request->isPost()) {
            $json = file_get_contents('php://input');
            $data = json_decode($json, TRUE);



            $promotions =  $this->Promotion->getAll();

            $total_coins_spent = 0;
            $total_video_views = 0;
            $total_destination_tap = 0;
            $total_video_likes = 0;
            if(count($promotions) > 0){

                foreach($promotions as $key=>$val){


                    $video_id =   $val['Promotion']['video_id'];
                    $start_datetime =   $val['Promotion']['start_datetime'];
                    $coin =   $val['Promotion']['coin'];
                    $end_datetime =   $val['Promotion']['end_datetime'];
                    $destination_tap =   $val['Promotion']['destination_tap'];

                    $videos_id_array[0] = $video_id;
                    $count_views =   $this->VideoWatch->countWatchVideos($videos_id_array,$start_datetime,$end_datetime);

                    $video_likes =   $this->VideoLike->countLikesBetweenDatetime($videos_id_array,$start_datetime,$end_datetime);

                    $promotions[$key]['Promotion']['views'] = $count_views;
                    $total_coins_spent = $coin + $total_coins_spent;
                    $total_video_views = $count_views + $total_video_views;
                    $total_destination_tap = $destination_tap + $total_destination_tap;
                    $total_video_likes = $video_likes + $total_video_likes;
                }



            }



            $output['code'] = 200;

            $output['msg']['Promotions'] = $promotions;
            $output['msg']['Stats']['coins_spent'] = $total_coins_spent;
            $output['msg']['Stats']['total_video_views'] = $total_video_views;
            $output['msg']['Stats']['total_destination_tap'] = $total_destination_tap;
            $output['msg']['Stats']['total_video_likes'] = $total_video_likes;


            echo json_encode($output);


            die();


        }


    }
    public function approvePromotion(){

        $this->loadModel('Promotion');

        if ($this->request->isPost()) {
            $json = file_get_contents('php://input');
            $data = json_decode($json, TRUE);

            $promotion_id = $data['promotion_id'];
            $active = $data['active'];

            $details = $this->Promotion->getDetails($promotion_id);
            if(count($details) > 0 ) {

                $this->Promotion->id = $promotion_id;
                $this->Promotion->saveField('active',$active);
                $details = $this->Promotion->getDetails($promotion_id);
                $output['code'] = 200;

                $output['msg'] = $details;


                echo json_encode($output);


                die();

            }else{

                $output['code'] = 201;

                $output['msg'] = "Invalid id: Do not exist";


                echo json_encode($output);


                die();


            }

        }




    }

    public function publishSound(){

        $this->loadModel('Sound');

        if ($this->request->isPost()) {
            $json = file_get_contents('php://input');
            $data = json_decode($json, TRUE);

            $sound_id = $data['sound_id'];
            $approve = $data['publish'];

            $details = $this->Sound->getDetails($sound_id);
            if(count($details) > 0 ) {

                $this->Sound->id = $sound_id;
                $this->Sound->saveField('publish',$approve);
                $details = $this->Sound->getDetails($sound_id);
                $output['code'] = 200;

                $output['msg'] = $details;


                echo json_encode($output);


                die();

            }else{

                $output['code'] = 201;

                $output['msg'] = "Invalid id: Do not exist";


                echo json_encode($output);


                die();


            }

        }




    }
    public function addSoundInSection(){

        $this->loadModel('Sound');

        if ($this->request->isPost()) {
            $json = file_get_contents('php://input');
            $data = json_decode($json, TRUE);

            $sound_id = $data['sound_id'];
            $sound_section_id = $data['sound_section_id'];

            $details = $this->Sound->getDetails($sound_id);
            if(count($details) > 0 ) {

                $this->Sound->id = $sound_id;
                $this->Sound->saveField('sound_section_id',$sound_section_id);
                $details = $this->Sound->getDetails($sound_id);
                $output['code'] = 200;

                $output['msg'] = $details;


                echo json_encode($output);


                die();

            }else{

                $output['code'] = 201;

                $output['msg'] = "Invalid id: Do not exist";


                echo json_encode($output);


                die();


            }

        }




    }

    public function deleteVideo(){

        $this->loadModel('Video');

        if ($this->request->isPost()) {
            $json = file_get_contents('php://input');
            $data = json_decode($json, TRUE);

            $video_id = $data['video_id'];

            if(APP_STATUS == "demo") {



                $code =  201;
                $msg = "You cannot delete account in demo account. You need to contant qboxus for that";

                $output['code'] = $code;

                $output['msg'] = $msg;



                die();

            }
            $details = $this->Video->getDetails($video_id);
            if(count($details) > 0 ) {


                $video_url =  $details['Video']['video'];
                $thum_url =  $details['Video']['thum'];
                $gif_url =  $details['Video']['gif'];
                $key = 'http';


                if (strpos($video_url, $key) !== false) {

                    $if_method_exist = method_exists('Premium', 'deleteObjectS3');

                    if ($if_method_exist) {
                        $result1 = Premium::deleteObjectS3($video_url);

                        $result2 = Premium::deleteObjectS3($thum_url);
                        $result3 = Premium::deleteObjectS3($gif_url);
                        if ($result1 && $result2 && $result3) {

                            $code = 200;
                            $msg = "deleted successfully";
                        } else {

                            $code = 201;
                            $msg = "something went wrong in deleting the file from the cdn";
                        }
                    }else{


                        $code =  201;
                        $msg = "Buy  premium features and setup S3. or delete S3 urls from database";
                    }
                }else{

                    unlink($video_url);
                    unlink($thum_url);
                    unlink($gif_url);
                    $code =  200;
                    $msg = "successfully deleted";


                }
            } else {

                $code =  200;
                $msg = "video has been already deleted";

            }


            $this->Video->delete($video_id,true);

            $output['code'] = $code;

            $output['msg'] = $msg;


            echo json_encode($output);


            die();

        }




    }

    public function showVideoComments(){

        $this->loadModel('VideoComment');




        if ($this->request->isPost()) {
            $json = file_get_contents('php://input');
            $data = json_decode($json, TRUE);

            $video_id = $data['video_id'];

            $videos = $this->VideoComment->getVideoComments($video_id);





            $output['code'] = 200;

            $output['msg'] = $videos;


            echo json_encode($output);


            die();


        }


    }


    public function deleteSound(){

        $this->loadModel('Sound');
        $this->loadModel('Video');

        if ($this->request->isPost()) {
            $json = file_get_contents('php://input');
            $data = json_decode($json, TRUE);

            $sound_id = $data['sound_id'];

            $details = $this->Sound->getDetails($sound_id);
            if(count($details) > 0 ) {
                $audio_url =  $details['Sound']['audio'];
                $key = 'http';


                if (strpos($audio_url, $key) !== false) {

                    $if_method_exist = method_exists('Premium', 'deleteObjectS3');

                    if ($if_method_exist) {
                        $result1 = Premium::deleteObjectS3($audio_url);

                        if ($result1) {

                            $code = 200;
                            $msg = "deleted successfully";
                        } else {

                            $code = 201;
                            $msg = "something went wrong in deleting the file from the cdn";
                        }
                    }else{


                        $code =  201;
                        $msg = "Buy an Premium license and setup S3. or delete S3 urls from database";
                    }
                }else{
                    Utility::unlinkFile($audio_url);


                    $code =  200;
                    $msg = "successfully deleted";


                }
                $this->Sound->delete($sound_id);

                $all_videos = $this->Video->getAllVideosAgainstSoundID($sound_id);

                if(count($all_videos) > 0) {
                    foreach ($all_videos as $key => $val) {

                        $video_ids[$key] = $val['Video']['id'];

                    }
                    $this->Video->updateSoundIDs($video_ids);

                }


                $output['code'] = 200;

                $output['msg'] = "deleted";


                echo json_encode($output);


                die();

            }else{

                $output['code'] = 201;

                $output['msg'] = "Invalid id: Do not exist";


                echo json_encode($output);


                die();


            }

        }




    }

    public function showAppSlider()
    {

        $this->loadModel("AppSlider");


        if ($this->request->isPost()) {

            $json = file_get_contents('php://input');
            $data = json_decode($json, TRUE);



            $images = $this->AppSlider->getAll();


            $output['code'] = 200;

            $output['msg'] = $images;
            echo json_encode($output);


            die();
        }
    }


    public function deleteAppSlider(){

        $this->loadModel('AppSlider');

        if ($this->request->isPost()) {
            $json = file_get_contents('php://input');
            $data = json_decode($json, TRUE);

            $id = $data['id'];

            $details = $this->AppSlider->getDetails($id);
            if(count($details) > 0 ) {

                $this->AppSlider->id = $id;
                $this->AppSlider->delete();

                $output['code'] = 200;

                $output['msg'] = "deleted";


                echo json_encode($output);


                die();

            }else{

                $output['code'] = 201;

                $output['msg'] = "Invalid id: Do not exist";


                echo json_encode($output);


                die();


            }

        }




    }

    public function addAppSlider()
    {


        $this->loadModel('AppSlider');
        if ($this->request->isPost()) {
            $json = file_get_contents('php://input');
            $data = json_decode($json, TRUE);


            $user_id = 1;
            $url = $data['url'];
            $image_data['url'] = $url;


            if (isset($data['id'])) {
                $id = $data['id'];

                if (isset($data['image']) && $data['image'] != " ") {

                    $app_slider = $this->AppSlider->getDetails($id);
                    $image_path = $app_slider['AppSlider']['image'];

                    @unlink($image_path);
                    $image = $data['image'];
                    $folder_url = UPLOADS_FOLDER_URI;

                    $filePath = Utility::uploadFileintoFolder($user_id, $image, $folder_url);
                    $image_data['image'] = $filePath;
                }


                $this->AppSlider->id = $id;
                $this->AppSlider->save($image_data);
                $app_slider = $this->AppSlider->getDetails($id);
                $output['code'] = 200;

                $output['msg'] = $app_slider;


                echo json_encode($output);


                die();

            } else {

                if (isset($data['image']) && $data['image'] != " ") {

                    $image = $data['image'];
                    $folder_url = UPLOADS_FOLDER_URI;

                    $filePath = Utility::uploadFileintoFolder($user_id, $image, $folder_url);
                    $image_data['image'] = $filePath;
                }




                $this->AppSlider->save($image_data);
                $id = $this->AppSlider->getInsertID();

                $app_slider = $this->AppSlider->getDetails($id);
                $output['code'] = 200;

                $output['msg'] = $app_slider;


                echo json_encode($output);


                die();
            }

        }
    }


    public function addAppSlider2()
    {


        $this->loadModel('AppSlider');
        if ($this->request->isPost()) {
            $json = file_get_contents('php://input');



            $url = $this->request->data('url');






                if (MEDIA_STORAGE == "s3") {
                    $result = Premium::fileUploadToS3Multipart("file", "png");


                    if ($result['code'] == 200) {



                        if (strpos($result['msg'], "tictic-video") !== false) {

                           $key =  str_replace("tictic-video","",$result['msg']);
                            $file_url = CLOUDFRONT_URL.$key;
                        }else{

                            $file_url = CLOUDFRONT_URL."/".$result['msg'];
                        }

                    }

                }else{

                    $file_url = Utility::uploadAMultipartFileIntoFolder("file",null, UPLOADS_FOLDER_URI);


                }

            $image_data['image'] = $file_url;
            $image_data['url'] = $url;
            $this->AppSlider->save($image_data);
            $id = $this->AppSlider->getInsertID();

            $app_slider = $this->AppSlider->getDetails($id);
            $output['code'] = 200;

            $output['msg'] = $app_slider;


            echo json_encode($output);


            die();


        }
    }

    public function showCategories()
    {

        $this->loadModel("Category");


        if ($this->request->isPost()) {



            $json = file_get_contents('php://input');
            $data = json_decode($json, TRUE);



            if(isset($data['id'])){

                $details = $this->Category->getDetails($data['id']);
            }else{

                $parent = $data['parent_id'];
                $details = $this->Category->getAll($parent);
            }





            if(count($details) > 0){



                $output['code'] = 200;

                $output['msg'] = $details;
                echo json_encode($output);


                die();
            }else{
                Message::EMPTYDATA();
                die();
            }
        }
    }
    public function deleteSticker(){

        $this->loadModel('Sticker');

        if ($this->request->isPost()) {
            $json = file_get_contents('php://input');
            $data = json_decode($json, TRUE);

            $id = $data['id'];

            $details = $this->Sticker->getDetails($id);
            if(count($details) > 0 ) {

                $this->Sticker->id = $id;
                $this->Sticker->delete();

                $output['code'] = 200;

                $output['msg'] = "deleted";


                echo json_encode($output);


                die();

            }else{

                $output['code'] = 201;

                $output['msg'] = "Invalid id: Do not exist";


                echo json_encode($output);


                die();


            }

        }




    }

    public function deleteTopic(){

        $this->loadModel('Topic');


        if ($this->request->isPost()) {
            $json = file_get_contents('php://input');
            $data = json_decode($json, TRUE);

            $id = $data['id'];

            $details = $this->Topic->getDetails($id);

            if(count($details) > 0 ) {
                $this->Topic->id = $id;
                $this->Topic->delete();


                $output['code'] = 200;

                $output['msg'] = "deleted";


                echo json_encode($output);


                die();

            }else{

                $output['code'] = 201;

                $output['msg'] = "Invalid id: Do not exist";


                echo json_encode($output);


                die();


            }

        }




    }

    public function addTopic()
    {



        $this->loadModel('Topic');

        if ($this->request->isPost()) {


            $json = file_get_contents('php://input');
            $data = json_decode($json, TRUE);



            $title = $data['title'];

            $post_data['title'] = $title;


            $details = $this->Topic->checkDuplicate($title);



            if(count($details) > 0){





                $this->Topic->id = $details['Topic']['id'];
                $this->Topic->saveField('title',$title);

                $details = $this->Topic->checkDuplicate($title);



                $output['code'] = 200;
                $output['msg'] = $details;
                echo json_encode($output);
                die();
            }else{




                $this->Topic->save($post_data);

                $id = $this->Topic->getInsertID();

                $output = array();
                $details = $this->Topic->getDetails($id);


                $output['code'] = 200;
                $output['msg'] = $details;
                echo json_encode($output);
                die();

            }



        }
    }

    public function showTopics(){

        $this->loadModel('Topic');




        if ($this->request->isPost()) {
            $json = file_get_contents('php://input');
            $data = json_decode($json, TRUE);



            if(isset($data['id'])){

                $details = $this->Topic->getDetails($data['id']);
            }else {
                $details = $this->Topic->getAll();

            }
            if(count($details) > 0) {


                $output['code'] = 200;

                $output['msg'] = $details;


                echo json_encode($output);


                die();
            }else{


                Message::EMPTYDATA();
                die();
            }

        }


    }
    public function showStickers()
    {

        $this->loadModel("Sticker");


        if ($this->request->isPost()) {

            $json = file_get_contents('php://input');
            $data = json_decode($json, TRUE);



            $images = $this->Sticker->getAllAdmin();


            $output['code'] = 200;

            $output['msg'] = $images;
            echo json_encode($output);


            die();
        }
    }

    public function addSticker()
    {


        $this->loadModel('Sticker');
        if ($this->request->isPost()) {
            $json = file_get_contents('php://input');
            $data = json_decode($json, TRUE);

            $image =  $data['image'];
            $title =  $data['title'];

            $folder_url = UPLOADS_FOLDER_URI;
            if (isset($data['id'])) {

                $id = $data['id'];


                $sticker_details = $this->Sticker->getDetails($id);


                if (count($sticker_details) > 0) {

                    $image_url = $sticker_details['Sticker']['image'];


                    if (strlen($image_url) > 5) {

                        $key = 'http';


                        if (strpos($image_url, $key) !== false) {

                            $if_method_exist = method_exists('Premium', 'addSticker');

                            if ($if_method_exist) {
                                Premium::deleteObjectS3($image_url);


                            }

                        } else {


                            @unlink($image_url);

                        }

                    }


                    $filePath = Utility::uploadFileintoFolderDir($image, $folder_url, "png");
                    $sticker_data['image'] = $filePath;
                    $sticker_data['title'] = $title;

                    $if_method_exist = method_exists('Premium', 'addSticker');
                    if (MEDIA_STORAGE == "s3") {


                        if ($if_method_exist) {


                            $result_big = Premium::fileUploadToS3($filePath, "png");


                            if ($result_big['code'] == 200) {
                                $sticker_data['image'] = Utility::getCloudFrontUrl($result_big['msg'], "/sticker");

                            }


                            @unlink($filePath);

                        }

                    }

                    $this->Sticker->id = $data['id'];
                    $this->Sticker->saveField("image", $sticker_data['image']);

                    $details = $this->Sticker->getDetails($data['id']);
                }
            }

            $filePath = Utility::uploadFileintoFolderDir($image, $folder_url, "png");


            $if_method_exist = method_exists('Premium', 'addSticker');
            if (MEDIA_STORAGE == "s3") {



                if ($if_method_exist) {



                    $result_big = Premium::fileUploadToS3($filePath, "png");


                    if ($result_big['code'] == 200) {
                        $sticker_data['image'] = Utility::getCloudFrontUrl($result_big['msg'], "/sticker");

                    }


                    @unlink($filePath);

                }

            }else {





                $sticker_data['image'] = $filePath;
            }

            $sticker_data['title'] = $title;
            $this->Sticker->save($sticker_data);
            $id = $this->Sticker->getInsertID();

            $app_slider = $this->Sticker->getDetails($id);
            $output['code'] = 200;

            $output['msg'] = $app_slider;


            echo json_encode($output);


            die();


        }
    }


    public function blockVideo()
    {


        $this->loadModel('Video');

        if ($this->request->isPost()) {


            $json = file_get_contents('php://input');
            $data = json_decode($json, TRUE);


            //$user['password'] = $data['password'];
            $block = $data['block'];

            $video_id = $data['video_id'];



            $video_details = $this->Video->getDetails($video_id);
            if(count($video_details)) {
                $this->Video->id = $video_id;
                $this->Video->saveField("block",$block);


                $output = array();
                $userDetails = $this->Video->getDetails($video_id);


                $output['code'] = 200;
                $output['msg'] = $userDetails;
                echo json_encode($output);

            }else{
                Message::EMPTYDATA();
                die();


            }
        }
    }

    public function test1(){
        $data = Utility::get_hashtags("hello Hi I am #irfan #but ");
        pr($data);

    }


    public function editVideo(){

        $this->loadModel('Video');
        $this->loadModel('Sound');
        $this->loadModel('Hashtag');
        $this->loadModel('HashtagVideo');
        $this->loadModel('User');
        $this->loadModel('Notification');


        if ($this->request->isPost()) {


            $created = date('Y-m-d H:i:s', time());
            $user_id = $this->request->data('user_id');

            $description = $this->request->data('description');
            $privacy_type = $this->request->data('privacy_type');
            $allow_comments = $this->request->data('allow_comments');
            $allow_duet = $this->request->data('allow_duet');
            $views = $this->request->data('views');
            $video_id = $this->request->data('video_id');
            $hashtags_json = $this->request->data('hashtags_json');
            $users_json = $this->request->data('users_json');





            $data_hashtag = json_decode($hashtags_json, TRUE);



            $details = $this->Video->getDetails($video_id);


            if(count($details) > 0) {


                $video_save['description'] = $description;
                $video_save['privacy_type'] = $privacy_type;
                $video_save['allow_comments'] = $allow_comments;
                $video_save['allow_duet'] = $allow_duet;
                $video_save['user_id'] = $user_id;
                $video_save['views'] = $views;
                $video_save['created'] = $created;

                $this->Video->id = $details['Video']['id'];
                if (!$this->Video->save($video_save)) {
                    echo Message::DATASAVEERROR();
                    die();
                }




                /**************hashtag save******************/



                /*************************end hashtag save ********************/


                $output = array();
                $album_details = $this->Video->getDetails($video_id);


                $output['code'] = 200;
                $output['msg'] = $album_details;
                echo json_encode($output);
            }else{


            }


        }

    }

    public function editVideoViews(){

        $this->loadModel('Video');


        if ($this->request->isPost()) {


            $json = file_get_contents('php://input');
            $data = json_decode($json, TRUE);


            $video_id = $data['video_id'];
            $views = $data['view'];

            $details = $this->Video->getDetails($video_id);


            if(count($details) > 0) {





                $this->Video->id = $video_id;
                $this->Video->saveField('view',$views);






                $output = array();
                $details = $this->Video->getDetails($video_id);


                $output['code'] = 200;
                $output['msg'] = $details;
                echo json_encode($output);
            }else{
                Message::EMPTYDATA();
                die();

            }


        }

    }



    public function addSound()
    {


        $this->loadModel('Sound');
        if ($this->request->isPost()) {
            $json = file_get_contents('php://input');
            $data = json_decode($json, TRUE);




            $id = $this->request->data('id');
            $sound['name'] = $this->request->data('name');
            $sound['description']  = $this->request->data('description');
            $sound['uploaded_by'] = "admin";
            $sound['publish'] = 1;
            $sound['created'] = date('Y-m-d H:i:s', time());
            $sound['sound_section_id'] = $this->request->data('sound_section_id');


            if ($id > 0) {



                $this->Sound->id = $id;
                $this->Sound->save($sound);
                $details = $this->Sound->getDetails($id);
                $output['code'] = 200;

                $output['msg'] = $details;


                echo json_encode($output);


                die();

            }




               /* $base64_audio = $data['audio'];
                $base64_decode_audio  = base64_decode($base64_audio);

                $base64_image = $data['thum'];
                $base64_decode_image  = base64_decode($base64_image);*/

                if(MEDIA_STORAGE == "s3") {
                    if (method_exists('Premium', 'fileUploadToS3')) {


                        $sound_file = Premium::fileUploadToS3Multipart("audio");
                        $thum_file = Premium::fileUploadToS3Multipart("thum");

                        if ($sound_file['code'] == 200 && $thum_file['code'] == 200) {


                            $sound_file_key = $sound_file['msg'];
                            $thum_file_key = $thum_file['msg'];

                            $cloudfront_url = str_replace('tictic-video', '', CLOUDFRONT_URL);
                            $file_sound_url = $cloudfront_url . $sound_file_key;


                            $file_thum_url = $cloudfront_url . $thum_file_key;


                            if ($result['code'] = 200) {
                                $audio_file_duration = Utility::getDurationofAudioFile($file_sound_url);

                                $sound['audio'] = $file_sound_url;
                                $sound['thum'] = $file_thum_url;
                                $sound['duration'] = $audio_file_duration;


                            } else {

                                $output['code'] = 201;

                                $output['msg'] = $result['msg'];


                                echo json_encode($output);


                                die();

                            }
                        }
                    }else{

                        $output['code'] = 201;

                        $output['msg'] = "It seems like you do not have Premium files. submit ticket on yeahhelp.com for support";


                        echo json_encode($output);


                        die();


                    }
                }else {



                    $audio_file_path = Utility::uploadAMultipartFileIntoFolder("audio",null, UPLOADS_FOLDER_URI);
                    $thum_file_path = Utility::uploadAMultipartFileIntoFolder("thum",null, UPLOADS_FOLDER_URI);


                    $audio_file_duration = Utility::getDurationofAudioFile($audio_file_path);


                    $sound['audio'] = $audio_file_path;
                    $sound['thum'] = $thum_file_path;
                    $sound['duration'] = $audio_file_duration;


                }

                $this->Sound->save($sound);
                $id = $this->Sound->getInsertID();

                $app_slider = $this->Sound->getDetails($id);
                $output['code'] = 200;

                $output['msg'] = $app_slider;


                echo json_encode($output);


                die();

        }





    }


    public function showOrders(){

        $this->loadModel('Order');
        $this->loadModel('RiderOrder');





        if ($this->request->isPost()) {
            $json = file_get_contents('php://input');
            $data = json_decode($json, TRUE);
            if(isset($data['status'])) {
                $status = $data['status'];
                $orders = $this->Order->getOrdersAccordingToStatus($status);
            }

            if(isset($data['order_id'])){

                $orders = $this->Order->getDetails($data['order_id']);

                if(count($orders) > 0){

                    $rider_order = $this->RiderOrder->getRiderOrderAgainstOrderID($orders['Order']['id']);

                    if(count($rider_order) > 0) {
                        $orders['Order']['RiderOrder'] = $rider_order['RiderOrder'];

                    }
                }

            }




            if(count($orders) > 0) {


                $output['code'] = 200;

                $output['msg'] = $orders;


                echo json_encode($output);


                die();
            }else{

                Message::EMPTYDATA();
                die();

            }

        }


    }


    public function adminResponseAgainstOrder()
    {


        $this->loadModel('Order');

        if ($this->request->isPost()) {


            $json = file_get_contents('php://input');
            $data = json_decode($json, TRUE);

            $order_id = $data['order_id'];
            $status = $data['status'];






            $this->Order->id = $order_id;
            $this->Order->saveField('status',$status);


            $output = array();
            $userDetails = $this->Order->getDetails($order_id);


            $output['code'] = 200;
            $output['msg'] = $userDetails;
            echo json_encode($output);


        }
    }


    public function assignOrderToRider()
    {

        $this->loadModel("RiderOrder");
        $this->loadModel("Order");
        $this->loadModel("User");


        if ($this->request->isPost()) {


            $json = file_get_contents('php://input');
            $data = json_decode($json, TRUE);

            $rider_user_id = $data['rider_user_id'];

            $order_id = $data['order_id'];
            $created = date('Y-m-d H:i:s', time());



            if(isset($data['id'])){

                $this->RiderOrder->delete($data['id']);

            }

            $rider_order['rider_user_id'] = $rider_user_id;

            $rider_order['order_id'] = $order_id;
            $rider_order['assign_date_time'] = $created;




            if ($this->RiderOrder->isDuplicateRecord($rider_user_id, $order_id) <= 0) {

                if ($this->RiderOrder->save($rider_order)) {

                    $rider_order_id = $this->RiderOrder->getInsertID();
                    $details = $this->RiderOrder->getDetails($rider_order_id);

                    $msg = "You have received the new order request";
                    $notification['to'] = $details['Rider']['device_token'];
                    $notification['notification']['title'] = $msg;
                    $notification['notification']['body'] = "";
                    $notification['notification']['badge'] = "1";
                    $notification['notification']['sound'] = "default";
                    $notification['notification']['icon'] = "";
                    $notification['notification']['type'] = "";
                    $notification['data']['title'] = $msg;
                    $notification['data']['body'] = '';
                    $notification['data']['icon'] = "";
                    $notification['data']['badge'] = "1";
                    $notification['data']['sound'] = "default";
                    $notification['data']['type'] = "";
                    Utility::sendPushNotificationToMobileDevice(json_encode($notification));




                    $output['code'] = 200;

                    $output['msg'] = $details;
                    echo json_encode($output);


                    die();

                } else {


                    echo Message::DUPLICATEDATE();
                    die();
                }

            }else{

                echo Message::DUPLICATEDATE();
                die();

            }
        }
    }

    public function showRiderOrders()
    {

        $this->loadModel("RiderOrder");


        if ($this->request->isPost()) {


            $json = file_get_contents('php://input');
            $data = json_decode($json, TRUE);

            $rider_user_id = $data['rider_user_id'];
            $orders = $this->RiderOrder->getAllRiderOrders($rider_user_id);


            $output['code'] = 200;

            $output['msg'] = $orders;
            echo json_encode($output);


            die();
        }
    }


    public function showReportReasons(){

        $this->loadModel('ReportReason');




        if ($this->request->isPost()) {
            $json = file_get_contents('php://input');
            $data = json_decode($json, TRUE);


            if(isset($data['id'])){

                $details = $this->ReportReason->getDetails($data['id']);

            }else{

                $details = $this->ReportReason->getAll();
            }




            if(count($details) > 0) {


                $output['code'] = 200;

                $output['msg'] = $details;


                echo json_encode($output);


                die();
            }else{


                Message::EMPTYDATA();
                die();
            }

        }


    }

    public function showReportedVideos(){

        $this->loadModel('ReportVideo');




        if ($this->request->isPost()) {
            $json = file_get_contents('php://input');
            $data = json_decode($json, TRUE);



            if(isset($data['id'])){
                $details = $this->ReportVideo->getDetails($data['id']);


            }else {
                $details = $this->ReportVideo->getAll();

            }
            if(count($details) > 0) {


                $output['code'] = 200;

                $output['msg'] = $details;


                echo json_encode($output);


                die();
            }else{


                Message::EMPTYDATA();
                die();
            }

        }


    }

    public function deleteReportedVideo(){

        $this->loadModel('ReportVideo');


        if ($this->request->isPost()) {
            $json = file_get_contents('php://input');
            $data = json_decode($json, TRUE);

            $id = $data['id'];

            $details = $this->ReportVideo->getDetails($id);

            if(count($details) > 0 ) {
                $this->ReportVideo->id = $id;
                $this->ReportVideo->delete();


                $output['code'] = 200;

                $output['msg'] = "deleted";


                echo json_encode($output);


                die();

            }else{

                $output['code'] = 201;

                $output['msg'] = "Invalid id: Do not exist";


                echo json_encode($output);


                die();


            }

        }




    }


    public function deleteReportedUser(){

        $this->loadModel('ReportUser');


        if ($this->request->isPost()) {
            $json = file_get_contents('php://input');
            $data = json_decode($json, TRUE);

            $id = $data['id'];

            $details = $this->ReportUser->getDetails($id);

            if(count($details) > 0 ) {
                $this->ReportUser->id = $id;
                $this->ReportUser->delete();


                $output['code'] = 200;

                $output['msg'] = "deleted";


                echo json_encode($output);


                die();

            }else{

                $output['code'] = 201;

                $output['msg'] = "Invalid id: Do not exist";


                echo json_encode($output);


                die();


            }

        }




    }


    public function showReportedUsers(){

        $this->loadModel('ReportUser');




        if ($this->request->isPost()) {
            $json = file_get_contents('php://input');
            $data = json_decode($json, TRUE);



            if(isset($data['id'])){
                $details = $this->ReportUser->getDetails($data['id']);


            }else {
                $details = $this->ReportUser->getAll();

            }
            if(count($details) > 0) {


                $output['code'] = 200;

                $output['msg'] = $details;


                echo json_encode($output);


                die();
            }else{


                Message::EMPTYDATA();
                die();
            }

        }


    }

    public function addReportReason()
    {


        $this->loadModel('ReportReason');


        if ($this->request->isPost()) {


            $json = file_get_contents('php://input');
            $data = json_decode($json, TRUE);

            $report['title'] = $data['title'];


            $report['created'] = date('Y-m-d H:i:s', time());
            $details = $this->ReportReason->checkDuplicate($data['title']);

            if(isset($data['id'])){

                $this->ReportReason->id = $data['id'];
                $this->ReportReason->save($report);

                $details = $this->ReportReason->getDetails($data['id']);



                $output['code'] = 200;
                $output['msg'] = $details;
                echo json_encode($output);
                die();
            }
            if($details < 1) {




                $this->ReportReason->save($report);

                $id = $this->ReportReason->getInsertID();

                $output = array();
                $details = $this->ReportReason->getDetails($id);


                $output['code'] = 200;
                $output['msg'] = $details;
                echo json_encode($output);
                die();



            }else{


                echo Message::DUPLICATEDATE();
                die();

            }

        }
    }


    public function editUser()
    {


        $this->loadModel('User');

        if ($this->request->isPost()) {


            $json = file_get_contents('php://input');
            $data = json_decode($json, TRUE);

            $user['first_name'] = $data['first_name'];
            $user['last_name'] = $data['last_name'];
            $user['gender'] = $data['gender'];
            $user['bio'] = $data['bio'];
            $user['website'] = $data['website'];
            //$user['password'] = $data['password'];
            $user['email'] = $data['email'];

            $user_id = $data['user_id'];



            $userDetails = $this->User->getUserDetailsFromID($user_id);
            if(count($userDetails)) {
                $this->User->id = $user_id;
                $this->User->save($user);


                $output = array();
                $userDetails = $this->User->getUserDetailsFromID($user_id);


                $output['code'] = 200;
                $output['msg'] = $userDetails;
                echo json_encode($output);

            }else{
                Message::EMPTYDATA();
                die();


            }
        }
    }

    public function blockUser()
    {


        $this->loadModel('User');

        if ($this->request->isPost()) {


            $json = file_get_contents('php://input');
            $data = json_decode($json, TRUE);


            //$user['password'] = $data['password'];
            $active = $data['active'];

            $user_id = $data['user_id'];



            $userDetails = $this->User->getUserDetailsFromID($user_id);
            if(count($userDetails)) {
                $this->User->id = $user_id;
                $this->User->saveField("active",$active);


                $output = array();
                $userDetails = $this->User->getUserDetailsFromID($user_id);


                $output['code'] = 200;
                $output['msg'] = $userDetails;
                echo json_encode($output);

            }else{
                Message::EMPTYDATA();
                die();


            }
        }
    }

    public function addHtmlPage()
    {


        $this->loadModel('HtmlPage');

        if ($this->request->isPost()) {


            $json = file_get_contents('php://input');
            $data = json_decode($json, TRUE);


            //$user['password'] = $data['password'];
            $name = $data['name'];

            $text = $data['text'];

            $created = date('Y-m-d H:i:s', time());

            $html['name']= $name;
            $html['text'] = $text;
            $html['created'] = $created;



            $ifExist = $this->HtmlPage->ifExist($name);
            if(count($ifExist) < 1 ) {

                $this->HtmlPage->save($html);

                $id = $this->HtmlPage->getInsertID();
                $output = array();
                $details = $this->HtmlPage->getDetails($id);


                $output['code'] = 200;
                $output['msg'] = $details;
                echo json_encode($output);
                die();

            }else{

                $this->HtmlPage->id = $ifExist['HtmlPage']['id'];
                $this->HtmlPage->save($html);


                $output = array();
                $details = $this->HtmlPage->getDetails($ifExist['HtmlPage']['id']);


                $output['code'] = 200;
                $output['msg'] = $details;
                echo json_encode($output);
                die();
            }
        }
    }


    public function showHtmlPage(){

        $this->loadModel('HtmlPage');




        if ($this->request->isPost()) {
            $json = file_get_contents('php://input');
            $data = json_decode($json, TRUE);

            $name = $data['name'];

            $ifExist = $this->HtmlPage->ifExist($name);

            if(count($ifExist) > 0 ) {





                $output['code'] = 200;

                $output['msg'] = $ifExist;


                echo json_encode($output);


                die();

            }else{

                Message::EMPTYDATA();
                die();


            }

        }


    }
    public function addUser()
    {


        $this->loadModel('User');

        if ($this->request->isPost()) {


            $json = file_get_contents('php://input');
            $data = json_decode($json, TRUE);

            $email = $data['email'];
            $password = $data['password'];
            $first_name = $data['first_name'];
            $last_name = $data['last_name'];
            $role = $data['role'];


            $created = date('Y-m-d H:i:s', time());


            if ($email != null && $password != null) {


                //$ip  = $data['ip'];

                $user['email'] = $email;
                $user['password'] = $password;
                $user['first_name'] = $first_name;
                $user['last_name'] = $last_name;
                $user['role'] = $role;
                $user['created'] = $created;


                $count = $this->User->isEmailAlreadyExist($email);



                if ($count && $count > 0) {
                    echo Message::DATAALREADYEXIST();
                    die();

                } else {


                    if (!$this->User->save($user)) {
                        echo Message::DATASAVEERROR();
                        die();
                    }

                    $user_id = $this->User->getInsertID();


                    $output = array();
                    $userDetails = $this->User->getUserDetailsFromID($user_id);

                    //CustomEmail::welcomeStudentEmail($email);
                    $output['code'] = 200;
                    $output['msg'] = $userDetails;
                    echo json_encode($output);


                }
            } else {
                echo Message::ERROR();
            }
        }
    }


    public function showUserDetail()
    {


        $this->loadModel("User");
        $this->loadModel("Video");
        $this->loadModel("VideoFavourite");
        $this->loadModel("VideoLike");
        $this->loadModel("VideoComment");
        $this->loadModel("Follower");



        if ($this->request->isPost()) {
            $json = file_get_contents('php://input');
            $data = json_decode($json, TRUE);

            $user_id = $data['user_id'];



            $userDetails = $this->User->getUserDetailsFromIDContain($user_id);



            $videos_public = $this->Video->getUserPublicVideosAdmin($user_id);

            foreach($videos_public as $key=>$val){

                $comment_count = $this->VideoComment->countComments($val['Video']['id']);
                $video_likes_count = $this->VideoLike->countLikes($val['Video']['id']);

                $videos_public[$key]['Video']['comment_count'] = $comment_count;
                $videos_public[$key]['Video']['like_count'] = $video_likes_count;

            }
            $videos_private = $this->Video->getUserPrivateVideosAdmin($user_id);

            foreach($videos_private as $key=>$val){

                $comment_count = $this->VideoComment->countComments($val['Video']['id']);
                $video_likes_count = $this->VideoLike->countLikes($val['Video']['id']);

                $videos_private[$key]['Video']['comment_count'] = $comment_count;
                $videos_private[$key]['Video']['like_count'] = $video_likes_count;

            }
            $videos_count = $this->Video->getUserVideosCount($user_id);
            $videos_favourite = $this->VideoFavourite->getUserFavouriteVideosAdmin($user_id);

            $followers = $this->Follower->getUserFollowersAdmin($user_id);
            $following = $this->Follower->getUserFollowingAdmin($user_id);

            $followers_count = $this->Follower->countFollowers($user_id);
            $following_count = $this->Follower->countFollowing($user_id);

            $wallet_total =  $this->walletTotal($user_id);
            $wallet_total = $wallet_total['total'];

            $userDetails['User']['Videos']['public'] = $videos_public;
            $userDetails['User']['Videos']['private'] = $videos_private;
            $userDetails['User']['videos_count'] = $videos_count;

            $userDetails['User']['Followers'] = $followers;
            //$userDetails['User']['VideoLiked'] = $followers;
            $userDetails['User']['followers_count'] = $followers_count;
            $userDetails['User']['FavouriteVideos'] = $videos_favourite;
            $userDetails['User']['Following'] = $following;
            $userDetails['User']['following_count'] = $following_count;
            $userDetails['User']['wallet'] = $wallet_total;


            $output['code'] = 200;

            $output['msg'] = $userDetails;


            echo json_encode($output);


            die();


        }
    }


    public function showPopularHashtags()
    {

        $this->loadModel("HashtagVideo");


        if ($this->request->isPost()) {

            $json = file_get_contents('php://input');
            $data = json_decode($json, TRUE);



            $hashtags = $this->HashtagVideo->getHastagsWhichHasGreaterNoOfVideosAdmin();



            if(count($hashtags) > 0) {

                $new_array = array();
                foreach ($hashtags as $key => $hashtag) {

                    $hashtag_videos = $this->HashtagVideo->countHashtagVideos($hashtag['Hashtag']['id']);
                    if($hashtag_videos > 0) {
                        $new_array[$key]["Hashtag"] = $hashtag['Hashtag'];
                        $new_array[$key]["Hashtag"]['views'] = $hashtag[0]['total_views'];
                        $new_array[$key]["Hashtag"]['videos'] = $hashtag_videos;



                    }
                }

            }

            if(count($new_array) > 0) {

                $output['code'] = 200;

                $output['msg'] = $new_array;
                echo json_encode($output);


                die();
            }else{

                Message::EMPTYDATA();
                die();
            }
        }
    }



    public function changePassword()
    {
        $this->loadModel('User');


        if ($this->request->isPost()) {
            $json = file_get_contents('php://input');
            //$json = $this->request->data('json');
            $data = json_decode($json, TRUE);


            $user_id        = $data['user_id'];
            $this->User->id = $user_id;



            $new_password   = $data['password'];




            $this->request->data['password'] = $new_password;
            $this->User->id                  = $user_id;


            if ($this->User->save($this->request->data)) {

                $user_info = $this->User->getUserDetailsFromID($user_id);
                $result['code'] = 200;
                $result['msg']  = $user_info;
                echo json_encode($result);
                die();
            } else {


                echo Message::DATASAVEERROR();
                die();


            }

        } else {

            echo Message::INCORRECTPASSWORD();
            die();




        }

    }
    public function deleteReportReason(){

        $this->loadModel('ReportReason');


        if ($this->request->isPost()) {
            $json = file_get_contents('php://input');
            $data = json_decode($json, TRUE);

            $id = $data['id'];

            $details = $this->ReportReason->getDetails($id);

            if(count($details) > 0 ) {
                $this->ReportReason->id = $id;
                $this->ReportReason->delete();


                $output['code'] = 200;

                $output['msg'] = "deleted";


                echo json_encode($output);


                die();

            }else{

                $output['code'] = 201;

                $output['msg'] = "Invalid id: Do not exist";


                echo json_encode($output);


                die();


            }

        }




    }

    public function deleteUser(){

        $this->loadModel('User');
        $this->loadModel('Follower');

        if ($this->request->isPost()) {
            $json = file_get_contents('php://input');
            $data = json_decode($json, TRUE);

            $user_id = $data['user_id'];

            $details = $this->User->getUserDetailsFromID($user_id);

            if(APP_STATUS == "demo") {



                $code =  201;
                $msg = "You cannot delete account in demo account. You need to contant qboxus for that";

                $output['code'] = $code;

                $output['msg'] = $msg;



                die();

            }
            if(count($details) > 0 ) {
                $this->User->delete($user_id,true);
                $this->Follower->deleteFollowerAgainstUserID($user_id);


                $output['code'] = 200;

                $output['msg'] = "deleted";


                echo json_encode($output);


                die();

            }else{

                $output['code'] = 201;

                $output['msg'] = "Invalid id: Do not exist";


                echo json_encode($output);


                die();


            }

        }




    }




    public function deleteAdmin(){

        $this->loadModel('Admin');

        if ($this->request->isPost()) {
            $json = file_get_contents('php://input');
            $data = json_decode($json, TRUE);

            $user_id = $data['user_id'];

            $details = $this->Admin->getUserDetailsFromID($user_id);
            if(count($details) > 0 ) {

                $this->Admin->id = $user_id;
                $this->Admin->delete();

                $output['code'] = 200;

                $output['msg'] = "deleted";


                echo json_encode($output);


                die();

            }else{

                $output['code'] = 201;

                $output['msg'] = "Invalid id: Do not exist";


                echo json_encode($output);


                die();


            }

        }




    }

    public function addAdminUser()
    {


        $this->loadModel('Admin');

        if ($this->request->isPost()) {


            $json = file_get_contents('php://input');
            $data = json_decode($json, TRUE);

            $email = $data['email'];
            $password = $data['password'];
            $first_name = $data['first_name'];
            $last_name = $data['last_name'];
            $role = $data['role'];



            $created = date('Y-m-d H:i:s', time());


            if ($email != null && $password != null) {


                //$ip  = $data['ip'];

                $user['email'] = $email;
                $user['password'] = $password;
                $user['first_name'] = $first_name;

                $user['last_name'] = $last_name;
                $user['role'] = $role;
                $user['created'] = $created;


                $count = $this->Admin->isEmailAlreadyExist($email);



                if ($count && $count > 0) {
                    echo Message::DATAALREADYEXIST();
                    die();

                } else {


                    if (!$this->Admin->save($user)) {
                        echo Message::DATASAVEERROR();
                        die();
                    }

                    $user_id = $this->Admin->getInsertID();


                    $output = array();
                    $userDetails = $this->Admin->getUserDetailsFromID($user_id);

                    //CustomEmail::welcomeStudentEmail($email);
                    $output['code'] = 200;
                    $output['msg'] = $userDetails;
                    echo json_encode($output);


                }
            } else {
                echo Message::ERROR();
            }
        }
    }


    public function editAdminUser()
    {


        $this->loadModel('Admin');

        if ($this->request->isPost()) {


            $json = file_get_contents('php://input');
            $data = json_decode($json, TRUE);

            $email = $data['email'];

            $first_name = $data['first_name'];
            $last_name = $data['last_name'];
            $role = $data['role'];

            $created = date('Y-m-d H:i:s', time());


            $user['email'] = $email;

            $user['first_name'] = $first_name;

            $user['last_name'] = $last_name;
            $user['role'] = $role;
            $user['created'] = $created;


            $user_id = $data['id'];



            $userDetails = $this->Admin->getUserDetailsFromID($user_id);
            if(count($userDetails)) {
                $this->Admin->id = $user_id;
                $this->Admin->save($user);


                $output = array();
                $userDetails = $this->Admin->getUserDetailsFromID($user_id);


                $output['code'] = 200;
                $output['msg'] = $userDetails;
                echo json_encode($output);

            }else{
                Message::EMPTYDATA();
                die();


            }
        }
    }

    public function showAdminUsers(){

        $this->loadModel('Admin');




        if ($this->request->isPost()) {



            $json = file_get_contents('php://input');
            $data = json_decode($json, TRUE);

            if(isset($data['id'])){

                $details = $this->Admin->getUserDetailsFromID($data['id']);


            }else{


                $details = $this->Admin->getAll();

            }

            if(count($details) > 0) {

                $output['code'] = 200;

                $output['msg'] = $details;
                echo json_encode($output);


                die();
            }else{
                Message::EMPTYDATA();
                die();
            }
        }

    }


    public function showAllUsers(){

        $this->loadModel('User');


        $json = file_get_contents('php://input');
        $data = json_decode($json, TRUE);


        $starting_point = 0;
        if(isset($data['starting_point'])){
            $starting_point = $data['starting_point'];
        }

        if ($this->request->isPost()) {




            $users = $this->User->getAll($starting_point);






            $output['code'] = 200;

            $output['msg'] = $users;


            echo json_encode($output);


            die();


        }


    }



    /*if($table_name == "admin"){



    }*/


    public function jsonFileForm(){
        $this->autoRender = true;

    }

    public function importJsonFile(){




        $this->autoRender = true;



        if(APP_STATUS == "demo"){


            $output['code'] = 201;

            $output['msg'] = "you cannot upload a file using demo";


            echo json_encode($output);


            die();

        }
        $filePath = Utility::uploadOriginalVideoFileIntoTemporaryFolder("json");
        //  echo $filePath;

        if($filePath) {

            $output['code'] = 200;

            $output['msg'] = $filePath;


            $this->set("data",$output);
        }else{

            $output['code'] = 201;

            $output['msg'] = "something went wrong in uploading a file.";


            echo json_encode($output);


            die();
        }

    }

    public function showJsonDatabaseFiles(){


        $files = glob(TEMP_UPLOADS_FOLDER_URI."/*.json");


        $output['code'] = 200;

        $output['msg'] = $files;


        echo json_encode($output);


        die();

    }


    public function deleteJsonFile(){


        if ($this->request->isPost()) {
            $json = file_get_contents('php://input');
            $data = json_decode($json, TRUE);

            $filePath = $data['file_path'];
            if (!file_exists($filePath)) {
                $output['code'] = 201;

                $output['msg'] = "file do not exist";


                echo json_encode($output);


                die();
            }
            @unlink($filePath);

            if (!file_exists($filePath)) {
                $output['code'] = 200;

                $output['msg'] = "deleted";


                echo json_encode($output);


                die();
            }else{


                $output['code'] = 201;

                $output['msg'] = "something went wrong in deleting a file";


                echo json_encode($output);


                die();
            }

        }
    }

    public function databaseImport()
    {
        $this->loadModel('User');

        if ($this->request->isPost()) {
            $json = file_get_contents('php://input');



            $data = json_decode($json, TRUE);

            $filePath = $data['file_path'];

            $string = file_get_contents($filePath);

            $json_a = json_decode($string, true);


            $admin_table_found = array_search("admin", array_column($json_a, 'name'));
            $users_table_found = array_search("users", array_column($json_a, 'name'));
            $sound_table_found = array_search("sound", array_column($json_a, 'name'));
            $sound_table_fav_found = array_search("fav_sound", array_column($json_a, 'name'));
            $video_table_found = array_search("videos", array_column($json_a, 'name'));
            $video_table_comment_found = array_search("video_comment", array_column($json_a, 'name'));
            $video_table_like_found = array_search("video_like_dislike", array_column($json_a, 'name'));
            $video_table_follow_found = array_search("follow_users", array_column($json_a, 'name'));

            /***************************Table admin Import********************************************/
            if ($admin_table_found > 0) {

                $admin_table_key = $admin_table_found + 1;
                $this->table_admin_import($json_a[$admin_table_key]);
            } else {

                $output['code'] = 201;

                $output['msg'] = "table admin not found";


                echo json_encode($output);


                die();
            }


            /***************************Table Users Import********************************************/


            if ($admin_table_found > 0) {

                $users_table_key = $users_table_found + 1;
                $this->table_users_import($json_a[$users_table_key]);
            } else {

                $output['code'] = 201;

                $output['msg'] = "table users not found";


                echo json_encode($output);


                die();
            }

            /***************************Table Sound Import********************************************/


            if ($sound_table_found > 0) {

                $sound_table_key = $sound_table_found + 1;
                $this->table_sound_import($json_a[$sound_table_key]);
            } else {

                $output['code'] = 201;

                $output['msg'] = "table sound not found";

                echo json_encode($output);


                die();
            }

            /***************************Table SoundFavourite Import********************************************/


            if ($sound_table_fav_found > 0) {

                $sound_table_fav_key = $sound_table_fav_found + 1;
                $this->table_sound_fav_import($json_a[$sound_table_fav_key]);
            } else {

                $output['code'] = 201;

                $output['msg'] = "table favsound not found";

                echo json_encode($output);


                die();
            }


            /***************************Table Video Import********************************************/


            if ($video_table_found > 0) {

                $video_table_key = $video_table_found + 1;
                $this->table_video_import($json_a[$video_table_key]);
            } else {

                $output['code'] = 201;

                $output['msg'] = "table video not found";

                echo json_encode($output);


                die();
            }


            /***************************Table Video Comment Import********************************************/


            if ($video_table_comment_found > 0) {

                $video_table_comment_key = $video_table_comment_found + 1;
                $this->table_video_comment_import($json_a[$video_table_comment_key]);
            } else {

                $output['code'] = 201;

                $output['msg'] = "table video comment not found";

                echo json_encode($output);


                die();
            }

            /***************************Table Video Like Import********************************************/


            if ($video_table_like_found > 0) {

                $video_table_like_key = $video_table_like_found + 1;
                $this->table_video_like_import($json_a[$video_table_like_key]);
            } else {

                $output['code'] = 201;

                $output['msg'] = "table video_like_dislike not found";

                echo json_encode($output);


                die();
            }


            /***************************Table Video Follow users Import********************************************/


            if ($video_table_follow_found > 0) {

                $video_table_follow_key = $video_table_follow_found + 1;
                $this->table_video_follow_import($json_a[$video_table_follow_key]);
            } else {

                $output['code'] = 201;

                $output['msg'] = "table follow_users  not found";

                echo json_encode($output);


                die();
            }


            die();




        }

    }

    function table_users_import($array_data){
        $this->loadModel('User');

        $table_name = $array_data['name'];

        $table_data_count = count($array_data['data']);


        echo "Table Name: ".$table_name.'<br>';
        echo "Table Rows: ".$table_data_count.'<br>';



        if(count($array_data['data']) > 0){
            foreach ($array_data['data'] as $v) {

                $if_exist = $this->User->isfbAlreadyExist($v['fb_id']);
                if ($if_exist < 1) {
                    $user['username'] = $v['username'];
                    $user['role'] = "user";
                    $user['first_name'] = $v['first_name'];
                    $user['last_name'] = $v['last_name'];
                    $user['gender'] = $v['gender'];
                    if($v['gender'] == "m"){

                        $user['gender'] = "Male";
                    }else if($v['gender'] == "f"){

                        $user['gender'] = "Female";
                    }


                    $user['bio'] = $v['bio'];
                    $user['block'] = $v['block'];
                    $user['version'] = $v['version'];
                    $user['device_token'] = $v['tokon'];

                    $user['verified'] = $v['verified'];
                    $user['device'] = $v['device'];
                    $user['created'] = $v['created'];
                    $user['social_id'] = $v['fb_id'];
                    $user['fb_id'] = $v['fb_id'];
                    if ($v['signup_type'] == "gmail") {

                        $user['social'] = "google";

                    } else {

                        $user['social'] = "facebook";

                    }


                    $profile_pic = $v['profile_pic'];
                    if(strlen($profile_pic) > 3){
                        $profile_pic_name =  basename($profile_pic);
                        $profile_pic_url = UPLOADS_FOLDER_URI."/images/".$profile_pic_name;
                        $user['profile_pic']  =  $profile_pic_url;

                    }

                    $this->User->save($user);
                    $this->User->clear();

                }
            }

            //  echo $table_name." data has been successfully imported".'<br>';
        }else{

            //  echo "no data exist in the ".$table_name.'<br>';
        }

    }
    public function test11(){

        $original_video_path = "app/ff/i.png";
        echo  $file = basename($original_video_path);


    }
    function table_admin_import($array_data){
        $this->loadModel('User');

        $created = date('Y-m-d H:i:s', time());
        $table_name = $array_data['name'];

        $table_data_count = count($array_data['data']);

        $this->User->query('TRUNCATE table user;');
        echo "Table Name: ".$table_name.'<br>';
        echo "Table Rows: ".$table_data_count.'<br>';


        if(count($array_data['data']) > 0){
            foreach ($array_data['data'] as $v) {

                $if_exist = $this->User->isEmailAlreadyExist($v['email']);
                if($if_exist < 1){
                    $user['email'] = $v['email'];
                    $user['role'] = "admin";
                    $user['created'] = $created;
                    $user['password'] = 123456;

                    $this->User->save($user);
                    $this->User->clear();
                }
            }


            // echo $table_name." data has been successfully imported".'<br>';
        }else{

            //echo "no data exist in the ".$table_name.'<br>';
        }

    }


    function table_sound_import($array_data){
        $this->loadModel('Sound');

        $created = date('Y-m-d H:i:s', time());
        $table_name = $array_data['name'];

        $table_data_count = count($array_data['data']);

        $this->Sound->query('TRUNCATE table sound;');
        echo "Table Name: ".$table_name.'<br>';
        echo "Table Rows: ".$table_data_count.'<br>';
        $sound = array();


        if(count($array_data['data']) > 0){
            $key=0;
            foreach ($array_data['data'] as $v) {

                $audio = UPLOADS_FOLDER_URI."/audio/".$v['id'].".aac";
                $if_exist = $this->Sound->checkDuplicate($audio,$v['created']);
                if($if_exist < 1){

                    $sound[$key]['name'] = $v['sound_name'];
                    $sound[$key]['description'] = $v['description'];
                    $sound[$key]['created'] = $v['created'];



                    if($v['uploaded_by'] == "admin"){
                        $sound[$key]['uploaded_by'] = $v['uploaded_by'];

                    }else{

                        $user_details = $this->User->getDetailsAgainstFBID($v['uploaded_by']);
                        if (count($user_details) > 0) {


                            $sound[$key]['user_id'] = $user_details['User']['id'];
                        }

                    }
                    $sound[$key]['audio'] = $audio;
                    $http = 'http';


                    if (strpos( $v['thum'], $http) !== false) {

                        $sound[$key]['thum'] = $v['thum'];

                    }else{

                        if(strlen($v['thum']) > 3){
                            $thum_name =  basename($v['thum']);
                            $thum_url = UPLOADS_FOLDER_URI."/audio/".$thum_name;
                            $sound[$key]['thum']  =  $thum_url;

                        }

                    }





                    $key++;
                }

            }

            if(count($sound) > 0) {
                $this->Sound->saveAll($sound);
                $this->Sound->clear();


            }

            //   echo $table_name." data has been successfully imported".'<br>';
        }else{

            //  echo "no data exist in the ".$table_name.'<br>';
        }

    }

    function table_sound_fav_import($array_data){
        $this->loadModel('SoundFavourite');
        $this->loadModel('Sound');
        $this->loadModel('User');


        $table_name = $array_data['name'];

        $table_data_count = count($array_data['data']);

        $this->SoundFavourite->query('TRUNCATE table sound_favourite;');
        echo "Table Name: ".$table_name.'<br>';
        echo "Table Rows: ".$table_data_count.'<br>';



        if(count($array_data['data']) > 0){
            foreach ($array_data['data'] as $v) {


                $user_Details = $this->User->getDetailsAgainstFBID($v['fb_id']);
                if(count($user_Details) > 0){
                    $user_id = $user_Details['User']['id'];
                    $audio = UPLOADS_FOLDER_URI."/audio/".$v['sound_id'].".aac";

                    $sound_details = $this->Sound->getSoundDetailsAgainstAudio($audio);

                    if(count($sound_details) > 0) {
                        $check_Duplicate['user_id'] = $user_id;
                        $check_Duplicate['sound_id'] = $sound_details['Sound']['id'];


                        $if_exist = $this->SoundFavourite->ifExist($check_Duplicate);
                        if (count($if_exist) < 1) {

                            $sound['user_id'] = $user_id;
                            $sound['sound_id'] = $sound_details['Sound']['id'];
                            $sound['created'] = $v['created'];


                            $this->SoundFavourite->save($sound);
                            $this->SoundFavourite->clear();
                        }
                    }
                }
            }


            // echo $table_name." data has been successfully imported".'<br>';
        }else{

            // echo "no data exist in the ".$table_name.'<br>';
        }

    }


    function table_video_import($array_data){
        $this->loadModel('Video');
        $this->loadModel('User');
        $this->loadModel('Sound');

        $video = array();
        $table_name = $array_data['name'];

        $table_data_count = count($array_data['data']);


        echo "Table Name: ".$table_name.'<br>';
        echo "Table Rows: ".$table_data_count.'<br>';

        $this->Video->query('TRUNCATE table video;');
        $key=0;
        if(count($array_data['data']) > 0){
            foreach ($array_data['data'] as $v) {




                // $audio = UPLOADS_FOLDER_URI."/audio/".$v['id'].".aac";
                $if_exist = $this->Video->checkDuplicateold($v);
                if($if_exist < 1){

                    $video[$key]['fb_id'] = $v['fb_id'];
                    $video[$key]['description'] =  $v['description'];
                    $video[$key]['old_video_id'] =  $v['id'];
                    if (array_key_exists("allow_duet",$v))
                    {
                        $video[$key]['allow_duet'] =  $v['allow_duet'];
                    }

                    if (array_key_exists("allow_comments",$v))
                    {
                        $video[$key]['allow_comments'] =  $v['allow_comments'];
                    }

                    if (array_key_exists("privacy_type",$v))
                    {
                        $video[$key]['privacy_type'] =  $v['privacy_type'];
                    }


                    $video[$key]['view'] =  $v['view'];
                    $video[$key]['created'] = $v['created'];


                    $user_details = $this->User->getDetailsAgainstFBID($v['fb_id']);
                    if (count($user_details) > 0) {


                        $video[$key]['user_id'] = $user_details['User']['id'];
                    }

                    $audio = UPLOADS_FOLDER_URI."/audio/".$v['sound_id'].".aac";

                    $audio_details = $this->Sound->getSoundDetailsAgainstAudio($audio);

                    if (count($audio_details) > 0) {


                        $video[$key]['sound_id'] = $audio_details['Sound']['id'];
                    }

                    $http = 'http';


                    if (strpos( $v['thum'], $http) !== false) {

                        $video[$key]['video'] =  $v['video'];
                        $video[$key]['thum'] =  $v['thum'];
                        $video[$key]['gif'] =  $v['gif'];

                    }else{

                        if(strlen($v['video']) > 3){
                            $video_name =  basename($v['video']);
                            $thum_name =  basename($v['thum']);
                            $gif_name =  basename($v['gif']);

                            $thum_url = UPLOADS_FOLDER_URI."/thum/".$thum_name;
                            $video_url = UPLOADS_FOLDER_URI."/video/".$video_name;
                            $gif_url = UPLOADS_FOLDER_URI."/gif/".$gif_name;

                            $video[$key]['video'] =  $video_url;
                            $video[$key]['thum'] =  $thum_url;
                            $video[$key]['gif'] =  $gif_url;

                        }

                    }






                    $key++;

                }
            }

            if(count($video) > 0) {



                $this->Video->saveAll($video);
                $this->Video->clear();
                echo $table_name." data has been successfully imported".'<br>';
            }else{

                //  echo $table_name." data has been already been imported".'<br>';
            }

        }else{

            //echo "no data exist in the ".$table_name.'<br>';
        }

    }


    function table_video_comment_import($array_data){

        $this->loadModel('VideoComment');
        $this->loadModel('Video');
        $this->loadModel('User');


        $table_name = $array_data['name'];

        $table_data_count = count($array_data['data']);

        $video_comment = array();
        echo "Table Name: ".$table_name.'<br>';
        echo "Table Rows: ".$table_data_count.'<br>';
        $this->VideoComment->query('TRUNCATE table video_comment;');


        $key=0;
        if(count($array_data['data']) > 0){
            foreach ($array_data['data'] as $v) {


                $user_Details = $this->User->getDetailsAgainstFBID($v['fb_id']);
                if (count($user_Details) > 0) {
                    $user_id = $user_Details['User']['id'];


                    $video_details = $this->Video->getDetailsAgainstOldVideoID($v['video_id']);
                    if (count($video_details) > 0) {
                        $video_id = $video_details['Video']['id'];
                        if (count($video_details) > 0) {
                            $video_comment[$key]['user_id'] = $user_id;
                            $video_comment[$key]['video_id'] = $video_id;
                            $video_comment[$key]['comment'] = $v['comments'];
                            $video_comment[$key]['created'] = $v['created'];


                            $key++;
                        }
                    }
                }
            }
            if(count($video_comment) > 0) {
                $this->VideoComment->saveAll($video_comment);
                $this->VideoComment->clear();
                echo $table_name . " data has been successfully imported" . '<br>';

            }else{

                //echo "no data exist in the ".$table_name.'<br>';
            }
        }else{

            // echo "no data exist in the ".$table_name.'<br>';
        }

    }

    function table_video_like_import($array_data){

        $this->loadModel('VideoLike');
        $this->loadModel('Video');
        $this->loadModel('User');


        $table_name = $array_data['name'];

        $table_data_count = count($array_data['data']);

        $video_like = array();
        echo "Table Name: ".$table_name.'<br>';
        echo "Table Rows: ".$table_data_count.'<br>';
        $this->VideoLike->query('TRUNCATE table video_like;');


        $key=0;
        if(count($array_data['data']) > 0){
            foreach ($array_data['data'] as $v) {


                $user_Details = $this->User->getDetailsAgainstFBID($v['fb_id']);
                if (count($user_Details) > 0) {
                    $user_id = $user_Details['User']['id'];


                    $video_details = $this->Video->getDetailsAgainstOldVideoID($v['video_id']);

                    if (count($video_details) > 0) {
                        $video_id = $video_details['Video']['id'];
                        if (count($video_details) > 0 && $v['action'] > 0) {
                            $video_like[$key]['user_id'] = $user_id;
                            $video_like[$key]['video_id'] = $video_id;
                            $video_like[$key]['created'] = $v['created'];


                            $key++;
                        }
                    }
                }
            }

            if(count($video_like) > 0) {
                $this->VideoLike->saveAll($video_like);
                $this->VideoLike->clear();
                echo $table_name . " data has been successfully imported" . '<br>';

            }else{

                // echo "no data exist in the ".$table_name.'<br>';
            }
        }else{

            // echo "no data exist in the ".$table_name.'<br>';
        }

    }


    function table_video_follow_import($array_data){

        $this->loadModel('Follower');
        $this->loadModel('User');


        $table_name = $array_data['name'];

        $table_data_count = count($array_data['data']);
        $created = date('Y-m-d H:i:s', time());
        $follower = array();
        echo "Table Name: ".$table_name.'<br>';
        echo "Table Rows: ".$table_data_count.'<br>';
        $this->Follower->query('TRUNCATE table follower;');


        $key=0;
        if(count($array_data['data']) > 0){
            foreach ($array_data['data'] as $v) {


                $receiver_details = $this->User->getDetailsAgainstFBID($v['fb_id']);


                $sender_details = $this->User->getDetailsAgainstFBID($v['followed_fb_id']);




                if(count($receiver_details) > 0 && count($sender_details) > 0) {
                    $receiver_id = $receiver_details['User']['id'];
                    $sender_id = $sender_details['User']['id'];

                    $follower[$key]['sender_id'] = $sender_id;
                    $follower[$key]['receiver_id'] = $receiver_id;
                    $follower[$key]['created'] = $created;









                    $key++;
                }
            }

            if(count($follower) > 0) {
                $this->Follower->saveAll($follower);
                $this->Follower->clear();
                echo $table_name . " data has been successfully imported" . '<br>';

            }else{

                //   echo "no data exist in the ".$table_name.'<br>';
            }
        }else{

            //  echo "no data exist in the ".$table_name.'<br>';
        }

    }




    public function changeAdminPassword()
    {
        $this->loadModel('Admin');


        if ($this->request->isPost()) {
            $json = file_get_contents('php://input');
            //$json = $this->request->data('json');
            $data = json_decode($json, TRUE);


            $user_id        = $data['user_id'];




            $new_password   = $data['password'];




            $this->request->data['password'] = $new_password;
            $this->Admin->id                  = $user_id;


            if ($this->Admin->save($this->request->data)) {

                $user_info = $this->Admin->getUserDetailsFromID($user_id);
                $result['code'] = 200;
                $result['msg']  = $user_info;
                echo json_encode($result);
                die();
            } else {


                echo Message::DATASAVEERROR();
                die();


            }

        } else {

            echo Message::INCORRECTPASSWORD();
            die();




        }

    }
    public function currentAdminChangePassword()
    {
        $this->loadModel('Admin');

        if ($this->request->isPost()) {
            $json = file_get_contents('php://input');
            $data = json_decode($json, TRUE);


            $user_id        = $data['user_id'];
            $this->Admin->id = $user_id;
            $email          = $this->Admin->field('email');

            $old_password   = $data['old_password'];
            $new_password   = $data['new_password'];


            if ($this->Admin->verifyPassword($email, $old_password)) {

                $this->request->data['password'] = $new_password;
                $this->Admin->id                  = $user_id;


                if ($this->Admin->save($this->request->data)) {

                    $user_info = $this->Admin->getUserDetailsFromID($user_id);
                    $result['code'] = 200;
                    $result['msg']  = $user_info;
                    echo json_encode($result);
                    die();
                } else {


                    echo Message::DATASAVEERROR();
                    die();


                }

            } else {

                echo Message::INCORRECTPASSWORD();
                die();

            }


        }

    }









}