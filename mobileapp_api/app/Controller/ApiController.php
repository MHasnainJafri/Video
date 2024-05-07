<?php

App::uses('Utility', 'Lib');
App::uses('Premium', 'Lib');
App::uses('Regular', 'Lib');
App::uses('Message', 'Lib');
App::uses('CustomEmail', 'Lib');
App::uses('BunnyCDNStorage', 'Lib');
App::uses('Google', 'Lib');
class ApiController extends AppController
{

    //public $components = array('Email');

    public $autoRender = false;
    public $layout = false;


    public function beforeFilter()
    {


        if ($this->request->isPost()) {



            $json = file_get_contents('php://input');
            $json_error = Utility::isJsonError($json);


            if (!function_exists('apache_request_headers')) {
                $headers = Utility::apache_request_headers();
            } else {
                $headers = apache_request_headers();
            }

            $request_uri = $_SERVER['REQUEST_URI'];
            $http_host = $_SERVER['HTTP_HOST'];

            $end_point = substr($request_uri, strrpos($request_uri, '/') + 1);

            if ($end_point == "getVideoDetection") {



            }else {


                $client_api_key = 0;
                if (array_key_exists("Api-Key", $headers)) {
                    $client_api_key = $headers['Api-Key'];

                } else if (array_key_exists("API-KEY", $headers)) {

                    $client_api_key = $headers['API-KEY'];
                } else if (array_key_exists("api-key", $headers)) {

                    $client_api_key = $headers['api-key'];
                }


                if (strlen($client_api_key) > 0) {


                    if ($client_api_key != API_KEY) {

                        Message::ACCESSRESTRICTED();
                        die();

                    }
                } else {
                    $output['code'] = 201;
                    $output['msg'] = "API KEY is missing";

                    echo json_encode($output);
                    die();

                }
            }

            if ($json_error == "false") {



                return true;


            } else {
                $privacy_type = $this->request->data('privacy_type');
                if (strlen($privacy_type) > 0) {


                }
                return true;
                $output['code'] = 202;
                $output['msg'] = $json_error;

                echo json_encode($output);
                die();


            }
        }
    }







    public function index(){


        echo "Congratulations!. You have configured your mobile api correctly";

    }


    public function registerUser()
    {


        $this->loadModel('User');
        $this->loadModel('PushNotification');
        $this->loadModel('PrivacySetting');
        $this->loadModel('VerificationRequest');
        $this->loadModel('ReferralUsed');

        if ($this->request->isPost()) {


            $json = file_get_contents('php://input');
            $data = json_decode($json, TRUE);



            $user['created'] = date('Y-m-d H:i:s', time());




            $user['role'] = "user";





            if(isset($data['first_name'])){

                $first_name = $data['first_name'];
                $last_name = $data['last_name'];


                $user['first_name'] = $first_name;


                $user['last_name'] = $last_name;
            }

            if(isset($data['username'])){

                $username = $data['username'];

                if(preg_match('/[^a-z_\-0-9]/i', $username)){
                    $output['code'] = 201;
                    $output['msg'] = "invalid username";
                    echo json_encode($output);
                    die();

                }



                $user['username'] = $username;



            }


            if(isset($data['social']) && !isset($data['dob'])){
                $social_id = $data['social_id'];
                $auth_token = $data['auth_token'];
                $social = $data['social'];





                $user_details = $this->User->isSocialIDAlreadyExist($social_id);

                if(isset($data['email'])){


                    $user_details = $this->User->getUserDetailsAgainstEmail($data['email']);




                }
                if(count($user_details) > 0) {
                    $verification_details = $this->VerificationRequest->getVerificationDetailsAgainstUserID($user_details['User']['id']);

                    if (count($verification_details) > 0) {

                        $user_details['User']['verification_applied'] = 1;
                    }else{

                        $user_details['User']['verification_applied'] = 0;

                    }
                }
                if(count($user_details) > 0 ){

                    $active = $user_details['User']['active'];

                    $wallet_total =  $this->walletTotal($user_details['User']['id']);
                    $user_details['User']['wallet'] = $wallet_total['total'];
                    $user_details['User']['total_all_time_coins'] = $wallet_total['gifts_receive'];

                    if($active > 1){


                        $output['code'] = 201;
                        $output['msg'] = "You have been blocked by the admin. Contact support";
                        echo json_encode($output);
                        die();

                    }

                    if($social == "facebook"){

                        $verify = Utility::getFacebookUserInfo($auth_token);
                        $verify = true;
                        if($verify){

                            //$this->User->id = $user_details['User']['id'];
                            //$this->User->saveField('auth_token',$auth_token);

                            $output['code'] = 200;
                            $output['msg'] = $user_details;
                            echo json_encode($output);
                            die();

                        }else{

                            $output['code'] = 201;
                            $output['msg'] = "token invalid";
                            echo json_encode($output);
                            die();


                        }

                    }

                    if($social == "google"){

                        $verify = Utility::getGoogleUserInfo($auth_token);
                        $verify = true;
                        if($verify){

                            // $this->User->id = $user_details['User']['id'];
                            // $this->User->saveField('auth_token',$auth_token);


                            $output['code'] = 200;
                            $output['msg'] = $user_details;
                            echo json_encode($output);
                            die();

                        }else{
                            return true;

                            $output['code'] = 201;
                            $output['msg'] = "token invalid";
                            echo json_encode($output);
                            die();


                        }

                    }


                    if (isset($data['profile_pic'])) {


                        $user['profile_pic'] = $data['profile_pic'];
                    }


                    if(strlen($data['referral_code']) > 0){


                        $user_details_referral = $this->User->getDetailsAgainstReferral($data['referral_code']);
                        if(count($user_details_referral) > 0){
                            $post_data['referral_owner'] = $user_details_referral['User']['id'];
                            $post_data['used_by'] = $user_details['User']['id'];
                            $this->ReferralUsed->save($post_data);
                        }

                    }


                    $output['code'] = 200;
                    $output['msg'] = $user_details;
                    echo json_encode($output);
                    die();

                }else{


                    $output['code'] = 201;
                    $output['msg'] = "open registration screen";
                    echo json_encode($output);
                    die();

                }
            }


            if(isset($data['social']) && isset($data['dob'])){
                $social = $data['social'];
                $auth_token = $data['auth_token'];
                $user['social_id'] = $data['social_id'];
                $user['social'] = $social;
                $user['dob'] = $data['dob'];

                if(isset($data['gender'])){

                    $user['gender'] = strtolower($data['gender']);
                }

                $referral_code = Utility::getToken(5);
                $user['referral_code'] = strtolower($referral_code);
                if (isset($data['profile_pic'])) {


                    $user['profile_pic'] = $data['profile_pic'];
                }
                $user['email'] = $data['email'];
                $username_count = $this->User->isUsernameAlreadyExist($username);
                if($username_count > 0){

                    $output['code'] = 201;
                    $output['msg'] = "This username isn't available";
                    echo json_encode($output);
                    die();
                }




                if($social == "facebook") {

                    $verify = Utility::getFacebookUserInfo($auth_token);
                    $verify = true;
                    if (!$verify) {


                        $output['code'] = 201;
                        $output['msg'] = "invalid token";
                        echo json_encode($output);
                        die();

                    }
                }

                if($social == "google") {

                    $verify = Utility::getGoogleUserInfo($auth_token);
                    $verify = true;
                    if (!$verify) {


                        $output['code'] = 201;
                        $output['msg'] = "invalid token";
                        echo json_encode($output);
                        die();

                    }
                }

                $this->User->save($user);
                $user_id = $this->User->getInsertID();




                $output = array();
                $userDetails = $this->User->getUserDetailsFromID($user_id);
                if(count($userDetails) > 0) {
                    $verification_details = $this->VerificationRequest->getVerificationDetailsAgainstUserID($user_id);

                    if (count($verification_details) > 0) {

                        $userDetails['User']['verification_applied'] = 1;
                    }else{

                        $userDetails['User']['verification_applied'] = 0;

                    }
                }


                if(isset($data['referral_code'])){
                if(strlen($data['referral_code']) > 0) {


                    $user_details_referral = $this->User->getDetailsAgainstReferral($data['referral_code']);
                    if (count($user_details_referral) > 0) {
                        $post_data['referral_owner'] = $user_details_referral['User']['id'];
                        $post_data['used_by'] = $userDetails['User']['id'];
                        $this->ReferralUsed->save($post_data);
                    }
                }
                }
                $notification['likes'] = 1;
                $notification['comments'] = 1;
                $notification['new_followers'] = 1;
                $notification['mentions'] = 1;
                $notification['direct_messages'] = 1;
                $notification['video_updates'] = 1;
                $notification['id'] = $user_id;

                $this->PushNotification->save($notification);


                $settings['videos_download'] = 1;
                $settings['videos_repost'] = 1;
                $settings['direct_message'] = "everyone";
                $settings['duet'] = "everyone";
                $settings['liked_videos'] = "me";
                $settings['video_comment'] = "everyone";
                $settings['id'] = $user_id;

                $this->PrivacySetting->save($settings);
                $output['code'] = 200;
                $output['msg'] = $userDetails;
                echo json_encode($output);
                die();

            }

            if(!isset($data['social']) && isset($data['email'])) {


                $session_token = Utility::generateSessionToken();
                $user['dob'] = $data['dob'];
                $user['auth_token'] = $session_token;
                $user['username'] = $username;
                $user['password'] = $data['password'];
                $user['email'] = $data['email'];

                if (isset($data['gender'])) {

                    $user['gender'] = strtolower($data['gender']);
                }

                if (isset($data['profile_pic'])) {


                    $image = $data['profile_pic'];
                    $folder_url = UPLOADS_FOLDER_URI;

                    $filePath = Utility::uploadFileintoFolder(1, $image, $folder_url);
                    $user['profile_pic'] = $filePath;
                }


                $email_count = $this->User->isEmailAlreadyExist($data['email']);
                if ($email_count > 0) {

                    $user_details = $this->User->getUserDetailsAgainstEmail($data['email']);
                    $active = $user_details['User']['active'];

                    if ($active > 1) {


                        $output['code'] = 201;
                        $output['msg'] = "You have been blocked by the admin. Contact support";
                        echo json_encode($output);
                        die();

                    }

                    $output['code'] = 201;
                    $output['msg'] = "The account already exist with this email";
                    echo json_encode($output);
                    die();
                }
                $username_count = $this->User->isUsernameAlreadyExist($data['username']);
                if ($username_count > 0) {

                    $user_details = $this->User->getUserDetailsAgainstUsername($data['username']);
                    $active = $user_details['User']['active'];

                    if ($active > 1) {


                        $output['code'] = 201;
                        $output['msg'] = "You have been blocked by the admin. Contact support";
                        echo json_encode($output);
                        die();

                    }

                    $output['code'] = 201;
                    $output['msg'] = "This username isn't available";
                    echo json_encode($output);
                    die();
                }


                $referral_code = Utility::getToken(5);
                $user['referral_code'] = strtolower($referral_code);


                $this->User->save($user);
                $user_id = $this->User->getInsertID();


                $output = array();
                $userDetails = $this->User->getUserDetailsFromID($user_id);
                if (count($userDetails) > 0) {
                    $verification_details = $this->VerificationRequest->getVerificationDetailsAgainstUserID($user_id);

                    if (count($verification_details) > 0) {

                        $userDetails['User']['verification_applied'] = 1;
                    } else {

                        $userDetails['User']['verification_applied'] = 0;

                    }
                }

                if(isset($data['referral_code'])){
                if (strlen($data['referral_code']) > 0) {


                    $user_details_referral = $this->User->getDetailsAgainstReferral($data['referral_code']);
                    if (count($user_details_referral) > 0) {
                        $post_data['referral_owner'] = $user_details_referral['User']['id'];
                        $post_data['used_by'] = $userDetails['User']['id'];
                        $this->ReferralUsed->save($post_data);
                    }

                }
            }
                $notification['likes'] = 1;
                $notification['comments'] = 1;
                $notification['new_followers'] = 1;
                $notification['mentions'] = 1;
                $notification['direct_messages'] = 1;
                $notification['video_updates'] = 1;
                $notification['id'] = $user_id;

                $this->PushNotification->save($notification);

                $settings['videos_download'] = 1;
                $settings['videos_repost'] = 1;
                $settings['direct_message'] = "everyone";
                $settings['duet'] = "everyone";
                $settings['liked_videos'] = "me";
                $settings['video_comment'] = "everyone";
                $settings['id'] = $user_id;

                $this->PrivacySetting->save($settings);
                $output['code'] = 200;
                $output['msg'] = $userDetails;
                echo json_encode($output);
                die();





            }


            if(isset($data['phone']) && !isset($data['dob'])) {
                //login

                $user['phone'] = $data['phone'];


                $phone_exist = $this->User->isphoneNoAlreadyExist($data['phone']);

                if (count($phone_exist) > 0) {


                    $active = $phone_exist['User']['active'];

                    if($active > 1){


                        $output['code'] = 201;
                        $output['msg'] = "You have been blocked by the admin. Contact support";
                        echo json_encode($output);
                        die();

                    }

                    if (isset($data['profile_pic'])) {


                        $image = $data['profile_pic'];
                        $folder_url = UPLOADS_FOLDER_URI;

                        $filePath = Utility::uploadFileintoFolder(1, $image, $folder_url);
                        $user['profile_pic'] = $filePath;
                    }
                    $session_token = Utility::generateSessionToken();
                    $user['auth_token'] = $session_token;
                    $this->User->id = $phone_exist['User']['id'];




                    $this->User->save($user);
                    $userDetails = $this->User->getUserDetailsFromID($phone_exist['User']['id']);


                    if(count($userDetails) > 0) {
                        $verification_details = $this->VerificationRequest->getVerificationDetailsAgainstUserID(($userDetails['User']['id']));

                        if (count($verification_details) > 0) {

                            $userDetails['User']['verification_applied'] = 1;
                        }else{

                            $userDetails['User']['verification_applied'] = 0;

                        }
                    }

                    $wallet_total =  $this->walletTotal($phone_exist['User']['id']);
                    $userDetails['User']['wallet'] = $wallet_total['total'];
                    $userDetails['User']['total_all_time_coins'] = $wallet_total['gifts_receive'];

                    $output['code'] = 200;
                    $output['msg'] = $userDetails;
                    echo json_encode($output);
                    die();
                } else {

                    $output['code'] = 201;
                    $output['msg'] = "open register screen";
                    echo json_encode($output);
                    die();

                }

            }else  if(isset($data['phone']) && isset($data['dob'])) {


                $referral_code = Utility::getToken(5);
                $user['referral_code'] = strtolower($referral_code);
                //register
                $session_token = Utility::generateSessionToken();
                $user['phone'] = $data['phone'];
                $user['auth_token'] = $session_token;

                $user['username'] = $username;
                $user['dob'] = $data['dob'];

                if (isset($data['gender'])) {

                    $user['gender'] = strtolower($data['gender']);
                }

                if (isset($data['first_name'])) {

                    $user['first_name'] = $data['first_name'];
                    $user['last_name'] = $data['last_name'];
                }

                $username_count = $this->User->isUsernameAlreadyExist($data['username']);
                if ($username_count > 0) {

                    $output['code'] = 201;
                    $output['msg'] = "This username isn't available";
                    echo json_encode($output);
                    die();
                }
                if (isset($data['profile_pic'])) {


                    $image = $data['profile_pic'];
                    $folder_url = UPLOADS_FOLDER_URI;

                    $filePath = Utility::uploadFileintoFolder(1, $image, $folder_url);
                    $user['profile_pic'] = $filePath;
                }
                $this->User->save($user);
                $user_id = $this->User->getInsertID();


                $output = array();
                $userDetails = $this->User->getUserDetailsFromID($user_id);

                if (count($userDetails) > 0) {
                    $verification_details = $this->VerificationRequest->getVerificationDetailsAgainstUserID(($userDetails['User']['id']));

                    if (count($verification_details) > 0) {

                        $userDetails['User']['verification_applied'] = 1;
                    }
                }
                if(isset($data['referral_code'])){
                if (strlen($data['referral_code']) > 0) {


                    $user_details_referral = $this->User->getDetailsAgainstReferral($data['referral_code']);
                    if (count($user_details_referral) > 0) {
                        $post_data['referral_owner'] = $user_details_referral['User']['id'];
                        $post_data['used_by'] = $user_details_referral['User']['id'];
                        $this->ReferralUsed->save($post_data);
                    }

                }
            }
                $notification['likes'] = 1;
                $notification['comments'] = 1;
                $notification['new_followers'] = 1;
                $notification['mentions'] = 1;
                $notification['direct_messages'] = 1;
                $notification['video_updates'] = 1;
                $notification['id'] = $user_id;
                $settings['videos_download'] = 1;
                $settings['videos_repost'] = 1;
                $settings['direct_message'] = "everyone";
                $settings['duet'] = "everyone";
                $settings['liked_videos'] = "me";
                $settings['video_comment'] = "everyone";
                $settings['id'] = $user_id;

                $this->PrivacySetting->save($settings);
                $this->PushNotification->save($notification);
                $output['code'] = 200;
                $output['msg'] = $userDetails;
                echo json_encode($output);
                die();


            }











        }
    }



    public function reportRoom()
    {


        $this->loadModel("ReportRoom");

        $this->loadModel("ReportReason");
        $this->loadModel("Room");




        if ($this->request->isPost()) {
            $json = file_get_contents('php://input');
            $data = json_decode($json, TRUE);


            $user_id = $data['user_id'];
            $room_id = $data['room_id'];
            $report_reason_id = $data['report_reason_id'];
            $description = $data['description'];
            $created = date('Y-m-d H:i:s', time());


            $report['user_id'] = $user_id;
            $report['room_id'] = $room_id;
            $report['report_reason_id'] = $report_reason_id;
            $report['description'] = $description;
            $report['created'] = $created;


            $room_exist = $this->ReportRoom->ifExist($user_id,$room_id);

            if(count($room_exist) > 0){


                $output['code'] = 201;
                $output['msg'] = "Room has already been reported by you";
                echo json_encode($output);


                die();

            }
            $details = $this->Room->getDetails($room_id);


            $report_reason_details =  $this->ReportReason->getDetails($report_reason_id);




            if (count($details) > 0) {

                if(count($report_reason_details) > 0){

                    $report['report_reason_title'] = $report_reason_details['ReportReason']['title'];

                }
                $this->ReportRoom->save($report);






                $id = $this->ReportRoom->getInsertID();
                $details = $this->ReportRoom->getDetails($id);


                $output['code'] = 200;
                $output['msg'] = $details;
                echo json_encode($output);


                die();
            }else{

                $output['code'] = 201;
                $output['msg'] = "user not available";
                echo json_encode($output);


                die();
            }


        }
    }

    public function login()
    {
        $this->loadModel('User');



        if ($this->request->isPost()) {


            $json = file_get_contents('php://input');

            $data = json_decode($json, TRUE);


            $password = $data['password'];







            if (isset($data['email'])) {

                $email = strtolower($data['email']);
                $userData = $this->User->verify($email, $password,"user");


                if(count($userData) < 0){

                    $userData = $this->User->verifyWithUsername($email, $password,"user");

                }
            }


            if (($userData)) {


                $user_id = $userData[0]['User']['id'];
                $active = $userData[0]['User']['active'];

                if($active > 1){

                    $output['code'] = 201;
                    $output['msg'] = "You have been blocked by the admin. Contact support";
                    echo json_encode($output);
                    die();
                }



                $session_token = Utility::generateSessionToken();
                $this->User->id = $user_id;
                $this->User->saveField('auth_token',$session_token);
                $output = array();
                $userDetails = $this->User->getUserDetailsFromID($user_id);

                //CustomEmail::welcomeStudentEmail($email);
                $output['code'] = 200;
                $output['msg'] = $userDetails;
                echo json_encode($output);
                die();


            } else {
                echo Message::INVALIDDETAILS();
                die();

            }


        }
    }


    public function showFollowers()
    {


        $this->loadModel("Follower");
        $this->loadModel("Video");


        if ($this->request->isPost()) {
            $json = file_get_contents('php://input');
            $data = json_decode($json, TRUE);


            $user_id = 0;
            if(isset($data['user_id'])){

                $user_id = $data['user_id'];
            }


            $starting_point = 0;
            if (isset($data['starting_point'])) {

                $starting_point = $data['starting_point'];

            }

            $followers = $this->Follower->getUserFollowers($user_id,$starting_point);

            if (count($followers) > 0) {
                foreach ($followers as $key => $follow) {

                    $person_user_id = $follow['FollowerList']['id'];


                    $follower_details = $this->Follower->ifFollowing($user_id, $person_user_id);
                    $follower_back_details = $this->Follower->ifFollowing($person_user_id, $user_id);

                    $followers_count = $this->Follower->countFollowers($person_user_id);

                    $video_count = $this->Video->getUserVideosCount($person_user_id);
                    $followers[$key]['FollowerList']['follower_count'] = $followers_count;
                    $followers[$key]['FollowerList']['video_count'] = $video_count;
                    if (count($follower_details) > 0 && count($follower_back_details) > 0) {

                        $followers[$key]['FollowerList']['button'] = "Friends";
                    } else if (count($follower_details) > 0) {

                        $followers[$key]['FollowerList']['button'] = "Following";

                    } else if (count($follower_back_details) > 0 && $follower_details < 0) {

                        $followers[$key]['FollowerList']['button'] = "Follow Back";

                    } else {

                        $followers[$key]['FollowerList']['button'] = "Follow";

                    }

                }
            }






            if(isset($data['other_user_id'])) {
                $other_user_id = $data['other_user_id'];

                $followers = $this->Follower->getUserFollowers($other_user_id,$starting_point);


                if (count($followers) > 0) {
                    foreach ($followers as $key => $follow) {

                        $person_user_id = $follow['FollowerList']['id'];

                        if ($user_id == $person_user_id) {

                            $followers[$key]['FollowerList']['button'] = "0";

                        } else {
                            $follower_details = $this->Follower->ifFollowing($user_id, $person_user_id);
                            $follower_back_details = $this->Follower->ifFollowing($person_user_id, $user_id);

                            $followers_count = $this->Follower->countFollowers($person_user_id);

                            $video_count = $this->Video->getUserVideosCount($person_user_id);
                            $followers[$key]['FollowerList']['follower_count'] = $followers_count;
                            $followers[$key]['FollowerList']['video_count'] = $video_count;
                            if (count($follower_details) > 0 && count($follower_back_details) > 0) {

                                $followers[$key]['FollowerList']['button'] = "Friends";
                            } else if (count($follower_details) > 0) {

                                $followers[$key]['FollowerList']['button'] = "Following";

                            } else if (count($follower_back_details) > 0 && $follower_details < 0) {

                                $followers[$key]['FollowerList']['button'] = "Follow Back";

                            } else {

                                $followers[$key]['FollowerList']['button'] = "Follow";

                            }
                        }
                    }
                }
            }
            if(count($followers) > 0 ) {

                $output['code'] = 200;

                $output['msg'] = $followers;


                echo json_encode($output);


                die();
            }else{

                Message::EMPTYDATA();
                die();

            }

        }
    }


    public function showProfileVisitors()
    {


        $this->loadModel("ProfileVisit");
        $this->loadModel("Follower");
        $this->loadModel("Video");



        if ($this->request->isPost()) {
            $json = file_get_contents('php://input');
            $data = json_decode($json, TRUE);


            $created = date('Y-m-d H:i:s', time());




                $user_id = $data['user_id'];

            $last_date = date('Y-m-d', strtotime("- 30 days", strtotime($created)));
            $this->ProfileVisit->updateReadCount($user_id);

            $starting_point = 0;
            if (isset($data['starting_point'])) {

                $starting_point = $data['starting_point'];

            }

            $visitors = $this->ProfileVisit->getProfileVisitors($user_id,$last_date,$starting_point);

            if (count($visitors) > 0) {
                foreach ($visitors as $key => $follow) {

                    $person_user_id = $follow['Visitor']['id'];


                    $follower_details = $this->Follower->ifFollowing($user_id, $person_user_id);
                    $follower_back_details = $this->Follower->ifFollowing($person_user_id, $user_id);

                    $followers_count = $this->Follower->countFollowers($person_user_id);

                    $video_count = $this->Video->getUserVideosCount($person_user_id);
                    $visitors[$key]['Visitor']['follower_count'] = $followers_count;
                    $visitors[$key]['Visitor']['video_count'] = $video_count;
                    if (count($follower_details) > 0 && count($follower_back_details) > 0) {

                        $visitors[$key]['Visitor']['button'] = "Friends";
                    } else if (count($follower_details) > 0) {

                        $visitors[$key]['Visitor']['button'] = "Following";

                    } else if (count($follower_back_details) > 0 && $follower_details < 0) {

                        $visitors[$key]['Visitor']['button'] = "Follow Back";

                    } else {

                        $visitors[$key]['Visitor']['button'] = "Follow";

                    }

                }
            }






            if(count($visitors) > 0 ) {

                $output['code'] = 200;

                $output['msg'] = $visitors;


                echo json_encode($output);


                die();
            }else{

                Message::EMPTYDATA();
                die();

            }

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
                        $msg = "Buy premium features and setup S3. or delete S3 urls from database";
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

    public function showFollowing()
    {


        $this->loadModel("Follower");
        $this->loadModel("Video");

        if ($this->request->isPost()) {
            $json = file_get_contents('php://input');
            $data = json_decode($json, TRUE);

            $user_id = 0;
            if(isset($data['user_id'])){

                $user_id = $data['user_id'];
            }
            $starting_point = 0;


            if (isset($data['starting_point'])) {

                $starting_point = $data['starting_point'];

            }
            $following = $this->Follower->getUserFollowing($user_id,$starting_point);

            if (count($following) > 0) {
                foreach ($following as $key => $follow) {

                    $person_user_id = $follow['FollowingList']['id'];



                    $follower_details = $this->Follower->ifFollowing($user_id, $person_user_id);
                    $follower_back_details = $this->Follower->ifFollowing($person_user_id, $user_id);

                    $following_count = $this->Follower->countFollowing($person_user_id);

                    $video_count = $this->Video->getUserVideosCount($person_user_id);
                    $following[$key]['FollowingList']['following_count'] = $following_count;
                    $following[$key]['FollowingList']['video_count'] = $video_count;

                    if (count($follower_details) > 0 && count($follower_back_details) > 0) {

                        $following[$key]['FollowingList']['button'] = "Friends";
                    } else if (count($follower_details) > 0) {

                        $following[$key]['FollowingList']['button'] = "Following";

                    } else if (count($follower_back_details) > 0 && $follower_details < 0) {

                        $following[$key]['FollowingList']['button'] = "Follow Back";

                    } else {

                        $following[$key]['FollowingList']['button'] = "Follow";

                    }

                }
            }

            if(isset($data['other_user_id'])) {
                $other_user_id = $data['other_user_id'];

                $following = $this->Follower->getUserFollowing($other_user_id,$starting_point);



                if (count($following) > 0) {
                    foreach ($following as $key => $follow) {

                        $person_user_id = $follow['FollowingList']['id'];

                        if ($user_id == $person_user_id) {

                            $following[$key]['FollowingList']['button'] = "0";

                        } else {
                            $follower_details = $this->Follower->ifFollowing($user_id, $person_user_id);
                            $follower_back_details = $this->Follower->ifFollowing($person_user_id, $user_id);

                            $following_count = $this->Follower->countFollowing($person_user_id);

                            $video_count = $this->Video->getUserVideosCount($person_user_id);
                            $following[$key]['FollowingList']['following_count'] = $following_count;
                            $following[$key]['FollowingList']['video_count'] = $video_count;
                            if (count($follower_details) > 0 && count($follower_back_details) > 0) {

                                $following[$key]['FollowingList']['button'] = "Friends";
                            } else if (count($follower_details) > 0) {

                                $following[$key]['FollowingList']['button'] = "Following";

                            } else if (count($follower_back_details) > 0 && $follower_details < 0) {

                                $following[$key]['FollowingList']['button'] = "Follow Back";

                            } else {

                                $following[$key]['FollowingList']['button'] = "Follow";

                            }
                        }
                    }
                }
            }


            if(count($following) > 0) {
                $output['code'] = 200;

                $output['msg'] = $following;


                echo json_encode($output);


                die();

            }else{



                Message::EMPTYDATA();
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
                $msg = "You cannot delete videos in demo account";

                $output['code'] = $code;

                $output['msg'] = $msg;

                echo json_encode($output);

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
                        $msg = "Buy an Premium license and setup S3. or delete S3 urls from database";
                    }
                }else{
                    if(file_exists($video_url)) {
                        unlink($video_url);
                    }

                    if(file_exists($thum_url)) {
                        unlink($thum_url);
                    }
                    if(file_exists($gif_url)) {
                        unlink($gif_url);
                    }

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

    public function followUser()
    {

        //$this->loadModel("FollowRequest");
        $this->loadModel("User");
        $this->loadModel("Notification");
        $this->loadModel("PushNotification");
        $this->loadModel("Follower");


        if ($this->request->isPost()) {
            $json = file_get_contents('php://input');
            $data = json_decode($json, TRUE);

            $sender_id = $data['sender_id'];
            $receiver_id = $data['receiver_id'];

            $created = date('Y-m-d H:i:s', time());


            $friend['sender_id'] = $sender_id;
            $friend['receiver_id'] = $receiver_id;
            $friend['created'] = $created;

            $sender_details = $this->User->getUserDetailsFromID($sender_id);

            if(count($sender_details) < 1){

                $output['code'] = 201;
                $output['msg'] = "Login First";
                echo json_encode($output);


                die();

            }

            $follower_details = $this->Follower->ifFollowing($sender_id, $receiver_id);

            $receiver_details = $this->User->getUserDetailsFromID($receiver_id);

            if (count($follower_details) < 1) {

                $sender_details = $this->User->getUserDetailsFromID($sender_id);


                $this->Follower->save($friend);
                $output = array();
                $id = $this->Follower->getInsertID();
                $details = $this->Follower->getDetails($id);
                //$unread = $this->FollowRequest->getUnreadRequests($receiver_id);
                $msg = $sender_details['User']['username'] . ' started following you';



                if (strlen($receiver_details['User']['device_token']) > 8) {
                    $notification['to'] = $receiver_details['User']['device_token'];

                    $notification['notification']['title'] = $msg;
                    $notification['notification']['body'] = "";
                    $notification['notification']['badge'] = "1";
                    $notification['notification']['sound'] = "default";
                    $notification['notification']['icon'] = "";
                    $notification['notification']['type'] = "follow";
                    $notification['notification']['receiver_id'] = $receiver_id;
                    $notification['notification']['sender_id'] = $sender_id;
                    $notification['data']['receiver_id'] = $receiver_id;
                    $notification['data']['sender_id'] = $sender_id;
                    $notification['data']['title'] = $msg;
                    $notification['data']['body'] = '';
                    $notification['data']['icon'] = "";
                    $notification['data']['badge'] = "1";
                    $notification['data']['sound'] = "default";
                    $notification['data']['type'] = "follow";



                    $if_exist = $this->PushNotification->getDetails($receiver_details['User']['id']);
                    if(count($if_exist) > 0) {

                        $likes = $if_exist['PushNotification']['new_followers'];
                        if($likes > 0) {
                            Utility::sendPushNotificationToMobileDevice(json_encode($notification));
                        }
                    }




                    $notification_data['sender_id'] = $sender_details['User']['id'];
                    $notification_data['receiver_id'] = $receiver_details['User']['id'];
                    $notification_data['type'] = "following";
                    $notification_data['video_id'] = 0;

                    $notification_data['string'] =  $msg;
                    $notification_data['created'] =  $created;

                    $this->Notification->save($notification_data);

                }

                $follower_details = $this->Follower->ifFollowing($sender_details['User']['id'], $receiver_details['User']['id']);
                $following_details = $this->Follower->ifFollowing($receiver_details['User']['id'], $sender_details['User']['id']);
                //$follow_request = $this->FollowRequest->checkIfDuplicate($sender_details['User']['id'], $receiver_details['User']['id']);

                if(count($follower_details) > 0 && count($following_details) > 0){

                    $receiver_details['User']['button'] = "Friends";

                } else   if(count($follower_details) > 0 && count($following_details) < 1){

                    $receiver_details['User']['button'] = "following";



                }

                $output['code'] = 200;
                $output['msg'] = $receiver_details;
                echo json_encode($output);




                die();
            }else{
                $receiver_details['User']['button'] = "follow";
                $id = $follower_details['Follower']['id'];
                $this->Follower->id = $id;
                $this->Follower->delete();
                $output['code'] = 200;
                $output['msg'] = $receiver_details;
                echo json_encode($output);


                die();
            }


        }
    }

    public function registerDevice()
    {

        $this->loadModel("Device");



        if ($this->request->isPost()) {
            $json = file_get_contents('php://input');
            $data = json_decode($json, TRUE);


            $name = $data['key'];


            $created = date('Y-m-d H:i:s', time());


            $device['key'] = $name;
            $friend['created'] = $created;


            $device_details = $this->Device->ifExist($name);


            if (count($device_details) < 1) {

                $this->Device->save($device);



                $id = $this->Device->getInsertID();
                $details = $this->Device->getDetails($id);


                $output['code'] = 200;
                $output['msg'] = $details;
                echo json_encode($output);


                die();
            }else{

                $output['code'] = 200;
                $output['msg'] = $device_details;
                echo json_encode($output);


                die();
            }


        }
    }


    public function updatePushNotificationSettings()
    {

        $this->loadModel("PushNotification");



        if ($this->request->isPost()) {
            $json = file_get_contents('php://input');
            $data = json_decode($json, TRUE);



            $user_id =  $data['user_id'];

            if(isset($data['likes'])){

                $likes =  $data['likes'];
                $notification['likes'] = $likes;

            }

            if(isset($data['comments'])){

                $comments =  $data['comments'];
                $notification['comments'] = $comments;

            }

            if(isset($data['new_followers'])){

                $new_followers =  $data['new_followers'];
                $notification['new_followers'] = $new_followers;

            }

            if(isset($data['mentions'])){

                $mentions =  $data['mentions'];
                $notification['mentions'] = $mentions;

            }

            if(isset($data['direct_messages'])){

                $direct_messages =  $data['direct_messages'];
                $notification['direct_messages'] = $direct_messages;

            }

            if(isset($data['video_updates'])){

                $video_updates =  $data['video_updates'];
                $notification['video_updates'] = $video_updates;

            }


            $details = $this->PushNotification->getDetails($user_id);

            if(count($details) > 0) {


                $this->PushNotification->id = $user_id;

                $this->PushNotification->save($notification);



                $details = $this->PushNotification->getDetails($user_id);


                $output['code'] = 200;
                $output['msg'] = $details;
                echo json_encode($output);


                die();

            }else {

                $this->PushNotification->save($notification);
                $id = $this->PushNotification->getInsertID();
                $details = $this->PushNotification->getDetails($id);



                $output['code'] = 200;
                $output['msg'] = $details;
                echo json_encode($output);


                die();

            }


        }
    }

    public function addPrivacySetting()
    {

        $this->loadModel("PrivacySetting");



        if ($this->request->isPost()) {
            $json = file_get_contents('php://input');
            $data = json_decode($json, TRUE);



            $user_id =  $data['user_id'];

            if(isset($data['videos_download'])){

                $videos_download =  $data['videos_download'];
                $setting['videos_download'] = $videos_download;

            }

            if(isset($data['direct_message'])){

                $direct_message =  $data['direct_message'];
                $setting['direct_message'] = $direct_message;

            }

            if(isset($data['duet'])){

                $duet =  $data['duet'];
                $setting['duet'] = $duet;

            }

            if(isset($data['liked_videos'])){

                $liked_videos =  $data['liked_videos'];
                $setting['liked_videos'] = $liked_videos;

            }

            if(isset($data['direct_messages'])){

                $direct_messages =  $data['direct_messages'];
                $setting['direct_messages'] = $direct_messages;

            }

            if(isset($data['video_comment'])){

                $video_comment =  $data['video_comment'];
                $setting['video_comment'] = $video_comment;

            }


            $details = $this->PrivacySetting->getDetails($user_id);

            if(count($details) > 0) {


                $this->PrivacySetting->id = $user_id;

                $this->PrivacySetting->save($setting);



                $details = $this->PrivacySetting->getDetails($user_id);


                $output['code'] = 200;
                $output['msg'] = $details;
                echo json_encode($output);


                die();

            }else {

                $this->PrivacySetting->save($setting);
                $id = $this->PrivacySetting->getInsertID();
                $details = $this->PrivacySetting->getDetails($id);



                $output['code'] = 200;
                $output['msg'] = $details;
                echo json_encode($output);


                die();

            }


        }
    }
    public function followerNotification(){

        $this->loadModel('Follower');




        if ($this->request->isPost()) {
            $json = file_get_contents('php://input');
            $data = json_decode($json, TRUE);

            $not['sender_id'] = $data['sender_id'];
            $not['receiver_id'] = $data['receiver_id'];
            $not['notification'] = $data['notification'];

            $details = $this->Follower->ifFollowing($data['sender_id'],$data['receiver_id']);


            if(count($details) > 0) {

                $this->Follower->id = $details['Follower']['id'];
                $this->Follower->saveField('notification',$not['notification']);
                $details = $this->Follower->ifFollowing($data['sender_id'],$data['receiver_id']);
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

    public function showVideoCompression(){






        if ($this->request->isPost()) {
            $json = file_get_contents('php://input');
            $data = json_decode($json, TRUE);







                $output['code'] = 200;

                $output['msg'] = VIDEO_COMPRESSION;


                echo json_encode($output);


                die();


        }


    }


    public function showAllPromotions(){

        $this->loadModel('Promotion');
        $this->loadModel('VideoWatch');
        $this->loadModel('VideoLike');
        $this->loadModel('Follower');




        if ($this->request->isPost()) {
            $json = file_get_contents('php://input');
            $data = json_decode($json, TRUE);

           $user_id =  $data['user_id'];
           $days =  $data['days'];
            $end_datetime = date('Y-m-d H:i:s', time());
           if($days == 7){


               $start_datetime = date('Y-m-d H:i:s', strtotime("-7 days", strtotime($end_datetime)));

           }else  if($days == 28){


               $start_datetime = date('Y-m-d H:i:s', strtotime("-28 days", strtotime($end_datetime)));

           }else if($days == 60){


                $start_datetime = date('Y-m-d H:i:s', strtotime("-60 days", strtotime($end_datetime)));

            }else if($days == "custom"){


               $start_datetime = $data['start_datetime'];
               $end_datetime = $data['end_datetime'];

           }


           $promotions =  $this->Promotion->getUserPromotions($user_id);

           $total_coins_spent = 0;
           $total_video_views = 0;
           $total_destination_tap = 0;
           $total_video_likes = 0;
           if(count($promotions) > 0){

               foreach($promotions as $key=>$val){


                 $video_id =   $val['Promotion']['video_id'];

                 $coin =   $val['Promotion']['coin'];

                 $destination_tap =   $val['Promotion']['destination_tap'];
                 $count_views =   $this->VideoWatch->countWatchVideos($video_id,$start_datetime,$end_datetime);
                 $video_likes =   $this->VideoLike->countLikesBetweenDatetime($video_id,$start_datetime,$end_datetime);

                 $promotions[$key]['Promotion']['views'] = $count_views;
                   $total_coins_spent = $coin + $total_coins_spent;
                   $total_video_views = $count_views + $total_video_views;
                   $total_destination_tap = $destination_tap + $total_destination_tap;
                   $total_video_likes = $video_likes + $total_video_likes;
               }



           }

            $starting_formattedDate = date('M d', strtotime($start_datetime));
            $ending_formattedDate = date('M d', strtotime($end_datetime));


            $output['code'] = 200;

            $output['msg']['Promotions'] = $promotions;
            $output['msg']['Stats']['coins_spent'] = $total_coins_spent;
            $output['msg']['Stats']['total_video_views'] = $total_video_views;
            $output['msg']['Stats']['total_destination_tap'] = $total_destination_tap;
            $output['msg']['Stats']['total_video_likes'] = $total_video_likes;
            $output['msg']['Stats']['starting_date'] = $starting_formattedDate;
            $output['msg']['Stats']['ending_date'] = $ending_formattedDate;


            echo json_encode($output);


            die();


        }


    }


    public function showAnalytics(){

        $this->loadModel('Video');
        $this->loadModel('VideoWatch');
        $this->loadModel('VideoLike');
        $this->loadModel('Follower');
        $this->loadModel('VideoComment');
        $this->loadModel('VideoCommentReply');
        $this->loadModel('ProfileVisit');




        if ($this->request->isPost()) {
            $json = file_get_contents('php://input');
            $data = json_decode($json, TRUE);

            $user_id =  $data['user_id'];
            $start_datetime = $data['start_datetime'];
            $end_datetime = $data['end_datetime'];

            $countries_followers_graph = $this->Follower->countFollowersByCountry($user_id,$start_datetime,$end_datetime);
            $city_followers_graph = $this->Follower->countFollowersByCity($user_id,$start_datetime,$end_datetime);



            $videos =  $this->Video->getAllUserVideos($user_id);

            $profile_visit =   $this->ProfileVisit->getProfileViewsBetweenDates($user_id,$start_datetime,$end_datetime);
            $total_followers =   $this->Follower->countFollowersBetweenDatetime($user_id,$start_datetime,$end_datetime);
            $male_followers =   $this->Follower->countFollowersByGender($user_id,$start_datetime,$end_datetime,"male");
            $female_followers =   $this->Follower->countFollowersByGender($user_id,$start_datetime,$end_datetime,"female");
            $age_group =   $this->Follower->getFollowersByAge($user_id,$start_datetime,$end_datetime);
            $countries_followers_graph_new = array();
            $city_followers_graph_new = array();
            $video_views_graph = array();

            $video_ids_array = array();
            $count_views = 0;
            $video_likes = 0;
            $video_comments = 0;

            $video_comments_reply = 0;

            if(count($countries_followers_graph) > 0) {

                foreach($countries_followers_graph as $key=> $follower_country){
                    $countries_followers_graph_new[$key]['Country'] = $follower_country['FollowingList']['country'];
                    $countries_followers_graph_new[$key]['count'] = $follower_country['0']['count'];

                }

            }


            if(count($city_followers_graph) > 0) {

                foreach($city_followers_graph as $key=>$follower_city){
                    $city_followers_graph_new[$key]['Country'] = $follower_city['FollowingList']['city'];
                    $city_followers_graph_new[$key]['count'] = $follower_city['0']['count'];

                }

            }
            if(count($videos) > 0) {

                foreach ($videos as $key => $val) {


                    $video_id = $val['Video']['id'];

                    $video_ids_array[$key] = $video_id;


                }


                $count_views = $this->VideoWatch->countWatchVideos($video_ids_array, $start_datetime, $end_datetime);

                $video_likes = $this->VideoLike->countLikesBetweenDatetime($video_ids_array, $start_datetime, $end_datetime);
                $video_comments = $this->VideoComment->countCommentsBetweenDates($video_ids_array, $start_datetime, $end_datetime);
                $video_comments_reply = $this->VideoCommentReply->countCommentsBetweenDates($video_ids_array, $start_datetime, $end_datetime);
                $video_views_graph = $this->VideoWatch->countWatchVideosByDate($video_ids_array, $start_datetime, $end_datetime);
            }
                $followers_graph =   $this->Follower->countFollowersByDate($user_id,$start_datetime,$end_datetime);

            $start_date = new DateTime($start_datetime); // Start date
            $end_date = new DateTime($end_datetime); // End date
            $end_date->modify('+1 day'); // Increase the date by one day
           // $end_datetime_add = $end_date->format('Y-m-d');
// Create a DatePeriod object to generate a range of dates
            $date_range = new DatePeriod($start_date, new DateInterval('P1D'), $end_date);

// Initialize the output array
            $output_array = array();
            foreach ($date_range as $date) {
                $date_str = $date->format('Y-m-d');

                $count_follower = 0;
                $count_view = 0;

                // Check if the current date exists in the date array
                foreach ($followers_graph as $sub_array) {
                    foreach ($sub_array as $item) {
                        if ($item['date'] === $date_str) {
                            $count_follower = $item['count'];
                            break;
                        }
                    }
                }

                if (count($video_views_graph) > 0){
                    foreach ($video_views_graph as $sub_array) {
                        foreach ($sub_array as $item) {
                            if ($item['date'] === $date_str) {
                                $count_view = $item['count'];
                                break;
                            }
                        }
                    }
            }

                // Add the date and count to the output array
                $output_array_followers[] = array(array('date' => $date_str, 'count' => $count_follower));
                $output_array_views[] = array(array('date' => $date_str, 'count' => $count_view));
            }








            $starting_formattedDate = date('M d', strtotime($start_datetime));
            $ending_formattedDate = date('M d', strtotime($end_datetime));


            $output['code'] = 200;

           // $output['msg']['Promotions'] = $promotions;
            $output['msg']['Stats']['starting_date'] = $starting_formattedDate;
            $output['msg']['Stats']['ending_date'] = $ending_formattedDate;
            $output['msg']['Stats']['total_video_comments'] = $video_comments + $video_comments_reply;
            $output['msg']['Stats']['total_video_views'] = $count_views;
            $output['msg']['Stats']['total_profile_visits'] = $profile_visit;

            $output['msg']['Stats']['total_video_likes'] = $video_likes;
            $output['msg']['Stats']['total_followers'] = $total_followers;
            $output['msg']['Stats']['total_male_followers'] = $male_followers;
            $output['msg']['Stats']['total_female_followers'] = $female_followers;
            $output['msg']['Stats']['age_group'] = $age_group;
            $output['msg']['Stats']['video_views_graph'] = $output_array_views;
            $output['msg']['Stats']['followers_graph'] = $output_array_followers;
            $output['msg']['Stats']['followers_country'] = $countries_followers_graph_new;
            $output['msg']['Stats']['followers_city'] = $city_followers_graph_new;




            echo json_encode($output);


            die();


        }


    }



    public function showDeviceDetail(){

        $this->loadModel('Device');




        if ($this->request->isPost()) {
            $json = file_get_contents('php://input');
            $data = json_decode($json, TRUE);

            $key = $data['key'];

            $details = $this->Device->ifExist($key);


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

    public function showRegisteredContacts(){

        $this->loadModel('User');
        $this->loadModel('Follower');
        $this->loadModel('Video');




        if ($this->request->isPost()) {
            $json = file_get_contents('php://input');
            $data = json_decode($json, TRUE);
            $user_id =  $data['user_id'];

            if(isset($data['phone_numbers'])) {
                $phone_numbers = $data['phone_numbers'];


                $i = 0;
                $new_data = array();
                if (count($phone_numbers) > 0) {

                    foreach ($phone_numbers as $key => $val) {


                        $phone = $val['phone'];

                        $phone_exist = $this->User->editIsphoneNoAlreadyExist($phone, $user_id);

                        if($phone_exist > 0){

                            $new_data[$i] = $phone;
                            $i++;
                        }

                    }


                }


                if(count($new_data) > 0) {


                    $output['code'] = 200;

                    $output['msg'] = $new_data;


                    echo json_encode($output);


                    die();
                }else{

                    Message::EMPTYDATA();
                    die();
                }
            }else if(isset($data['facebook_ids'])) {
                $facebook_ids = $data['facebook_ids'];





                if (count($facebook_ids) > 0) {

                    foreach ($facebook_ids as $key => $val) {


                        $fb_ids[$key] = $val['fb_id'];


                    }

                    if(count($fb_ids) > 0){




                        $facebook_users = $this->User->getAllFacebookUsers($fb_ids,$user_id);

                        if(count($facebook_users)){

                            foreach($facebook_users as $fb=>$fbval){



                                $person_user_id = $fbval['User']['id'];


                                $follower_details = $this->Follower->ifFollowing($user_id, $person_user_id);
                                $follower_back_details = $this->Follower->ifFollowing($person_user_id, $user_id);

                                $followers_count = $this->Follower->countFollowers($person_user_id);

                                $video_count = $this->Video->getUserVideosCount($person_user_id);
                                $facebook_users[$fb]['User']['follower_count'] = $followers_count;
                                $facebook_users[$fb]['User']['video_count'] = $video_count;
                                if (count($follower_details) > 0 && count($follower_back_details) > 0) {

                                    $facebook_users[$fb]['User']['button'] = "Friends";
                                } else if (count($follower_details) > 0) {

                                    $facebook_users[$fb]['User']['button'] = "Following";

                                } else if (count($follower_back_details) > 0 && $follower_details < 0) {

                                    $facebook_users[$fb]['User']['button'] = "Follow Back";

                                } else {

                                    $facebook_users[$fb]['User']['button'] = "Follow";

                                }

                            }


                        }

                    }
                }

                if(count($facebook_users) > 0) {


                    $output['code'] = 200;

                    $output['msg'] = $facebook_users;


                    echo json_encode($output);


                    die();
                }else{


                    Message::EMPTYDATA();
                    die();
                }
            }





        }


    }

    public function showPromotedProducts()
    {

        $this->loadModel("Product");
        $this->loadModel("ProductRating");
        $this->loadModel("OrderProduct");


        if ($this->request->isPost()) {



            $json = file_get_contents('php://input');
            $data = json_decode($json, TRUE);
            $user_id = $data['user_id'];
            $starting_point = 0;
            if(isset($data['starting_point'])){

                $starting_point = $data['starting_point'];
            }

            $products = $this->Product->getPromotedProducts($starting_point);



            if(count($products) > 0){

                foreach($products as $key=>$product) {

                    $ratings = $this->ProductRating->getAvgRatings($product['Product']['id']);
                    $sold_products_count = $this->OrderProduct->countSoldProducts($product['Product']['id']);


                    $products[$key]['Product']['sold'] = $sold_products_count;
                    $products[$key]['Product']['TotalRatings']["avg"] = 0;
                    $products[$key]['Product']['TotalRatings']["totalRatings"] = 0;

                    if (count($ratings) > 0) {

                        $products[$key]['Product']['TotalRatings']["avg"] = $ratings[0]['average'];
                        $products[$key]['Product']['TotalRatings']["totalRatings"] = $ratings[0]['total_ratings'];
                    }

                }

                $output['code'] = 200;

                $output['msg'] = $products;
                echo json_encode($output);


                die();
            }else{
                Message::EMPTYDATA();
                die();
            }
        }
    }


    public function showTopViewedProducts()
    {

        $this->loadModel("Product");
        $this->loadModel("ProductRating");
        $this->loadModel("OrderProduct");


        if ($this->request->isPost()) {



            $json = file_get_contents('php://input');
            $data = json_decode($json, TRUE);
            $user_id = $data['user_id'];
            $starting_point = 0;
            if(isset($data['starting_point'])){

                $starting_point = $data['starting_point'];
            }

            $products = $this->Product->getTopViewedProducts($starting_point);



            if(count($products) > 0){
                foreach($products as $key=>$product) {

                    $ratings = $this->ProductRating->getAvgRatings($product['Product']['id']);
                    $sold_products_count = $this->OrderProduct->countSoldProducts($product['Product']['id']);


                    $products[$key]['Product']['sold'] = $sold_products_count;
                    $products[$key]['Product']['TotalRatings']["avg"] = 0;
                    $products[$key]['Product']['TotalRatings']["totalRatings"] = 0;

                    if (count($ratings) > 0) {

                        $products[$key]['Product']['TotalRatings']["avg"] = $ratings[0]['average'];
                        $products[$key]['Product']['TotalRatings']["totalRatings"] = $ratings[0]['total_ratings'];
                    }

                }
                $output['code'] = 200;

                $output['msg'] = $products;
                echo json_encode($output);


                die();
            }else{
                Message::EMPTYDATA();
                die();
            }
        }
    }


    public function showProductCategories()
    {

        $this->loadModel("Category");


        if ($this->request->isPost()) {



            $json = file_get_contents('php://input');
            $data = json_decode($json, TRUE);
            $user_id = $data['user_id'];
            $starting_point = 0;
            if(isset($data['starting_point'])){

                $starting_point = $data['starting_point'];
            }

            $store_products = $this->Category->getAllMostOccurance($starting_point);



            if(count($store_products) > 0){



                $output['code'] = 200;

                $output['msg'] = $store_products;
                echo json_encode($output);


                die();
            }else{
                Message::EMPTYDATA();
                die();
            }
        }
    }


    public function showOnlyProductCategories()
    {

        $this->loadModel("Category");


        if ($this->request->isPost()) {



            $json = file_get_contents('php://input');
            $data = json_decode($json, TRUE);
            $parent = $data['parent_id'];



            $details = $this->Category->getAll($parent);



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


    public function showCategories()
    {

        $this->loadModel("Category");


        if ($this->request->isPost()) {



            $json = file_get_contents('php://input');
            $data = json_decode($json, TRUE);
            $parent = $data['parent_id'];



            $details = $this->Category->getAll($parent);



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
    public function showProducts()
    {

        $this->loadModel("Product");


        if ($this->request->isPost()) {



            $json = file_get_contents('php://input');
            $data = json_decode($json, TRUE);
            $user_id = $data['user_id'];
            $starting_point = 0;
            if(isset($data['starting_point'])){

                $starting_point = $data['starting_point'];
            }

            $store_products = $this->Product->getProductsAgainstUser($user_id,$starting_point);



            if(count($store_products) > 0){



                $output['code'] = 200;

                $output['msg'] = $store_products;
                echo json_encode($output);


                die();
            }else{
                Message::EMPTYDATA();
                die();
            }
        }
    }


    public function showProductsAgainstCategories()
    {

        $this->loadModel("Product");


        if ($this->request->isPost()) {



            $json = file_get_contents('php://input');
            $data = json_decode($json, TRUE);
            $user_id = $data['user_id'];
            $category_id = $data['category_id'];
            $starting_point = 0;
            if(isset($data['starting_point'])){

                $starting_point = $data['starting_point'];
            }

            $store_products = $this->Product->getProductsAgainstCategory($category_id,$starting_point);



            if(count($store_products) > 0){



                $output['code'] = 200;

                $output['msg'] = $store_products;
                echo json_encode($output);


                die();
            }else{
                Message::EMPTYDATA();
                die();
            }
        }
    }

    public function showProductsAttributes()
    {

        $this->loadModel("ProductAttribute");


        if ($this->request->isPost()) {



            $json = file_get_contents('php://input');
            $data = json_decode($json, TRUE);
            $id = $data['id'];


            $details = $this->ProductAttribute->getDetails($id);



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

    public function showProductsAttributeVariation()
    {

        $this->loadModel("ProductAttributeVariation");


        if ($this->request->isPost()) {



            $json = file_get_contents('php://input');
            $data = json_decode($json, TRUE);
            $id = $data['id'];


            $details = $this->ProductAttributeVariation->getDetails($id);



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


    public function addProductRating()
    {

        $this->loadModel("ProductRating");
        $this->loadModel("Product");
        $this->loadModel("User");

        if ($this->request->isPost()) {
            $json          = file_get_contents('php://input');
            $data          = json_decode($json, TRUE);
            $user_id       = $data['user_id'];
            $product_id = $data['product_id'];
            $star          = $data['star'];
            $comment       = $data['comment'];
            $order_id       = $data['order_id'];
            $created       = date('Y-m-d H:i:s', time());


            $rating['user_id']       = $user_id;
            $rating['product_id'] = $product_id;
            $rating['star']          = $star;
            $rating['comment']       = $comment;
            $rating['order_id']       = $order_id;

            $rating['created'] = $created;

            $if_rating_exist = $this->ProductRating->ifExist($user_id,$product_id,$order_id);
            if(count($if_rating_exist) > 0){

                $rating_id = $if_rating_exist['ProductRating']['id'];
                $this->ProductRating->id = $if_rating_exist['ProductRating']['id'];
                $this->ProductRating->save($rating);

            }else{


                $this->ProductRating->save($rating);
                $rating_id = $this->ProductRating->getInsertID();


            }

                $product_details = $this->Product->getDetailsWithUser($product_id);

                $rating_details = $this->ProductRating->getDetails($rating_id);


                $notification['to'] = $product_details['User']['device_token'];
                $notification['notification']['title'] = "A new rating has been posted";
                $notification['notification']['body'] = $data['comment'];
                $notification['notification']['badge'] = "1";
                $notification['notification']['sound'] = "default";
                $notification['notification']['icon'] = "";
                $notification['notification']['type'] = "ratings";
                $notification['notification']['product_id'] = $product_id;
                $notification['notification']['order_id'] = $order_id;


            $notification['data']['title'] = "A new rating has been posted";
            $notification['data']['body'] = $data['comment'];
                $notification['data']['type']= "ratings";
                $notification['data']['icon'] = "";
                $notification['data']['badge'] = "1";
                $notification['data']['sound'] = "default";

            $notification['data']['product_id'] = $product_id;
            $notification['data']['order_id'] = $order_id;




            Utility::sendPushNotificationToMobileDevice(json_encode($notification));


                $output['code'] = 200;

                $output['msg'] = $rating_details;
                echo json_encode($output);


                die();



        }
    }

    public function verifyCoupon()
    {

        $this->loadModel("Coupon");
        $this->loadModel("CouponUsed");
        // $this->loadModel("RestaurantRating");

        if ($this->request->isPost()) {
            $json = file_get_contents('php://input');
            $data = json_decode($json, TRUE);

            $user_id       = $data['user_id'];
            $coupon_code   = $data['coupon_code'];


            $coupon_exist  = $this->Coupon->isCouponCodeExist($coupon_code);



            if(count($coupon_exist) > 0) {


                $coupon_id = $coupon_exist['Coupon']['id'];
                $user_limit = $coupon_exist['Coupon']['limit_users'];
                $count_coupon_used = $this->CouponUsed->countCouponUsed($coupon_id);



                $coupon_user_used = $this->CouponUsed->ifCouponCodeUsedByUser($coupon_id, $user_id);


                if (count($coupon_exist) > 0 && $coupon_user_used == 1) {

                    $output['code'] = 201;


                    $output['msg'] = "invalid coupon code";

                    echo json_encode($output);

                    die();

                } else if (count($coupon_exist)> 0 && $coupon_user_used == 0 && $count_coupon_used < $user_limit) {

                    $coupon = $this->Coupon->getDetails($coupon_id);


                    $output['code'] = 200;


                    $output['msg'] = $coupon;

                    echo json_encode($output);

                    die();


                }else{



                    $output['code'] = 201;


                    $output['msg'] = "invalid coupon code";

                    echo json_encode($output);

                    die();
                }


            }else{


                $output['code'] = 201;


                $output['msg'] = "invalid coupon code";

                echo json_encode($output);

                die();

            }








        }
    }

    public function showProductDetail()
    {

        $this->loadModel("Product");
        $this->loadModel("ProductFavourite");
        $this->loadModel("ProductRating");
        $this->loadModel("OrderProduct");
        $this->loadModel("DeliveryAddress");


        if ($this->request->isPost()) {

            $json = file_get_contents('php://input');
            $data = json_decode($json, TRUE);

            $product_id = $data['product_id'];
            $user_id = $data['user_id'];


            $product_details = $this->Product->getDetailsWithAttributes($product_id);





            if(count($product_details) > 0) {



                    $delivery_address = $this->DeliveryAddress->getUserDefaultDeliveryAddresses($data['user_id']);
                    $favourite = $this->ProductFavourite->ifProductFavourite($data['user_id'], $product_details['Product']['id']);
                    $favourite_count = $this->ProductFavourite->productFavouriteCount($product_details['Product']['id']);
                    if (count($favourite) > 0) {

                        $product_details['ProductFavourite']['favourite'] = "1";

                    } else {

                        $product_details['ProductFavourite']['favourite'] = "0";

                    }
                $product_details['ProductFavourite']['favourite_count'] = $favourite_count;


                if(count($delivery_address) > 0){
                    $product_details['Product']['Shipping'] = $delivery_address;
                }

                $ratings = $this->ProductRating->getAvgRatings($product_id);
                $sold_products_count = $this->OrderProduct->countSoldProducts($product_id);


                $product_details['Product']['sold'] = $sold_products_count;
                $product_details['Product']['TotalRatings']["avg"] = 0;
                $product_details['Product']['TotalRatings']["totalRatings"] = 0;

                if (count($ratings) > 0) {

                    $product_details['Product']['TotalRatings']["avg"]  = $ratings[0]['average'];
                    $product_details['Product']['TotalRatings']["totalRatings"] = $ratings[0]['total_ratings'];
                }

                $this->Product->id = $product_id;
                $this->Product->saveField('view',$product_details['Product']['view'] + 1);


                $output['code'] = 200;

                $output['msg'] = $product_details;


                echo json_encode($output);


                die();

            }else{

                Message::EMPTYDATA();
                die();
            }
        }
    }

    public function editVideoSettings()
    {

        $this->loadModel("Video");




        if ($this->request->isPost()) {
            $json = file_get_contents('php://input');
            $data = json_decode($json, TRUE);



            $video_id = $data['video_id'];

            if(isset($data['privacy_type'])){


                $post_data['privacy_type'] = $data['privacy_type'];
            }

            if(isset($data['allow_comments'])){


                $post_data['allow_comments'] = $data['allow_comments'];
            }

            if(isset($data['allow_duet'])){


                $post_data['allow_duet'] = $data['allow_duet'];
            }





            $video_details = $this->Video->getOnlyVideoDetails($video_id);

            if(count($video_details) < 1 ) {


                Message::EMPTYDATA();
                die();


            }

            $this->Video->id = $video_id;
            $this->Video->save($post_data);





            $video_details = $this->Video->getOnlyVideoDetails($video_id);

            $output['code'] = 200;
            $output['msg'] = $video_details;
            echo json_encode($output);


            die();








        }
    }
    public function addUserLanguage()
    {

        $this->loadModel("Language");




        if ($this->request->isPost()) {
            $json = file_get_contents('php://input');
            $data = json_decode($json, TRUE);


            $user_id = $data['user_id'];
            $language = $data['language'];













            if(count($language) > 0){
                foreach ($language as $key=>$lang){



                    $language_data[$key]['lang_id'] = $lang['lang_id'];

                    $language_data[$key]['user_id'] = $user_id;
                }

                $this->Language->saveAll($language_data);


            }
            $userDetails = $this->User->getUserDetailsFromID($user_id);

            $output['code'] = 200;
            $output['msg'] = $userDetails;
            echo json_encode($output);


            die();








        }
    }

    public function addUserInterest()
    {

        $this->loadModel("UserInterest");




        if ($this->request->isPost()) {
            $json = file_get_contents('php://input');
            $data = json_decode($json, TRUE);


            $user_id = $data['user_id'];
            $interests = $data['interests'];










            $this->UserInterest->deleteAllInterests($user_id);

            $interest_data = array();
            if(count($interests) > 0){
                foreach ($interests as $key=>$interest){



                    $if_exist = $this->UserInterest->getUserInterests($user_id,$interest['interest_id']);
                    if(count($if_exist) < 1){
                        $interest_data[$key]['interest_id'] = $interest['interest_id'];

                        $interest_data[$key]['user_id'] = $user_id;
                    }
                }

                if(count($interest_data) > 0){
                    $this->UserInterest->saveAll($interest_data);
                }

            }
            $userDetails = $this->User->getUserDetailsFromID($user_id);

            $output['code'] = 200;
            $output['msg'] = $userDetails;
            echo json_encode($output);


            die();








        }
    }

    public function addUserLanguagePreferences()
    {

        $this->loadModel("LanguagePreference");




        if ($this->request->isPost()) {
            $json = file_get_contents('php://input');
            $data = json_decode($json, TRUE);


            $user_id = $data['user_id'];
            $language = $data['language'];










            $this->LanguagePreference->deleteAllPreference($user_id);


            if(count($language) > 0){


                foreach ($language as $key=>$lang){



                    //$if_exist = $this->LanguagePreference->getUserLanguagePreferences($user_id,$lang['lang_id']);


                    $lang_data[$key]['lang_id'] = $lang['lang_id'];

                    $lang_data[$key]['user_id'] = $user_id;

                }


                if(count($lang_data) > 0){

                    $this->LanguagePreference->saveAll($lang_data);
                }


            }
            $userDetails = $this->User->getUserDetailsFromID($user_id);

            $output['code'] = 200;
            $output['msg'] = $userDetails;
            echo json_encode($output);


            die();








        }
    }
    public function showLanguages(){

        $this->loadModel('Language');
        $this->loadModel('LanguagePreference');




        if ($this->request->isPost()) {

            $json = file_get_contents('php://input');
            $data = json_decode($json, TRUE);

            $user_id = $data['user_id'];


            $details = $this->Language->getAll();




            if(count($details) > 0) {

                foreach($details as $key=>$detail){
                    $details[$key]['Language']['selected'] = 0;
                    if(isset($data['user_id'])){

                        $if_exist = $this->LanguagePreference->ifLanguageIsSelected($data['user_id'],$detail['Language']['id']);

                        if(count($if_exist) > 0){

                            $details[$key]['Language']['selected'] = 1;

                        }

                    }
                }


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

    public function showInterests(){

        $this->loadModel('Interest');
        $this->loadModel('UserInterest');




        if ($this->request->isPost()) {

            $json = file_get_contents('php://input');
            $data = json_decode($json, TRUE);

            $lang_id = $data['lang_id'];


            $details = $this->Interest->getInterestsAgainstLanguage($lang_id);


            if(count($details) > 0) {


                foreach($details as $key=>$detail){
                    $details[$key]['Interest']['selected'] = 0;
                    if(isset($data['user_id'])){

                        $if_exist = $this->UserInterest->getUserInterests($data['user_id'],$detail['Interest']['id']);

                        if(count($if_exist) > 0){

                            $details[$key]['Interest']['selected'] = 1;

                        }

                    }
                }

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


    public function watchLiveStream()
    {



        $this->loadModel('LiveStreamingWatch');
        $this->loadModel('LiveStreaming');
        $this->loadModel('User');


        if ($this->request->isPost()) {


            $json = file_get_contents('php://input');
            $data = json_decode($json, TRUE);



            $stream_data['user_id'] = $data['user_id'];
            $stream_data['live_streaming_id'] = $data['live_streaming_id'];
            $coin = $data['coin'];
            $stream_data['coin'] = $coin;


            $sender_details = $this->User->getUserDetailsFromID($data['user_id']);
            $receiver_details = $this->LiveStreaming->getDetails($data['live_streaming_id']);

            $watch_details = $this->LiveStreamingWatch->checkDuplicate($data['user_id'],$data['live_streaming_id']);

            if(count($watch_details) > 0){

                $block =  $watch_details['LiveStreamingWatch']['block'];

                if($block > 0){

                    $output['code'] = 201;
                    $output['msg'] = "You have been blocked from watching this live stream";
                    echo json_encode($output);
                    die();
                }

            }


            if(count($sender_details) > 0){

                $wallet_total =  $this->walletTotal($data['user_id']);

                $wallet_total = $wallet_total['total'];

                if($wallet_total < $coin){


                    $output['code'] = 201;
                    $output['msg'] = "You do not have enough coins to send gift";
                    echo json_encode($output);


                    die();
                }

            }





            $this->LiveStreaming->id =  $data['live_streaming_id'];
            $this->LiveStreaming->saveField('earn_coin',$receiver_details['LiveStreaming']['earn_coin'] + $coin);
            $this->LiveStreaming->clear();



            $this->LiveStreamingWatch->save($stream_data);
            $id = $this->LiveStreamingWatch->getInsertID();

            $this->LiveStreamingWatch->id = $id;
            $this->LiveStreamingWatch->saveField('coin',$coin);
           // $details = $this->LiveStreamingWatch->getDetails($id);
            $wallet_total =  $this->walletTotal($data['user_id']);

            $wallet_total = $wallet_total['total'];
            $sender_details['User']['wallet'] = $wallet_total;

            $output['code'] = 200;
            $output['msg'] = $sender_details;
            echo json_encode($output);
            die();




        }
    }

    public function watchVideo()
    {

        $this->loadModel("VideoWatch");
        $this->loadModel("Promotion");
        $this->loadModel("Video");



        if ($this->request->isPost()) {
            $json = file_get_contents('php://input');
            $data = json_decode($json, TRUE);


            $video_id = $data['video_id'];
            $device_id = $data['device_id'];

            $video_details = $this->Video->getOnlyVideoDetails($video_id);

            if(count($video_details) > 0) {


                $created = date('Y-m-d H:i:s', time());


                if (isset($data['user_id'])) {


                    $watch['user_id'] = $data['user_id'];
                }
                $watch['video_id'] = $video_id;
                $watch['device_id'] = $device_id;
                $watch['created'] = $created;

                $promotion_details = $this->Promotion->getActivePromotionAgainstVideoID($video_id, $created);


                if (count($promotion_details) > 0) {

                    $watch['promotion_id'] = $promotion_details['Promotion']['id'];
                }
                //  $watch_details = $this->VideoWatch->ifExist($watch);


                //if (count($watch_details) < 1) {

                $this->VideoWatch->save($watch);
                $id = $this->VideoWatch->getInsertID();
                $this->Video->id = $video_id;
                $views = $this->Video->field('view');
                $this->Video->id = $video_id;
                $this->Video->savefield('view', $views + 1);



                $details = $this->VideoWatch->getDetails($id);


                $output['code'] = 200;
                $output['msg'] = $details;
                echo json_encode($output);


                die();


            }else{

                Message::EMPTYDATA();
                die();

            }
            /*   }else{

                   $output['code'] = 201;
                   $output['msg'] = "duplicate";
                   echo json_encode($output);


                   die();
               }*/


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
            $coin_data['price'] = $data['price'];
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

    public function showCoinWorth()
    {






        if ($this->request->isPost()) {
            $json = file_get_contents('php://input');
            $data = json_decode($json, TRUE);



            if (in_array("CoinWorth", App::objects('Model'))) {
                $this->loadModel("CoinWorth");


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

                    if(isset($data['type'])){

                        $gifts = $this->Gift->getAllAgainstType($data['type']);
                    }else {

                        $gifts = $this->Gift->getAll();

                    }
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


    public function showCartSlider()
    {

        $this->loadModel("CartSlider");


        if ($this->request->isPost()) {

            $json = file_get_contents('php://input');
            $data = json_decode($json, TRUE);



            $images = $this->CartSlider->getAll();


            $output['code'] = 200;

            $output['msg'] = $images;
            echo json_encode($output);


            die();
        }
    }

    public function showShopSlider()
    {

        $this->loadModel("ShopSlider");


        if ($this->request->isPost()) {

            $json = file_get_contents('php://input');
            $data = json_decode($json, TRUE);



            $images = $this->ShopSlider->getAll();


            $output['code'] = 200;

            $output['msg'] = $images;
            echo json_encode($output);


            die();
        }
    }

    public function showDiscoverySections()
    {

        $this->loadModel("HashtagVideo");
        $this->loadModel("Follower");
        $this->loadModel("VideoLike");
        $this->loadModel("VideoFavourite");
        $this->loadModel("VideoComment");
        $this->loadModel("RepostVideo");


        if ($this->request->isPost()) {

            $json = file_get_contents('php://input');
            $data = json_decode($json, TRUE);


            $starting_point = 0;


            if (isset($data['starting_point'])) {

                $starting_point = $data['starting_point'];

            }
            $hashtags = $this->HashtagVideo->getHastagsWhichHasGreaterNoOfVideos($starting_point);



            $new_array = array();

            if(count($hashtags) > 0) {

                $new_array = array();
                $key1=0;
                foreach ($hashtags as $key => $hashtag) {

                    $hashtag_videos = $this->HashtagVideo->getHashtagVideosLimit($hashtag['Hashtag']['id']);
                    $hashtag_videos_count = $this->HashtagVideo->countHashtagVideos($hashtag['Hashtag']['id']);


                    if(count($hashtag_videos) > 0) {



                        $i = 0;
                        foreach($hashtag_videos as $v) {
                            $hashtag_videos[$i]['Video']['like'] = 0;
                            $hashtag_videos[$i]['Video']['favourite'] = 0;
                            if (isset($data['user_id'])) {
                                $user_id = $data['user_id'];

                                $video_user_id = $v['Video']['user_id'];
                                $video_id = $v['Video']['id'];

                                if ($user_id != $video_user_id) {

                                    $follower_details = $this->Follower->ifFollowing($user_id, $video_user_id);

                                    if (count($follower_details) > 0) {

                                        $hashtag_videos[$i]['Video']['User']['button'] = "unfollow";

                                    } else {


                                        $hashtag_videos[$i]['Video']['User']['button'] = "follow";
                                    }



                                    $video_data['user_id'] = $user_id;
                                    $video_data['video_id'] = $video_id;
                                    $video_like_detail = $this->VideoLike->ifExist($video_data);
                                    $video_favourite_detail = $this->VideoFavourite->ifExist($video_data);
                                    $repost_detail = $this->RepostVideo->ifExist($video_data);

                                    if (count($repost_detail) > 0) {

                                        $hashtag_videos[$i]['Video']['repost'] = 1;

                                    } else {

                                        $hashtag_videos[$i]['Video']['repost'] = 0;
                                    }
                                    if (count($video_like_detail) > 0) {

                                        $hashtag_videos[$i]['Video']['like'] = 1;

                                    }

                                    if (count($video_favourite_detail) > 0) {

                                        $hashtag_videos[$i]['Video']['favourite'] = 1;

                                    }



                                }

                                $video_like_count = $this->VideoLike->countLikes($video_id);
                                $video_comment_count = $this->VideoComment->countComments($video_id);
                                $hashtag_videos[$i]['Video']['like_count'] = $video_like_count;
                                $hashtag_videos[$i]['Video']['comment_count'] = $video_comment_count;
                                $video_fav_count = $this->VideoFavourite->getFavVideosCount($video_id);
                                $hashtag_videos[$i]['Video']['favourite_count'] = $video_fav_count;





                                $i++;
                            }

                        }
                        $new_array[$key1]["Hashtag"] = $hashtag['Hashtag'];
                        $new_array[$key1]["Hashtag"]['views'] = $hashtag[0]['total_views'];

                        $new_array[$key1]["Hashtag"]['Videos'] = $hashtag_videos;
                        $new_array[$key1]["Hashtag"]['videos_count'] = $hashtag_videos_count;
                        $key1++;
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





    public function showOrderSession()
    {

        $this->loadModel("OrderSession");
        $this->loadModel("User");
        //$this->loadModel("PaymentCard");
        //$this->loadModel("StripeCustomer");


        if ($this->request->isPost()) {
            $json = file_get_contents('php://input');
            $data = json_decode($json, TRUE);

            $id = $data['id'];

            $details = $this->OrderSession->getDetails($id);


            $output['code'] = 200;

            $output['msg'] = $details;

            echo json_encode($output);


            die();
        }else{

            Message::EmptyDATA();
            die();



        }
    }

    public function postCommentOnVideo()
    {

        $this->loadModel("VideoComment");
        $this->loadModel("User");
        $this->loadModel("Video");
        $this->loadModel("Notification");
        $this->loadModel("PushNotification");



        if ($this->request->isPost()) {
            $json = file_get_contents('php://input');
            $data = json_decode($json, TRUE);


            $video_id = $data['video_id'];
            $user_id = $data['user_id'];
            $comment = $data['comment'];


            $created = date('Y-m-d H:i:s', time());


            $comment_video['video_id'] = $video_id;
            $comment_video['user_id'] = $user_id;
            $comment_video['comment'] = $comment;
            $comment_video['created'] = $created;



            $video_details = $this->Video->getDetails($video_id);
            $userDetails = $this->User->getUserDetailsFromID($user_id);

            if(count($userDetails) > 0) {




                $this->VideoComment->save($comment_video);


                $id = $this->VideoComment->getInsertID();
                $details = $this->VideoComment->getDetails($id);


                if($user_id == $video_details['Video']['user_id']){


                }else {

                    $notification_msg = $userDetails['User']['username'] . ' commented: ' . $comment;
                    $notification['to'] = $video_details['User']['device_token'];
                    $notification['notification']['title'] = $notification_msg;
                    $notification['notification']['body'] = "";
                    $notification['notification']['badge'] = "1";
                    $notification['notification']['sound'] = "default";
                    $notification['notification']['icon'] = "";
                    $notification['notification']['type'] = "comment";
                    $notification['notification']['video_id'] = $video_id;
                    $notification['data']['title'] = $notification_msg;
                    $notification['data']['body'] = '';
                    $notification['data']['icon'] = "";
                    $notification['data']['badge'] = "1";
                    $notification['data']['sound'] = "default";
                    $notification['data']['type'] = "comment";
                    $notification['data']['video_id'] = $video_id;
                    $notification['notification']['receiver_id'] = $video_details['User']['id'];
                    $notification['data']['receiver_id'] = $video_details['User']['id'];

                    $if_exist = $this->PushNotification->getDetails($video_details['User']['id']);
                    if (count($if_exist) > 0) {

                        $likes = $if_exist['PushNotification']['new_followers'];
                        if ($likes > 0) {
                            Utility::sendPushNotificationToMobileDevice(json_encode($notification));
                        }
                    }
                }


                if(isset($data['users_json'])) {
                    $data_users = $data['users_json'];

                    if (count($data_users) > 0) {

                        foreach ($data_users as $key => $value) {

                            $other_user_id = $value['user_id'];

                            if($user_id == $other_user_id){

                            }else {
                                $tagged_userDetails = $this->User->getUserDetailsFromID($other_user_id);
                                $msg = $userDetails['User']['username'] . ' mentioned you in a comment: ' . $comment;;

                                if (strlen($tagged_userDetails['User']['device_token']) > 8) {
                                    $notification['to'] = $tagged_userDetails['User']['device_token'];

                                    $notification['notification']['title'] = $msg;
                                    $notification['notification']['body'] = "";
                                    $notification['notification']['badge'] = "1";
                                    $notification['notification']['sound'] = "default";
                                    $notification['notification']['icon'] = "";
                                    $notification['notification']['type'] = "comment_tag";
                                    $notification['notification']['video_id'] = $video_id;
                                    $notification['data']['title'] = $msg;
                                    $notification['data']['body'] = '';
                                    $notification['data']['icon'] = "";
                                    $notification['data']['badge'] = "1";
                                    $notification['data']['sound'] = "default";
                                    $notification['data']['type'] = "comment_tag";
                                    $notification['data']['video_id'] = $video_id;
                                    $notification['notification']['receiver_id'] = $tagged_userDetails['User']['id'];
                                    $notification['data']['receiver_id'] = $tagged_userDetails['User']['id'];
                                    Utility::sendPushNotificationToMobileDevice(json_encode($notification));

                                    /* $if_exist = $this->PushNotification->getDetails($tagged_userDetails['User']['id']);

                                     if (count($if_exist) > 0) {

                                         $video_updates = $if_exist['PushNotification']['video_updates'];
                                         if ($video_updates > 0) {
                                             Utility::sendPushNotificationToMobileDevice(json_encode($notification));
                                         }
                                     }*/


                                    $notification_data['sender_id'] = $user_id;
                                    $notification_data['receiver_id'] = $other_user_id;
                                    $notification_data['type'] = "comment_tag";
                                    $notification_data['video_id'] = $video_id;

                                    $notification_data['string'] = $msg;
                                    $notification_data['created'] = $created;

                                    $this->Notification->save($notification_data);
                                    $this->Notification->clear();

                                }
                            }

                        }
                    }
                }

                if($user_id == $video_details['Video']['user_id']){

                }else {
                    $notification_data['sender_id'] = $user_id;
                    $notification_data['receiver_id'] = $video_details['User']['id'];
                    $notification_data['type'] = "video_comment";
                    $notification_data['video_id'] = $video_id;

                    $notification_data['string'] = $notification_msg;
                    $notification_data['created'] = $created;

                    $this->Notification->save($notification_data);
                    $this->Notification->clear();
                }
                $output['code'] = 200;
                $output['msg'] = $details;
                echo json_encode($output);


                die();
            }else{

                $output['code'] = 201;
                $output['msg'] = "Login First";
                echo json_encode($output);


                die();

            }


        }
    }



    public function postCommentReply()
    {

        $this->loadModel("VideoComment");
        $this->loadModel("VideoCommentReply");
        $this->loadModel("Video");
        $this->loadModel("User");
        $this->loadModel("Notification");
        $this->loadModel("PushNotification");



        if ($this->request->isPost()) {
            $json = file_get_contents('php://input');
            $data = json_decode($json, TRUE);


            $comment_id = $data['comment_id'];
            $user_id = $data['user_id'];
            $comment = $data['comment'];


            $created = date('Y-m-d H:i:s', time());


            $comment_video['comment_id'] = $comment_id;
            $comment_video['user_id'] = $user_id;
            $comment_video['comment'] = $comment;
            $comment_video['created'] = $created;




            $userDetails = $this->User->getUserDetailsFromID($user_id);
            $comment_details = $this->VideoComment->getDetails($comment_id);


            $comment_video['video_id'] = $comment_details['VideoComment']['video_id'];


            $this->VideoCommentReply->save($comment_video);



            $id = $this->VideoCommentReply->getInsertID();
            $details = $this->VideoCommentReply->getDetails($id);



            if($user_id == $comment_details['User']['id']){


            }else {
                $notification_msg = $userDetails['User']['username'] . ' replied to your comment: ' . $comment;
                $notification['to'] = $comment_details['User']['device_token'];
                $notification['notification']['title'] = $notification_msg;
                $notification['notification']['body'] = "";
                $notification['notification']['badge'] = "1";
                $notification['notification']['sound'] = "default";
                $notification['notification']['icon'] = "";
                $notification['notification']['type'] = "comment";
                $notification['data']['title'] = $notification_msg;
                $notification['data']['body'] = '';
                $notification['data']['icon'] = "";
                $notification['data']['badge'] = "1";
                $notification['data']['sound'] = "default";
                $notification['data']['type'] = "comment";


                $notification_data['sender_id'] = $user_id;
                $notification_data['receiver_id'] = $comment_details['User']['id'];
                $notification_data['type'] = "video_comment";
                $notification_data['video_id'] = 0;

                $notification_data['string'] = $notification_msg;
                $notification_data['created'] = $created;

                $this->Notification->save($notification_data);
                $this->Notification->clear();
            }
            if(isset($data['users_json'])) {
                $data_users = $data['users_json'];

                if (count($data_users) > 0) {

                    foreach ($data_users as $key => $value) {

                        $other_user_id = $value['user_id'];

                        $tagged_userDetails = $this->User->getUserDetailsFromID($other_user_id);
                        $msg  = $userDetails['User']['username'] . ' mentioned you in a comment: ' . $comment;;
                        if($user_id == $other_user_id){


                        }else {

                            if (strlen($tagged_userDetails['User']['device_token']) > 8) {
                                $notification['to'] = $tagged_userDetails['User']['device_token'];

                                $notification['notification']['title'] = $msg;
                                $notification['notification']['body'] = "";
                                $notification['notification']['badge'] = "1";
                                $notification['notification']['sound'] = "default";
                                $notification['notification']['icon'] = "";
                                $notification['notification']['type'] = "comment_tag";
                                $notification['notification']['video_id'] = $comment_details['Video']['id'];

                                $notification['data']['title'] = $msg;
                                $notification['data']['body'] = '';
                                $notification['data']['icon'] = "";
                                $notification['data']['badge'] = "1";
                                $notification['data']['sound'] = "default";
                                $notification['data']['type'] = "comment_tag";
                                $notification['data']['video_id'] = $comment_details['Video']['id'];

                                $notification['notification']['receiver_id'] = $tagged_userDetails['User']['id'];
                                $notification['data']['receiver_id'] = $tagged_userDetails['User']['id'];

                                Utility::sendPushNotificationToMobileDevice(json_encode($notification));
                                /* $if_exist = $this->PushNotification->getDetails($tagged_userDetails['User']['id']);

                                 if (count($if_exist) > 0) {

                                     $video_updates = $if_exist['PushNotification']['video_updates'];
                                     if ($video_updates > 0) {
                                         Utility::sendPushNotificationToMobileDevice(json_encode($notification));
                                     }
                                 }*/


                                $notification_data['sender_id'] = $user_id;
                                $notification_data['receiver_id'] = $other_user_id;
                                $notification_data['type'] = "comment_tag";
                                //$notification_data['comment_id'] = $comment_id;
                                $notification_data['video_id'] = $comment_details['Video']['id'];

                                $notification_data['string'] = $msg;
                                $notification_data['created'] = $created;

                                $this->Notification->save($notification_data);
                                $this->Notification->clear();
                            }
                        }

                    }
                }
            }
            $output['code'] = 200;
            $output['msg'] = $details;
            echo json_encode($output);


            die();



        }
    }

    public function downloadVideo()
    {

        $this->loadModel("Video");



        if ($this->request->isPost()) {
            $json = file_get_contents('php://input');
            $data = json_decode($json, TRUE);


            $video_id = $data['video_id'];


            $video_details = $this->Video->getDetails($video_id);
            if (array_key_exists("video_with_watermark",$video_details['Video'])){

                if(strlen($video_details['Video']['video_with_watermark']) > 4){


                    $output['code'] = 200;
                    $output['msg'] = $video_details['Video']['video_with_watermark'];
                    echo json_encode($output,JSON_UNESCAPED_SLASHES);


                    die();
                }


            }
            if(count($video_details) > 0) {

                if (method_exists('Premium', 'addWaterMarkAndText')) {
                    $download_video = Premium::addWaterMarkAndText($video_details['Video']['video'], 1, $video_details['User']['username'],$video_details['Video']['duration']);

                    if(strlen(IAM_KEY) > 5 ) {




                        $result = Premium::fileUploadToS3($download_video,"mp4");
                        if($result['code'] == 200) {
                            $video_url = Utility::getCloudFrontUrl($result['msg'], "/video");
                        }else{

                            $video_url = $result['msg'];
                        }

                    }else{

                        $video_url =      $download_video;

                    }

                    $this->Video->id = $video_id;
                    $this->Video->saveField('video_with_watermark',$video_url);
                    $this->Video->clear();
                    if ($download_video) {


                        $output['code'] = 200;
                        $output['msg'] = $video_url;
                        echo json_encode($output,JSON_UNESCAPED_SLASHES);




                        die();
                    } else {


                        $output['code'] = 200;
                        $output['msg'] = $video_details['Video']['video'];
                        echo json_encode($output,JSON_UNESCAPED_SLASHES);


                        die();
                    }


                }else{


                    $output['code'] = 200;
                    $output['msg'] = $video_details['Video']['video'];
                    echo json_encode($output,JSON_UNESCAPED_SLASHES);


                    die();

                }
            }else{

                Message::EMPTYDATA();
                die();


            }




        }
    }
    public function downloadVideoold()
    {

        $this->loadModel("Video");



        if ($this->request->isPost()) {
            $json = file_get_contents('php://input');
            $data = json_decode($json, TRUE);


            $video_id = $data['video_id'];


            $video_details = $this->Video->getDetails($video_id);
            if (array_key_exists("video_with_watermark",$video_details['Video'])){

                if(strlen($video_details['Video']['video_with_watermark']) > 4){


                    $output['code'] = 200;
                    $output['msg'] = $video_details['Video']['video_with_watermark'];
                    echo json_encode($output,JSON_UNESCAPED_SLASHES);


                    die();
                }


            }
            if(count($video_details) > 0) {
                if(MEDIA_STORAGE == "s3") {

                    if (method_exists('Premium', 'addWaterMarkAndText')) {
                        $download_video = Premium::addWaterMarkAndText($video_details['Video']['video'], 1, $video_details['User']['username'],$video_details['Video']['duration']);

                        $result = Premium::fileUploadToS3($download_video,"mp4");
                        if($result['code'] == 200) {
                            $video_url = Utility::getCloudFrontUrl($result['msg'], "/video");
                        }else{

                            $video_url = $result['msg'];
                        }

                        $this->Video->id = $video_id;
                        $this->Video->saveField('video_with_watermark',$video_url);
                        $this->Video->clear();
                        if ($download_video) {


                            $output['code'] = 200;
                            $output['msg'] = $video_url;
                            echo json_encode($output,JSON_UNESCAPED_SLASHES);




                            die();
                        } else {


                            $output['code'] = 200;
                            $output['msg'] = $video_details['Video']['video'];
                            echo json_encode($output,JSON_UNESCAPED_SLASHES);


                            die();
                        }


                    }else{

                        $output['code'] = 201;

                        $output['msg'] = "It seems like you do not have premium files. submit ticket on yeahhelp.com for support";


                        echo json_encode($output,JSON_UNESCAPED_SLASHES);


                        die();
                    }
                }else{

                    $output['code'] = 200;
                    $output['msg'] = $video_details['Video']['video'];
                    echo json_encode($output,JSON_UNESCAPED_SLASHES);


                    die();

                }


            }





        }
    }


    public function deleteWaterMarkVideo(){

        if ($this->request->isPost()) {
            $json = file_get_contents('php://input');
            $data = json_decode($json, TRUE);


            $url = $data['video_url'];

            if (strlen($url) > 5) {
                @unlink($url);

                $output['code'] = 200;
                $output['msg'] = "deleted";
                echo json_encode($output);


                die();
            }

        }else{
            $output['code'] = 201;
            $output['msg'] = "invalid url";
            echo json_encode($output);


            die();



        }
    }
    public function showHtmlPage()
    {
        $this->loadModel("HtmlPage");
        $this->autoRender = true;
        $params = $this->params['url'];
        $page_name = $params['page'];
        $page_exist = $this->HtmlPage->ifExist($page_name);
        if(count($page_exist) > 0){

            $this->set("data",$page_exist);
        }
    }

    public function likeVideo()
    {

        $this->loadModel("VideoLike");
        $this->loadModel("PushNotification");
        $this->loadModel("Notification");
        $this->loadModel("User");

        $this->loadModel("VideoLike");



        if ($this->request->isPost()) {
            $json = file_get_contents('php://input');
            $data = json_decode($json, TRUE);


            $video_id = $data['video_id'];
            $user_id = $data['user_id'];



            $created = date('Y-m-d H:i:s', time());


            $like_video['video_id'] = $video_id;
            $like_video['user_id'] = $user_id;
            $like_video['created'] = $created;

            //$video_details = $this->Video->getDetails($video_id);



            $details = $this->VideoLike->ifExist($like_video);
            $userDetail = $this->User->getUserDetailsFromID($user_id);








            if(count($details) > 0){




                $this->VideoLike->id = $details['VideoLike']['id'];
                $this->VideoLike->delete();

                $msg = "unlike";
                $like_video = 0;
            }else{

                $this->VideoLike->save($like_video);





                $id = $this->VideoLike->getInsertID();
                $details = $this->VideoLike->getDetails($id);

                $msg = $details;
                $like_video = 1;




               if($user_id == $details['Video']['user_id']){


               }else {


                   $notification_msg = $userDetail['User']['username'] . ' liked your video';
                   $notification['to'] = $details['Video']['User']['device_token'];
                   $notification['notification']['title'] = $userDetail['User']['username'];
                   $notification['notification']['image'] = BASE_URL . $userDetail['User']['profile_pic'];
                   $notification['notification']['body'] = $notification_msg;
                   $notification['notification']['badge'] = "1";
                   $notification['notification']['sound'] = "default";
                   $notification['notification']['icon'] = "";
                   $notification['notification']['type'] = "video_like";
                   $notification['notification']['video_id'] = $video_id;
                   $notification['notification']['receiver_id'] = $details['Video']['user_id'];
                   $notification['data']['title'] = $userDetail['User']['username'];
                   $notification['data']['body'] = $notification_msg;
                   $notification['data']['icon'] = BASE_URL . $userDetail['User']['profile_pic'];
                   $notification['data']['badge'] = "1";
                   $notification['data']['sound'] = "default";
                   $notification['data']['type'] = "video_like";
                   $notification['data']['video_id'] = $video_id;
                   $notification['data']['receiver_id'] = $details['Video']['user_id'];

                   $if_exist = $this->PushNotification->getDetails($userDetail['User']['id']);
                   if (count($if_exist) > 0) {

                       $likes = $if_exist['PushNotification']['likes'];
                       if ($likes > 0) {
                           Utility::sendPushNotificationToMobileDevice(json_encode($notification));
                       }
                   }
                   $notification_data['video_id'] = $video_id;
                   $notification_data['sender_id'] = $user_id;
                   $notification_data['receiver_id'] = $details['Video']['user_id'];
                   $notification_data['type'] = "video_like";
                   $notification_data['string'] = $notification_msg;
                   $notification_data['created'] = $created;

                   $this->Notification->save($notification_data);

               }

            }
            $like_count = $this->VideoLike->countLikes($video_id);
            $output['code'] = 200;
            $output['msg'] = $msg;
            $output['like'] = $like_video;
            $output['like_count'] = $like_count;

            echo json_encode($output);


            die();




        }
    }

    public function addRoom()
    {


        $this->loadModel('Room');
        $this->loadModel('RoomMember');

        if ($this->request->isPost()) {


            $json = file_get_contents('php://input');
            $data = json_decode($json, TRUE);

            $user_id = $data['user_id'];
            $title = $data['title'];
            $privacy = $data['privacy'];
            $created = date('Y-m-d H:i:s', time());

            if(isset($data['topic_id'])){
                $topic_id = $data['topic_id'];
                $post_data_room['topic_id'] = $topic_id;
            }


            $post_data_room['user_id'] = $user_id;
            $post_data_room['title'] = $title;
            $post_data_room['privacy'] = $privacy;
            $post_data_room['created'] = $created;


            $this->Room->save($post_data_room);
            $id = $this->Room->getInsertID();

            $post_data_room_member['user_id'] = $user_id;
            $post_data_room_member['room_id'] = $id;
            $post_data_room_member['moderator'] = 1;
            $post_data_room_member['created'] = $created;

            $this->RoomMember->save($post_data_room_member);

            $output = array();
            $details = $this->Room->getDetails($id);



            $output['code'] = 200;
            $output['msg'] = $details;
            echo json_encode($output);
            die();


        }
    }


    public function deleteRoom(){

        $this->loadModel('Room');




        if ($this->request->isPost()) {
            $json = file_get_contents('php://input');
            $data = json_decode($json, TRUE);


            $room_id = $data['id'];

            $details = $this->Room->getDetails($room_id);


            if (count($details) > 0) {

                $this->Room->delete($room_id,true);
                // $this->Room->saveField('delete', 1);

                $output['code'] = 200;

                $output['msg'] = "deleted";


                echo json_encode($output);


                die();

            } else {

                Message::EMPTYDATA();
                die();

            }

        }

    }

    public function leaveRoom()
    {


        $this->loadModel('Room');
        $this->loadModel('RoomMember');

        if ($this->request->isPost()) {


            $json = file_get_contents('php://input');
            $data = json_decode($json, TRUE);

            $post_data['user_id'] = $data['user_id'];
            $post_data['room_id'] = $data['room_id'];
            $post_data['created'] = date('Y-m-d H:i:s', time());






            $details = $this->Room->getDetails($data['room_id']);
            $room_member_details = $this->RoomMember->ifExist($data['room_id'],$data['user_id']);
            if(count($details) > 0 && count($room_member_details) > 0) {



                $this->RoomMember->id = $room_member_details['RoomMember']['id'];
                $this->RoomMember->delete();


                $output['code'] = 200;
                $output['msg'] = "removed member";
                echo json_encode($output);
            }else{


                Message::EMPTYDATA();
                die();


            }

        }
    }

    public function assignModerator()
    {


        $this->loadModel('Room');
        $this->loadModel('RoomMember');

        if ($this->request->isPost()) {


            $json = file_get_contents('php://input');
            $data = json_decode($json, TRUE);

            $user_id = $data['user_id'];
            $room_id = $data['room_id'];
            $moderator = $data['moderator'];






            $details = $this->Room->getDetails($room_id);
            $room_member_details = $this->RoomMember->ifExist($room_id,$user_id);
            if(count($details) > 0 && count($room_member_details) > 0) {



                $this->RoomMember->id = $room_member_details['RoomMember']['id'];
                $this->RoomMember->saveField('moderator',$moderator);

                $room_member_details = $this->RoomMember->ifExist($room_id,$user_id);


                $output['code'] = 200;
                $output['msg'] = $room_member_details;
                echo json_encode($output);
            }else{


                Message::EMPTYDATA();
                die();


            }

        }
    }


    public function showRoomDetail()
    {


        $this->loadModel('Room');
        $this->loadModel('RoomMember');


        if ($this->request->isPost()) {
            $json = file_get_contents('php://input');
            $data = json_decode($json, TRUE);




            //$user_id = $data['user_id'];
            $room_id = $data['room_id'];



            $detail = $this->Room->getDetails($room_id);
            $detail['Room']['member'] = 0;

            if(isset($data['user_id'])){


                $user_id = $data['user_id'];

                $if_member = $this->RoomMember->ifExist($room_id,$user_id);
                $detail['Room']['member'] = 0;
                $detail['Room']['member_count'] = 0;
                //$detail['Room']['role'] = 0;
                if(count($if_member) > 0){

                    $detail['Room']['member'] = 1;

                    $member_count = $this->RoomMember->getMembersCount($room_id);
                    $detail['Room']['member_count'] = $member_count;
                }

            }



            $output['code'] = 200;

            $output['msg'] = $detail;



            echo json_encode($output);


            die();


        }


    }
    public function showRooms(){

        $this->loadModel('Room');




        if ($this->request->isPost()) {
            $json = file_get_contents('php://input');
            $data = json_decode($json, TRUE);

            //$user_id = $data['user_id'];



            //$details = $this->Room->getRoomsCreatedAgainstUser();


            if(isset($data['club_id'])){

                $club_id = $data['club_id'];

                $details = $this->Room->getRoomsAgainstClub($club_id);


            }else{

                $details = $this->Room->getAll();
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


    public function inviteUserToRoom()
    {


        $this->loadModel('Room');

        $this->loadModel('Notification');
        $this->loadModel('User');

        if ($this->request->isPost()) {


            $json = file_get_contents('php://input');
            $data = json_decode($json, TRUE);

            $sender_id = $data['sender_id'];
            $receivers = $data['receivers'];
            $room_id = $data['room_id'];
            $type = "room_invitation";
            $created = date('Y-m-d H:i:s', time());


            $sender_details = $this->User->getUserDetailsFromID($sender_id);

            $room_details = $this->Room->getDetails($room_id);



            if(count($receivers) > 0){


                foreach($receivers as $receiver_id){
                    $receiver_id = $receiver_id['receiver_id'];
                    $receiver_details = $this->User->getUserDetailsFromID($receiver_id);


                    if (count($sender_details) > 0 && count($receiver_details) > 0 && count($room_details) > 0) {
                        $msg = $sender_details['User']['first_name'] . " has invited you to Join the room " . $room_details['Room']['title'];

                        if (strlen($receiver_details['User']['device_token']) > 8) {

                            $notification['to'] = $receiver_details['User']['device_token'];


                            $notification['notification']['title'] = "Room Invitation";
                            $notification['notification']['body'] = $msg;
                            $notification['notification']['badge'] = "1";
                            $notification['notification']['sound'] = "default";
                            $notification['notification']['icon'] = "";
                            $notification['notification']['type'] = "room_invitation";
                            $notification['notification']['receiver_id'] = $receiver_id;
                            $notification['data']['receiver_id'] = $receiver_id;
                            $notification['data']['title'] = "Room Invitation";
                            $notification['data']['body'] = $msg;
                            $notification['data']['icon'] = "";
                            $notification['data']['badge'] = "1";
                            $notification['data']['sound'] = "default";
                            $notification['data']['type'] = "room_invitation";


                            $r = Utility::sendPushNotificationToMobileDevice(json_encode($notification));




                        }


                        $post_data['sender_id'] = $sender_id;
                        $post_data['receiver_id'] = $receiver_id;
                        $post_data['string'] = $msg;
                        $post_data['room_id'] = $room_id;
                        $post_data['type'] = $type;
                        $post_data['created'] = $created;


                        $this->Notification->save($post_data);
                        $this->Notification->clear();

                        //$id = $this->Notification->getInsertID();


                        $output = array();
                        //$details = $this->Notification->getDetails($id);



                    }

                }

            }

            $output['code'] = 200;
            $output['msg'] = "success";
            echo json_encode($output);
            die();
        }
    }


    public function showUserJoinedRooms(){

        $this->loadModel('RoomMember');




        if ($this->request->isPost()) {
            $json = file_get_contents('php://input');
            $data = json_decode($json, TRUE);

            $user_id = $data['user_id'];


            $details = $this->RoomMember->getUserRooms($user_id);


            if (count($details) > 0) {

                foreach ($details as $key => $detail) {

                    $room_id = $detail['RoomMember']['room_id'];

                    $moderators = $this->RoomMember->getRoomModerators($room_id);
                    $details[$key]['Room']['Moderators'] = $moderators;



                }

                $output['code'] = 200;

                $output['msg'] = $details;


                echo json_encode($output);


                die();
            } else {

                Message::EMPTYDATA();
                die();

            }
        }


    }


    public function joinRoom()
    {


        $this->loadModel('Room');
        $this->loadModel('RoomMember');

        if ($this->request->isPost()) {


            $json = file_get_contents('php://input');
            $data = json_decode($json, TRUE);

            $post_data['user_id'] = $data['user_id'];
            $post_data['room_id'] = $data['room_id'];
            $post_data['moderator'] = $data['moderator'];
            $post_data['created'] = date('Y-m-d H:i:s', time());






            $details = $this->Room->getDetails($data['room_id']);
            $room_member_details = $this->RoomMember->ifExist($data['room_id'],$data['user_id']);
            if(count($details) > 0 && count($room_member_details) < 1) {



                $this->RoomMember->save($post_data);
                $id = $this->RoomMember->getInsertID();
                $output = array();
                $details = $this->RoomMember->getDetails($id);


                $output['code'] = 200;
                $output['msg'] = $details;
                echo json_encode($output);
            }else{


                $output['code'] = 201;
                $output['msg'] = "You are already a member of this group";
                echo json_encode($output);


            }

        }
    }




    /*public function likeVideo()
    {

        $this->loadModel("VideoLike");
        $this->loadModel("PushNotification");
        $this->loadModel("Notification");
        $this->loadModel("User");
        $this->loadModel("Video");



        if ($this->request->isPost()) {
            $json = file_get_contents('php://input');
            $data = json_decode($json, TRUE);


            $video_id = $data['video_id'];
            $user_id = $data['user_id'];



            $created = date('Y-m-d H:i:s', time());


            $like_video['video_id'] = $video_id;
            $like_video['user_id'] = $user_id;
            $like_video['created'] = $created;




            $details = $this->VideoLike->ifExist($like_video);
            $userDetail = $this->User->getUserDetailsFromID($user_id);
            $video_details = $this->Video->getDetails($video_id);


            if(count($userDetail) > 0) {

                if (count($details) > 0) {

                    $this->VideoLike->id = $details['VideoLike']['id'];
                    $this->VideoLike->delete();
                    $msg = "unlike";
                } else {

                    $this->VideoLike->save($like_video);


                    $id = $this->VideoLike->getInsertID();
                    $details = $this->VideoLike->getDetails($id);

                    $msg = $details;


                    $notification_msg = $userDetail['User']['username'] . ' liked your video';
                    $notification['to'] = $video_details['User']['device_token'];
                    $notification['notification']['title'] = $notification_msg;
                    $notification['notification']['body'] = "";
                    $notification['notification']['badge'] = "1";
                    $notification['notification']['sound'] = "default";
                    $notification['notification']['icon'] = "";
                    $notification['notification']['type'] = "";
                    $notification['data']['title'] = $notification_msg;
                    $notification['data']['body'] = '';
                    $notification['data']['icon'] = "";
                    $notification['data']['badge'] = "1";
                    $notification['data']['sound'] = "default";
                    $notification['data']['type'] = "";
                    $notification['notification']['receiver_id'] =  $video_details['User']['id'];
                    $notification['data']['receiver_id'] = $video_details['User']['id'];

                    $if_exist = $this->PushNotification->getDetails($userDetail['User']['id']);
                    if (count($if_exist) > 0) {

                        $likes = $if_exist['PushNotification']['likes'];
                        if ($likes > 0) {
                            Utility::sendPushNotificationToMobileDevice(json_encode($notification));
                        }
                    }
                    $notification_data['video_id'] = $video_id;
                    $notification_data['sender_id'] = $user_id;
                    $notification_data['receiver_id'] = $video_details['User']['id'];
                    $notification_data['type'] = "video_like";
                    $notification_data['string'] = $notification_msg;
                    $notification_data['created'] = $created;

                    $this->Notification->save($notification_data);


                }

                $output['code'] = 200;
                $output['msg'] = $msg;
                echo json_encode($output);


                die();

            } else{

                $output['code'] = 201;
                $output['msg'] = "Login First";
                echo json_encode($output);


                die();

            }


        }
    }*/

    public function addVideoFavourite()
    {

        $this->loadModel("VideoFavourite");



        if ($this->request->isPost()) {
            $json = file_get_contents('php://input');
            $data = json_decode($json, TRUE);


            $video_id = $data['video_id'];
            $user_id = $data['user_id'];



            $created = date('Y-m-d H:i:s', time());


            $fav_video['video_id'] = $video_id;
            $fav_video['user_id'] = $user_id;
            $fav_video['created'] = $created;




            $details = $this->VideoFavourite->ifExist($fav_video);

            if(count($details) > 0){

                $this->VideoFavourite->id = $details['VideoFavourite']['id'];
                $this->VideoFavourite->delete();
                $msg = "unfavourite";
            }else{

                $this->VideoFavourite->save($fav_video);



                $id = $this->VideoFavourite->getInsertID();
                $details = $this->VideoFavourite->getDetails($id);

                $msg = $details;

            }

            $output['code'] = 200;
            $output['msg'] = $msg;
            echo json_encode($output);


            die();




        }
    }

    public function likeComment()
    {

        $this->loadModel("VideoCommentLike");




        if ($this->request->isPost()) {
            $json = file_get_contents('php://input');
            $data = json_decode($json, TRUE);


            $comment_id = $data['comment_id'];
            $user_id = $data['user_id'];



            $created = date('Y-m-d H:i:s', time());


            $fav_comm['comment_id'] = $comment_id;
            $fav_comm['user_id'] = $user_id;
            $fav_comm['created'] = $created;




            $details = $this->VideoCommentLike->ifExist($fav_comm);



            if(count($details) > 0){

                $this->VideoCommentLike->id = $details['VideoCommentLike']['id'];
                $this->VideoCommentLike->delete();
                $msg = "unfavourite";
            }else{

                $this->VideoCommentLike->save($fav_comm);



                $id = $this->VideoCommentLike->getInsertID();
                $details = $this->VideoCommentLike->getDetails($id);
                $details['VideoCommentLike']['owner_like'] = 0;
                if($details['VideoComment']['Video']['user_id'] == $user_id){


                    $details['VideoCommentLike']['owner_like'] = 1;
                }
                $msg = $details;

            }
            $like_count = $this->VideoCommentLike->countLikes($comment_id);
            $output['code'] = 200;
            $output['msg'] = $msg;
            $output['like_count'] = $like_count;
            echo json_encode($output);


            die();




        }
    }
    public function likeCommentReply()
    {

        $this->loadModel("VideoCommentReplyLike");



        if ($this->request->isPost()) {
            $json = file_get_contents('php://input');
            $data = json_decode($json, TRUE);


            $video_comment_reply_id = $data['comment_reply_id'];
            $user_id = $data['user_id'];



            $created = date('Y-m-d H:i:s', time());


            $fav_comm['comment_reply_id'] = $video_comment_reply_id;
            $fav_comm['user_id'] = $user_id;
            $fav_comm['created'] = $created;




            $details = $this->VideoCommentReplyLike->ifExist($fav_comm);

            if(count($details) > 0){

                $this->VideoCommentReplyLike->id = $details['VideoCommentReplyLike']['id'];
                $this->VideoCommentReplyLike->delete();
                $msg = "unfavourite";
            }else{

                $this->VideoCommentReplyLike->save($fav_comm);



                $id = $this->VideoCommentReplyLike->getInsertID();
                $details = $this->VideoCommentReplyLike->getDetails($id);

                $msg = $details;

            }

            $like_count = $this->VideoCommentReplyLike->countLikes($video_comment_reply_id);

            $output['code'] = 200;
            $output['msg'] = $msg;
            $output['like_count'] = $like_count;
            echo json_encode($output);


            die();




        }
    }

    public function test3(){


         //pr(Premium::checkNudity("http://cdn.qboxus.com/tictic-video/video/videoplayback.mp4"));
    }
    public function addPlaylist()
    {



        $this->loadModel('Playlist');
        $this->loadModel('PlaylistVideo');


        if ($this->request->isPost()) {


            $json = file_get_contents('php://input');
            $data = json_decode($json, TRUE);

            $created = date('Y-m-d H:i:s', time());
            $name = $data['name'];
            $user_id = $data['user_id'];
            $videos = $data['videos'];
            $post_data['name'] = $name;
            $post_data['user_id'] = $user_id;
            $post_data['created'] = $created;




            if(isset($data['id'])){


                //$this->Playlist->id = $data['id'];
                $this->PlaylistVideo->deletePlaylistVideo($data['id']);




                $this->Playlist->id = $data['id'];
                $this->Playlist->save($post_data);


                if(count($videos) > 0){


                    foreach ($videos as $key=>$val){


                        $post_videolist[$key]['video_id'] =  $val['video_id'];
                        $post_videolist[$key]['order'] = $val['order'];
                        $post_videolist[$key]['playlist_id'] = $data['id'];

                    }

                    $this->PlaylistVideo->saveAll($post_videolist);


                }







                $details = $this->Playlist->getDetails($data['id']);

                $output['code'] = 200;
                $output['msg'] = $details;
                echo json_encode($output);
                die();

            }

            $this->Playlist->save($post_data);
            $id = $this->Playlist->getInsertID();


            if(count($videos) > 0){


                foreach ($videos as $key=>$val){


                    $post_videolist[$key]['video_id'] =  $val['video_id'];
                    $post_videolist[$key]['order'] = $val['order'];
                    $post_videolist[$key]['playlist_id'] = $id;

                }

                $this->PlaylistVideo->saveAll($post_videolist);


            }







            $details = $this->Playlist->getDetails($id);

            $output['code'] = 200;
            $output['msg'] = $details;
            echo json_encode($output);
            die();


        }
    }

    public function showUserOrders()
    {

        $this->loadModel("Order");

        $this->loadModel("User");



        if ($this->request->isPost()) {
            $json = file_get_contents('php://input');
            $data = json_decode($json, TRUE);

            $user_id = $data['user_id'];


            $type = $data['type'];

            if($type == "completed"){

                $status = 2;
            }else if($type == "shipped"){

                $status = 1;
            }else if($type == "cancelled"){

                $status = 3;
            }else if($type == "all"){

                $status = 5;
            }




                $starting_point =  $data['starting_point'];







            $orders = $this->Order->getUserOrders($user_id,$status,$starting_point);


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





    public function showOrderDetail()
    {

        $this->loadModel("Order");

        $this->loadModel("User");
        $this->loadModel("ProductRating");



        if ($this->request->isPost()) {
            $json = file_get_contents('php://input');
            $data = json_decode($json, TRUE);

            $order_id = $data['order_id'];
            $user_id = $data['user_id'];







            $orders = $this->Order->getDetails($order_id);


            if(count($orders) > 0) {

                if(count($orders['OrderProduct']) > 0){

                    foreach( $orders['OrderProduct'] as $key=>$product) {
                       $product_id =  $product['product_id'];
                      $product_rating_exist =   $this->ProductRating->ifExist($user_id,$product_id,$order_id);

                      if(count($product_rating_exist) > 0) {
                          $orders['OrderProduct'][$key]['ProductRating'] = $product_rating_exist['ProductRating'];

                      }
                    }
                }
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


    public function showStoreOrders()
    {

        $this->loadModel("Order");

        $this->loadModel("User");



        if ($this->request->isPost()) {
            $json = file_get_contents('php://input');
            $data = json_decode($json, TRUE);

            $user_id = $data['user_id'];


            $type = $data['type'];

            if($type == "completed"){

                $status = 3;
            }else if($type == "shipped"){

                $status = 2;
            }else if($type == "all"){

                $status = 5;
            }




            $starting_point =  $data['starting_point'];







            $orders = $this->Order->getStoreOrders($user_id,$status,$starting_point);



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
    public function showPlaylists(){

        $this->loadModel('Playlist');
        $this->loadModel('VideoLike');
        $this->loadModel('VideoFavourite');
        $this->loadModel('Follower');
        $this->loadModel('VideoComment');




        if ($this->request->isPost()) {
            $json = file_get_contents('php://input');
            $data = json_decode($json, TRUE);


            $id = $data['id'];

            $details = $this->Playlist->getDetails($id);



            if(count($details) > 0) {


                $i = 0;
                foreach($details['PlaylistVideo'] as $key=>$val){

                    $video_id = $val['video_id'];
                    $video_user_id = $val['Video']['user_id'];

                    $details['PlaylistVideo'][$key]['Video']['like'] = 0;
                    $details['PlaylistVideo'][$key]['Video']['favourite'] = 0;

                    if (isset($data['user_id'])) {

                        $user_id = $data['user_id'];




                        if ($user_id != $video_user_id) {

                            $follower_details = $this->Follower->ifFollowing($user_id, $video_user_id);

                            if (count($follower_details) > 0) {

                                $details['PlaylistVideo'][$key]['Video']['User']['button'] = "unfollow";

                            } else {


                                $details['PlaylistVideo'][$key]['Video']['User']['button'] = "follow";
                            }


                            $video_data['user_id'] = $user_id;
                            $video_data['video_id'] = $video_id;
                            $video_like_detail = $this->VideoLike->ifExist($video_data);
                            $video_favourite_detail = $this->VideoFavourite->ifExist($video_data);

                            if (count($video_like_detail) > 0) {

                                $details['PlaylistVideo'][$key]['Video']['like'] = 1;

                            }

                            if (count($video_favourite_detail) > 0) {

                                $details['PlaylistVideo'][$key]['Video']['favourite'] = 1;

                            }


                        }



                    }

                    $video_like_count = $this->VideoLike->countLikes($video_id);
                    $video_comment_count = $this->VideoComment->countComments($video_id);
                    $details['PlaylistVideo'][$key]['Video']['like_count'] = $video_like_count;
                    $details['PlaylistVideo'][$key]['Video']['comment_count'] = $video_comment_count;

                }
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


    public function deletePlaylist(){

        $this->loadModel('Playlist');




        if ($this->request->isPost()) {
            $json = file_get_contents('php://input');
            $data = json_decode($json, TRUE);


            $id = $data['id'];

            $this->Playlist->delete($id,true);





            $output['code'] = 200;

            $output['msg'] = "success";


            echo json_encode($output);


            die();


        }


    }

    public function deletePlaylistVideo(){

        $this->loadModel('Playlist');




        if ($this->request->isPost()) {
            $json = file_get_contents('php://input');
            $data = json_decode($json, TRUE);


            $id = $data['id'];
            $this->PlaylistVideo->id = $id;
            $this->PlaylistVideo->delete();


            $output['code'] = 200;

            $output['msg'] = "success";


            echo json_encode($output);


            die();

        }
    }


    public function addProductAttribute()
    {

        $this->loadModel("ProductAttribute");



        if ($this->request->isPost()) {
            $json = file_get_contents('php://input');
            $data = json_decode($json, TRUE);


            $product_id = $data['product_id'];
            $name = $data['name'];



            $created = date('Y-m-d H:i:s', time());


            $post_data['product_id'] = $product_id;
            $post_data['name'] = $name;
            $post_data['created'] = $created;



            if(isset($data['id'])){
                $this->ProductAttribute->id = $data['id'];
                $this->ProductAttribute->save($post_data);




                $details = $this->ProductAttribute->getDetails($data['id']);

                $msg = $details;

            }else {

                $details = $this->ProductAttribute->ifExist($post_data);

                if (count($details) > 0) {

                    Message::DUPLICATEDATE();
                    die();

                } else {

                    $this->ProductAttribute->save($post_data);


                    $id = $this->ProductAttribute->getInsertID();
                    $details = $this->ProductAttribute->getDetails($id);

                    $msg = $details;

                }
            }
            $output['code'] = 200;
            $output['msg'] = $msg;
            echo json_encode($output);


            die();




        }
    }
    public function showDailyCheckins(){



        $this->loadModel('User');
        $this->loadModel('DailyCheckin');


        if ($this->request->isPost()) {
            $json = file_get_contents('php://input');
            $data = json_decode($json, TRUE);

            $user_id =  $data['user_id'];
            $created = date('Y-m-d H:i:s', time());
            $today_date = date('Y-m-d', time());

            $user_details = $this->User->getOnlyUserDetailsFromID($user_id);


            if(count($user_details) > 0){



                $user_registration_datetime =  $user_details['User']['created'];

                $user_registration_datetime_day = new DateTime($user_registration_datetime);
                $today_date_day = new DateTime($created);
                $day_starting_name = $user_registration_datetime_day->format('l');
                $today_date_day_name = $today_date_day->format('l');
                if($today_date_day_name == $day_starting_name){

                    $newdate = $today_date;
                }else{


                    $newdate =  $this->getRecentPastDate($day_starting_name);
                }

                $recent_checkins = $this->DailyCheckin->getRecentCheckins($user_id,$newdate);



                $output['code'] = 200;

                $output['msg']['checkins'] = $recent_checkins;
                $output['msg']['starting_date'] = $newdate;
                $output['msg']['server_datetime'] = $created;


                echo json_encode($output);


                die();



            }






        }


    }


    public function addDailyCheckin(){



        $this->loadModel('User');
        $this->loadModel('DailyCheckin');


        if ($this->request->isPost()) {
            $json = file_get_contents('php://input');
            $data = json_decode($json, TRUE);

            $user_id =  $data['user_id'];
            $coins =  $data['coin'];
            $created = date('Y-m-d H:i:s', time());
            $date = date('Y-m-d', time());

            $user_details = $this->User->getOnlyUserDetailsFromID($user_id);


            if(count($user_details) > 0){



                $post_data['user_id'] = $user_id;
                $post_data['created'] = $created;
                $post_data['coin'] = $coins;

                $if_exist = $this->DailyCheckin->ifExist($user_id,$date);

                if(count($if_exist) < 1){


                    $this->DailyCheckin->save($post_data);

                    $id = $this->DailyCheckin->getInsertID();
                    $daily_checkin_details =   $this->DailyCheckin->getDetails($id);


                    $output['code'] = 200;

                    $output['msg'] = $daily_checkin_details;



                    echo json_encode($output);


                    die();

                }else{
                    $output['code'] = 201;

                    $output['msg'] = "Already exist";



                    echo json_encode($output);


                    die();
                }

            }






        }


    }

    function getRecentPastDate($day){


        $now = new DateTime(); // Current date and time



        // Get the most recent past Monday within the current week
        $recentPastMonday = clone $now;
        $recentPastMonday->modify("last $day");


        $recentPastMondayDate = $recentPastMonday->format('Y-m-d');
        return $recentPastMondayDate;
    }

    public function addCategoryInProduct()
    {

        $this->loadModel("ProductCategory");
        $this->loadModel("Product");



        if ($this->request->isPost()) {
            $json = file_get_contents('php://input');
            $data = json_decode($json, TRUE);


            $product_category_id = $data['product_category_id'];
            $product_id = $data['product_id'];






            $details = $this->Product->getDetails($product_id);

            if(count($details) < 1){

                Message::EMPTYDATA();
                die();

            }else{
                $this->Product->id = $product_id;
                $this->Product->saveField('product_category_id',$product_category_id);



                $details = $this->Product->getDetails($product_id);

                $msg = $details;

            }

            $output['code'] = 200;
            $output['msg'] = $msg;
            echo json_encode($output);


            die();




        }
    }


    public function addProductAttributeVariation()
    {

        $this->loadModel("ProductAttributeVariation");
        $this->loadModel("ProductAttribute");



        if ($this->request->isPost()) {
            $json = file_get_contents('php://input');
            $data = json_decode($json, TRUE);


            $product_attribute_id = $data['product_attribute_id'];
            $options = $data['option'];
            //$price = $data['price'];



            $created = date('Y-m-d H:i:s', time());




            if(count($options) > 0){
                $this->ProductAttributeVariation->deleteAllVariations($product_attribute_id);
                foreach ($options as $key=>$value){

                 $post_data[$key]['value']  =   $value['value'];
                 $post_data[$key]['product_attribute_id']  =   $product_attribute_id;
                 $post_data[$key]['created']  =   $created;
                }

                $this->ProductAttributeVariation->saveAll($post_data);
            }

           $details =  $this->ProductAttribute->getDetails($product_attribute_id);





            $output['code'] = 200;
            $output['msg'] = $details;
            echo json_encode($output);


            die();




        }
    }


    public function addProductAttributeCombination()
    {

        $this->loadModel("ProductAttributeCombination");




        if ($this->request->isPost()) {
            $json = file_get_contents('php://input');
            $data = json_decode($json, TRUE);


            $product_id = $data['product_id'];
            $options = $data['option'];
            //$price = $data['price'];



            $created = date('Y-m-d H:i:s', time());




            if(count($options) > 0){
                $this->ProductAttributeCombination->deleteAllCombinations($product_id);
                foreach ($options as $key=>$value){

                    $post_data[$key]['combination_string']  =   $value['combination_string'];
                    $post_data[$key]['sku']  =   $value['sku'];
                    $post_data[$key]['price']  =   $value['price'];
                    $post_data[$key]['product_id']  =   $product_id;
                    $post_data[$key]['available_stock']  =  $value['available_stock'];
                    $post_data[$key]['created']  =   $created;
                }

                $this->ProductAttributeCombination->saveAll($post_data);
            }

            //$details =  $this->ProductAttribute->getDetails($product_attribute_id);





            $output['code'] = 200;
            $output['msg'] = "success";
            echo json_encode($output);


            die();




        }
    }


    public function addHashtagFavourite()
    {

        $this->loadModel("HashtagFavourite");



        if ($this->request->isPost()) {
            $json = file_get_contents('php://input');
            $data = json_decode($json, TRUE);


            $hashtag_id = $data['hashtag_id'];
            $user_id = $data['user_id'];



            $created = date('Y-m-d H:i:s', time());


            $hashtag_fav['hashtag_id'] = $hashtag_id;
            $hashtag_fav['user_id'] = $user_id;
            $hashtag_fav['created'] = $created;




            $details = $this->HashtagFavourite->ifExist($hashtag_fav);

            if(count($details) > 0){

                $this->HashtagFavourite->id = $details['HashtagFavourite']['id'];
                $this->HashtagFavourite->delete();
                $msg = "unfavourite";
            }else{

                $this->HashtagFavourite->save($hashtag_fav);



                $id = $this->HashtagFavourite->getInsertID();
                $details = $this->HashtagFavourite->getDetails($id);

                $msg = $details;

            }

            $output['code'] = 200;
            $output['msg'] = $msg;
            echo json_encode($output);


            die();




        }
    }

    public function showFavouriteHashtags()
    {

        $this->loadModel("HashtagFavourite");
        $this->loadModel("HashtagVideo");


        if ($this->request->isPost()) {

            $json = file_get_contents('php://input');
            $data = json_decode($json, TRUE);
            $user_id = $data['user_id'];
            $starting_point = 0;


            if (isset($data['starting_point'])) {

                $starting_point = $data['starting_point'];

            }
            $fav_posts = $this->HashtagFavourite->getUserFavouriteHashtags($user_id,$starting_point);



            if(count($fav_posts) > 0) {


                foreach ($fav_posts as $key=>$hashtag){


                    $hashtag_views = $this->HashtagVideo->countHashtagViews($hashtag['Hashtag']['id']);
                    $hashtag_videos_count = $this->HashtagVideo->countHashtagVideos($hashtag['Hashtag']['id']);

                    $fav_posts[$key]['Hashtag']['videos_count'] = $hashtag_videos_count;
                    $fav_posts[$key]['Hashtag']['views'] = $hashtag_views[0]['total_sum'];
                }
                $output['code'] = 200;

                $output['msg'] = $fav_posts;


                echo json_encode($output);


                die();

            }else{
                Message::EMPTYDATA();
                die();


            }
        }
    }

    public function addSoundFavourite()
    {

        $this->loadModel("SoundFavourite");



        if ($this->request->isPost()) {
            $json = file_get_contents('php://input');
            $data = json_decode($json, TRUE);


            $video_id = $data['sound_id'];
            $user_id = $data['user_id'];



            $created = date('Y-m-d H:i:s', time());


            $fav_sound['sound_id'] = $video_id;
            $fav_sound['user_id'] = $user_id;
            $fav_sound['created'] = $created;




            $details = $this->SoundFavourite->ifExist($fav_sound);

            if(count($details) > 0){

                $this->SoundFavourite->id = $details['SoundFavourite']['id'];
                $this->SoundFavourite->delete();
                $msg = "unfavourite";
            }else{

                $this->SoundFavourite->save($fav_sound);



                $id = $this->SoundFavourite->getInsertID();
                $details = $this->SoundFavourite->getDetails($id);

                $msg = $details;

            }

            $output['code'] = 200;
            $output['msg'] = $msg;
            echo json_encode($output);


            die();




        }
    }


    public function showUsersAccordingToGiftReceive()
    {

        $this->loadModel("GiftSend");


        if ($this->request->isPost()) {

            $json = file_get_contents('php://input');
            $data = json_decode($json, TRUE);
            $user_id = $data['user_id'];
            $type = $data['type'];
            $starting_point = 0;
            $datetime = date('Y-m-d H:i:s', time());
            $date = date('Y-m-d', time());

            if (isset($data['starting_point'])) {

                $starting_point = $data['starting_point'];

            }
            if($type == "daily") {
                $senders = $this->GiftSend->getDailyReceivers($date, $starting_point);
            } else if($type == "hourly") {

                $currentDateTime = new DateTime();

                $currentDateTime->modify('-1 hour');


                $formattedDateTime = $currentDateTime->format('H');

                $senders = $this->GiftSend->getHourlyReceivers($formattedDateTime, $starting_point);
            }

            if(count($senders) > 0){


                foreach($senders as $key=>$sender){

                    $sender_id =  $sender['GiftSend']['sender_id'];

                    $gift_send =  $this->GiftSend->countGiftReceiveByUser($sender_id);

                    if(strlen($gift_send[0]['total_amount']) < 1){

                        $gift_send[0]['total_amount'] = "0";

                    }
                    $senders[$key]['GiftSend']['total_coins'] =  $gift_send[0]['total_amount'];

                }
            }


            if(count($senders) > 0) {
                $output['code'] = 200;

                $output['msg']['data'] = $senders;
                $output['msg']['server_datetime'] = $datetime;


                echo json_encode($output);


                die();

            }else{
                Message::EMPTYDATA();
                die();


            }
        }
    }

    public function showUsersAccordingToGiftSent()
    {

        $this->loadModel("GiftSend");


        if ($this->request->isPost()) {

            $json = file_get_contents('php://input');
            $data = json_decode($json, TRUE);
            $user_id = $data['user_id'];
            $type = $data['type'];
            $starting_point = 0;
            $datetime = date('Y-m-d H:i:s', time());
            $date = date('Y-m-d', time());

            if (isset($data['starting_point'])) {

                $starting_point = $data['starting_point'];

            }
            if($type == "daily") {
                $senders = $this->GiftSend->getDailySenders($date, $starting_point);
            } else if($type == "hourly") {

                $currentDateTime = new DateTime();

                $currentDateTime->modify('-1 hour');


                $formattedDateTime = $currentDateTime->format('H');

                $senders = $this->GiftSend->getHourlySenders($formattedDateTime, $starting_point);
            }

            if(count($senders) > 0){


                foreach($senders as $key=>$sender){

                   $sender_id =  $sender['GiftSend']['sender_id'];

                    $gift_send =  $this->GiftSend->countGiftSendByUser($sender_id);
                    $top_gifts_send =  $this->GiftSend->getTopGiftsSendByUser($sender_id);

                    if(strlen($gift_send[0]['total_amount']) < 1){

                        $gift_send[0]['total_amount'] = "0";

                    }
                    $senders[$key]['GiftSend']['total_coins'] =  $gift_send[0]['total_amount'];
                    $senders[$key]['User']['top_gifts'] =  $top_gifts_send;

                }
            }


            if(count($senders) > 0) {
                $output['code'] = 200;

                $output['msg']['data'] = $senders;
                $output['msg']['server_datetime'] = $datetime;



                echo json_encode($output);


                die();

            }else{
                Message::EMPTYDATA();
                die();


            }
        }
    }
    public function showFavouriteSounds()
    {

        $this->loadModel("SoundFavourite");


        if ($this->request->isPost()) {

            $json = file_get_contents('php://input');
            $data = json_decode($json, TRUE);
            $user_id = $data['user_id'];
            $starting_point = 0;


            if (isset($data['starting_point'])) {

                $starting_point = $data['starting_point'];

            }
            $fav_posts = $this->SoundFavourite->getUserFavouriteSounds($user_id,$starting_point);


            if(count($fav_posts) > 0) {
                $output['code'] = 200;

                $output['msg'] = $fav_posts;


                echo json_encode($output);


                die();

            }else{
                Message::EMPTYDATA();
                die();


            }
        }
    }

    public function showPromotions()
    {

        $this->loadModel("Promotion");
        $this->loadModel("VideoWatch");
        $this->loadModel("VideoLike");


        if ($this->request->isPost()) {

            $json = file_get_contents('php://input');
            $data = json_decode($json, TRUE);
            $user_id = $data['user_id'];
            $start_datetime = $data['start_datetime'];
            $end_datetime = $data['end_datetime'];





                $starting_point = $data['starting_point'];


            $details = $this->Promotion->getUserPromotions($user_id,$start_datetime,$end_datetime,$starting_point);


            if(count($details) > 0) {

                $total_coins = 0;
                $total_destination_tap = 0;
                $total_likes = 0;
                $total_views = 0;
                foreach($details as $key=>$detail){

                    //$video_ids_array[$key] =$video_id;

                   $video_id =  $detail['Promotion']['video_id'];
                   $coin =  $detail['Promotion']['coin'];
                   $destination_tap =  $detail['Promotion']['destination_tap'];
                   $start_datetime =  $detail['Promotion']['start_datetime'];
                   $end_datetime =  $detail['Promotion']['end_datetime'];

                    $video_ids_single[0] = $video_id;
                    $count_views = $this->VideoWatch->countWatchVideos($video_ids_single, $start_datetime, $end_datetime);
                    $count_likes = $this->VideoLike->countLikesBetweenDatetime($video_ids_single, $start_datetime, $end_datetime);
                    $details[$key]['Promotion']['video_views'] = $count_views;

                    $total_coins = $total_coins + $coin;
                    $total_destination_tap = $total_destination_tap + $destination_tap;
                    $total_likes = $total_likes + $count_likes;
                    $total_views = $total_views + $count_views;

                }


                $output['code'] = 200;

                $output['msg']['Details'] = $details;
                $output['msg']['Stats']['total_coins'] = $total_coins;
                $output['msg']['Stats']['total_destination_tap'] = $total_destination_tap;
                $output['msg']['Stats']['total_likes'] = $total_likes;
                $output['msg']['Stats']['total_views'] = $total_views;


                echo json_encode($output);


                die();

            }else{
                Message::EMPTYDATA();
                die();


            }
        }
    }


    public function useSticker()
    {
        $this->loadModel("Sticker");
        $this->loadModel("StickerUsed");


        if ($this->request->isPost()) {
            $json = file_get_contents('php://input');
            $data = json_decode($json, TRUE);



            $user_id = $data['user_id'];
            $sticker_id = $data['sticker_id'];





            $details =  $this->Sticker->getDetails($sticker_id);


            if(count($details)< 1){


                Message::EMPTYDATA();
                die();
            }


            $used_count = $details['Sticker']['used_count'];
            $post_data['user_id'] = $user_id;

            $post_data['sticker_id'] = $sticker_id;





            $this->StickerUsed->save($post_data);

            $used_count = $details['Sticker']['used_count'];
            $this->Sticker->saveField('used_count',$used_count+1);
            $output['code'] = 200;
            $output['msg'] = "success";
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
    public function showStickers()
    {

        $this->loadModel("Sticker");


        if ($this->request->isPost()) {

            $json = file_get_contents('php://input');
            $data = json_decode($json, TRUE);
            $user_id = $data['user_id'];
            $type = $data['type'];





                $starting_point = $data['starting_point'];


            $details = $this->Sticker->getAll($type,$starting_point);



            if(count($details) > 0) {
                $output['code'] = 200;

                $output['msg'] = $details;


                echo json_encode($output);


                die();

            }else {
                Message::EMPTYDATA();
                die();


            }
        }
    }

    public function showFavouriteVideos()
    {

        $this->loadModel("VideoFavourite");
        $this->loadModel("VideoLike");
        $this->loadModel("Follower");
        $this->loadModel("VideoComment");
        $this->loadModel("VideoLike");
        $this->loadModel("RepostVideo");


        if ($this->request->isPost()) {

            $json = file_get_contents('php://input');
            $data = json_decode($json, TRUE);
            $user_id = $data['user_id'];

            $starting_point = 0;


            if (isset($data['starting_point'])) {

                $starting_point = $data['starting_point'];

            }
            $fav_posts = $this->VideoFavourite->getUserFavouriteVideos($user_id,$starting_point);


            if(count($fav_posts) > 0) {


                foreach ($fav_posts as $key=>$videos) {

                    $video_data['user_id'] = $user_id;
                    $video_data['video_id'] = $videos['Video']['id'];

                    $video_like_detail = $this->VideoLike->ifExist($video_data);
                    $video_favourite_detail = $this->VideoFavourite->ifExist($video_data);

                    if (count($video_like_detail) > 0) {

                        $fav_posts[$key]['Video']['like'] = 1;

                    } else {

                        $fav_posts[$key]['Video']['like'] = 0;
                    }

                    if (count($video_favourite_detail) > 0) {

                        $fav_posts[$key]['Video']['favourite'] = 1;

                    } else {

                        $fav_posts[$key]['Video']['favourite'] = 0;
                    }

                    $repost_detail = $this->RepostVideo->ifExist($video_data);


                    if (count($repost_detail) > 0) {

                        $fav_posts[$key]['Video']['repost'] = 1;

                    } else {

                        $fav_posts[$key]['Video']['repost'] = 0;
                    }


                    $comment_count = $this->VideoComment->countComments($videos['Video']['id']);
                    $video_likes_count = $this->VideoLike->countLikes($videos['Video']['id']);
                    $video_fav_count = $this->VideoFavourite->getFavVideosCount($videos['Video']['id']);

                    $fav_posts[$key]['Video']['comment_count'] = $comment_count;
                    $fav_posts[$key]['Video']['like_count'] = $video_likes_count;
                    $fav_posts[$key]['Video']['favourite_count'] = $video_fav_count;
                }

                $output['code'] = 200;

                $output['msg'] = $fav_posts;


                echo json_encode($output);


                die();

            }else{
                Message::EMPTYDATA();
                die();


            }
        }
    }



    public function getVideoDetection(){

        $this->loadModel("Video");
        if ($this->request->isPost()) {


            $json = file_get_contents('php://input');
            $data = json_decode($json, TRUE);


            $video_id = $data['file_id'];
            $moderation_result= $data['ModerationResult'];


            $video_post_data['quality_check'] = 1;

            $video_details = $this->Video->getOnlyVideoDetails($video_id);


            if (count($video_details) > 0) {

                if(count($moderation_result) > 0){


                    $label =  strtolower($moderation_result['Name']);
                    $parent =  strtolower($moderation_result['ParentName']);

                    if (strpos($label, "nudity") !== false || (strpos($parent, "nudity") !== false)) {



                        $video_post_data['block'] = 1;
                    }
                }




                if (count($video_details) > 0) {

                    $this->Video->id = $video_id;
                    $this->Video->save($video_post_data);
                    $this->Video->clear();

                }

            }
        }

    }



    public function liveStream()
    {



        $this->loadModel('LiveStreaming');


        if ($this->request->isPost()) {


            $json = file_get_contents('php://input');
            $data = json_decode($json, TRUE);
            if(isset($data['user_id'])) {

                $stream_data['user_id'] = $data['user_id'];

            }

            if(isset($data['duration'])) {

                $stream_data['duration'] = $data['duration'];

            }


            if(isset($data['started_at'])) {

                $stream_data['started_at'] = $data['started_at'];

            }else    if(isset($data['ended_at'])) {

                $details = $this->LiveStreaming->getDetails($data['id']);
                if(count($details) > 0) {
                    $stream_data['ended_at'] = $data['ended_at'];

                    $seconds = Utility::getSeconds($details['LiveStreaming']['started_at'], $data['ended_at']);

                    $stream_data['duration'] = $seconds;

                }
            }

            if(isset($data['id'])){



                $this->LiveStreaming->id = $data['id'];
                $this->LiveStreaming->save($stream_data);
                $details = $this->LiveStreaming->getDetails($data['id']);

                $output['code'] = 200;
                $output['msg'] = $details;
                echo json_encode($output);
                die();

            }
            $created = date('Y-m-d H:i:s', time());
            $stream_data['created'] = $created;



            $this->LiveStreaming->save($stream_data);
            $id = $this->LiveStreaming->getInsertID();
            $details = $this->LiveStreaming->getDetails($id);

            $output['code'] = 200;
            $output['msg'] = $details;
            echo json_encode($output);
            die();




        }
    }


    public function showUnReadNotifications(){

        $this->loadModel('Notification');




        if ($this->request->isPost()) {
            $json = file_get_contents('php://input');
            $data = json_decode($json, TRUE);

            $user_id = $data['user_id'];



            $notifications = $this->Notification->getUserUnreadNotification($user_id);


            if (count($notifications) > 0) {

                $output['code'] = 200;

                $output['msg'] = $notifications;


                echo json_encode($output);


                die();

            } else {

                Message::EMPTYDATA();
                die();

            }
        }


    }

    public function readNotification()
    {

        $this->loadModel("Notification");
        $this->loadModel("User");



        if ($this->request->isPost()) {
            $json = file_get_contents('php://input');
            $data = json_decode($json, TRUE);



            $user_id =  $data['user_id'];


            $details  = $this->Notification->getUserNotifications($user_id,0);

            if(count($details) > 0) {

                $this->Notification->readNotification($user_id);


                $output['code'] = 200;
                $output['msg'] = "success";
                echo json_encode($output);


                die();
            }else{


                $output['code'] = 201;
                $output['msg'] = "no notification exist";
                echo json_encode($output);


                die();

            }




        }
    }
    public function acceptStreamingInvite()
    {



        $this->loadModel('Notification');


        if ($this->request->isPost()) {


            $json = file_get_contents('php://input');
            $data = json_decode($json, TRUE);

            $id = $data['id'];
            $status = $data['status'];





            $details = $this->Notification->getDetails($id);

            if(count($details) > 0 ) {


                $this->Notification->id = $id;
                $this->Notification->saveField('status', $status);
                $details = $this->Notification->getDetails($id);


                $output['code'] = 200;
                $output['msg'] = $details;
                echo json_encode($output);
                die();


            }


        }
    }


    public function showUsers(){

        $this->loadModel('User');
        $this->loadModel('Follower');
        $this->loadModel('Video');




        if ($this->request->isPost()) {
            $json = file_get_contents('php://input');
            $data = json_decode($json, TRUE);

            $users = $data['users'];
            $user_id = $data['user_id'];


           $ids = array();


            if (count($users) > 0) {


                foreach ($users as $key => $user) {

                   $ids[$key] = $user['user_id'];



                }
                $userDetail =  $this->User->getMultipleUsersData($ids);

                if(count($userDetail) > 0){

                    foreach($userDetail as $key=>$user){
                        $follower_details = $this->Follower->ifFollowing($user_id, $user['User']['id']);
                        $following_details = $this->Follower->ifFollowing($user['User']['id'], $user_id);
                        $followers_count = $this->Follower->countFollowers($user['User']['id']);
                        $following_count = $this->Follower->countFollowing($user['User']['id']);


                        $video_count = $this->Video->getUserVideosCount($user['User']['id']);

                        $userDetail[$key]['User']['followers_count'] = $followers_count;
                        $userDetail[$key]['User']['following_count'] = $following_count;
                        $userDetail[$key]['User']['video_count'] = $video_count;
                        $userDetail[$key]['User']['block'] = 0;
                        if(count($follower_details) > 0 && count($following_details) > 0){

                            $userDetail[$key]['User']['button'] = "Friends";

                        } else   if(count($follower_details) > 0 && count($following_details) < 1){

                            $userDetail[$key]['User']['button'] = "following";

                        }else if (count($following_details) > 0){


                            $userDetail[$key]['User']['button'] = "follow back";
                        }else{


                            $userDetail[$key]['User']['button'] = "follow";
                        }


                        if(count($follower_details) > 0){


                            $userDetail[$key]['User']['notification'] = $follower_details['Follower']['notification'];
                        }else{

                            $userDetail[$key]['User']['notification'] = 0;

                        }
                    }


                }
                $output['code'] = 200;

                $output['msg'] = $userDetail;


                echo json_encode($output);


                die();
            } else {

                Message::EMPTYDATA();
                die();

            }
        }


    }
    public function inviteUserToStreaming(){

        $this->loadModel('Notification');
        $this->loadModel('LiveStreaming');
        $this->loadModel('User');


        if ($this->request->isPost()) {


            $json = file_get_contents('php://input');
            $data = json_decode($json, TRUE);

            $created = date('Y-m-d H:i:s', time());
            $live_streaming_id = $data['live_streaming_id'];
            $users = $data['users'];
            $type = $data['type'];


            if(count($users) > 0){

                foreach($users as $val){

                    $user_id = $val['user_id'];

                    $group_details  = $this->LiveStreaming->getDetails($live_streaming_id);

                    $receiver_details  = $this->User->getUserDetailsFromID($user_id);


                    if(count($group_details) > 0) {

                        $msg = "You have been invited in live streaming session";

                        $group_user_id = $group_details['LiveStreaming']['user_id'];
                        $group_id = $group_details['LiveStreaming']['id'];

                        $if_notification_exist = $this->Notification->ifStreamingNotificationExist($group_user_id,$user_id,$live_streaming_id);
                        if($if_notification_exist < 1) {
                            $notification_data['sender_id'] = $group_user_id;
                            $notification_data['receiver_id'] = $user_id;
                            $notification_data['live_streaming_id'] = $live_streaming_id;
                            $notification_data['type'] = $type;


                            $notification_data['string'] = $msg;
                            $notification_data['created'] = $created;


                            $this->Notification->save($notification_data);

                        }
                        $notification['to'] = $receiver_details['User']['device_token'];

                        $notification['notification']['title'] = "streaming invitation";
                        $notification['notification']['body'] = $msg;
                        $notification['notification']['badge'] = "1";
                        $notification['notification']['sound'] = "default";
                        $notification['notification']['icon'] = "";
                        $notification['notification']['live_streaming_id'] = $live_streaming_id;
                        $notification['notification']['type'] = "live";
                        $notification['notification']['user_id'] = $user_id;
                        $notification['notification']['receiver_id'] = $receiver_details['User']['id'];
                        //$notification['notification']['name'] = $user_details['User']['first_name']." ".$user_details['User']['last_name'];
                        // $notification['notification']['image'] = $user_details['User']['profile_pic'];

                        $notification['data']['title'] = "streaming invitation";
                        $notification['data']['body'] = $msg;
                        $notification['data']['icon'] = "";
                        $notification['data']['badge'] = "1";
                        $notification['data']['sound'] = "default";
                        $notification['data']['type'] = "live";
                        $notification['data']['live_streaming_id'] = $live_streaming_id;
                        $notification['data']['user_id'] = $user_id;
                        $notification['data']['receiver_id'] = $receiver_details['User']['id'];

                        // $notification['data']['receiver_id'] = $receiver_id;
                        // $notification['notification']['receiver_id'] = $receiver_id;


                        Utility::sendPushNotificationToMobileDevice(json_encode($notification));


                    }
                }
            }

            $output['code'] = 200;
            $output['msg'] = "success";
            echo json_encode($output);
            die();


        }
    }

    public function showUserDetail()
    {

        $this->loadModel("User");
        $this->loadModel("Follower");
        $this->loadModel("VideoLike");
        $this->loadModel("Video");
        $this->loadModel("ProfileVisit");
        $this->loadModel("BlockUser");
        $this->loadModel("LiveStreaming");
        $this->loadModel("Notification");





        if ($this->request->isPost()) {


            $json = file_get_contents('php://input');
            $data = json_decode($json, TRUE);
            $user_id = 0;
            $created = date('Y-m-d H:i:s', time());
            $one_day_before = date('Y-m-d H:i:s', strtotime('-1 days', strtotime($created)));

            if(isset($data['user_id'])){

                $user_id = $data['user_id'];
            }
            $notifications = $this->Notification->getUserUnreadNotification($user_id);

            if(isset($data['other_user_id'])){

                $other_user_id = $data['other_user_id'];

                if($other_user_id < 1){
                    $username = $data['username'];

                    $user_id_details = $this->User->getUserDetailsFromUsername($username);

                    $other_user_id = $user_id_details['User']['id'];
                }
                $userDetail = $this->User->getUserDetailsFromID($other_user_id);






                    $likes_count = $this->VideoLike->countLikesOnAllUserVideos($other_user_id);


                $other_person_user_id = $other_user_id;
                $follower_details = $this->Follower->ifFollowing($user_id, $other_person_user_id);
                $following_details = $this->Follower->ifFollowing($other_person_user_id, $user_id);
                $followers_count = $this->Follower->countFollowers($other_user_id);
                $following_count = $this->Follower->countFollowing($other_user_id);


                $video_count = $this->Video->getUserVideosCount($other_user_id);
                $userDetail['User']['followers_count'] = $followers_count;
                $userDetail['User']['following_count'] = $following_count;
                $userDetail['User']['likes_count'] = $likes_count;
                $userDetail['User']['video_count'] = $video_count;
                $userDetail['User']['block'] = 0;
                if(count($follower_details) > 0 && count($following_details) > 0){

                    $userDetail['User']['button'] = "Friends";

                } else   if(count($follower_details) > 0 && count($following_details) < 1){

                    $userDetail['User']['button'] = "following";

                }else if (count($following_details) > 0){


                    $userDetail['User']['button'] = "follow back";
                }else{


                    $userDetail['User']['button'] = "follow";
                }


                if(count($follower_details) > 0){


                    $userDetail['User']['notification'] = $follower_details['Follower']['notification'];
                }else{

                    $userDetail['User']['notification'] = 0;

                }


                $user_story = $this->Video->getUserStory($other_person_user_id,$one_day_before);
                $userDetail['User']['story'] = $user_story;

                $if_blocked = $this->BlockUser->ifBlocked($user_id, $other_person_user_id);
                $if_other_blocked = $this->BlockUser->ifBlocked($other_person_user_id,$user_id);

                if(count($if_blocked) > 0 ){

                    $userDetail['User']['block'] = 1;
                    $userDetail['User']['BlockUser'] = $if_blocked['BlockUser'];

                }

                if(count($if_other_blocked) > 0){


                    $userDetail['User']['block'] = 1;
                    $userDetail['User']['BlockUser'] = $if_other_blocked['BlockUser'];

                }





                if($other_user_id != $user_id) {
                    $profile_visit['user_id'] = $other_user_id;
                    $profile_visit['visitor_id'] = $user_id;

                    $profile_visit['created'] = $created;

                    $this->ProfileVisit->save($profile_visit);
                }
            }else{









                    $likes_count = $this->VideoLike->countLikesOnAllUserVideos($user_id);



                $userDetail = $this->User->getUserDetailsFromID($user_id);
                $wallet_total =  $this->walletTotal($user_id);
                $userDetail['User']['wallet'] = $wallet_total['total'];
                $userDetail['User']['total_all_time_coins'] = $wallet_total['gifts_receive'];
                $visit_profile_count = $this->ProfileVisit->getProfileVisitorsUnreadCount($user_id);
                if(!$visit_profile_count){
                    $visit_profile_count = 0;
                }
                $user_story = $this->Video->getUserStory($user_id,$one_day_before);
                $userDetail['User']['story'] = $user_story;
                $sender_user_details = $userDetail;
                $followers_count = $this->Follower->countFollowers($user_id);
                $following_count = $this->Follower->countFollowing($user_id);
                //$likes_count = $this->VideoLike->countUserAllVideoLikes($user_id);

                $video_count = $this->Video->getUserVideosCount($user_id);
                $userDetail['User']['followers_count'] = $followers_count;
                $userDetail['User']['following_count'] = $following_count;
                $userDetail['User']['likes_count'] = $likes_count;
                $userDetail['User']['video_count'] = $video_count;
                $userDetail['User']['profile_visit_count'] = $visit_profile_count;





            }


            if(isset($data['username'])){

                $user_id_details = $this->User->getUserDetailsFromUsername($data['username']);
                $other_user_id = $user_id_details['User']['id'];


                $video_ids = $this->Video->getUserVideosIDs($other_user_id);



                    $likes_count = $this->VideoLike->countLikesOnAllUserVideos($other_user_id);

                if($other_user_id < 1){
                    $username = $data['username'];

                    $user_id_details = $this->User->getUserDetailsFromUsername($username);

                    $other_user_id = $user_id_details['User']['id'];
                }
                $userDetail = $this->User->getUserDetailsFromID($other_user_id);
                $user_story = $this->Video->getUserStory($other_user_id,$one_day_before);
                $userDetail['User']['story'] = $user_story;
                $other_person_user_id = $other_user_id;
                $follower_details = $this->Follower->ifFollowing($user_id, $other_person_user_id);
                $following_details = $this->Follower->ifFollowing($other_person_user_id, $user_id);
                $if_blocked = $this->BlockUser->ifBlocked($user_id, $other_person_user_id);
                $if_other_blocked = $this->BlockUser->ifBlocked($other_person_user_id,$user_id);
                $followers_count = $this->Follower->countFollowers($other_user_id);
                $following_count = $this->Follower->countFollowing($other_user_id);

                $video_count = $this->Video->getUserVideosCount($other_user_id);
                $userDetail['User']['followers_count'] = $followers_count;
                $userDetail['User']['following_count'] = $following_count;
                $userDetail['User']['likes_count'] = $likes_count;
                $userDetail['User']['video_count'] = $video_count;
                $userDetail['User']['block'] = 0;
                if(count($follower_details) > 0 && count($following_details) > 0){

                    $userDetail['User']['button'] = "Friends";

                } else   if(count($follower_details) > 0 && count($following_details) < 1){

                    $userDetail['User']['button'] = "following";

                }else if (count($following_details) > 0){


                    $userDetail['User']['button'] = "follow back";
                }else{


                    $userDetail['User']['button'] = "follow";
                }

                if(count($if_blocked) > 0 ){

                    $userDetail['User']['block'] = 1;
                    $userDetail['User']['BlockUser'] = $if_blocked['BlockUser'];

                }

                if(count($if_other_blocked) > 0){


                    $userDetail['User']['block'] = 1;
                    $userDetail['User']['BlockUser'] = $if_other_blocked['BlockUser'];

                }

                if(count($follower_details) > 0){


                    $userDetail['User']['notification'] = $follower_details['Follower']['notification'];
                }else{

                    $userDetail['User']['notification'] = 0;

                }

                if($other_user_id != $user_id) {
                    $profile_visit['user_id'] = $other_user_id;
                    $profile_visit['visitor_id'] = $user_id;
                    $profile_visit['created'] = $created;

                    $this->ProfileVisit->save($profile_visit);
                }
            }




            $userDetail['User']['unread_notification'] = $notifications;



            $output['code'] = 200;

            $output['msg'] = $userDetail;

            echo json_encode($output);


            die();
        }
    }

    public function showVideosAgainstUserID(){

        $this->loadModel('Video');
        $this->loadModel('VideoLike');
        $this->loadModel('VideoFavourite');
        $this->loadModel('VideoComment');
        $this->loadModel('Follower');
        $this->loadModel('PlaylistVideo');
        $this->loadModel('RepostVideo');







        if ($this->request->isPost()) {
            $json = file_get_contents('php://input');
            $data = json_decode($json, TRUE);

            $user_id = 0;
            $starting_point = 0;


            $videos_private = array();
            $videos_public = array();
            if (isset($data['user_id'])) {

                $user_id = $data['user_id'];

            }

            if (isset($data['starting_point'])) {

                $starting_point = $data['starting_point'];

            }



            if($user_id > 0){

                $videos_public = $this->Video->getUserPublicVideos($user_id,$starting_point);
                $videos_private = $this->Video->getUserPrivateVideos($user_id,$starting_point);
            }



            if (isset($data['other_user_id'])) {


                $videos_public = $this->Video->getUserPublicVideos($data['other_user_id'],$starting_point);



            }


            if (count($videos_public) > 0) {


                foreach ($videos_public as $key => $video) {


                    if ($user_id > 0) {
                        $video_data['user_id'] = $user_id;
                        $video_data['video_id'] = $video['Video']['id'];
                        $video_like_detail = $this->VideoLike->ifExist($video_data);
                        $video_favourite_detail = $this->VideoFavourite->ifExist($video_data);
                        $repost_detail = $this->RepostVideo->ifExist($video_data);


                        if (count($repost_detail) > 0) {

                            $videos_public[$key]['Video']['repost'] = 1;

                        } else {

                            $videos_public[$key]['Video']['repost'] = 0;
                        }



                        if (count($video_like_detail) > 0) {

                            $videos_public[$key]['Video']['like'] = 1;


                        } else {

                            $videos_public[$key]['Video']['like'] = 0;
                        }

                        if (count($video_favourite_detail) > 0) {

                            $videos_public[$key]['Video']['favourite'] = 1;

                        } else {

                            $videos_public[$key]['Video']['favourite'] = 0;
                        }


                    } else {


                        $videos_public[$key]['Video']['like'] = 0;
                        $videos_public[$key]['Video']['favourite'] = 0;
                        $videos_public[$key]['Video']['repost'] = 0;


                    }


                    if (isset($data['other_user_id'])) {
                        $other_person_user_id = $data['other_user_id'];
                        $follower_details = $this->Follower->ifFollowing($user_id, $other_person_user_id);
                        $following_details = $this->Follower->ifFollowing($other_person_user_id, $user_id);
                        $followers_count = $this->Follower->countFollowers($other_person_user_id);
                        $following_count = $this->Follower->countFollowing($other_person_user_id);
                        $likes_count = $this->VideoLike->countUserAllVideoLikes($other_person_user_id);
                        $video_count = $this->Video->getUserVideosCount($other_person_user_id);
                        $videos_public[$key]['User']['followers_count'] = $followers_count;
                        $videos_public[$key]['User']['following_count'] = $following_count;
                        $videos_public[$key]['User']['likes_count'] = $likes_count;
                        $videos_public[$key]['User']['video_count'] = $video_count;
                        if (count($follower_details) > 0 && count($following_details) > 0) {

                            $videos_public[$key]['User']['button'] = "Friends";

                        } else if (count($follower_details) > 0 && count($following_details) < 1) {

                            $videos_public[$key]['User']['button'] = "following";

                        } else if (count($following_details) > 0) {


                            $videos_public[$key]['User']['button'] = "follow back";
                        } else {


                            $videos_public[$key]['User']['button'] = "follow";
                        }
                    }

                    $comment_count = $this->VideoComment->countComments($video['Video']['id']);
                    $video_likes_count = $this->VideoLike->countLikes($video['Video']['id']);
                    $video_fav_count = $this->VideoFavourite->getFavVideosCount($video['Video']['id']);
                    $videos_public[$key]['Video']['favourite_count'] = $video_fav_count;

                    $videos_public[$key]['Video']['comment_count'] = $comment_count;
                    $videos_public[$key]['Video']['like_count'] = $video_likes_count;



                    $playlist_details = $this->PlaylistVideo->getDetailsAgainstVideoID($video['Video']['id']);

                    if(count($playlist_details) > 0) {

                        $videos_public[$key]['Video']['PlaylistVideo'] = $playlist_details['PlaylistVideo'];
                        $videos_public[$key]['Video']['PlaylistVideo']['Playlist'] = $playlist_details['Playlist'];

                    }else{

                        $videos_public[$key]['Video']['PlaylistVideo']['id'] = 0;

                    }

                }





            }

            $output['code'] = 200;

            $output['msg']['public'] = $videos_public;
            $output['msg']['private'] = $videos_private;


            echo json_encode($output);

        }
    }


    public function showUserVideosTrendingAndRecent(){

        $this->loadModel('Video');








        if ($this->request->isPost()) {
            $json = file_get_contents('php://input');
            $data = json_decode($json, TRUE);









                $user_id = $data['user_id'];
                $start_datetime = $data['start_datetime'];
                $end_datetime = $data['end_datetime'];








                $recent_videos= $this->Video->getUserRecentVideos($user_id,$start_datetime,$end_datetime);
                $trending_videos= $this->Video->getUserTrendingVideos($user_id,$start_datetime,$end_datetime);
                $count= $this->Video->getUserVideosCount($user_id);















            $output['code'] = 200;

            $output['msg']['Recent'] = $recent_videos;
            $output['msg']['Trending'] = $trending_videos;
            $output['msg']['VideoCount'] = $count;


            echo json_encode($output);

        }
    }

    public function checkEmail(){

        $this->loadModel('User');




        if ($this->request->isPost()) {
            $json = file_get_contents('php://input');
            $data = json_decode($json, TRUE);

            $email = $data['email'];

            $email_count = $this->User->isEmailAlreadyExist($data['email']);

            if ($email_count > 0) {


                $output['code'] = 201;

                $output['msg'] = "An account is already registered with your email address.";


                echo json_encode($output);


                die();
            } else {


                $output['code'] = 200;

                $output['msg'] = "register";


                echo json_encode($output);


                die();


            }

        }
    }


    public function checkUsername(){

        $this->loadModel('User');




        if ($this->request->isPost()) {
            $json = file_get_contents('php://input');
            $data = json_decode($json, TRUE);

            $username = $data['username'];

            $details = $this->User->getUserDetailsFromUsername($username);


            if(count($details) < 1) {


                $output['code'] = 200;

                $output['msg'] = "do not exist";


                echo json_encode($output);


                die();
            }else{


                $output['code'] = 201;

                $output['msg'] = "already exist";


                echo json_encode($output);


                die();
            }

        }


    }

    public function showVideoComments(){

        $this->loadModel('VideoComment');
        $this->loadModel('VideoCommentLike');
        $this->loadModel('VideoCommentReplyLike');




        if ($this->request->isPost()) {
            $json = file_get_contents('php://input');
            $data = json_decode($json, TRUE);

            $video_id = $data['video_id'];
            $user_id = 0;
            if (isset($data['user_id'])) {

                $user_id = $data['user_id'];
            }

            $starting_point = 0;


            if (isset($data['starting_point'])) {

                $starting_point = $data['starting_point'];

            }

            $comments = $this->VideoComment->getVideoComments($video_id,$starting_point);

//pr($comments);
            if(count($comments) > 0) {
                foreach ($comments as $key => $comment) {


                    $comment_data['user_id'] = $user_id;
                    $comment_data['comment_id'] = $comment['VideoComment']['id'];

                    $video_like_detail = $this->VideoCommentLike->ifExist($comment_data);
                    $comments[$key]['VideoComment']['owner_like'] = 0;
                    $comment_data_owner['user_id'] = $comment['Video']['user_id'];
                    $comment_data_owner['comment_id'] = $comment['VideoComment']['id'];

                    $video_owner_like_detail = $this->VideoCommentLike->ifExist($comment_data_owner);

                    if (count($video_like_detail) > 0) {

                        $comments[$key]['VideoComment']['like'] = 1;

                    } else {

                        $comments[$key]['VideoComment']['like'] = 0;
                    }

                    if(count($video_owner_like_detail) > 0){

                        $comments[$key]['VideoComment']['owner_like'] = 1;
                    }
                    $video_comment_replies = $comment['VideoCommentReply'];

                    if(count($video_comment_replies) > 0){


                        foreach ($video_comment_replies  as $key2=>$comment_reply){



                            $comment_reply_data['user_id'] = $user_id;
                            $comment_reply_data['comment_reply_id'] = $comment_reply['id'];

                            $comment_reply_like_detail = $this->VideoCommentReplyLike->ifExist($comment_reply_data);

                            if (count($comment_reply_like_detail) > 0) {

                                $comments[$key]['VideoCommentReply'][$key2]['like'] = 1;

                            } else {

                                $comments[$key]['VideoCommentReply'][$key2]['like'] = 0;
                            }

                            $comment_data_owner['user_id'] = $comment['Video']['user_id'];
                            $comment_data_owner['comment_reply_id'] = $comment_reply['id'];
                            $video_owner_like_detail = $this->VideoCommentLike->ifExist($comment_data_owner);

                            if(count($video_owner_like_detail) > 0){

                                $comments[$key]['VideoCommentReply'][$key2]['owner_like'] = 1;
                            }

                            $like_count = $this->VideoCommentReplyLike->countLikes($comment_reply['id']);
                            $comments[$key]['VideoCommentReply'][$key2]['like_count'] = $like_count;
                        }
                    }



                    $like_count = $this->VideoCommentLike->countLikes($comment['VideoComment']['id']);
                    $comments[$key]['VideoComment']['like_count'] = $like_count;

                }


                $output['code'] = 200;

                $output['msg'] = $comments;


                echo json_encode($output);


                die();

            }else{

                Message::EMPTYDATA();
                die();

            }
        }


    }
    public function showReportReasons(){

        $this->loadModel('ReportReason');




        if ($this->request->isPost()) {
            $json = file_get_contents('php://input');
            $data = json_decode($json, TRUE);



            $details = $this->ReportReason->getAll();


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

    public function showTopics(){

        $this->loadModel('Topic');




        if ($this->request->isPost()) {
            $json = file_get_contents('php://input');
            $data = json_decode($json, TRUE);



            $details = $this->Topic->getAll();


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



    public function reportVideo()
    {


        $this->loadModel("ReportVideo");
        $this->loadModel("ReportReason");
        $this->loadModel("Video");



        if ($this->request->isPost()) {
            $json = file_get_contents('php://input');
            $data = json_decode($json, TRUE);


            $video_id = $data['video_id'];
            $user_id = $data['user_id'];
            $report_reason_id = $data['report_reason_id'];
            $description = $data['description'];
            $created = date('Y-m-d H:i:s', time());


            $report['video_id'] = $video_id;
            $report['user_id'] = $user_id;
            $report['report_reason_id'] = $report_reason_id;
            $report['description'] = $description;
            $report['created'] = $created;


            $video_details = $this->Video->getDetails($video_id);

            $report_reason_details =  $this->ReportReason->getDetails($report_reason_id);

            if (count($video_details) > 0) {

                if(count($report_reason_details) > 0){

                    $report['report_reason_title'] = $report_reason_details['ReportReason']['title'];

                }

                $this->ReportVideo->save($report);






                $id = $this->ReportVideo->getInsertID();
                $details = $this->ReportVideo->getDetails($id);


                $output['code'] = 200;
                $output['msg'] = $details;
                echo json_encode($output);


                die();
            }else{

                $output['code'] = 201;
                $output['msg'] = "video not available";
                echo json_encode($output);


                die();
            }


        }
    }


    public function NotInterestedVideo()
    {


        $this->loadModel("NotInterestedVideo");




        if ($this->request->isPost()) {
            $json = file_get_contents('php://input');
            $data = json_decode($json, TRUE);


            $video_id = $data['video_id'];
            $user_id = $data['user_id'];

            $created = date('Y-m-d H:i:s', time());


            $not_interested['video_id'] = $video_id;
            $not_interested['user_id'] = $user_id;

            $not_interested['created'] = $created;


            $details = $this->NotInterestedVideo->getDetails($user_id,$video_id);



            if (count($details) < 1) {

                $this->NotInterestedVideo->save($not_interested);






                $id = $this->NotInterestedVideo->getInsertID();
                $details = $this->NotInterestedVideo->getDetails($id);


                $output['code'] = 200;
                $output['msg'] = $details;
                echo json_encode($output);


                die();
            }else{

                $output['code'] = 201;
                $output['msg'] = "already added";
                echo json_encode($output);


                die();
            }


        }
    }

    public function showUserLikedVideos(){

        $this->loadModel('VideoLike');
        $this->loadModel('Follower');
        $this->loadModel('Video');
        $this->loadModel('VideoComment');
        $this->loadModel('VideoFavourite');
        $this->loadModel('RepostVideo');





        if ($this->request->isPost()) {
            $json = file_get_contents('php://input');
            $data = json_decode($json, TRUE);

            $user_id = $data['user_id'];
            $starting_point = 0;


            if (isset($data['starting_point'])) {

                $starting_point = $data['starting_point'];

            }
            if(isset($data['other_user_id'])){
                $videos = $this->VideoLike->getUserAllVideoLikes($data['other_user_id'],$starting_point);

            }else{

                $videos = $this->VideoLike->getUserAllVideoLikes($data['user_id'],$starting_point);

            }

            if(count($videos) > 0) {
                foreach ($videos as $key => $video) {


                    $video_likes_count = $this->VideoLike->countLikes($video['Video']['id']);
                    $videos[$key]['Video']['like_count'] = $video_likes_count;
                    $videos[$key]['Video']['like'] = 1;


                    $video_comment_count = $this->VideoComment->countComments($video['Video']['id']);

                    $videos[$key]['Video']['comment_count'] = $video_comment_count;


                    $video_fav_count = $this->VideoFavourite->getFavVideosCount($video['Video']['id']);
                    $videos[$key]['Video']['favourite_count'] = $video_fav_count;
                    $video_data['user_id'] = $user_id;
                    $video_data['video_id'] = $video['Video']['id'];

                    $video_favourite_detail = $this->VideoFavourite->ifExist($video_data);

                    if (count($video_favourite_detail) > 0) {

                        $videos[$key]['Video']['favourite'] = 1;

                    } else {

                        $videos[$key]['Video']['favourite'] = 0;
                    }


                    $repost_detail = $this->RepostVideo->ifExist($video_data);


                    if (count($repost_detail) > 0) {

                        $videos[$key]['Video']['repost'] = 1;

                    } else {

                        $videos[$key]['Video']['repost'] = 0;
                    }

                    if (isset($data['other_user_id'])) {
                        $other_person_user_id = $data['other_user_id'];
                        $follower_details = $this->Follower->ifFollowing($user_id, $other_person_user_id);
                        $following_details = $this->Follower->ifFollowing($other_person_user_id, $user_id);
                        $followers_count = $this->Follower->countFollowers($other_person_user_id);
                        $following_count = $this->Follower->countFollowing($other_person_user_id);
                        $likes_count = $this->VideoLike->countUserAllVideoLikes($other_person_user_id);
                        $video_count = $this->Video->getUserVideosCount($other_person_user_id);
                        $videos[$key]['User']['followers_count'] = $followers_count;
                        $videos[$key]['User']['following_count'] = $following_count;
                        $videos[$key]['User']['likes_count'] = $likes_count;
                        $videos[$key]['User']['video_count'] = $video_count;


                        $video_like_detail = $this->VideoLike->ifExist($video_data);

                        if (count($video_like_detail) > 0) {

                            $videos[$key]['Video']['like'] = 1;

                        } else {

                            $videos[$key]['Video']['like'] = 0;
                        }
                        if (count($follower_details) > 0 && count($following_details) > 0) {

                            $videos[$key]['User']['button'] = "Friends";

                        } else if (count($follower_details) > 0 && count($following_details) < 1) {

                            $videos[$key]['User']['button'] = "following";

                        } else if (count($following_details) > 0) {


                            $videos[$key]['User']['button'] = "follow back";
                        } else {


                            $videos[$key]['User']['button'] = "follow";
                        }
                    }
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


    public function showUserRepostedVideos(){

        $this->loadModel('VideoLike');
        $this->loadModel('Follower');
        $this->loadModel('Video');
        $this->loadModel('VideoFavourite');
        $this->loadModel('VideoComment');
        $this->loadModel('RepostVideo');





        if ($this->request->isPost()) {
            $json = file_get_contents('php://input');
            $data = json_decode($json, TRUE);

            $user_id = $data['user_id'];
            $starting_point = 0;


            if (isset($data['starting_point'])) {

                $starting_point = $data['starting_point'];

            }
            $videos = $this->RepostVideo->getAllRespostedVideos($user_id,$starting_point);
            if(count($videos) > 0) {
                foreach ($videos as $key => $video) {


                    $video_likes_count = $this->VideoLike->countLikes($video['Video']['id']);
                    $videos[$key]['Video']['like_count'] = $video_likes_count;
                    $videos[$key]['Video']['like'] = 1;


                    $video_comment_count = $this->VideoComment->countComments($video['Video']['id']);

                    $videos[$key]['Video']['comment_count'] = $video_comment_count;


                    $video_fav_count = $this->VideoFavourite->getFavVideosCount($video['Video']['id']);
                    $videos[$key]['Video']['favourite_count'] = $video_fav_count;
                    $video_data['user_id'] = $user_id;
                    $video_data['video_id'] = $video['Video']['id'];

                    $video_favourite_detail = $this->VideoFavourite->ifExist($video_data);

                    if (count($video_favourite_detail) > 0) {

                        $videos[$key]['Video']['favourite'] = 1;

                    } else {

                        $videos[$key]['Video']['favourite'] = 0;
                    }




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


    public function showVideoDetail(){

        $this->loadModel('Video');
        $this->loadModel('VideoLike');
        $this->loadModel('VideoFavourite');
        $this->loadModel('VideoComment');
        $this->loadModel('Follower');
        $this->loadModel('RepostVideo');




        if ($this->request->isPost()) {
            $json = file_get_contents('php://input');
            $data = json_decode($json, TRUE);

            $video_id = $data['video_id'];
            $user_id = 0;

            if(isset($data['user_id'])){

                $user_id = $data['user_id'];

            }
            $video_detail = $this->Video->getDetails($video_id);



            $duet_video_id = $video_detail['Video']['duet_video_id'];
            //duet
            if ($duet_video_id > 0) {
                $video_detail_duet = $this->Video->getDetails($duet_video_id);
                if(count($video_detail_duet) > 0){
                    $video_detail['Video']['duet'] = $video_detail_duet;
                }
            }
            if($user_id > 0) {
                $video_data['user_id'] = $user_id;
                $video_data['video_id'] = $video_detail['Video']['id'];
                $video_like_detail = $this->VideoLike->ifExist($video_data);

                $video_favourite_detail = $this->VideoFavourite->ifExist($video_data);

                if (count($video_like_detail) > 0) {

                    $video_detail['Video']['like'] = 1;

                } else {

                    $video_detail['Video']['like'] = 0;
                }

                if (count($video_favourite_detail) > 0) {

                    $video_detail['Video']['favourite'] = 1;

                } else {

                    $video_detail['Video']['favourite'] = 0;
                }

                $repost_detail = $this->RepostVideo->ifExist($video_data);

                if (count($repost_detail) > 0) {

                    $video_detail['Video']['repost'] = 1;

                } else {

                    $video_detail['Video']['repost'] = 0;
                }

                $follower_details = $this->Follower->ifFollowing($user_id, $video_detail['Video']['user_id']);
                $following_details = $this->Follower->ifFollowing($video_detail['Video']['user_id'], $user_id);
                if(count($follower_details) > 0 && count($following_details) > 0){

                    $video_detail['User']['button'] = "Friends";

                } else   if(count($follower_details) > 0 && count($following_details) < 1){

                    $video_detail['User']['button'] = "following";

                }else if (count($following_details) > 0){


                    $video_detail['User']['button'] = "follow back";
                }else{


                    $video_detail['User']['button'] = "follow";
                }
            }else{


                $video_detail['Video']['like'] = 0;
                $video_detail['Video']['favourite'] = 0;



            }

            $video_like_count = $this->VideoLike->countLikes($video_detail['Video']['id']);
            $video_comment_count = $this->VideoComment->countComments($video_detail['Video']['id']);
            $video_fav_count = $this->VideoFavourite->getFavVideosCount($video_detail['Video']['id']);
            $video_detail['Video']['favourite_count'] = $video_fav_count;
            $video_detail['Video']['like_count'] = $video_like_count;
            $video_detail['Video']['comment_count'] = $video_comment_count;



            $output['code'] = 200;

            $output['msg'] = $video_detail;


            echo json_encode($output);


            die();


        }


    }

    public function showDeliveryAddresses()
    {

        $this->loadModel('DeliveryAddress');



        if ($this->request->isPost()) {


            $json = file_get_contents('php://input');
            $data = json_decode($json, TRUE);


            $user_id   = $data['user_id'];
            $addresses = $this->DeliveryAddress->getUserDeliveryAddresses($user_id);


            $output['code'] = 200;
            $output['msg']  = $addresses;

            echo json_encode($output);
            die();


        }

    }

    public function deleteDeliveryAddress()
    {

        $this->loadModel('DeliveryAddress');
        // $this->loadModel("RestaurantRating");

        if ($this->request->isPost()) {
            $json = file_get_contents('php://input');
            $data = json_decode($json, TRUE);

            $id      = $data['id'];
            $user_id = $data['user_id'];
            $this->DeliveryAddress->query('SET FOREIGN_KEY_CHECKS=0');
            $this->DeliveryAddress->id = $id;
            if ($this->DeliveryAddress->delete()) {


                $addresses = $this->DeliveryAddress->getUserDeliveryAddresses($user_id);


                $output['code'] = 200;
                $output['msg']  = $addresses;
                echo json_encode($output);
                die();
                //$this->RiderTiming->deleteAll(array('upvote_question_id' => $upvote_question_id), false);
            } else {

                Message::ALREADYDELETED();
                die();


            }
        }
    }


    public function addDeliveryAddress()
    {


        $this->loadModel("DeliveryAddress");
        $this->loadModel("User");



        if ($this->request->isPost()) {


            $json = file_get_contents('php://input');
            $data = json_decode($json, TRUE);




            if(isset($data['street'])){
                $street      = $data['street'];
                $address['street']       = $street;
            }

            if(isset($data['lat'])){
                $lat      = $data['lat'];
                $address['lat']       = $lat;
            }

            if(isset($data['long'])){
                $long      = $data['long'];
                $address['long']       = $long;
            }

            if(isset($data['apartment'])){
                $apartment   = $data['apartment'];
                $address['apartment']    = $apartment;
            }

            if(isset($data['user_id'])){
                $user_id     = $data['user_id'];
                $address['user_id']       = $user_id;
            }

            if(isset($data['city'])){
                $city        = $data['city'];
                $address['city']       = $city;
            }


            if(isset($data['state'])){
                $state       = $data['state'];
                $address['state']       = $state;
            }


            if(isset($data['zip'])){
                $zip         = $data['zip'];
                $address['zip']       = $zip;
            }

            if(isset($data['country_id'])){
                $country_id     = $data['country_id'];
                $address['country_id']       = $country_id;
            }


            if(isset($data['default'])){
                $default       = $data['default'];
                $address['default']       = $default;
            }

            //update
            if (isset($data['id'])) {

                $id                = $data['id'];
                $this->DeliveryAddress->id = $id;
                $this->DeliveryAddress->save($address);

                $details    = $this->DeliveryAddress->getDetails($id);
                $output['code'] = 200;
                $output['msg']  = $details;
                echo json_encode($output);


                die();
            }
                if ($this->DeliveryAddress->save($address)) {


                    $id = $this->DeliveryAddress->getInsertID();


                    $details = $this->DeliveryAddress->getDetails($id);

                    //CustomEmail::welcomeStudentEmail($email);
                    $output['code'] = 200;

                    $output['msg'] = $details;
                    echo json_encode($output);

                    die();

                } else {


                    echo Message::DATASAVEERROR();
                    die();
                }


        }


    }


    public function showVideoAnalytics(){

        $this->loadModel('Video');
        $this->loadModel('VideoLike');
        $this->loadModel('VideoFavourite');
        $this->loadModel('VideoComment');
        $this->loadModel('VideoCommentReply');
        $this->loadModel('VideoWatch');





        if ($this->request->isPost()) {
            $json = file_get_contents('php://input');
            $data = json_decode($json, TRUE);

            $video_id = $data['video_id'];
            $user_id = 0;



                //$user_id = $data['user_id'];


            $video_detail = $this->Video->getDetails($video_id);



            if(count($video_detail) > 0){

                $play_count = $this->VideoWatch->countWatchVideosTotal($video_id);
                $like_count = $this->VideoLike->countLikes($video_id);
                $video_comments =   $this->VideoComment->countComments($video_id);
                $video_count_comments_reply =   $this->VideoCommentReply->countComments($video_id);
                $fav_count =   $this->VideoFavourite->getFavVideosCount($video_id);

                $video_detail['Video']['play_count'] = $play_count;
                $video_detail['Video']['like_count'] = $like_count;
                $video_detail['Video']['comment_count'] = $video_comments+$video_count_comments_reply;
                $video_detail['Video']['favourite_count'] = $fav_count;

                $output['code'] = 200;

                $output['msg'] = $video_detail;


                echo json_encode($output);


                die();


            }else{

                Message::EmptyDATA();
                die();
            }





        }


    }


    public function showVideoDetailAd(){

        $this->loadModel('Video');
        $this->loadModel('VideoLike');
        $this->loadModel('VideoFavourite');
        $this->loadModel('VideoComment');
        $this->loadModel('Follower');
        $this->loadModel('RepostVideo');




        if ($this->request->isPost()) {
            $json = file_get_contents('php://input');
            $data = json_decode($json, TRUE);


            $user_id = 0;

            if(isset($data['user_id'])){

                $user_id = $data['user_id'];

            }

            $video_detail = $this->Video->getPromotedVideo();

            if(count($video_detail) > 0) {
                $duet_video_id = $video_detail['Video']['duet_video_id'];
                //duet
                if ($duet_video_id > 0) {
                    $video_detail_duet = $this->Video->getDetails($duet_video_id);
                    if (count($video_detail_duet) > 0) {
                        $video_detail['Video']['duet'] = $video_detail_duet;
                    }
                }
                if ($user_id > 0) {
                    $video_data['user_id'] = $user_id;
                    $video_data['video_id'] = $video_detail['Video']['id'];
                    $video_like_detail = $this->VideoLike->ifExist($video_data);
                    $video_favourite_detail = $this->VideoFavourite->ifExist($video_data);

                    if (count($video_like_detail) > 0) {

                        $video_detail['Video']['like'] = 1;

                    } else {

                        $video_detail['Video']['like'] = 0;
                    }

                    if (count($video_favourite_detail) > 0) {

                        $video_detail['Video']['favourite'] = 1;

                    } else {

                        $video_detail['Video']['favourite'] = 0;
                    }

                    $repost_detail = $this->RepostVideo->ifExist($video_data);

                    if (count($repost_detail) > 0) {

                        $video_detail['Video']['repost'] = 1;

                    } else {

                        $video_detail['Video']['repost'] = 0;
                    }

                    $follower_details = $this->Follower->ifFollowing($user_id, $video_detail['Video']['user_id']);
                    $following_details = $this->Follower->ifFollowing($video_detail['Video']['user_id'], $user_id);

                    if (count($follower_details) > 0 && count($following_details) > 0) {

                        $video_detail['User']['button'] = "Friends";

                    } else if (count($follower_details) > 0 && count($following_details) < 1) {

                        $video_detail['User']['button'] = "following";

                    } else if (count($following_details) > 0) {


                        $video_detail['User']['button'] = "follow back";
                    } else {


                        $video_detail['User']['button'] = "follow";
                    }
                } else {

                    $video_detail['User']['button'] = "follow";
                    $video_detail['Video']['like'] = 0;
                    $video_detail['Video']['favourite'] = 0;


                }

                $video_like_count = $this->VideoLike->countLikes($video_detail['Video']['id']);
                $video_comment_count = $this->VideoComment->countComments($video_detail['Video']['id']);
                $video_detail['Video']['like_count'] = $video_like_count;
                $video_detail['Video']['comment_count'] = $video_comment_count;
                $video_fav_count = $this->VideoFavourite->getFavVideosCount($video_detail['Video']['id']);
                $video_detail['Video']['favourite_count'] = $video_fav_count;

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

    public function reportUser()
    {


        $this->loadModel("ReportUser");
        $this->loadModel("User");
        $this->loadModel("ReportReason");




        if ($this->request->isPost()) {
            $json = file_get_contents('php://input');
            $data = json_decode($json, TRUE);


            $report_user_id = $data['report_user_id'];
            $user_id = $data['user_id'];
            $report_reason_id = $data['report_reason_id'];
            $description = $data['description'];
            $created = date('Y-m-d H:i:s', time());


            $report['report_user_id'] = $report_user_id;
            $report['user_id'] = $user_id;
            $report['report_reason_id'] = $report_reason_id;
            $report['description'] = $description;
            $report['created'] = $created;


            $details = $this->User->getUserDetailsFromID($report_user_id);


            $report_reason_details =  $this->ReportReason->getDetails($report_reason_id);




            if (count($details) > 0) {

                if(count($report_reason_details) > 0){

                    $report['report_reason_title'] = $report_reason_details['ReportReason']['title'];

                }
                $this->ReportUser->save($report);






                $id = $this->ReportUser->getInsertID();
                $details = $this->ReportUser->getDetails($id);


                $output['code'] = 200;
                $output['msg'] = $details;
                echo json_encode($output);


                die();
            }else{

                $output['code'] = 201;
                $output['msg'] = "user not available";
                echo json_encode($output);


                die();
            }


        }
    }

    public function reportProduct()
    {


        $this->loadModel("ReportProduct");
        $this->loadModel("User");
        $this->loadModel("ReportReason");
        $this->loadModel("Product");




        if ($this->request->isPost()) {
            $json = file_get_contents('php://input');
            $data = json_decode($json, TRUE);


            $product_id = $data['product_id'];
            $user_id = $data['user_id'];
            $report_reason_id = $data['report_reason_id'];
            $description = $data['description'];
            $created = date('Y-m-d H:i:s', time());


            $report['product_id'] = $product_id;
            $report['user_id'] = $user_id;
            $report['report_reason_id'] = $report_reason_id;
            $report['description'] = $description;
            $report['created'] = $created;


            $details = $this->Product->getDetails($product_id);


            $report_reason_details =  $this->ReportReason->getDetails($report_reason_id);




            if (count($details) > 0) {

                if(count($report_reason_details) > 0){

                    $report['report_reason_title'] = $report_reason_details['ReportReason']['title'];

                }
                $this->ReportProduct->save($report);






                $id = $this->ReportProduct->getInsertID();
                $details = $this->ReportProduct->getDetails($id);


                $output['code'] = 200;
                $output['msg'] = $details;
                echo json_encode($output);


                die();
            }else{

                $output['code'] = 201;
                $output['msg'] = "user not available";
                echo json_encode($output);


                die();
            }


        }
    }


    public function updateVideoDetail()
    {


        $this->loadModel("Video");





        if ($this->request->isPost()) {
            $json = file_get_contents('php://input');
            $data = json_decode($json, TRUE);


            $video_id = $data['video_id'];
            $video['privacy_type'] = $data['privacy_type'];
            $video['allow_duet'] = $data['allow_duet'];
            $video['allow_comments'] = $data['allow_comments'];


            $video_detail = $this->Video->getDetails($video_id);



            if (count($video_detail) > 0) {
                $this->Video->id = $video_id;
                $this->Video->save($video);






                $video_detail = $this->Video->getDetails($video_id);


                $output['code'] = 200;
                $output['msg'] = $video_detail;
                echo json_encode($output);


                die();
            }else{

                $output['code'] = 201;
                $output['msg'] = "user not available";
                echo json_encode($output);


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


            $sound['name'] = $data['name'];
            $sound['description'] = $data['description'];
            $sound['user_id'] = $data['user_id'];
            $sound['uploaded_by'] = "user";
            $sound['created'] = date('Y-m-d H:i:s', time());


            if (isset($data['id'])) {
                $id = $data['id'];


                $this->Sound->id = $id;
                $this->Sound->save($sound);
                $details = $this->Sound->getDetails($id);
                $output['code'] = 200;

                $output['msg'] = $details;


                echo json_encode($output);


                die();

            }
            if (isset($data['audio'])) {




                if (method_exists('Premium', 'fileUploadToS3')) {

                    $base64_audio = $data['audio'];
                    $base64_decode_audio  = base64_decode($base64_audio);

                    $base64_image = $data['thum'];
                    $base64_decode_image  = base64_decode($base64_image);



                    $result_audio = Premium::fileUploadToS3($base64_decode_audio,"mp3");
                    $result_image = Premium::fileUploadToS3($base64_decode_image,"jpeg");




                    if($result['code'] = 200){
                        $audio_file_duration = Premium::getDurationofAudioFile($result_audio['msg']);
                        $sound['audio'] = $result_audio['msg'];
                        $sound['thum'] = $result_image['msg'];
                        $sound['duration'] = $audio_file_duration;

                    }else{

                        $output['code'] = 201;

                        $output['msg'] = $result['msg'];


                        echo json_encode($output);


                        die();

                    }
                } else {

                    $output['code'] = 201;

                    $output['msg'] = "It seems like you do not have premium files. submit ticket on yeahhelp.com for support";


                    echo json_encode($output);


                    die();
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





    }



    public function showAllNotifications(){

        $this->loadModel('Notification');
        $this->loadModel('Follower');




        if ($this->request->isPost()) {
            $json = file_get_contents('php://input');
            $data = json_decode($json, TRUE);

            $user_id = $data['user_id'];
            $starting_point = $data['starting_point'];


            $notifications = $this->Notification->getUserNotifications($user_id, $starting_point);



            if (count($notifications) > 0) {

                foreach($notifications as $key=>$val){

                    $follower_details = $this->Follower->ifFollowing($user_id, $val['Notification']['sender_id']);
                    $following_details = $this->Follower->ifFollowing($user_id, $val['Notification']['sender_id']);
                    if(count($follower_details) > 0 && count($following_details) > 0){

                        $notifications[$key]['Sender']['button'] = "Friends";

                    } else   if(count($follower_details) > 0 && count($following_details) < 1){

                        $notifications[$key]['Sender']['button'] = "following";

                    }else if (count($following_details) > 0){


                        $notifications[$key]['Sender']['button'] = "follow back";
                    }else{


                        $notifications[$key]['Sender']['button'] = "follow";
                    }

                }
                $output['code'] = 200;

                $output['msg'] = $notifications;


                echo json_encode($output);


                die();

            } else {

                Message::EMPTYDATA();
                die();

            }
        }


    }



    public function showSounds(){

        $this->loadModel('Sound');
        $this->loadModel('Video');
        $this->loadModel('SoundFavourite');
        $this->loadModel('SoundSection');




        if ($this->request->isPost()) {
            $json = file_get_contents('php://input');
            $data = json_decode($json, TRUE);

            $starting_point = $data['starting_point'];

            $user_id = 0;

            if(isset($data['user_id'])){

                $user_id = $data['user_id'];

            }

            $sound_section = $this->SoundSection->getAll($starting_point);






            //pr($sound_section);
            // pr($sound_section);

            $i=0;



            foreach($sound_section as $key=>$section) {




                if(count($sound_section[$i]['Sound']) > 0){


                    foreach ($section['Sound'] as $key2 => $sound) {

                       $videos_count =  $this->Video->getVideosCountAgainstSound($sound['id']);

                        $sound_section[$i]['Sound'][$key2]['total_videos'] = $videos_count;
                        if ($user_id > 0) {

                            $sound_data['user_id'] = $user_id;
                            $sound_data['sound_id'] = $sound['id'];



                            $sound_favourite_detail = $this->SoundFavourite->ifExist($sound_data);


                            if (count($sound_favourite_detail) > 0) {

                                $sound_section[$i]['Sound'][$key2]['favourite'] = 1;

                            } else {

                                $sound_section[$i]['Sound'][$key2]['favourite'] = 0;
                            }
                        } else {

                            $sound_section[$i]['Sound'][$key2]['favourite'] = 0;
                        }


                    }
                    $i++;
                }else{

                    unset($sound_section[$key]);
                    $sound_section = array_values($sound_section);


                }
            }





            $output['code'] = 200;

            $output['msg'] = $sound_section;



            echo json_encode($output);


            die();
        }else{


            Message::EMPTYDATA();
            die();
        }




    }

    public function showSoundsAgainstSection(){

        $this->loadModel('Sound');
        $this->loadModel('SoundFavourite');
        $this->loadModel('Video');





        if ($this->request->isPost()) {
            $json = file_get_contents('php://input');
            $data = json_decode($json, TRUE);

            $starting_point = $data['starting_point'];
            $sound_section_id = $data['sound_section_id'];

            $user_id = 0;

            if(isset($data['user_id'])){

                $user_id = $data['user_id'];

            }

            $sounds = $this->Sound->getSoundsAgainstSection($sound_section_id,$starting_point);


            //pr($sound_section);
            // pr($sound_section);

            foreach($sounds as $key=>$sound) {






                if ($user_id > 0) {

                    $sound_data['user_id'] = $user_id;
                    $sound_data['sound_id'] = $sound['Sound']['id'];

                    $sound_favourite_detail = $this->SoundFavourite->ifExist($sound_data);

                    $videos_count =  $this->Video->getVideosCountAgainstSound($sound['Sound']['id']);

                    $sounds[$key]['Sound']['total_videos'] = $videos_count;

                    if (count($sound_favourite_detail) > 0) {

                        $sounds[$key]['Sound']['favourite'] = 1;

                    } else {

                        $sounds[$key]['Sound']['favourite'] = 0;
                    }
                }else{

                    $sounds[$key]['Sound']['favourite'] = 0;
                }







            }





            $output['code'] = 200;

            $output['msg'] = $sounds;



            echo json_encode($output);


            die();
        }else{


            Message::EMPTYDATA();
            die();
        }




    }

    public function searchSoundsAgainstSection(){

        $this->loadModel('Sound');
        $this->loadModel('SoundFavourite');





        if ($this->request->isPost()) {
            $json = file_get_contents('php://input');
            $data = json_decode($json, TRUE);

            $starting_point = $data['starting_point'];
            $sound_section_id = $data['sound_section_id'];
            $keyword = $data['keyword'];

            $user_id = 0;

            if (isset($data['user_id'])) {

                $user_id = $data['user_id'];

            }

            $sounds = $this->Sound->getSearchSoundsAgainstSection($keyword, $sound_section_id, $starting_point);


            //pr($sound_section);
            // pr($sound_section);

            if (count($sounds) > 0) {
                foreach ($sounds as $key => $sound) {


                    if ($user_id > 0) {

                        $sound_data['user_id'] = $user_id;
                        $sound_data['sound_id'] = $sound['Sound']['id'];

                        $sound_favourite_detail = $this->SoundFavourite->ifExist($sound_data);


                        if (count($sound_favourite_detail) > 0) {

                            $sounds[$key]['Sound']['favourite'] = 1;

                        } else {

                            $sounds[$key]['Sound']['favourite'] = 0;
                        }
                    } else {

                        $sounds[$key]['Sound']['favourite'] = 0;
                    }


                }


                $output['code'] = 200;

                $output['msg'] = $sounds;


                echo json_encode($output);


                die();

            } else {


                Message::EMPTYDATA();
                die();
            }

        }


    }
    public function showVideosAgainstHashtag(){

        $this->loadModel('HashtagVideo');
        $this->loadModel('HashtagFavourite');
        $this->loadModel('Hashtag');
        $this->loadModel('Video');
        $this->loadModel('VideoLike');
        $this->loadModel('VideoFavourite');
        $this->loadModel('VideoComment');
        $this->loadModel('Follower');
        $this->loadModel('RepostVideo');




        if ($this->request->isPost()) {
            $json = file_get_contents('php://input');
            $data = json_decode($json, TRUE);
            $user_id = 0;

            if(isset($data['user_id'])){

                $user_id = $data['user_id'];

            }

            $starting_point = 0;
            if (isset($data['starting_point'])) {

                $starting_point = $data['starting_point'];

            }

            $hashtag = $data['hashtag'];

            $hashtag_details = $this->Hashtag->ifExist($hashtag);

            if(count($hashtag_details) > 0) {
                $videos = $this->HashtagVideo->getHashtagVideosWithLimit($hashtag_details['Hashtag']['id'],$starting_point);

                $hashtag_views = $this->HashtagVideo->countHashtagViews($hashtag_details['Hashtag']['id']);

            }else{



                Message::EMPTYDATA();
                die();
            }

            if(count($videos) > 0) {


                foreach($videos as $key=>$video){



                    if($user_id > 0) {
                        $video_data['user_id'] = $user_id;
                        $video_data['video_id'] = $video['Video']['id'];
                        $video_data['hashtag_id'] = $hashtag_details['Hashtag']['id'];
                        $video_like_detail = $this->VideoLike->ifExist($video_data);
                        $video_favourite_detail = $this->VideoFavourite->ifExist($video_data);
                        $hashtag_favourite_detail = $this->HashtagFavourite->ifExist($video_data);

                        if (count($video_like_detail) > 0) {

                            $videos[$key]['Video']['like'] = 1;

                        } else {

                            $videos[$key]['Video']['like'] = 0;
                        }

                        if (count($video_favourite_detail) > 0) {

                            $videos[$key]['Video']['favourite'] = 1;

                        } else {

                            $videos[$key]['Video']['favourite'] = 0;
                        }

                        if (count($hashtag_favourite_detail) > 0) {

                            $videos[$key]['Hashtag']['favourite'] = 1;

                        } else {

                            $videos[$key]['Hashtag']['favourite'] = 0;
                        }

                        $repost_detail = $this->RepostVideo->ifExist($video_data);

                        if (count($repost_detail) > 0) {

                            $videos[$key]['Video']['repost'] = 1;

                        } else {

                            $videos[$key]['Video']['repost'] = 0;
                        }


                        $follower_details = $this->Follower->ifFollowing($user_id, $video['Video']['user_id']);
                        $following_details = $this->Follower->ifFollowing($video['Video']['user_id'], $user_id);
                        if(count($follower_details) > 0 && count($following_details) > 0){

                            $videos[$key]['Video']['User']['button'] = "Friends";

                        } else   if(count($follower_details) > 0 && count($following_details) < 1){

                            $videos[$key]['Video']['User']['button'] = "following";

                        }else if (count($following_details) > 0){


                            $videos[$key]['Video']['User']['button'] = "follow back";
                        }else{


                            $videos[$key]['Video']['User']['button'] = "follow";
                        }
                    }else{


                        $videos[$key]['Video']['like'] = 0;
                        $videos[$key]['Video']['favourite'] = 0;
                        $videos[$key]['Video']['User']['button'] = "follower";
                        $videos[$key]['Hashtag']['favourite'] = 0;


                    }

                    $comment_count = $this->VideoComment->countComments($video['Video']['id']);
                    $video_likes_count = $this->VideoLike->countLikes($video['Video']['id']);


                    $videos[$key]['Video']['comment_count'] = $comment_count;
                    $videos[$key]['Video']['like_count'] = $video_likes_count;
                    $video_fav_count = $this->VideoFavourite->getFavVideosCount($video['Video']['id']);
                    $videos[$key]['Video']['favourite_count'] = $video_fav_count;


                }
                $hashtag_videos_count = $this->HashtagVideo->countHashtagVideos($hashtag_details['Hashtag']['id']);



                $output['code'] = 200;

                $output['msg'] = $videos;
                $output['views'] = $hashtag_views[0]['total_sum'];
                $output['videos_count'] = $hashtag_videos_count;


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
        $this->loadModel('PlaylistVideo');
        $this->loadModel('SoundFavourite');
        $this->loadModel('RepostVideo');




        if ($this->request->isPost()) {
            $json = file_get_contents('php://input');
            $data = json_decode($json, TRUE);
            $user_id = 0;
            $device_id = 0;

            if(isset($data['device_id'])){
                $device_id = $data['device_id'];

            }
            $starting_point = $data['starting_point'];
            $sound_id = $data['sound_id'];
            $sound_fav = 0;
            $videos = array();

            if(isset($data['user_id'])){

                $user_id = $data['user_id'];

                $sound_data['user_id'] = $user_id;
                $sound_data['sound_id'] = $sound_id;


                $sound_favourite_detail = $this->SoundFavourite->ifExist($sound_data);

                if (count($sound_favourite_detail) > 0) {

                    $sound_fav =  1;

                }

            }



            if(isset($data['web'])) {


                $videos = $this->Video->getVideosAgainstSoundIDWeb($user_id, $device_id, $starting_point, $sound_id);


            }else{

                $videos = $this->Video->getVideosAgainstSoundID($user_id, $device_id, $starting_point, $sound_id);


            }
            if(count($videos) > 0) {


                foreach($videos as $key=>$video){



                    if($user_id > 0) {
                        $video_data['user_id'] = $user_id;
                        $video_data['video_id'] = $video['Video']['id'];
                        $video_like_detail = $this->VideoLike->ifExist($video_data);
                        $video_favourite_detail = $this->VideoFavourite->ifExist($video_data);

                        if (count($video_like_detail) > 0) {

                            $videos[$key]['Video']['like'] = 1;

                        } else {

                            $videos[$key]['Video']['like'] = 0;
                        }
                        $video_favourite_detail = $this->VideoFavourite->ifExist($video_data);

                        if (count($video_favourite_detail) > 0) {

                            $videos[$key]['Video']['favourite'] = 1;

                        } else {

                            $videos[$key]['Video']['favourite'] = 0;
                        }

                        $repost_detail = $this->RepostVideo->ifExist($video_data);

                        if (count($repost_detail) > 0) {

                            $videos[$key]['Video']['repost'] = 1;

                        } else {

                            $videos[$key]['Video']['repost'] = 0;
                        }
                    }else{


                        $videos[$key]['Video']['like'] = 0;
                        $videos[$key]['Video']['favourite'] = 0;
                        $videos[$key]['Video']['repost'] = 0;


                    }

                    $playlist_details = $this->PlaylistVideo->getDetailsAgainstVideoID($video['Video']['id']);

                    if(count($playlist_details) > 0) {

                        $videos[$key]['Video']['PlaylistVideo'] = $playlist_details['PlaylistVideo'];
                        $videos[$key]['Video']['PlaylistVideo']['Playlist'] = $playlist_details['Playlist'];

                    }else{

                        $videos[$key]['Video']['PlaylistVideo']['id'] = 0;

                    }

                    $comment_count = $this->VideoComment->countComments($video['Video']['id']);
                    $video_likes_count = $this->VideoLike->countLikes($video['Video']['id']);
                    $video_fav_count = $this->VideoFavourite->getFavVideosCount($video['Video']['id']);
                    $videos[$key]['Video']['favourite_count'] = $video_fav_count;



                    $videos[$key]['Video']['comment_count'] = $comment_count;
                    $videos[$key]['Video']['like_count'] = $video_likes_count;

                }

                $output['code'] = 200;

                $output['msg'] = $videos;
                $output['sound_fav'] = $sound_fav;


                echo json_encode($output);


                die();
            }else{


                $output['code'] = 201;

                $output['msg'] = $videos;
                $output['sound_fav'] = $sound_fav;


                echo json_encode($output);


                die();

            }

        }


    }

    public function showNearbyVideos(){

        $this->loadModel('Video');
        $this->loadModel('VideoComment');
        $this->loadModel('VideoLike');
        $this->loadModel('VideoFavourite');
        $this->loadModel('Follower');
        $this->loadModel('User');
        $this->loadModel('PlaylistVideo');
        $this->loadModel('RepostVideo');




        if ($this->request->isPost()) {
            $json = file_get_contents('php://input');
            $data = json_decode($json, TRUE);

            $user_id = 0;
            $device_id = $data['device_id'];
            $starting_point = $data['starting_point'];
            $lat = $data['lat'];
            $long = $data['long'];


            if(isset($data['user_id'])) {
                $user_id = $data['user_id'];

            }
            $created = date('Y-m-d H:i:s', time());
            $one_day_before = date('Y-m-d H:i:s', strtotime('-1 days', strtotime($created)));

            $user_details = $this->User->getOnlyUserDetailsFromID($user_id);




                $videos = $this->Video->getNearByRelatedVideosNotWatched($lat,$long,$user_id, $device_id, $starting_point);




                if (count($videos) < 1) {

                    $videos = $this->Video->getNearByRelatedVideosWatched($lat,$long,$user_id, $device_id, $starting_point);
                }




            if(count($videos) > 0) {


                foreach($videos as $key=>$video){



                    if($user_id > 0) {
                        $videos[$key]['PinComment']['id'] =0;
                        $video_data['user_id'] = $user_id;
                        $video_data['video_id'] = $video['Video']['id'];
                        $video_like_detail = $this->VideoLike->ifExist($video_data);
                        $video_favourite_detail = $this->VideoFavourite->ifExist($video_data);
                        $repost_detail = $this->RepostVideo->ifExist($video_data);

                        if (count($repost_detail) > 0) {

                            $videos[$key]['Video']['repost'] = 1;

                        } else {

                            $videos[$key]['Video']['repost'] = 0;
                        }


                        if (array_key_exists("PinComment",$video)){



                            if($video['PinComment']['id'] < 1){
                                $videos[$key]['PinComment']['id'] =0;


                            }
                        }
                        if (count($video_like_detail) > 0) {

                            $videos[$key]['Video']['like'] = 1;

                        } else {

                            $videos[$key]['Video']['like'] = 0;
                        }

                        if (count($video_favourite_detail) > 0) {

                            $videos[$key]['Video']['favourite'] = 1;

                        } else {

                            $videos[$key]['Video']['favourite'] = 0;
                        }





                        $playlist_details = $this->PlaylistVideo->getDetailsAgainstVideoID($video['Video']['id']);

                        if(count($playlist_details) > 0) {

                            $videos[$key]['Video']['PlaylistVideo'] = $playlist_details['PlaylistVideo'];
                            $videos[$key]['Video']['PlaylistVideo']['Playlist'] = $playlist_details['Playlist'];

                        }else{

                            $videos[$key]['Video']['PlaylistVideo']['id'] = 0;

                        }

                        $user_story = $this->Video->getUserStory($video['Video']['user_id'],$one_day_before);
                        $videos[$key]['User']['story'] = $user_story;
                        $follower_details = $this->Follower->ifFollowing($user_id, $video['Video']['user_id']);
                        $following_details = $this->Follower->ifFollowing($video['Video']['user_id'], $user_id);
                        if(count($follower_details) > 0 && count($following_details) > 0){

                            $videos[$key]['User']['button'] = "Friends";

                        } else   if(count($follower_details) > 0 && count($following_details) < 1){

                            $videos[$key]['User']['button'] = "following";

                        }else if (count($following_details) > 0){


                            $videos[$key]['User']['button'] = "follow back";
                        }else{


                            $videos[$key]['User']['button'] = "follow";
                        }
                    }else{


                        $videos[$key]['Video']['like'] = 0;
                        $videos[$key]['Video']['favourite'] = 0;
                        $videos[$key]['Video']['repost'] = 0;
                        $videos[$key]['User']['button'] = "follow";



                    }

                    $comment_count = $this->VideoComment->countComments($video['Video']['id']);
                    $video_likes_count = $this->VideoLike->countLikes($video['Video']['id']);
                    $video_fav_count = $this->VideoFavourite->getFavVideosCount($video['Video']['id']);
                    $videos[$key]['Video']['favourite_count'] = $video_fav_count;

                    $videos[$key]['Video']['comment_count'] = $comment_count;
                    $videos[$key]['Video']['like_count'] = $video_likes_count;


                }



                if(count($user_details) > 0) {
                    $i = rand(3,9);;

                    $promo_video = $this->getPromotionalVideo($user_details);





                    if($promo_video){


                        //array_unshift($videos, $promo_video);
                        $videos[$i] = $promo_video;


                    }


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

    public function showRelatedVideos(){

        $this->loadModel('Video');
        $this->loadModel('VideoComment');
        $this->loadModel('VideoLike');
        $this->loadModel('VideoFavourite');
        $this->loadModel('Follower');
        $this->loadModel('User');
        $this->loadModel('PlaylistVideo');
        $this->loadModel('RepostVideo');




        if ($this->request->isPost()) {
            $json = file_get_contents('php://input');
            $data = json_decode($json, TRUE);

            $user_id = 0;
            $device_id = $data['device_id'];
            $starting_point = $data['starting_point'];
            $lang_ids = array();
            $interest_ids = array();

            if(isset($data['user_id'])) {
                $user_id = $data['user_id'];

            }
            $created = date('Y-m-d H:i:s', time());
            $one_day_before = date('Y-m-d H:i:s', strtotime('-1 days', strtotime($created)));

             $user_details = $this->User->getOnlyUserDetailsFromID($user_id);

            if(APP_STATUS == "demo") {
                $videos = $this->Video->getRelatedVideosDemo($user_id, $device_id, $starting_point);
            }else{



                $videos = $this->Video->getRelatedVideosNotWatched($user_id, $device_id, $starting_point);



                if (count($videos) < 1) {

                    $videos = $this->Video->getRelatedVideosWatched($user_id, $device_id, $starting_point);
                }
            }



            if(count($videos) > 0) {


                foreach($videos as $key=>$video){



                    if($user_id > 0) {
                        $videos[$key]['PinComment']['id'] =0;
                        $videos[$key]['Video']['promote'] = 0;
                        $video_data['user_id'] = $user_id;
                        $video_data['video_id'] = $video['Video']['id'];
                        $video_like_detail = $this->VideoLike->ifExist($video_data);
                        $video_favourite_detail = $this->VideoFavourite->ifExist($video_data);
                        $repost_detail = $this->RepostVideo->ifExist($video_data);

                        if (count($repost_detail) > 0) {

                            $videos[$key]['Video']['repost'] = 1;

                        } else {

                            $videos[$key]['Video']['repost'] = 0;
                        }

                        if (array_key_exists("PinComment",$video)){



                            if($video['PinComment']['id'] < 1){
                                $videos[$key]['PinComment']['id'] =0;


                            }
                        }
                        if (count($video_like_detail) > 0) {

                            $videos[$key]['Video']['like'] = 1;

                        } else {

                            $videos[$key]['Video']['like'] = 0;
                        }

                        if (count($video_favourite_detail) > 0) {

                            $videos[$key]['Video']['favourite'] = 1;

                        } else {

                            $videos[$key]['Video']['favourite'] = 0;
                        }




                        $playlist_details = $this->PlaylistVideo->getDetailsAgainstVideoID($video['Video']['id']);

                        if(count($playlist_details) > 0) {

                            $videos[$key]['Video']['PlaylistVideo'] = $playlist_details['PlaylistVideo'];
                            $videos[$key]['Video']['PlaylistVideo']['Playlist'] = $playlist_details['Playlist'];

                        }else{

                            $videos[$key]['Video']['PlaylistVideo']['id'] = 0;

                        }

                        $user_story = $this->Video->getUserStory($video['Video']['user_id'],$one_day_before);
                        $videos[$key]['User']['story'] = $user_story;
                        $follower_details = $this->Follower->ifFollowing($user_id, $video['Video']['user_id']);
                        $following_details = $this->Follower->ifFollowing($video['Video']['user_id'], $user_id);
                        if(count($follower_details) > 0 && count($following_details) > 0){

                            $videos[$key]['User']['button'] = "Friends";

                        } else   if(count($follower_details) > 0 && count($following_details) < 1){

                            $videos[$key]['User']['button'] = "following";

                        }else if (count($following_details) > 0){


                            $videos[$key]['User']['button'] = "follow back";
                        }else{


                            $videos[$key]['User']['button'] = "follow";
                        }
                    }else{


                        $videos[$key]['Video']['like'] = 0;
                        $videos[$key]['Video']['favourite'] = 0;
                        $videos[$key]['Video']['repost'] = 0;
                        $videos[$key]['User']['button'] = "follow";



                    }

                    $comment_count = $this->VideoComment->countComments($video['Video']['id']);
                    $video_likes_count = $this->VideoLike->countLikes($video['Video']['id']);
                    $video_fav_count = $this->VideoFavourite->getFavVideosCount($video['Video']['id']);
                    $videos[$key]['Video']['favourite_count'] = $video_fav_count;

                    $videos[$key]['Video']['comment_count'] = $comment_count;
                    $videos[$key]['Video']['like_count'] = $video_likes_count;


                }




                if(count($user_details) > 0) {
                    $i = rand(3,9);;

                    $promo_video = $this->getPromotionalVideo($user_details);






                    if($promo_video){


                        //array_unshift($videos, $promo_video);
                        $videos[$i] = $promo_video;


                    }


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




    public function showRelatedVideosTest(){

        $this->loadModel('Video');
        $this->loadModel('VideoComment');
        $this->loadModel('VideoLike');
        $this->loadModel('VideoFavourite');
        $this->loadModel('Follower');
        $this->loadModel('User');
        $this->loadModel('PlaylistVideo');




        if ($this->request->isPost()) {
            $json = file_get_contents('php://input');
            $data = json_decode($json, TRUE);

            $user_id = 0;
            $device_id = $data['device_id'];
            $starting_point = $data['starting_point'];
            $lang_ids = array();
            $interest_ids = array();

            if(isset($data['user_id'])) {
                $user_id = $data['user_id'];

            }
            $created = date('Y-m-d H:i:s', time());
            $one_day_before = date('Y-m-d H:i:s', strtotime('-1 days', strtotime($created)));

            $user_details = $this->User->getOnlyUserDetailsFromID($user_id);

            if(APP_STATUS == "demo") {
                $videos = $this->Video->getRelatedVideosDemo($user_id, $device_id, $starting_point);
            }else{



                $videos = $this->Video->getRelatedVideosNotWatched($user_id, $device_id, $starting_point);



                if (count($videos) < 1) {

                    $videos = $this->Video->getRelatedVideosWatched($user_id, $device_id, $starting_point);
                }
            }



            if(count($videos) > 0) {


                foreach($videos as $key=>$video){



                    if($user_id > 0) {
                        $videos[$key]['PinComment']['id'] =0;
                        $video_data['user_id'] = $user_id;
                        $video_data['video_id'] = $video['Video']['id'];
                        $video_like_detail = $this->VideoLike->ifExist($video_data);
                        $video_favourite_detail = $this->VideoFavourite->ifExist($video_data);
                        $if_reposted = $this->Video->ifUserRepostedVideo($user_id,$video['Video']['id']);

                        if (count($if_reposted) > 0) {

                            $videos[$key]['Video']['respost'] = 1;

                        }else{
                            $videos[$key]['Video']['repost'] = 0;
                        }

                        if (array_key_exists("PinComment",$video)){



                            if($video['PinComment']['id'] < 1){
                                $videos[$key]['PinComment']['id'] =0;


                            }
                        }
                        if (count($video_like_detail) > 0) {

                            $videos[$key]['Video']['like'] = 1;

                        } else {

                            $videos[$key]['Video']['like'] = 0;
                        }

                        if (count($video_favourite_detail) > 0) {

                            $videos[$key]['Video']['favourite'] = 1;

                        } else {

                            $videos[$key]['Video']['favourite'] = 0;
                        }


                        if (count($if_reposted) > 0) {

                            $videos[$key]['Video']['respost'] = 1;

                        }else{
                            $videos[$key]['Video']['repost'] = 0;
                        }

                        $playlist_details = $this->PlaylistVideo->getDetailsAgainstVideoID($video['Video']['id']);

                        if(count($playlist_details) > 0) {

                            $videos[$key]['Video']['PlaylistVideo'] = $playlist_details['PlaylistVideo'];
                            $videos[$key]['Video']['PlaylistVideo']['Playlist'] = $playlist_details['Playlist'];

                        }else{

                            $videos[$key]['Video']['PlaylistVideo']['id'] = 0;

                        }

                        $user_story = $this->Video->getUserStory($video['Video']['user_id'],$one_day_before);
                        $videos[$key]['User']['story'] = $user_story;
                        $follower_details = $this->Follower->ifFollowing($user_id, $video['Video']['user_id']);
                        $following_details = $this->Follower->ifFollowing($video['Video']['user_id'], $user_id);
                        if(count($follower_details) > 0 && count($following_details) > 0){

                            $videos[$key]['User']['button'] = "Friends";

                        } else   if(count($follower_details) > 0 && count($following_details) < 1){

                            $videos[$key]['User']['button'] = "following";

                        }else if (count($following_details) > 0){


                            $videos[$key]['User']['button'] = "follow back";
                        }else{


                            $videos[$key]['User']['button'] = "follow";
                        }
                    }else{


                        $videos[$key]['Video']['like'] = 0;
                        $videos[$key]['Video']['favourite'] = 0;
                        $videos[$key]['Video']['repost'] = 0;
                        $videos[$key]['User']['button'] = "follow";



                    }

                    $comment_count = $this->VideoComment->countComments($video['Video']['id']);
                    $video_likes_count = $this->VideoLike->countLikes($video['Video']['id']);
                    $video_fav_count = $this->VideoFavourite->getFavVideosCount($video['Video']['id']);
                    $videos[$key]['Video']['favourite_count'] = $video_fav_count;

                    $videos[$key]['Video']['comment_count'] = $comment_count;
                    $videos[$key]['Video']['like_count'] = $video_likes_count;


                }



                if(count($user_details) > 0) {
                    $i = rand(3,9);;

                    $promo_video = $this->getPromotionalVideo($user_details);



                    pr($promo_video);



                    if($promo_video){


                        //array_unshift($videos, $promo_video);
                        $videos[$i] = $promo_video;


                    }


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

    public function showSuggestedUsers()
    {

        $this->loadModel("User");
        $this->loadModel("Follower");


        if ($this->request->isPost()) {

            $json = file_get_contents('php://input');
            $data = json_decode($json, TRUE);

            $starting_point = 0;


            if (isset($data['starting_point'])) {

                $starting_point = $data['starting_point'];

            }
            if(isset($data['user_id'])) {

                $user_id = $data['user_id'];

                $followers = $this->Follower->isFollowerOrFollowed($user_id);
                $newarray = array();
                if (count($followers) > 0) {
                    foreach ($followers as $key => $val) {


                        $sender_id = $val['Follower']['sender_id'];
                        $receiver_id = $val['Follower']['receiver_id'];

                        if ($user_id == $sender_id) {

                            $newarray[$key] = $receiver_id;
                        } else {

                            $newarray[$key] = $sender_id;

                        }
                    }
                }
                $users = $this->User->getRecommendedUsers($user_id, $newarray,$starting_point);
            }else{


                $users = $this->User->getRecommendedRandomUsers();
            }


            if(count($users) > 0) {

                foreach($users as $key=>$user){

                    $followers_count = $this->Follower->countFollowers($user['User']['id']);
                    $users[$key]['User']['followers_count'] = $followers_count;

                }
                $output['code'] = 200;

                $output['msg'] = $users;


                echo json_encode($output);


                die();

            }else{
                Message::EMPTYDATA();
                die();


            }
        }
    }

    public function addPaymentCard()
    {

        $this->loadModel('StripeCustomer');
        $this->loadModel('PaymentCard');

        if ($this->request->isPost()) {
            $json = file_get_contents('php://input');
            $data = json_decode($json, TRUE);


            $user_id = $data['user_id'];
            $default = $data['default'];





            $name      = $data['name'];
            $card      = $data['card'];
            $cvc       = $data['cvc'];
            $exp_month = $data['exp_month'];
            $exp_year  = $data['exp_year'];

            if ($card != null && $cvc != null) {

                $a      = array(

                    // 'email' => $email,
                    'card' => array(
                        //'name' => $first_name . " " . $last_name,
                        'number' => $card,
                        'cvc' => $cvc,
                        'exp_month' => $exp_month,
                        'exp_year' => $exp_year,
                        'name' => $name


                    )
                );
                $stripe = $this->StripeCustomer->save($a);



                if ($stripe) {





                    $payment['stripe']  = $stripe['StripeCustomer']['id'];
                    $payment['user_id'] = $user_id;
                    $payment['default'] = $default;
                    $result             = $this->PaymentCard->save($payment);
                    $count              = $this->PaymentCard->isUserStripeCustIDExist($user_id);
                    if ($count > 0) {

                        $cards = $this->PaymentCard->getUserCards($user_id);


                        foreach ($cards as $card) {

                            $response[] = $this->StripeCustomer->getCardDetails($card['PaymentCard']['stripe']);

                        }



                        $i = 0;
                        foreach ($response as $re) {

                            $stripeCustomer                        = $re[0]['StripeCustomer']['sources']['data'][0];
                            $stripData[$i]['CardDetails']['brand'] = $stripeCustomer['brand'];
                            $stripData[$i]['CardDetails']['brand'] = $stripeCustomer['brand'];
                            $stripData[$i]['CardDetails']['last4'] = $stripeCustomer['last4'];
                            $stripData[$i]['CardDetails']['name']  = $stripeCustomer['name'];

                            $i++;
                        }


                        $output['code'] = 200;
                        $output['msg']  = $stripData;
                        echo json_encode($output);
                        die();
                    } else {
                        Message::EmptyDATA();
                        die();
                    }




                } else {
                    $error['code'] = 400;
                    $error['msg']  = $this->StripeCustomer->getStripeError();
                    echo json_encode($error);
                }
            } else {
                echo Message::ERROR();



            }

        }

    }


    public function showUserCards()
    {



        $this->loadModel('StripeCustomer');
        $this->loadModel('PaymentCard');


        if ($this->request->isPost()) {
            //$json = file_get_contents('php://input');
            $json    = file_get_contents('php://input');
            $data    = json_decode($json, TRUE);
            $user_id = $data['user_id'];
            if ($user_id != null) {

                $count = $this->PaymentCard->isUserStripeCustIDExist($user_id);

                if ($count > 0) {

                    $cards = $this->PaymentCard->getUserCards($user_id);

                    $j = 0;
                    foreach ($cards as $card) {

                        $response[$j]['Stripe']              = $this->StripeCustomer->getCardDetails($card['PaymentCard']['stripe']);
                        $response[$j]['PaymentCard']['id'] = $card['PaymentCard']['id'];
                        $j++;
                    }


                    $i = 0;
                    foreach ($response as $re) {

                        $stripeCustomer                       = $re['Stripe'][0]['StripeCustomer']['sources']['data'][0];

                        $stripData[$i]['brand']               = $stripeCustomer['brand'];
                        $stripData[$i]['brand']               = $stripeCustomer['brand'];
                        $stripData[$i]['last4']               = $stripeCustomer['last4'];
                        $stripData[$i]['name']                = $stripeCustomer['name'];
                        $stripData[$i]['exp_month']           = $stripeCustomer['exp_month'];
                        $stripData[$i]['exp_year']            = $stripeCustomer['exp_year'];
                        $stripData[$i]['PaymentCard']['id'] = $re['PaymentCard']['id'];

                        $i++;
                    }


                    $output['code'] = 200;
                    $output['msg']  = $stripData;
                    echo json_encode($output);
                    die();
                } else {
                    Message::EmptyDATA();
                    die();
                }

            } else {
                echo Message::ERROR();
            }
        }
    }







    public function deletePaymentCard()
    {

        $this->loadModel("PaymentCard");
        $this->loadModel("StripeCustomer");
        // $this->loadModel("RestaurantRating");

        if ($this->request->isPost()) {
            $json = file_get_contents('php://input');
            $data = json_decode($json, TRUE);

            $id      = $data['id'];
            $user_id = $data['user_id'];
            $this->PaymentCard->query('SET FOREIGN_KEY_CHECKS=0');
            if ($this->PaymentCard->delete($id)) {



                $count = $this->PaymentCard->isUserStripeCustIDExist($user_id);

                if ($count > 0) {

                    $cards = $this->PaymentCard->getUserCards($user_id);


                    foreach ($cards as $card) {

                        $response[] = $this->StripeCustomer->getCardDetails($card['PaymentCard']['stripe']);

                    }



                    $i = 0;
                    foreach ($response as $re) {

                        $stripeCustomer         = $re[0]['StripeCustomer']['sources']['data'][0];
                        /* $stripData[$i]['CardDetails']['brand'] = $stripeCustomer['brand'];
                        $stripData[$i]['CardDetails']['brand'] = $stripeCustomer['brand'];
                        $stripData[$i]['CardDetails']['last4'] = $stripeCustomer['last4'];
                        $stripData[$i]['CardDetails']['name'] = $stripeCustomer['name'];*/
                        $stripData[$i]['brand'] = $stripeCustomer['brand'];
                        $stripData[$i]['brand'] = $stripeCustomer['brand'];
                        $stripData[$i]['last4'] = $stripeCustomer['last4'];
                        $stripData[$i]['name']  = $stripeCustomer['name'];

                        $i++;
                    }


                    $output['code'] = 200;
                    $output['msg']  = $stripData;
                    echo json_encode($output);
                    die();
                } else {
                    Message::EmptyDATA();
                    die();
                }
            } else {

                Message::ALREADYDELETED();
                die();

            }



        }
    }
    public function getPromotionalVideo($user_details){

        $this->loadModel('Promotion');
        $this->loadModel('VideoLike');
        $this->loadModel('VideoFavourite');
        $this->loadModel('Follower');
        $this->loadModel('User');
        $this->loadModel('VideoComment');
        $this->loadModel('RepostVideo');



        $user_details = $this->User->getOnlyUserDetailsFromID($user_details['User']['id']);

        if(count($user_details) > 0) {

            $user_id = $user_details['User']['id'];
            $dob = $user_details['User']['dob'];
            $gender = $user_details['User']['gender'];


            $promotion_details = $this->Promotion->getPromotionalVideo($user_id,$dob,$gender);







            // $log = $this->Promotion->getDataSource()->getLog(false, false);

            if (count($promotion_details) > 0) {



                $video_id = $promotion_details['Video']['id'];
                if($video_id > 0){
                    $promotion_id = $promotion_details['Promotion']['id'];
                    $reach = $promotion_details['Promotion']['reach'];
                    $total_reach = $promotion_details['Promotion']['total_reach'];
                    $video_user_id = $promotion_details['Video']['user_id'];
                    $video_likes_count = $this->VideoLike->countLikes($promotion_details['Video']['id']);
                    $video_like = $video_likes_count;

                    if($reach < $total_reach) {

                        $promo_video['Video'] = $promotion_details['Video'];
                        $promo_video['Sound'] = $promotion_details['Video']['Sound'];
                        $promo_video['User'] = $promotion_details['Video']['User'];

                        $promo_video['PinComment'] = $promotion_details['Video']['PinComment'];


                        if(count($promotion_details['Video']['PinComment']) < 1){
                            $promo_video['PinComment']['id'] =0;


                        }
                        $promo_video['Video']['promote'] = 1;
                        $promo_video['Video']['promotion_id'] = $promotion_details['Promotion']['id'];
                        $promo_video['Video']['Promotion'] = $promotion_details['Promotion'];


                        $video_data['user_id'] = $user_id;
                        $video_data['video_id'] = $video_id;
                        $video_like_detail = $this->VideoLike->ifExist($video_data);
                        $video_favourite_detail = $this->VideoFavourite->ifExist($video_data);

                        if (count($video_like_detail) > 0) {

                            $promo_video['Video']['like'] = 1;

                        } else {

                            $promo_video['Video']['like'] = 0;
                        }

                        if (count($video_favourite_detail) > 0) {

                            $promo_video['Video']['favourite'] = 1;

                        } else {

                            $promo_video['Video']['favourite'] = 0;
                        }
                        $repost_detail = $this->RepostVideo->ifExist($video_data);

                        if (count($repost_detail) > 0) {

                            $promo_video['Video']['repost'] = 1;

                        } else {

                            $promo_video['Video']['repost'] = 0;
                        }

                        $follower_details = $this->Follower->ifFollowing($user_id, $video_user_id);
                        $following_details = $this->Follower->ifFollowing($video_user_id, $user_id);

                        if (count($follower_details) > 0 && count($following_details) > 0) {

                            $promo_video['User']['button'] = "Friends";

                        } else if (count($follower_details) > 0 && count($following_details) < 1) {

                            $promo_video['User']['button'] = "following";

                        } else if (count($following_details) > 0) {


                            $promo_video['User']['button'] = "follow back";
                        } else {


                            $promo_video['User']['button'] = "follow";
                        }

                        $comment_count = $this->VideoComment->countComments($video_id);
                        //  $video_likes_count = $this->VideoLike->countLikes($video['Video']['id']);

                        $video_likes_count = $video_like;
                        $followers_count = $this->Follower->countFollowers($video_user_id);

                        $promo_video['Video']['comment_count'] = $comment_count;
                        $promo_video['User']['followers_count'] = $followers_count;
                        $promo_video['Video']['like_count'] = $video_likes_count;

                        //$video_repost_count = $this->Video->getVideoRepostCount($video['Video']['id']);


                        $this->Promotion->id = $promotion_id;
                        $this->Promotion->saveField('reach', $reach + 1);

                        return $promo_video;
                    }
                }else{

                    return false;

                }
            }

            return false;


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
           // $schedule = $this->request->data('schedule');


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
                   // $video_save['schedule'] = $schedule;

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

                    /*************************end hashtag save ********************/


                    /**************pushnotification to tagged users******************/

                   /* if (count($data_users) < 1) {

                        preg_match_all('/#\w+/', $description, $matches);

                        $data_users = array_map(function($match) {
                            return str_replace('@', '', $match);
                        }, $matches[0]);
                    }*/



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


    public function postVideoTest(){

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

                        $result_video = Google::s3_video_upload($user_id, $type, $sound_details, $video_details, $duet);
                        pr($result_video);
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

                    /*************************end hashtag save ********************/


                    /**************pushnotification to tagged users******************/

                    /* if (count($data_users) < 1) {

                         preg_match_all('/#\w+/', $description, $matches);

                         $data_users = array_map(function($match) {
                             return str_replace('@', '', $match);
                         }, $matches[0]);
                     }*/



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


    public function showFriends(){

        $this->loadModel('Follower');




        if ($this->request->isPost()) {
            $json = file_get_contents('php://input');
            $data = json_decode($json, TRUE);


            $friends = array();


            $user_id = $data['user_id'];
            $starting_point = $data['starting_point'];

            $followers = $this->Follower->getUserFollowers($user_id,$starting_point);


            if(count($followers) > 0 ){


                foreach($followers as $key=>$val){

                    $follower_back_details = $this->Follower->ifFollowing($user_id, $val['FollowerList']['id']);

                    if(count($follower_back_details) > 0){

                        $friends[$key]['Friends'] = $val['FollowerList'];
                    }
                }
            }





            if(count($friends) > 0) {

                $output['code'] = 200;

                $output['msg'] = $friends;


                echo json_encode($output);


                die();

            }else{

                Message::EMPTYDATA();
                die();

            }
        }


    }


    public function showFriendsStories(){

        $this->loadModel('Video');
        $this->loadModel('Follower');
        $this->loadModel('User');

        if ($this->request->isPost()) {
            $json = file_get_contents('php://input');
            $data = json_decode($json, TRUE);


            $user_id = $data['user_id'];
            $starting_point = $data['starting_point'];
            $created = date('Y-m-d H:i:s', time());
            $one_day_before = date('Y-m-d H:i:s', strtotime('-1 days', strtotime($created)));
            $followers = $this->Follower->getUserFollowing($user_id,$starting_point);


            $user_story = $this->Video->getUserStory($user_id,$one_day_before);
            $friend_stories = array();
            $i=0;
            $user_details = $this->User->getOnlyUserDetailsFromID($user_id);
            if(count($user_story) > 0){


                $friend_stories['User'][$i] = $user_details['User'];
                $friend_stories['User'][$i]['Video'] = $user_story;
                $i++;
            }

            if(count($followers) > 0){




                foreach ($followers as $key=>$follow){

                    $receiver_id = $follow['FollowingList']['id'];

                    $if_following = $this->Follower->ifFollowing($receiver_id,$user_id);



                    if(count($if_following) > 0){

                        //$friends_ids[$key] = $receiver_id;
                        $friend_stories['User'][$i] = $if_following['FollowerList'];
                        $friend_stories['User'][$i]['Video'] = $this->Video->getUserStory($receiver_id,$one_day_before);
                        $i++;
                    }

                }







            }

            if(count($friend_stories) > 0) {
                $output['code'] = 200;

                $output['msg'] = $friend_stories;


                echo json_encode($output);


                die();


            }

            Message::EMPTYDATA();
            die();
        }
        }

    public function showFollowingVideos(){

        $this->loadModel('Video');
        $this->loadModel('VideoComment');
        $this->loadModel('VideoLike');
        $this->loadModel('VideoFavourite');
        $this->loadModel('Follower');
        $this->loadModel('RepostVideo');




        if ($this->request->isPost()) {
            $json = file_get_contents('php://input');
            $data = json_decode($json, TRUE);

            $user_id = 0;
            $device_id = $data['device_id'];
            $starting_point = $data['starting_point'];
            $created = date('Y-m-d H:i:s', time());
            $one_day_before = date('Y-m-d H:i:s', strtotime('-1 days', strtotime($created)));
            if(isset($data['user_id'])) {

                $user_id = $data['user_id'];
            }

            $following_users = $this->Follower->getUserFollowingWithoutLimit($user_id);

            $ids = array();
            if(count($following_users) > 0) {
                foreach ($following_users as $key => $following) {

                    $ids[$key] = $following['FollowingList']['id'];

                }
            }

            if(count($ids) > 0){
                $videos = $this->Video->getFollowingVideosNotWatched($user_id, $device_id, $starting_point,$ids);


                if (count($videos) < 1) {

                    $videos = $this->Video->getFollowingVideosWatched($user_id, $device_id, $starting_point,$ids);
                }
                if(count($videos) > 0) {

                    foreach ($videos as $key => $video) {

                        if ($user_id > 0) {
                            $video_data['user_id'] = $user_id;
                            $video_data['video_id'] = $video['Video']['id'];
                            $video_like_detail = $this->VideoLike->ifExist($video_data);
                            $video_favourite_detail = $this->VideoFavourite->ifExist($video_data);

                            if (count($video_like_detail) > 0) {

                                $videos[$key]['Video']['like'] = 1;

                            } else {

                                $videos[$key]['Video']['like'] = 0;
                            }

                            if (count($video_favourite_detail) > 0) {

                                $videos[$key]['Video']['favourite'] = 1;

                            } else {

                                $videos[$key]['Video']['favourite'] = 0;
                            }

                            $repost_detail = $this->RepostVideo->ifExist($video_data);

                            if (count($repost_detail) > 0) {

                                $promo_video['Video']['repost'] = 1;

                            } else {

                                $promo_video['Video']['repost'] = 0;
                            }
                        } else {
                            $videos[$key]['Video']['like'] = 0;
                            $videos[$key]['Video']['favourite'] = 0;
                            $videos[$key]['Video']['repost'] = 0;


                        }
                        $comment_count = $this->VideoComment->countComments($video['Video']['id']);
                        $video_likes_count = $this->VideoLike->countLikes($video['Video']['id']);
                        $video_fav_count = $this->VideoFavourite->getFavVideosCount($video['Video']['id']);
                        $videos[$key]['Video']['favourite_count'] = $video_fav_count;
                        $videos[$key]['Video']['comment_count'] = $comment_count;
                        $videos[$key]['Video']['like_count'] = $video_likes_count;


                        $user_story = $this->Video->getUserStory($video['Video']['user_id'],$one_day_before);
                        $videos[$key]['User']['story'] = $user_story;
                    }

                    $output['code'] = 200;

                    $output['msg'] = $videos;


                    echo json_encode($output);


                    die();
                }else{

                    Message::EMPTYDATA();
                    die();
                }
            }else{

                $output['code'] = 201;

                $output['msg'] = "you are not following anyone yet";


                echo json_encode($output);


                die();

            }

        }


    }

    public function showCountries(){

        $this->loadModel('Country');




        if ($this->request->isPost()) {
            $json = file_get_contents('php://input');
            $data = json_decode($json, TRUE);



            $countries = $this->Country->getAll();





            $output['code'] = 200;

            $output['msg'] = $countries;


            echo json_encode($output);


            die();


        }


    }


    public function editProfile()
    {


        $this->loadModel('User');

        if ($this->request->isPost()) {


            $json = file_get_contents('php://input');
            $data = json_decode($json, TRUE);













            $user_id = $data['user_id'];




            if(isset($data['first_name'])){


                $user['first_name'] = $data['first_name'];
            }

            if(isset($data['profile_gif'])){


                $user['profile_gif'] = $data['profile_gif'];
            }

            if(isset($data['profile_view'])){


                $user['profile_view'] = $data['profile_view'];
            }
            if(isset($data['last_name'])){


                $user['last_name'] = $data['last_name'];
            }
            if(isset($data['bio'])){


                $user['bio'] = $data['bio'];
            }
            if(isset($data['website'])){


                $user['website'] = $data['website'];
            }

            if(isset($data['phone'])){


                $user['phone'] = $data['phone'];
            }

            if(isset($data['private'])){


                $user['private'] = $data['private'];
            }

            if(isset($data['language_id'])){


                $user['lang_id'] = $data['lang_id'];
            }



            if(isset($data['facebook'])){


                $user['facebook'] = $data['facebook'];
            }

            if(isset($data['instagram'])){


                $user['instagram'] = $data['instagram'];
            }

            if(isset($data['youtube'])){


                $user['youtube'] = $data['youtube'];
            }

            if(isset($data['twitter'])){


                $user['twitter'] = $data['twitter'];
            }
            if(isset($data['username'])){


                $user['username'] = $data['username'];

                $username_exist = $this->User->editIsUsernameAlreadyExist($data['username'], $user_id);
                //$email_exist = $this->User->editIsEmailAlreadyExist($data['email'], $user_id);

                if($username_exist > 0){

                    $output['code'] = 201;
                    $output['msg'] = "username already exist";
                    echo json_encode($output);
                    die();
                }
            }

            if(isset($data['gender'])){

                $user['gender'] = strtolower($data['gender']);

            }

            // $phone = $this->User->editIsphoneNoAlreadyExist($data['phone'], $user_id);


            $this->User->id = $user_id;
            $this->User->save($user);


            $output = array();
            $userDetails = $this->User->getUserDetailsFromID($user_id);


            $output['code'] = 200;
            $output['msg'] = $userDetails;
            echo json_encode($output);


        }
    }


    public function logout()
    {


        $this->loadModel('User');

        if ($this->request->isPost()) {


            $json = file_get_contents('php://input');
            $data = json_decode($json, TRUE);




            $user_id = $data['user_id'];
            $user['device_token'] = "";
            $user['auth_token'] = "";


            $userDetails = $this->User->getUserDetailsFromID($user_id);
            if(count($userDetails) > 0) {

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
    public function showAudiencesReach(){

        $this->loadModel('Audience');
        $this->loadModel('User');




        if ($this->request->isPost()) {
            $json = file_get_contents('php://input');
            $data = json_decode($json, TRUE);

            $min_age = 0;
            $max_age = 0;
            $gender = "all";
            if(isset($data['min_age'])){

                $min_age = $data['min_age'];
            }

            if(isset($data['max_age'])){

                $max_age = $data['max_age'];
            }

            if(isset($data['gender'])){

                $gender = trtolower($data['gender']);
            }



            $locations = $data['locations'];





            if($min_age < 1 && $max_age < 1){

                //it means only location reach

                $total_reach = 0;
                foreach ($locations as $key => $location) {


                    $city_id = $location['city_id'];
                    $country_id = $location['country_id'];
                    $state_id = $location['state_id'];

                    $audience_reach = $this->User->totalAudienceAgainstCityID($city_id);
                    if($location['city_id'] < 1 || $audience_reach < 1 ){

                        $audience_reach = $this->User->totalAudienceAgainstStateID($state_id);

                        if($location['state_id'] < 1 || $audience_reach < 1 ){

                            $audience_reach = $this->User->totalAudienceAgainstCountryID($country_id);
                        }

                    }



                    $total_reach = $audience_reach + $total_reach;
                }

            }else if($gender == "all"){


                $total_reach = 0;
                foreach ($locations as $key => $location) {

                    $city_id = $location['city_id'];
                    $country_id = $location['country_id'];
                    $state_id = $location['state_id'];


                    $audience_reach = $this->User->totalAudienceWithoutGenderAndCity($min_age,$max_age,$city_id);
                    if($city_id < 1 || $audience_reach < 1 ){

                        $audience_reach = $this->User->totalAudienceWithoutGenderAndState($min_age,$max_age,$state_id);

                        if($state_id < 1 || $audience_reach < 1 ){

                            $audience_reach = $this->User->totalAudienceWithoutGenderAndCountry($min_age,$max_age,$country_id);
                        }

                    }



                    $total_reach = $audience_reach[0][0]['total_audience'] + $total_reach;
                }

            }else{

                $total_reach = 0;
                foreach ($locations as $key => $location) {

                    $city_id = $location['city_id'];
                    $country_id = $location['country_id'];
                    $state_id = $location['state_id'];


                    $audience_reach = $this->User->totalAudienceAgainstGenderAndCity($min_age,$max_age,$gender,$city_id);
                    if($city_id < 1 || $audience_reach < 1 ){

                        $audience_reach = $this->User->totalAudienceAgainstGenderAndState($min_age,$max_age,$gender,$state_id);

                        if($state_id < 1 || $audience_reach < 1 ){

                            $audience_reach = $this->User->totalAudienceAgainstGenderAndCountry($min_age,$max_age,$gender,$country_id);
                        }

                    }



                    $total_reach = $audience_reach[0][0]['total_audience'] + $total_reach;
                }


            }






            /*
             *
             *   $location_with_commas = "'" . implode("','", $location_new) . "'";
            if ($gender != "any") {
                    $count = $this->User->totalAudienceAgainstGender($min_age, $max_age, $gender, $location_with_commas);



                }else{

                $count = $this->User->totalAudienceWithoutGender($min_age, $max_age, $location_with_commas);



            }*/



            $output['code'] = 200;

            $output['msg'] = $total_reach;


            echo json_encode($output);


            die();

        }

    }

    public function showLocations()
    {


        $this->loadModel("User");
        $this->loadModel("Country");
        $this->loadModel("City");
        $this->loadModel("State");



        if ($this->request->isPost()) {
            $json = file_get_contents('php://input');
            $data = json_decode($json, TRUE);
            $keyword = $data['keyword'];


            // $countries = $this->User->searchLocation($keyword);

            $countries = $this->Country->getCountriesAgainstKeyword($keyword);

            $location = array();
            if(count($countries) > 0) {

                foreach ($countries as $key => $country) {
                    $id = $country[0]['id'];
                    $name = $country[0]['name'];
                    $type = $country[0]['type'];


                    if($type == "country"){

                        $location[$key]['country_id'] = $id;
                        $location[$key]['city_id'] = 0;
                        $location[$key]['state_id'] = 0;
                        $location[$key]['name'] = $name;


                    }else if($type == "state"){


                        $details =  $this->State->getDetails($id);
                        $location[$key]['country_id'] = $details['Country']['id'];
                        $location[$key]['state_id'] = $id;
                        $location[$key]['city_id'] = 0;
                        $location[$key]['name'] = $name.",".$details['Country']['name'];

                    }else if($type == "city"){


                        $details =  $this->City->getDetails($id);


                        $location[$key]['country_id'] = $details['City']['country_id'];
                        $location[$key]['state_id'] = $details['State']['id'];
                        $location[$key]['city_id'] = $id;
                        $location[$key]['name'] = $name.",".$details['Country']['name'];
                    }
                }
            }


            if(count($location) > 0) {

                $output['code'] = 200;

                $output['msg'] = $location;


                echo json_encode($output);


                die();

            }else{

                Message::EMPTYDATA();
                die();
            }
        }
    }

    public function addAudience()
    {

        $this->loadModel("Audience");




        if ($this->request->isPost()) {
            $json = file_get_contents('php://input');
            $data = json_decode($json, TRUE);


            $name = $data['name'];
            $user_id = $data['user_id'];

            $min_age = $data['min_age'];
            $max_age = $data['max_age'];
            $gender = strtolower($data['gender']);
            $created = date('Y-m-d H:i:s', time());


            $audience['name'] = $name;
            $audience['user_id'] = $user_id;

            $audience['min_age'] = $min_age;
            $audience['max_age'] = $max_age;
            $audience['gender'] = $gender;
            $audience['created'] = $created;









            $this->Audience->save($audience);



            $id = $this->Audience->getInsertID();


            $details = $this->Audience->getDetails($id);

            $output['code'] = 200;
            $output['msg'] = $details;
            echo json_encode($output);


            die();








        }
    }


    public function addDefaultAudience()
    {

        $this->loadModel("Audience");




            $name = "default";
            $user_id = 0;

            $min_age = 18;
            $max_age = 100;
            $gender = "all";
            $created = date('Y-m-d H:i:s', time());


            $audience['name'] = $name;
            $audience['user_id'] = $user_id;

            $audience['min_age'] = $min_age;
            $audience['max_age'] = $max_age;
            $audience['gender'] = $gender;
            $audience['created'] = $created;









            $this->Audience->save($audience);



            $id = $this->Audience->getInsertID();




           return $id;









    }




    public function deleteAudience(){



        $this->loadModel('Audience');
        $this->loadModel('Promotion');

        if ($this->request->isPost()) {
            $json = file_get_contents('php://input');
            $data = json_decode($json, TRUE);

            //$trip['trip_id'] =

            $id =  $data['id'];
            $created = date('Y-m-d H:i:s', time());
            $details =  $this->Promotion->getActivePromotionAudience($id,$created);


            if(count($details) < 1) {




                $this->Audience->id = $id;
                $this->Audience->delete();




                $output['code'] = 200;

                $output['msg'] = "deleted";


                echo json_encode($output);


                die();




            }else{

                $output['code'] = 201;

                $output['msg'] = "You cannot delete an audience since your ad is active against this audience.";


                echo json_encode($output);


                die();
            }

        }




    }

    public function showAudiences(){

        $this->loadModel('Audience');
        $this->loadModel('User');




        if ($this->request->isPost()) {
            $json = file_get_contents('php://input');
            $data = json_decode($json, TRUE);

            $user_id = $data['user_id'];

            $audiences = $this->Audience->getUserAudiences($user_id);





            $output['code'] = 200;

            $output['msg'] = $audiences;


            echo json_encode($output);


            die();


        }


    }

    public function sendGift()
    {
        $this->loadModel("User");
        $this->loadModel("PushNotification");


        if ($this->request->isPost()) {
            $json = file_get_contents('php://input');
            $data = json_decode($json, TRUE);

            if (in_array("GiftSend", App::objects('Model'))) {
                $this->loadModel("GiftSend");
                $this->loadModel("Gift");
            }else{

                $output['code'] = 201;

                $output['msg'] = "Contact hello@qboxus.com to get these premium features";


                echo json_encode($output);


                die();
            }

            $sender_id = $data['sender_id'];
            $receiver_id = $data['receiver_id'];
            $gift_id = $data['gift_id'];
            $gift_count = $data['gift_count'];




            $gift_details =  $this->Gift->getDetails($gift_id);


            if(count($gift_details)< 1){


                Message::EMPTYDATA();
                die();
            }


            $gift_data['image'] = $gift_details['Gift']['image'];
            $gift_data['coin'] = $gift_details['Gift']['coin'] * $gift_count;
            $gift_data['gift_id'] = $gift_details['Gift']['id'];
            $gift_data['title'] = $gift_details['Gift']['title'];
            $gift_data['sender_id'] = $sender_id;
            $gift_data['receiver_id'] = $receiver_id;

            $receiver_details =  $this->User->getUserDetailsFromID($receiver_id);
            $sender_details =  $this->User->getUserDetailsFromID($sender_id);


            if(count($sender_details) > 0){

                $total_coins_sender  =  $this->walletTotal($sender_details['User']['id']);
               // $total_coins_receiver = $this->walletTotal($receiver_details['User']['id']);

                if($total_coins_sender['total'] < $gift_data['coin']){


                    $output['code'] = 201;
                    $output['msg'] = "You do not have enough coins to send gift";
                    echo json_encode($output);


                    die();
                }

            }



            $this->GiftSend->save($gift_data);


            /*********************************START NOTIFICATION******************************/

            $notification['to'] = $receiver_details['User']['device_token'];




            $notification['notification']['title'] = "You have received a gift";
            $notification['notification']['body'] = $gift_details['Gift']['title']." worth ".$gift_data['coin']." coins";
            $notification['notification']['user_id'] = $sender_details['User']['id'];
            $notification['notification']['image'] = $sender_details['User']['profile_pic'];
            $notification['notification']['name'] = $sender_details['User']['username'];
            $notification['notification']['badge'] = "1";
            $notification['notification']['sound'] = "default";
            $notification['notification']['icon'] = "";
            $notification['notification']['type'] = "gift";

            $notification['data']['title'] = "You have received a gift";
            $notification['data']['name'] = $sender_details['User']['username'];
            $notification['data']['body'] = $gift_details['Gift']['title']." worth ".$gift_data['coin']." coins";
            $notification['data']['icon'] = "";
            $notification['data']['badge'] = "1";
            $notification['data']['sound'] = "default";
            $notification['data']['type'] = "gift";
            $notification['data']['user_id'] = $sender_details['User']['id'];
            $notification['data']['image'] = $sender_details['User']['profile_pic'];



            $if_exist = $this->PushNotification->getDetails($receiver_details['User']['id']);
            if(count($if_exist) > 0) {

                $likes = $if_exist['PushNotification']['direct_messages'];
                if($likes > 0) {
                    Utility::sendPushNotificationToMobileDevice(json_encode($notification));
                }
            }

            /*********************************END NOTIFICATION******************************/


           /* $this->User->id = $sender_details['User']['id'];
            $this->User->saveField('wallet',$total_coins_sender - $gift_data['coin']);


            $this->User->id = $receiver_details['User']['id'];
            $this->User->saveField('wallet',$total_coins_receiver + $gift_data['coin']);*/


            $sender_details =  $this->User->getUserDetailsFromID($sender_id);
            $wallet_total = $this->walletTotal($sender_details['User']['id']);
            $sender_details['User']['wallet'] = $wallet_total['total'];
            $output['code'] = 200;
            $output['msg'] = $sender_details;
            echo json_encode($output);


            die();
        }

    }

    public function withdrawRequest()
    {



        $this->loadModel('User');


        if ($this->request->isPost()) {


            $json = file_get_contents('php://input');
            $data = json_decode($json, TRUE);

            if (in_array("WithdrawRequest", App::objects('Model'))) {
                $this->loadModel('WithdrawRequest');

            }else{

                $output['code'] = 201;

                $output['msg'] = "Contact hello@qboxus.com to get these premium features";


                echo json_encode($output);


                die();
            }
            $withdraw_data['user_id'] = $data['user_id'];
            $withdraw_data['amount'] = $data['amount'];
            if(isset($data['coin'])){
                $withdraw_data['coin'] = $data['coin'];
            }

            $withdraw_data['created'] = date('Y-m-d H:i:s', time());



            $details = $this->WithdrawRequest->getUserPendingWithdrawRequest($data['user_id']);

            if(count($details) > 0 ){

                $output['code'] = 201;
                $output['msg'] = "You have already requested a payout.";
                echo json_encode($output);
                die();

            }




            $this->WithdrawRequest->save($withdraw_data);

            $id = $this->WithdrawRequest->getInsertID();

            $output = array();
            $details = $this->WithdrawRequest->getDetails($id);
            $wallet_total =  $this->walletTotal($data['user_id']);

            $wallet_total = $wallet_total['total'];
            $details['User']['wallet'] = $wallet_total;


            $output['code'] = 200;
            $output['msg'] = $details;
            echo json_encode($output);
            die();





        }
    }



    public function addStory()
    {



        $this->loadModel('Story');


        if ($this->request->isPost()) {


            $json = file_get_contents('php://input');
            $data = json_decode($json, TRUE);

            $created = date('Y-m-d H:i:s', time());
            $type = $data['type'];
            $attachment = $data['attachment'];
            $video_duration = Utility::getDurationOfVideoFile($attachment);

            $user_id = $data['user_id'];
            $datetime = $data['datetime'];

            $post_data['type'] = $type;
            $post_data['user_id'] = $user_id;
            $post_data['attachment'] = $attachment;
            $post_data['created'] = $datetime;
            $post_data['duration'] = $video_duration;


            $this->Story->save($post_data);
            $id = $this->Story->getInsertID();










            $details = $this->Story->getDetails($id);

            $output['code'] = 200;
            $output['msg'] = $details;
            echo json_encode($output);
            die();


        }
    }

    public function showStory(){

        $this->loadModel('Story');




        if ($this->request->isPost()) {
            $json = file_get_contents('php://input');
            $data = json_decode($json, TRUE);



            $details = array();
            $date = $data['date'];
            //$date_readable =  date("Y-m-d", strtotime($date));

            $story_others = $this->Story->getOtherStories($date);

            if(isset($data['user_id'])){


                $details = $this->Story->getUserStories($data['user_id'],$date);
            }



            $output['code'] = 200;

            $output['msg']['mystory'] = $details;
            $output['msg']['other'] = $story_others;


            echo json_encode($output);


            die();


        }


    }


    public function test(){

        $apiKey = '70c6f978-3538-4be6-b6f2c7b04e65-1548-4529';
        $storageZoneName = 'tictic';
        $file = 'app/webroot/uploads/647d3ef1ab8dd.mp4';

        $curl = curl_init();

        curl_setopt_array($curl, array(
            CURLOPT_URL => 'https://sg.storage.bunnycdn.com/tictic/new/gameon.mp4',
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => '',
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 0,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => 'PUT',
            CURLOPT_POSTFIELDS => array('file'=> new CURLFILE($file)),
            CURLOPT_HTTPHEADER => array(
                'AccessKey: 70c6f978-3538-4be6-b6f2c7b04e65-1548-4529',
                'Content-Type: application/octet-stream'
            ),
        ));

        $response = curl_exec($curl);

        curl_close($curl);
        echo $response;
// Check the response
        if ($response === false) {
            echo 'Error uploading the file.';
        } else {
            echo 'File uploaded successfully.';
        }





    }
    public function deleteStory(){

        $this->loadModel('Story');




        if ($this->request->isPost()) {
            $json = file_get_contents('php://input');
            $data = json_decode($json, TRUE);


            $id = $data['id'];

            $this->Story->id = $id;
            $this->Story->delete();





            $output['code'] = 200;

            $output['msg'] = "success";


            echo json_encode($output);


            die();


        }


    }

    public function addGroup()
    {



        $this->loadModel('Group');


        if ($this->request->isPost()) {


            $json = file_get_contents('php://input');
            $data = json_decode($json, TRUE);

            $created = date('Y-m-d H:i:s', time());
            $post_data['title'] = $data['title'];
            $post_data['user_id'] = $data['user_id'];
            $post_data['created'] = $created;


            if(isset($data['id'])){


                $group_details = $this->Group->getDetails($data['id']);

                if(isset($data['image'])) {
                    $image_db = $group_details['Group']['image'];
                    if (strlen($image_db) > 5) {
                        @unlink($image_db);

                    }

                    $image = $data['image'];
                    $folder_url = UPLOADS_FOLDER_URI;

                    $filePath = Utility::uploadFileintoFolderDir($image, $folder_url);
                    $post_data['image'] = $filePath;

                }


                $this->Group->id = $data['id'];
                $this->Group->save($post_data);

                $group_details = $this->Group->getDetails($data['id']);

                $output['code'] = 200;
                $output['msg'] = $group_details;
                echo json_encode($output);
                die();
            }


            if(isset($data['image'])) {


                $image = $data['image'];
                $folder_url = UPLOADS_FOLDER_URI;

                $filePath = Utility::uploadFileintoFolderDir($image, $folder_url);
                $post_data['image'] = $filePath;

            }

            $this->Group->save($post_data);
            $id = $this->Group->getInsertID();
            $group_details = $this->Group->getDetails($id);

            $output['code'] = 200;
            $output['msg'] = $group_details;
            echo json_encode($output);
            die();


        }
    }


    public function pinComment()
    {

        $this->loadModel("Video");




        if ($this->request->isPost()) {
            $json = file_get_contents('php://input');
            $data = json_decode($json, TRUE);


            $video_id = $data['video_id'];
            $pin_comment_id = $data['pin_comment_id'];
            $details = $this->Video->getDetails($video_id);

            if(count($details) > 0 ){


                $this->Video->id = $video_id;
                $this->Video->saveField('pin_comment_id',$pin_comment_id);



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


    public function inviteUserToGroup(){

        $this->loadModel('Notification');
        $this->loadModel('Group');


        if ($this->request->isPost()) {


            $json = file_get_contents('php://input');
            $data = json_decode($json, TRUE);

            $created = date('Y-m-d H:i:s', time());
            $group_id = $data['group_id'];
            $user_id = $data['user_id'];



            $group_details  = $this->Group->getDetails($group_id);
            $receiver_details  = $this->User->getUserDetailsFromID($user_id);

            if(count($group_details) > 0){
                $title = $group_details['Group']['title'];
                $msg = "You have been invite in ".$title." group";

                $group_user_id = $group_details['Group']['user_id'];
                $group_id = $group_details['Group']['id'];

                $notification_data['sender_id'] = $group_user_id;
                $notification_data['receiver_id'] = $user_id;
                $notification_data['group_id'] = $group_id;
                $notification_data['type'] = "group_invite";


                $notification_data['string'] = $msg;
                $notification_data['created'] = $created;


                $this->Notification->save($notification_data);
                $id = $this->Notification->getInsertID();
                $notification['to'] = $receiver_details['User']['device_token'];

                $notification['notification']['title'] =  "group invitation";
                $notification['notification']['body'] = $msg;
                $notification['notification']['badge'] = "1";
                $notification['notification']['sound'] = "default";
                $notification['notification']['icon'] = "";
                $notification['notification']['type'] = "live";
                $notification['notification']['user_id'] = $user_id;
                //$notification['notification']['name'] = $user_details['User']['first_name']." ".$user_details['User']['last_name'];
                // $notification['notification']['image'] = $user_details['User']['profile_pic'];

                $notification['data']['title'] = "group invitation";
                $notification['data']['body'] = $msg;
                $notification['data']['icon'] = "";
                $notification['data']['badge'] = "1";
                $notification['data']['sound'] = "default";
                $notification['data']['type'] = "live";
                $notification['data']['user_id'] = $user_id;
                // $notification['data']['receiver_id'] = $receiver_id;
                // $notification['notification']['receiver_id'] = $receiver_id;




                Utility::sendPushNotificationToMobileDevice(json_encode($notification));
                $details  = $this->Notification->getDetails($id);
                $output['code'] = 200;
                $output['msg'] = $details;
                echo json_encode($output);
                die();


            }


        }
    }

    public function pinVideo()
    {

        $this->loadModel("Video");




        if ($this->request->isPost()) {
            $json = file_get_contents('php://input');
            $data = json_decode($json, TRUE);


            $video_id = $data['video_id'];
            $pin = $data['pin'];
            $details = $this->Video->getDetails($video_id);

            if(count($details) > 0 ){


                $this->Video->id = $video_id;
                $this->Video->saveField('pin',$pin);



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


    public function acceptGroupInvite()
    {



        $this->loadModel('Notification');


        if ($this->request->isPost()) {


            $json = file_get_contents('php://input');
            $data = json_decode($json, TRUE);

            $id = $data['id'];
            $status = $data['status'];





            $details = $this->Notification->getDetails($id);

            if(count($details) > 0 ) {


                $this->Notification->id = $id;
                $this->Notification->saveField('status', $status);
                $details = $this->Notification->getDetails($id);


                $output['code'] = 200;
                $output['msg'] = $details;
                echo json_encode($output);
                die();


            }


        }
    }

    public function addPayout()
    {



        $this->loadModel('User');


        if ($this->request->isPost()) {


            $json = file_get_contents('php://input');
            $data = json_decode($json, TRUE);


            if(isset($data['paypal'])){

                $payout['paypal'] = $data['paypal'];
            }

            if(isset($data['wallet_address'])){

                $payout['paypal'] = $data['wallet_address'];
            }






            $details = $this->User->getUserDetailsFromID($data['user_id']);

            if(count($details) > 0 ) {


                $this->User->id = $data['user_id'];
                $this->User->save($payout);
                $details = $this->User->getUserDetailsFromID($data['user_id']);


                $output['code'] = 200;
                $output['msg'] = $details;
                echo json_encode($output);
                die();


            }


        }
    }


    public function addProduct()
    {


        $this->loadModel('Product');
        $this->loadModel('ProductImage');

        if ($this->request->isPost()) {


            $json = file_get_contents('php://input');
            $data = json_decode($json, TRUE);

            $user_id =  $data['user_id'];
            if(isset($data['category_id'])){
                $category_id = $data['category_id'];
                $product['category_id'] = $category_id;

            }


            if(isset( $data['title'])){
                $title = $data['title'];
                $product['title'] = $title;

            }

            if(isset( $data['status'])){
                $status = $data['status'];
                $product['status'] = $status;

            }

            if(isset( $data['price'])){
                $price = $data['price'];
                $product['price'] = $price;

            }

            if(isset( $data['description'])){
                $description = $data['description'];
                $product['description'] = $description;

            }






            $created = date('Y-m-d H:i:s', time());


            $product['user_id'] = $user_id;







            $folder_url = UPLOADS_FOLDER_URI;


            if (isset($data['id'])) {
                $product_id = $data['id'];
                $product['updated'] = $created;


                $this->Product->id = $product_id;
                $this->Product->save($product);
                $details = $this->Product->getDetails($product_id);

                $output['code'] = 200;
                $output['msg'] = $details;
                echo json_encode($output);
                die();
            } else {

                $product['created'] = $created;
                $this->Product->save($product);
                $this->Product->clear();
                $product_id = $this->Product->getInsertID();
                $details = $this->Product->getDetails($product_id);

                $order_no = Utility::randomNumber(8).uniqid().$product_id;
                $this->Product->id = $product_id;
                $this->Product->saveField('order_no',$order_no);
                $this->Product->clear();
                if (isset($data['images'])) {
                    if (count($data['images']) > 0) {

                        foreach ($data['images'] as $key=>$image_url) {

                            $images[$key]['image'] = $image_url['url'];
                            $images[$key]['product_id'] = $product_id;
                            $images[$key]['created'] = $created;


                        }
                    }
                    $this->ProductImage->saveAll($images);
                }


                $output['code'] = 200;
                $output['msg'] = $details;
                echo json_encode($output);
                die();

            }
        }



    }


    public function addProductFavourite()
    {


        $this->loadModel('ProductFavourite');
        $this->loadModel('Product');

        if ($this->request->isPost()) {


            $json = file_get_contents('php://input');
            $data = json_decode($json, TRUE);

            $favourite['user_id'] = $data['user_id'];
            $favourite['product_id'] = $data['product_id'];



            $favourite['created'] = date('Y-m-d H:i:s', time());
            $details = $this->Product->getDetails($data['product_id']);
            if(count($details) > 0) {

                $if_favourite =  $this->ProductFavourite->ifProductFavourite($data['user_id'], $data['product_id']);
                if(count($if_favourite) < 1) {
                    //$favourite['favourite'] = $data['favourite'];
                    $this->ProductFavourite->save($favourite);

                    $id = $this->ProductFavourite->getInsertID();

                    $output = array();
                    $details = $this->ProductFavourite->getDetails($id);


                    $output['code'] = 200;
                    $output['msg'] = $details;
                    echo json_encode($output);
                }else{

                    $this->ProductFavourite->deleteFavourite($data['user_id'], $data['product_id']);

                    $output['code'] = 200;
                    $output['msg'] = "deleted";
                    echo json_encode($output);
                    die();

                }


            }else{

                $output['code'] = 201;
                $output['msg'] = "Product do not exist";
                echo json_encode($output);

            }

        }
    }


    public function addProductImage(){

        $this->loadModel('ProductImage');
        $this->loadModel('Product');
        if ($this->request->isPost()) {

            $product_id = $this->request->data('product_id');

            $created = date('Y-m-d H:i:s', time());




            $details = $this->Product->getDetails($product_id);

            if (count($details > 0)) {


                $full_image_url = Utility::uploadAMultipartFileIntoFolder("file","png", UPLOADS_FOLDER_URI);

                $crop_image_url = Utility::cropImage($full_image_url);

                if (MEDIA_STORAGE == "s3") {
                    $full_image_url = Premium::fileUploadToS3($full_image_url, "png",UPLOADS_FOLDER_URI);

                    $crop_image_url = Premium::fileUploadToS3($crop_image_url, "png", UPLOADS_FOLDER_URI);
                }

                if ($full_image_url['code'] == 200) {



                    //$file_url = Utility::getCloudFrontUrl($result['msg'], "/tictic-video");
                    //$file_video_url = Utility::getCloudFrontUrl($result_video['msg'], "/profile");

                    $file_url_full = str_replace("tictic-video/", "", $full_image_url['msg']);
                    $half_image_url = str_replace("tictic-video/", "", $crop_image_url['msg']);

                    $parsedUrl_full = parse_url($file_url_full);
                    $parsedUrl_half = parse_url($half_image_url);

                    $cdn_url_full = CLOUDFRONT_URL . $parsedUrl_full['path'];
                    $cdn_url_half = CLOUDFRONT_URL . $parsedUrl_half['path'];

                    $product_image['product_id'] = $product_id;
                    $product_image['image'] = $cdn_url_full;
                    $product_image['thum'] = $cdn_url_half;
                    $product_image['created'] = $created;
                    $this->ProductImage->save($product_image);
                    $id = $this->ProductImage->getInsertID();
                    $details = $this->ProductImage->getDetails($id);

                    $output['code'] = 200;
                    $output['msg'] = $details;

                    echo json_encode($output);
                    die();
                }else{

                    $output['code'] = 201;
                    $output['msg'] = "Something went wrong in saving the data";

                    echo json_encode($output);
                    die();
                }

            }
        }
    }
    public function addProductImages()
    {


        $this->loadModel('Product');
        $this->loadModel('ProductImage');

        if ($this->request->isPost()) {


            $json = file_get_contents('php://input');
            $data = json_decode($json, TRUE);

            $product_id = $data['product_id'];

            $created = date('Y-m-d H:i:s', time());



            $folder_url = UPLOADS_FOLDER_URI;



            $details = $this->Product->getDetails($product_id);

            if (count($details > 0)) {



                if (isset($data['images'])) {
                    if (count($data['images']) > 0) {


                        $images = $data['images'];
                        $product_images = $this->ProductImage->getProductImageAgainstProductID($product_id);
                        if(count($product_images) > 0) {
                            foreach ($product_images as $product_image) {

                                $product_db_image = $product_image['ProductImage']['image'];
                                if (strlen($product_db_image) > 5) {
                                    @unlink($product_db_image);

                                }
                            }

                            $this->ProductImage->deleteAll(array(
                                'product_id' => $product_id
                            ), false);
                        }
                        foreach ($images as $k => $v) {




                            $filePath = Utility::uploadFileintoFolder($product_id, $v['image'], $folder_url);

                            $images[$k]['image'] = $filePath;
                            $images[$k]['product_id'] = $product_id;
                            $images[$k]['created'] = $created;

                        }

                        $this->ProductImage->saveAll($images);
                    }





                }

                $details = $this->Product->getDetails($product_id);

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

    public function deleteProductAttribute(){

        $this->loadModel('ProductAttribute');






        if ($this->request->isPost()) {
            $json = file_get_contents('php://input');
            $data = json_decode($json, TRUE);

            $id = $data['id'];
            $details = $this->ProductAttribute->getDetails($id);

            if (count($details) > 0) {





                $this->ProductAttribute->delete($id,true);
                $output['code'] = 200;

                $output['msg'] = "deleted";


                echo json_encode($output);


                die();
            }else{

                Message::ALREADYDELETED();
                die();
            }

        }


    }

    public function deleteProduct(){

        $this->loadModel('Product');
        $this->loadModel('ProductImage');





        if ($this->request->isPost()) {
            $json = file_get_contents('php://input');
            $data = json_decode($json, TRUE);

            $product_id = $data['product_id'];
            $details = $this->Product->getDetails($product_id);

            if (count($details) > 0) {



                $product_images = $this->ProductImage->getProductImageAgainstProductID($product_id);

                if (count($product_images) > 0) {
                    foreach ($product_images as $product_image) {

                        $product_db_image = $product_image['ProductImage']['image'];
                        if (strlen($product_db_image) > 5) {
                            @unlink($product_db_image);

                        }
                    }


                }

                $this->Product->delete($product_id,true);
                $output['code'] = 200;

                $output['msg'] = "deleted";


                echo json_encode($output);


                die();
            }

        }


    }


    public function deleteProductSingleImage(){

        $this->loadModel('Product');
        $this->loadModel('ProductImage');





        if ($this->request->isPost()) {
            $json = file_get_contents('php://input');
            $data = json_decode($json, TRUE);

            $product_image_id = $data['product_image_id'];
            $details = $this->ProductImage->getDetails($product_image_id);

            if (count($details > 0)) {






                $product_db_image = $details['ProductImage']['image'];
                if (strlen($product_db_image) > 5) {
                    @unlink($product_db_image);

                }

                $this->ProductImage->id = $product_image_id;
                $this->ProductImage->delete();


                $output['code'] = 200;

                $output['msg'] = "deleted";


                echo json_encode($output);


                die();
            }

        }


    }

    public function addPromotion()
    {

        $this->loadModel("Promotion");




        if ($this->request->isPost()) {
            $json = file_get_contents('php://input');
            $data = json_decode($json, TRUE);


            $video_id = $data['video_id'];
            $user_id = $data['user_id'];
            $total_reach = $data['total_reach'];
            $destination = $data['destination'];
            $audience_id = $data['audience_id'];

            if($destination == "website"){

                $website_url = $data['website_url'];
                $promotion_data['website_url'] = $website_url;
                $action_button = $data['action_button'];
                $promotion_data['action_button'] = $action_button;
            }



            if($audience_id < 1){

                $audience_id =  $this->addDefaultAudience();
            }
            $coin = $data['coin'];



            $start_datetime = $data['start_datetime'];
            $end_datetime = $data['end_datetime'];

            $active = 0;
            $created = date('Y-m-d H:i:s', time());

            $date1 = new DateTime($start_datetime);
            $date2 = new DateTime($end_datetime);
            $interval = $date1->diff($date2);
            $days = $interval->format('%a days');


            $promotion_data['video_id'] = $video_id;
            $promotion_data['user_id'] = $user_id;
            $promotion_data['destination'] = $destination;
            $promotion_data['coin'] = $coin;
            $promotion_data['audience_id'] = $audience_id;


            $promotion_data['total_reach'] = $total_reach;


            $promotion_data['start_datetime'] = $start_datetime;
            $promotion_data['end_datetime'] = $end_datetime;
            $promotion_data['active'] = $active;
            $promotion_data['created'] = $created;









            $this->Promotion->save($promotion_data);




            $id = $this->Promotion->getInsertID();




            $details = $this->Promotion->getDetails($id);
           $wallet_total =  $this->walletTotal($user_id);
            $details['User']['wallet'] = $wallet_total['total'];

            $output['code'] = 200;
            $output['msg'] = $details;
            echo json_encode($output);


            die();




        }
    }


    function walletTotal($user_id){
        $this->loadModel('WithdrawRequest');
        $this->loadModel('Promotion');
        $this->loadModel('LiveStreaming');
        $this->loadModel('LiveStreamingWatch');
        $this->loadModel('ReferralUsed');
        $this->loadModel('Setting');
        $this->loadModel('Order');
        $this->loadModel('DailyCheckin');
        $purchase_amount_total = 0;
        $gift_receive[0]['total_amount'] = 0;
        $gift_send[0]['total_amount'] = 0;
        $olderThanDate = date('Y-m-d H:i:s', strtotime('-15 days'));
        $coins_daily_checkin = $this->DailyCheckin->counCoins($user_id);
        $withdraw_request_detail = $this->WithdrawRequest->getTotalCoins($user_id);

        if(strlen($withdraw_request_detail[0]['total_coin']) < 1){

            $withdraw_request_detail[0]['total_coin'] = 0;

        }
        if(strlen($coins_daily_checkin[0]['total_coin']) < 1){

            $coins_daily_checkin[0]['total_coin'] = "0";

        }
        if (in_array("Gift", App::objects('Model'))) {



            $this->loadModel('PurchaseCoin');
            $this->loadModel('GiftSend');



            $purchase_amount_total = $this->PurchaseCoin->totalAmountPurchase($user_id);
            $gift_send = $this->GiftSend->countGiftSendByUser($user_id);
            $gift_receive = $this->GiftSend->countGiftReceiveByUser($user_id);
        }


        $user_place_order_coins = $this->Order->getTotalCoins($user_id);
        $seller_pending_coins = $this->Order->getPendingSellerBalance($user_id,$olderThanDate);
        $seller_coins = $this->Order->getSellerBalance($user_id,$olderThanDate);
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



        if(strlen($seller_coins[0]['total_amount']) < 1){

            $seller_coins[0]['total_amount'] = "0";

        }

        if(strlen($seller_pending_coins[0]['total_amount']) < 1){

            $seller_pending_coins[0]['total_amount'] = "0";

        }
        if(strlen($user_place_order_coins[0]['total_amount']) < 1){

            $user_place_order_coins[0]['total_amount'] = "0";

        }
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







        $earned_money =  $gift_receive[0]['total_amount'] + $coins_daily_checkin[0]['total_coin'] +$purchase_amount_total[0]['total_amount']+  $live_stream_earned_coin[0]['total_amount'] + $referral_earn + $seller_coins[0]['total_amount'];





        $total_money = $earned_money -  $gift_send[0]['total_amount'] - $promotion_coin[0]['total_amount'] - $live_stream_watch_coin[0]['total_amount'] -  $withdraw_request_detail[0]['total_coin'] - $user_place_order_coins[0]['total_amount'] - $seller_coins[0]['total_amount'];



        $output['gifts_receive'] =  (string)$gift_receive[0]['total_amount']  + $live_stream_earned_coin[0]['total_amount'];
        $output['gifts_send'] =  (string)$gift_send[0]['total_amount'];

        $output['total'] =  (string)$total_money;
        $output['seller_pending_balance'] =  (string)$seller_pending_coins[0]['total_amount'];
        return $output;


    }


    public function destinationTap()
    {

        $this->loadModel("Promotion");



        if ($this->request->isPost()) {
            $json = file_get_contents('php://input');
            $data = json_decode($json, TRUE);


            $promotion_id = $data['promotion_id'];
            $details = $this->Promotion->getDetails($promotion_id);

            if(count($details) > 0 ){

                $destination_tap =  $details['Promotion']['destination_tap'];
                $this->Promotion->id = $promotion_id;
                $this->Promotion->saveField('destination_tap',$destination_tap + 1);



                $details = $this->Promotion->getDetails($promotion_id);

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
    /*public function addDeviceData()
    {


        $this->loadModel('User');
        $this->loadModel('PushNotification');
        $this->loadModel('PrivacySetting');

        if ($this->request->isPost()) {


            $json = file_get_contents('php://input');
            $data = json_decode($json, TRUE);

            $user['device_token'] = $data['device_token'];
            $user['ip'] = $data['ip'];
            $user['device'] = $data['device'];
            $user['version'] = $data['version'];
            $ipdat = @json_decode(file_get_contents(
                "http://www.geoplugin.net/json.gp?ip=" .  $data['ip']));


            if(count($ipdat) > 0 ) {



                $user['city'] = strtolower($ipdat->geoplugin_city);
                $user['country'] = strtolower($ipdat->geoplugin_countryName);
            }
            $user_id = $data['user_id'];


            $userDetails = $this->User->getUserDetailsFromID($user_id);
            if(count($userDetails) > 0) {

                $this->User->id = $user_id;
                $this->User->save($user);
                $if_exist = $this->PushNotification->getDetails($user_id);


                if(count($if_exist) < 1) {
                    $notification['likes'] = 1;
                    $notification['comments'] = 1;
                    $notification['new_followers'] = 1;
                    $notification['mentions'] = 1;
                    $notification['direct_messages'] = 1;
                    $notification['video_updates'] = 1;
                    $notification['id'] = $user_id;

                    $this->PushNotification->save($notification);
                }

                $if_exist = $this->PrivacySetting->getDetails($user_id);
                if(count($if_exist) < 1) {
                    $settings['videos_download'] = 1;
                    $settings['videos_repost'] = 1;
                    $settings['direct_message'] = "everyone";
                    $settings['duet'] = "everyone";
                    $settings['liked_videos'] = "me";
                    $settings['video_comment'] = "everyone";
                    $settings['id'] = $user_id;

                    $this->PrivacySetting->save($settings);
                }
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
*/
    public function addDeviceData()
    {


        $this->loadModel('User');
        $this->loadModel('Country');
        $this->loadModel('State');
        $this->loadModel('City');
        // $this->loadModel('UserOnline');
        $this->loadModel('PushNotification');
        $this->loadModel('PrivacySetting');


        if ($this->request->isPost()) {


            $json = file_get_contents('php://input');
            $data = json_decode($json, TRUE);

            $user['device_token'] = $data['device_token'];
            $user['ip'] = $data['ip'];
            $user['device'] = $data['device'];
            $user['version'] = $data['version'];
            $created = date('Y-m-d H:i:s', time());



            if(isset($data['lat'])){

                $user['lat'] = $data['lat'];
                $user['long'] = $data['long'];

                $location_details = Utility::getCountryCityProvinceFromLatLong($data['lat'],$data['long']);

                if(count($location_details) > 0){

                    if(strlen($location_details['location_string']) > 2){


                        $user['country'] = $location_details['country'];
                    }
                }
            }








            $user_id = $data['user_id'];


            $userDetails = $this->User->getUserDetailsFromID($user_id);
            if(count($userDetails) > 0) {

                $this->User->id = $user_id;
                $this->User->save($user);

                $if_exist = $this->PushNotification->getDetails($user_id);


                if(count($if_exist) < 1) {
                    $notification['likes'] = 1;
                    $notification['comments'] = 1;
                    $notification['new_followers'] = 1;
                    $notification['mentions'] = 1;
                    $notification['direct_messages'] = 1;
                    $notification['video_updates'] = 1;
                    $notification['id'] = $user_id;

                    $this->PushNotification->save($notification);
                }

                $if_exist = $this->PrivacySetting->getDetails($user_id);
                if(count($if_exist) < 1) {
                    $settings['videos_download'] = 1;
                    $settings['videos_repost'] = 1;
                    $settings['direct_message'] = "everyone";
                    $settings['duet'] = "everyone";
                    $settings['liked_videos'] = "me";
                    $settings['video_comment'] = "everyone";
                    $settings['id'] = $user_id;

                    $this->PrivacySetting->save($settings);
                }

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


    public function addPhoneDeviceData($device_token,$ip,$device,$version,$user_id)
    {


        $this->loadModel('User');
        $this->loadModel('Country');
        $this->loadModel('State');
        $this->loadModel('City');
        // $this->loadModel('UserOnline');
        $this->loadModel('PushNotification');
        $this->loadModel('PrivacySetting');






        $json = file_get_contents('php://input');
        $data = json_decode($json, TRUE);

        $user['device_token'] = $device_token;
        $user['ip'] = $ip;
        $user['device'] = $device;
        $user['version'] = $version;
        $created = date('Y-m-d H:i:s', time());

        $ipdat =   Utility::getLocationFromIP($ip);



        $state_id = 0;
        $country_id = 0;
        $city_id = 0;
        if(count($ipdat) > 0 ) {

            /*echo 'Country Name: ' . $ipdat->geoplugin_countryName . "\n";
            echo 'City Name: ' . $ipdat->geoplugin_city . "\n";
            echo 'Continent Name: ' . $ipdat->geoplugin_continentName . "\n";
            echo 'Latitude: ' . $ipdat->geoplugin_latitude . "\n";
            echo 'Longitude: ' . $ipdat->geoplugin_longitude . "\n";
            echo 'Currency Symbol: ' . $ipdat->geoplugin_currencySymbol . "\n";
            echo 'Currency Code: ' . $ipdat->geoplugin_currencyCode . "\n";
            echo 'Timezone: ' . $ipdat->geoplugin_timezone;*/
            $state_name = strtolower($ipdat['geoplugin_region']);
            $state_short_name = strtolower($ipdat['geoplugin_regionCode']);
            $user['city'] = strtolower($ipdat['geoplugin_city']);
            $user['country'] = strtolower($ipdat['geoplugin_countryName']);
            $short_country_name = strtolower($ipdat['geoplugin_countryCode']);



            $country_details = $this->Country->getCountryAgainstName($user['country']);
            $country_details_short_name = $this->Country->getCountryAgainstShortName($short_country_name);





            if(count($country_details) > 0){

                $country_id =  $country_details['Country']['id'];



            }else    if(count($country_details_short_name) > 0){

                $country_id =  $country_details_short_name['Country']['id'];

            }

            $state_details = $this->State->getStateAgainstName($state_name,$country_id);
            $state_short_details = $this->State->getStateAgainstShortName($state_short_name,$country_id);

            if(count($state_details) > 0){


                $state_id =  $state_details['State']['id'];


            }else if(count($state_short_details) > 0 ){

                $state_id = $state_short_details['State']['id'];
            }
            $city_details = $this->City->getCityAgainstName($user['city'],$state_id,$country_id);
            if(count($city_details) > 0){


                $city_id =  $city_details['City']['id'];


            }
        }
        $user_id = $user_id;

        $user['country_id'] = $country_id;
        $user['state_id'] = $state_id;
        $user['city_id'] = $city_id;

        $userDetails = $this->User->getUserDetailsFromID($user_id);
        if(count($userDetails) > 0) {

            $this->User->id = $user_id;
            $this->User->save($user);

            $if_exist = $this->PushNotification->getDetails($user_id);


            if(count($if_exist) < 1) {
                $notification['likes'] = 1;
                $notification['comments'] = 1;
                $notification['new_followers'] = 1;
                $notification['mentions'] = 1;
                $notification['direct_messages'] = 1;
                $notification['video_updates'] = 1;
                $notification['id'] = $user_id;

                $this->PushNotification->save($notification);
            }

            $if_exist = $this->PrivacySetting->getDetails($user_id);
            if(count($if_exist) < 1) {
                $settings['videos_download'] = 1;
                $settings['videos_repost'] = 1;
                $settings['direct_message'] = "everyone";
                $settings['duet'] = "everyone";
                $settings['liked_videos'] = "me";
                $settings['video_comment'] = "everyone";
                $settings['id'] = $user_id;

                $this->PrivacySetting->save($settings);
            }
            //$user_online['user_id'] = $user_id;
            //$user_online['created'] = $created;
            //$this->UserOnline->save($user_online);

            $output = array();
            $userDetails = $this->User->getUserDetailsFromID($user_id);


            return $userDetails;
        }
        return true;

    }


    public function repostVideo()
    {



        $this->loadModel("Video");
        $this->loadModel("Notification");
        $this->loadModel("User");
        $this->loadModel("RepostVideo");



        if ($this->request->isPost()) {
            $json = file_get_contents('php://input');
            $data = json_decode($json, TRUE);


            $video_id = $data['video_id'];
            $user_id = $data['repost_user_id'];
            if(isset($data['user_id'])){

                $user_id = $data['user_id'];
            }






            $video_details = $this->Video->getOnlyVideoDetails($video_id);
            $post_data['user_id'] = $user_id;
            $post_data['video_id'] = $video_id;
            $post_data['created'] = date('Y-m-d H:i:s', time());


            if (count($video_details) > 0) {
                $ifExist = $this->RepostVideo->ifExist($post_data);

                if(count($ifExist) < 1) {



                    $this->RepostVideo->save($post_data);


                    $id = $this->RepostVideo->getInsertID();
                    $details = $this->RepostVideo->getDetails($id);


                    $userDetails = $this->User->getUserDetailsFromID($user_id);

                    if(count($userDetails) < 1){

                        Message::EMPTYDATA();
                        die();
                    }

                    if($user_id == $details['Video']['user_id']){


                    }else {
                        $msg = $userDetails['User']['username'] . " has reposted your video";

                        if (strlen($video_details['User']['device_token']) > 8) {
                            $notification['to'] = $video_details['User']['device_token'];

                            $notification['notification']['title'] = "video reposted";
                            $notification['notification']['body'] = $msg;
                            $notification['notification']['badge'] = "1";
                            $notification['notification']['sound'] = "default";
                            $notification['notification']['icon'] = "";
                            $notification['notification']['type'] = "video_reposted";
                            $notification['notification']['video_id'] = $video_id;
                            $notification['notification']['name'] = $userDetails['User']['first_name'] . " " . $userDetails['User']['last_name'];
                            $notification['notification']['image'] = $userDetails['User']['profile_pic'];

                            $notification['data']['title'] = "video reposted";
                            $notification['data']['body'] = $msg;
                            $notification['data']['icon'] = "";
                            $notification['data']['badge'] = "1";
                            $notification['data']['sound'] = "default";
                            $notification['data']['type'] = "video_reposted";
                            $notification['data']['video_id'] = $video_id;
                            $notification['data']['name'] = $userDetails['User']['first_name'] . " " . $userDetails['User']['last_name'];
                            $notification['data']['image'] = $userDetails['User']['profile_pic'];
                            //$notification['data']['receiver_id'] = $receiver_id;
                            //$notification['notification']['receiver_id'] = $receiver_id;


                            Utility::sendPushNotificationToMobileDevice(json_encode($notification));


                            $notification_data['sender_id'] = $user_id;
                            $notification_data['receiver_id'] = $video_details['User']['id'];
                            $notification_data['type'] = "video_reposted";
                            $notification_data['video_id'] = $video_id;

                            $notification_data['string'] = $msg;
                            $notification_data['created'] = date('Y-m-d H:i:s', time());

                            $this->Notification->save($notification_data);
                        }

                    }
                    $output['code'] = 200;
                    $output['msg'] = $details;
                    echo json_encode($output);


                    die();
                }else{

                    $this->RepostVideo->id = $ifExist['RepostVideo']['id'];
                    $this->RepostVideo->delete();

                    $output['code'] = 200;
                    $output['msg'] = "success";
                    echo json_encode($output);


                    die();
                }
            }else{

                $output['code'] = 201;
                $output['msg'] = "video not available";
                echo json_encode($output);


                die();
            }


        }
    }

    public function test0(){

      echo  Google::fileUpload();
    }

    public function placeOrder()
    {


        $this->loadModel("Order");
        $this->loadModel("User");
        $this->loadModel("OrderProduct");

        $this->loadModel("Product");

        $this->loadModel("DeliveryAddress");
        $this->loadModel("OrderTransaction");

        $this->loadModel("CouponUsed");





        if ($this->request->isPost()) {


            $json = file_get_contents('php://input');
            $data = json_decode($json, TRUE);




            $user_id       = $data['user_id'];
            $store_user_id       = $data['store_user_id'];


            $payment_id    = 0;
            $delivery_address_id    = $data['delivery_address_id'];

            $cod           = $data['cod'];




            $instruction  = $data['instruction'];
            $coupon_id     = $data['coupon_id'];
            $status        = 0;
            $device        = @$data['device'];
            $version     =   @$data['version'];

            $products        = $data['products'];


            $delivery_fee  = $data['delivery_fee'];
            $delivery      = $data['delivery'];
            $total      = $data['total'];

            $discount = $data['discount'];

            $created = date('Y-m-d H:i:s', time());






            if(count($this->User->getUserDetailsFromID($user_id)) < 0){

                echo Message::ERROR();
                die();
            }




            $user_details_check = $this->User->getUserDetailsFromID($user_id);


            if(count($user_details_check) > 0) {


                $order['user_id'] = $user_id;
                $order['store_user_id'] = $store_user_id;

                $order['status'] = $status;
                $order['created'] = $created;


                $order['discount'] = $discount;
                $order['payment_method_id'] = $payment_id;
                $order['cod'] = $cod;
                $order['total'] = $total;
                $order['version'] = $version;

                $order['delivery_address_id'] = $delivery_address_id;


                $order['device'] = $device;
                $order['delivery'] = $delivery;

                $order['discount'] = $discount;

                $order['instruction'] = $instruction;
                $order['delivery_fee'] = $delivery_fee;


                $cart_random_id = Utility::randomNumber(6);
                $this->Order->save($order);
                $this->Order->clear();
                $order_id = $this->Order->getInsertID();

                foreach($products as $key=>$product){

                    //$count = $this->Apps->checkIfDuplicate($apps[$i]['app_name'], $apps[$i]['app_bundle_id'],$phone_id);




                    $product_details = $this->Product->getDetails($product['product_id']);
                    if(count($product_details) > 0) {

                        $order_product[$key]['product_id'] = $product['product_id'];
                        $order_product[$key]['order_id'] = $order_id;
                        $order_product[$key]['product_title'] = $product_details['Product']['title'];
                        $order_product[$key]['product_price'] = $product_details['Product']['price'];
                        $order_product[$key]['product_quantity'] = $product['product_quantity'];
                       // $order_product[$key]['product_attribute_variation_id'] = $product['product_attribute_variation_id'];
                        $order_product[$key]['product_image'] =  $product_details['ProductImage'][0]['image'];

                        //$order_product['product_attritube_combination_id'] = $product['product_attritube_combination_id'];

                    }











                    if ($coupon_id > 0) {
                        $store_coupon['coupon_id'] = $coupon_id;
                        $store_coupon['order_id'] = $order_id;
                        $store_coupon['user_id'] = $user_id;
                        $store_coupon['created'] = $created;
                        $this->CouponUsed->save($store_coupon);
                        $this->CouponUsed->clear();
                    }


                    if (count($order_product) > 0) {

                        $this->OrderProduct->saveAll($order_product);
                    }

                }


                /*if ($payment_id > 0) {
                    $stripe_charge = $this->deductPayment($payment_id, round($total));
                    $order['stripe_charge'] = $stripe_charge;
                }*/
                if ($coupon_id > 0) {
                    $coupon['coupon_id'] = $coupon_id;
                    $coupon['order_id'] = $order_id;
                    $coupon['user_id'] = $user_id;
                    $coupon['created'] = $created;

                    $this->CouponUsed->save($coupon);
                }

                /*if (isset($data['transaction'])) {


                    $transaction = $data['transaction'];

                    if(count($transaction) > 0){

                        $order_transaction['type'] = $transaction['type'];

                        if($transaction['type'] == "stripe"){

                            $order_transaction['value'] = $order['stripe_charge'];
                        }

                        $order_transaction['value'] = $transaction['value'];

                        $order_transaction['cart_random_id'] = $cart_random_id;
                        $order_transaction['created'] = $created;

                        $this->OrderTransaction->save($order_transaction);


                    }
                }*/

                /*for ($i = 0; $i < count($products); $i++) {

                    $order_product[$i]['title'] = $products[$i]['title'];
                    $order_product[$i]['quantity'] = $products[$i]['quantity'];
                    $order_product[$i]['price'] = $products[$i]['price'];
                    $order_product[$i]['order_id'] = $order_id;



                }

                $this->OrderProduct->saveAll($order_product);*/
                $order_detail = $this->Order->getDetails($order_id);





                $output['code'] = 200;

                $output['msg'] = $order_detail;
                echo json_encode($output);
                die();
            }else{

                $output['code'] = 201;

                $output['msg'] = "user id  do not exist";
                echo json_encode($output);
                die();


            }





        }
    }
    public function addUserImage()
    {


        $this->loadModel('User');


        if ($this->request->isPost()) {




            $user_id = $this->request->data('user_id');
            $extension = $this->request->data('extension');

            $userDetails = $this->User->getUserDetailsFromID($user_id);


            if (count($userDetails) > 0) {

                if($extension == "gif"){

                    $file_url = $userDetails['User']['profile_gif'];

                }else{
                    $file_url = $userDetails['User']['profile_pic'];

                }

                $key = 'http';
                if (strlen($file_url) > 5) {

                if (strpos($file_url, $key) !== false) {

                    $if_method_exist = method_exists('Premium', 'deleteObjectS3');

                    if ($if_method_exist) {

                        //$result1 = Premium::deleteObjectS3($file_url);

                    }

                }else{


                    if(file_exists($file_url)) {
                        @unlink($file_url);
                    }
                    }
                }

                $folder_url = UPLOADS_FOLDER_URI;
                if (MEDIA_STORAGE == "s3") {

                    if($extension == "mp4"){


                        $video_url = Utility::uploadAMultipartFileIntoFolder("file", "mp4", $folder_url);

                        $gif_url = Utility::videoToGif($video_url,$user_id);




                        $profile_folder = $folder_url."/profile";
                        $result = Premium::fileUploadToS3($gif_url,"gif",$profile_folder);
                        $result_video = Premium::fileUploadToS3($video_url,"mp4",$profile_folder);







                    }else{

                        $result = Premium::fileUploadToS3Multipart("file", $extension);
                    }




                    if($result['code'] == 200) {

                        if (strpos($result['msg'], "http") !== false) {



                            $file_url = Utility::getCloudFrontUrl($result['msg'], "/profile");
                            //$file_video_url = Utility::getCloudFrontUrl($result_video['msg'], "/profile");



                        }else {

                            $file_key = $result['msg'];

                            $delimiter = '/';
                            $arr = explode($delimiter, $file_key);
                            $pos = array_search('profile', $arr);
                            if ($pos !== false && $pos > 0) {
                                $key = implode($delimiter, array_slice($arr, $pos));
                            } else {
                                $key = $file_key;
                            }

                            if (strpos($result['msg'], "tictic-video/") !== false) {

                                $key =  str_replace("tictic-video/",'',$key);

                            }

                            $file_url = CLOUDFRONT_URL . "/" . $key;


                        }
                    }
                }else{



                    $file_url = Utility::uploadAMultipartFileIntoFolder("file", $extension, $folder_url);

                    if($extension == "mp4"){


                        $file_url = Utility::videoToGif($file_url,$user_id);
                    }
                }


                if($extension == "mp4"){

                    $user['profile_gif'] = $file_url;
                }else{

                    $user['profile_pic'] = $file_url;
                }



                if(strlen(DEEPENGIN_KEY) > 5){



                    $if_nudity_exist =  Utility::verifyPhoto($file_url);






                    if($if_nudity_exist['code'] == 200){

                        if(count($if_nudity_exist['msg']['ModerationResult'] > 0)){




                            $name = strtolower($if_nudity_exist['msg']['ModerationResult']['Name']);
                            $parent_name = strtolower($if_nudity_exist['msg']['ModerationResult']['ParentName']);

                           $found1 = Utility::ifWordFound(strtolower($name));
                            $found2 = Utility::ifWordFound(strtolower($parent_name));
                            if ($found1 || $found2) {
                                $output['code'] = 201;
                                $output['msg'] = "Nudity found. Your picture cannot be updated";
                                echo json_encode($output);

                                die();
                            }
                        }

                    }
                }

                $this->User->id = $user_id;
                if (!$this->User->save($user)) {
                    echo Message::DATASAVEERROR();
                    die();
                }


                $output = array();
                $userDetails = $this->User->getUserDetailsFromID($user_id);


                $output['code'] = 200;
                $output['msg'] = $userDetails;
                echo json_encode($output);


            } else {

                $output['code'] = 201;
                $output['msg'] = "user do not exist";
                echo json_encode($output);
            }


        }
    }

    public function addUserImageNewTest()
    {


        $this->loadModel('User');


        if ($this->request->isPost()) {


            $json = file_get_contents('php://input');
            $data = json_decode($json, TRUE);

            $user_id = $data['user_id'];
            $extension = $data['extension'];
            $folder_url = UPLOADS_FOLDER_URI;
            $userDetails = $this->User->getUserDetailsFromID($user_id);



            //*******************delete images****************/


            if(count($userDetails) > 0){

                $profile_image_url = $userDetails['User']['profile_pic'];
                $profile_image_small_url = $userDetails['User']['profile_pic_small'];






                //post image

                $image_big = $data['profile_pic'];
                $image_small = $data['profile_pic_small'];

                $filePath_big = Utility::uploadFileintoFolderDir($image_big, $folder_url,$extension);
                $filePath_small = Utility::uploadFileintoFolderDir($image_small, $folder_url,$extension);




                $if_method_exist = method_exists('Premium', 'profileImageToS3');


                $file_url = BASE_URL.$filePath_big;

                $user['profile_pic'] = $filePath_big;
                $user['profile_pic_small'] = $filePath_small;






                if(count($user) > 0) {
                    $this->User->id = $user_id;
                    if (!$this->User->save($user)) {
                        echo Message::DATASAVEERROR();
                        die();
                    }


                }



                $output = array();
                $userDetails = $this->User->getUserDetailsFromID($user_id);


                $output['code'] = 200;
                $output['msg'] = $userDetails;
                echo json_encode($output);

                die();

            }

            Message::EMPTYDATA();
            die();

        }
    }


    public function addUserImageNew()
    {


        $this->loadModel('User');


        if ($this->request->isPost()) {


            $json = file_get_contents('php://input');
            $data = json_decode($json, TRUE);

            $user_id = $data['user_id'];
            $extension = $data['extension'];
            $folder_url = UPLOADS_FOLDER_URI;
            $userDetails = $this->User->getUserDetailsFromID($user_id);



                //*******************delete images****************/


            if(count($userDetails) > 0){

                $profile_image_url = $userDetails['User']['profile_pic'];
                $profile_image_small_url = $userDetails['User']['profile_pic_small'];


                if(strlen($profile_image_url) > 5) {

                    $key = 'http';


                    if (strpos($profile_image_url, $key) !== false) {

                        $if_method_exist = method_exists('Premium', 'deleteObjectS3');

                        if ($if_method_exist) {
                           // Premium::deleteObjectS3($profile_image_url);
                           // Premium::deleteObjectS3($profile_image_small_url);


                        }

                    } else {


                        @unlink($profile_image_url);
                        @unlink($profile_image_small_url);
                    }

                }



                //post image

                $image_big = $data['profile_pic'];
                $image_small = $data['profile_pic_small'];

                $filePath_big = Utility::uploadFileintoFolderDir($image_big, $folder_url,$extension);
                $filePath_small = Utility::uploadFileintoFolderDir($image_small, $folder_url,$extension);




                $if_method_exist = method_exists('Premium', 'profileImageToS3');
                if (MEDIA_STORAGE == "s3") {


                    if($if_method_exist){


                        if($extension == "jpg"){

                            $extension = "jpeg";
                        }
                        $result_big = Premium::profileImageToS3($filePath_big,$extension);
                        $result_small = Premium::profileImageToS3($filePath_small,$extension);


                        if($result_big['code'] == 200) {
                            $user['profile_pic'] = Utility::getCloudFrontUrl($result_big['msg'], "/profile");

                        }

                        if($result_small['code'] == 200) {

                            $user['profile_pic_small'] = Utility::getCloudFrontUrl($result_small['msg'], "/profile");
                        }
                        @unlink($filePath_big);
                        @unlink($filePath_small);

                        $file_url = Utility::getCloudFrontUrl($result_big['msg'], "/profile");
                    }




                }else{

                    $file_url = BASE_URL.$filePath_big;

                    $user['profile_pic'] = $filePath_big;
                    $user['profile_pic_small'] = $filePath_small;


                }

                if(strlen(DEEPENGIN_KEY) > 5){



                   $if_nudity_exist =  Utility::verifyPhoto($file_url);



                   if($if_nudity_exist['code'] == 200){

                       if(count($if_nudity_exist['msg']['ModerationResult'] > 0)){



                           $name = strtolower($if_nudity_exist['msg']['ModerationResult']['Name']);
                           $parent_name = strtolower($if_nudity_exist['msg']['ModerationResult']['ParentName']);

                           $found1 = Utility::ifWordFound(strtolower($name));
                           $found2 = Utility::ifWordFound(strtolower($parent_name));
                           if ($found1 || $found2) {
                               $output['code'] = 201;
                               $output['msg'] = "Nudity found. Your picture cannot be updated";
                               echo json_encode($output);

                               die();
                           }
                       }

                   }
                }

                if(count($user) > 0) {
                    $this->User->id = $user_id;
                    if (!$this->User->save($user)) {
                        echo Message::DATASAVEERROR();
                        die();
                    }


                }



                $output = array();
                $userDetails = $this->User->getUserDetailsFromID($user_id);


                $output['code'] = 200;
                $output['msg'] = $userDetails;
                echo json_encode($output);

                die();

            }

            Message::EMPTYDATA();
            die();

        }
    }

    public function shareVideo(){

        $this->loadModel('Video');

        if ($this->request->isPost()) {
            $json = file_get_contents('php://input');
            $data = json_decode($json, TRUE);

            $video_id = $data['video_id'];


            $video_details = $this->Video->getOnlyVideoDetails($video_id);

            if(count($video_details) > 0) {




                $this->Video->id = $video_id;
                $this->Video->saveField('share',$video_details['Video']['share'] + 1);
                $video_details = $this->Video->getOnlyVideoDetails($video_id);
                $output['code'] = 200;

                $output['msg'] = $video_details;


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

    public function addUserProfileVideo()
    {


        $this->loadModel('User');


        if ($this->request->isPost()) {


            $user_id = $this->request->data('user_id');

            $userDetails = $this->User->getUserDetailsFromID($user_id);










                    if(count($userDetails) > 0) {


                        //************************ delete Video ***********************/
                        $profile_gif_url = $userDetails['User']['profile_gif'];

                        if(strlen($profile_gif_url) > 5) {

                            $key = 'http';


                            if (strpos($profile_gif_url, $key) !== false) {

                                $if_method_exist = method_exists('Premium', 'deleteObjectS3');

                                if ($if_method_exist) {
                                    Premium::deleteObjectS3($profile_gif_url);


                                }

                            } else {


                                @unlink($profile_gif_url);
                        }

                        }
                        //************************ end delete***********************/


                        $file_url = Utility::uploadFileintoFolderDir("", "", "mp4");


                        $gif_url = Utility::videoToGif($file_url,$user_id);


                        @unlink($file_url);

                        if (MEDIA_STORAGE == "s3") {
                            if (method_exists('Premium', 'profileImageToS3')) {


                                $result = Premium::profileImageToS3($gif_url,"gif");

                                if($result['code'] == 200){

                                    @unlink($gif_url);
                                    $gif_url = Utility::getCloudFrontUrl($result['msg'], "/profile");


                                }
                            }



                            }
                        $user['profile_gif'] = $gif_url;





                        if (count($user) > 0) {
                            $this->User->id = $user_id;
                            if (!$this->User->save($user)) {
                                echo Message::DATASAVEERROR();
                                die();
                            }


                        }


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

    public function userVerificationRequest()
    {


        $this->loadModel('VerificationRequest');
        $this->loadModel('User');


        if ($this->request->isPost()) {


            $json = file_get_contents('php://input');
            $data = json_decode($json, TRUE);

            $user_id = $data['user_id'];
            $created = date('Y-m-d H:i:s', time());
            $details = $this->VerificationRequest->getVerificationDetailsAgainstUserID($user_id);



            if (isset($data['attachment'])) {


                $attachment = $data['attachment'];
                $folder_url = UPLOADS_FOLDER_URI;

                $filePath = Utility::uploadFileintoFolder($user_id, $attachment, $folder_url);

                $verification_data['user_id'] = $user_id;
                $verification_data['attachment'] = $filePath;
                $verification_data['update_time']  = $created;

                if(count($details) > 0) {
                    $attachment = $details['VerificationRequest']['attachment'];
                    if (strlen($attachment) > 5) {
                        @unlink($attachment);

                    }



                    $this->VerificationRequest->id = $details['VerificationRequest']['id'];
                    $this->VerificationRequest->save($verification_data);
                    $id = $details['VerificationRequest']['id'];

                }else{

                    $verification_data['created']  = $created;
                    $this->VerificationRequest->save($verification_data);
                    $id = $this->VerificationRequest->getInsertID();
                }



                $output = array();
                $details = $this->VerificationRequest->getDetails($id);
                $user_details = $this->User->getUserDetailsFromID($user_id);
                if(count($user_details) > 0) {
                    $verification_details = $this->VerificationRequest->getVerificationDetailsAgainstUserID($user_details['User']['id']);

                    if (count($verification_details) > 0) {

                        $user_details['User']['verification_applied'] = 1;
                    }else{

                        $user_details['User']['verification_applied'] = 0;

                    }
                }

                $output['code'] = 200;
                $output['msg'] = $details;
                echo json_encode($output);


            }else{

                $output['code'] = 201;
                $output['msg'] = "please send the correct image";
                echo json_encode($output);
            }



        }
    }
    public function search()
    {



        $this->loadModel("Video");
        $this->loadModel("VideoLike");
        $this->loadModel("VideoFavourite");

        $this->loadModel("SoundFavourite");
        $this->loadModel("User");
        $this->loadModel("Sound");
        $this->loadModel("Hashtag");
        $this->loadModel("HashtagVideo");
        $this->loadModel("HashtagFavourite");
        $this->loadModel("VideoComment");
        $this->loadModel("RepostVideo");

        $this->loadModel("Follower");


        if ($this->request->isPost()) {
            $json = file_get_contents('php://input');
            $data = json_decode($json, TRUE);

            $keyword = $data['keyword'];
            $type = $data['type'];
            $starting_point = $data['starting_point'];


            $videos = array();

            $user_id = 0;

            if(isset($data['user_id'])){

                $user_id = $data['user_id'];
            }

            if($type == "top"){


                $users = $this->User->getSearchResults($keyword,$starting_point,$user_id);
                if(count($users) > 0) {
                    foreach ($users as $key => $val) {


                        $count_followers =  $this->Follower->countFollowers($val['User']['id']);
                        $video_count = $this->Video->getUserVideosCount($val['User']['id']);
                        $users[$key]['User']['followers_count'] = $count_followers;
                        $users[$key]['User']['video_count'] = $video_count;


                        $follower_details = $this->Follower->ifFollowing($user_id, $val['User']['id']);
                        $following_details = $this->Follower->ifFollowing($val['User']['id'], $user_id);



                        if(count($follower_details) > 0 && count($following_details) > 0){

                            $users[$key]['User']['button'] = "Friends";

                        } else   if(count($follower_details) > 0 && count($following_details) < 1){

                            $users[$key]['User']['button'] = "following";

                        }else if (count($following_details) > 0){


                            $users[$key]['User']['button'] = "follow back";
                        }else{


                            $users[$key]['User']['button'] = "follow";
                        }

                    }
                    }


                $searchData['videos'] = $videos;


                $searchData['users'] = $users;


            }  else if($type == "user"){


                $users = $this->User->getSearchResults($keyword,$starting_point,$user_id);
                if(count($users) > 0){
                    foreach ($users as $key=>$val){

                        $count_followers =  $this->Follower->countFollowers($val['User']['id']);
                        $video_count = $this->Video->getUserVideosCount($val['User']['id']);
                        $users[$key]['User']['followers_count'] = $count_followers;
                        $users[$key]['User']['video_count'] = $video_count;


                        $follower_details = $this->Follower->ifFollowing($user_id, $val['User']['id']);
                        $following_details = $this->Follower->ifFollowing($val['User']['id'], $user_id);



                        if(count($follower_details) > 0 && count($following_details) > 0){

                            $users[$key]['User']['button'] = "Friends";

                        } else   if(count($follower_details) > 0 && count($following_details) < 1){

                            $users[$key]['User']['button'] = "following";

                        }else if (count($following_details) > 0){


                            $users[$key]['User']['button'] = "follow back";
                        }else{


                            $users[$key]['User']['button'] = "follow";
                        }

                    }


                }


                $searchData = $users;

            }else   if($type == "video"){


                $videos = $this->Video->getSearchResults($keyword,$starting_point,$user_id);
                if(count($videos) > 0){
                    foreach ($videos as $key=>$val){

                        if($user_id > 0) {
                            $video_data['user_id'] = $user_id;
                            $video_data['video_id'] = $val['Video']['id'];
                            $video_like_detail = $this->VideoLike->ifExist($video_data);
                            $video_favourite_detail = $this->VideoFavourite->ifExist($video_data);

                            if (count($video_like_detail) > 0) {

                                $videos[$key]['Video']['like'] = 1;

                            } else {

                                $videos[$key]['Video']['like'] = 0;
                            }

                            if (count($video_favourite_detail) > 0) {

                                $videos[$key]['Video']['favourite'] = 1;

                            } else {

                                $videos[$key]['Video']['favourite'] = 0;
                            }

                            $repost_detail = $this->RepostVideo->ifExist($video_data);

                            if (count($repost_detail) > 0) {

                                $videos[$key]['Video']['repost'] = 1;

                            } else {

                                $videos[$key]['Video']['repost'] = 0;
                            }
                        }else{


                            $videos[$key]['Video']['like'] = 0;
                            $videos[$key]['Video']['favourite'] = 0;
                            $videos[$key]['Video']['repost'] = 0;



                        }

                        $video_like_count = $this->VideoLike->countLikes($val['Video']['id']);
                        $video_comment_count = $this->VideoComment->countComments($val['Video']['id']);

                        $videos[$key]['Video']['comment_count'] = $video_comment_count;
                        $videos[$key]['Video']['like_count'] = $video_like_count;

                        $video_fav_count = $this->VideoFavourite->getFavVideosCount($val['Video']['id']);
                        $videos[$key]['Video']['favourite_count'] = $video_fav_count;


                    }


                }


                $searchData = $videos;

            }else  if($type == "sound"){


                $sounds = $this->Sound->getSearchResults($keyword,$starting_point);

                if(count($sounds) > 0) {
                    foreach ($sounds as $key => $val) {

                        if ($user_id > 0) {
                            $sound_data['user_id'] = $user_id;
                            $sound_data['sound_id'] = $val['Sound']['id'];

                            $sound_favourite_detail = $this->SoundFavourite->ifExist($sound_data);


                            if (count($sound_favourite_detail) > 0) {

                                $sounds[$key]['Sound']['favourite'] = 1;

                            } else {

                                $sounds[$key]['Sound']['favourite'] = 0;
                            }
                        } else {


                            $sounds[$key]['Sound']['favourite'] = 0;


                        }
                    }
                }
                $searchData = $sounds;


            }else  if($type == "hashtag"){


                $hashtags = $this->Hashtag->getSearchResults($keyword,$starting_point);

                if(count($hashtags) > 0) {
                    foreach ($hashtags as $key => $hashtag) {

                        $hashtag_data['hashtag_id'] = $hashtag['Hashtag']['id'];
                        $hashtag_data['user_id'] = $user_id;
                        $hashtag_views = $this->HashtagVideo->countHashtagViews($hashtag['Hashtag']['id']);
                        $hashtag_videos_count = $this->HashtagVideo->countHashtagVideos($hashtag['Hashtag']['id']);

                        $hashtags[$key]['Hashtag']['videos_count'] = $hashtag_videos_count;
                        $hashtags[$key]['Hashtag']['views'] = $hashtag_views[0]['total_sum'];
                        $hashtag_favourite_detail = $this->HashtagFavourite->ifExist($hashtag_data);

                        if (count($hashtag_favourite_detail) > 0) {

                            $hashtags[$key]['Hashtag']['favourite'] = 1;

                        } else {

                            $hashtags[$key]['Hashtag']['favourite'] = 0;
                        }

                    }



                }
                $searchData = $hashtags;

            }else if($type == "following"){


                $following = $this->Follower->searchFollowing($keyword,$starting_point,$user_id);

                if (count($following) > 0) {
                    foreach ($following as $key => $follow) {

                        $person_user_id = $follow['FollowingList']['id'];



                        $follower_details = $this->Follower->ifFollowing($user_id, $person_user_id);
                        $follower_back_details = $this->Follower->ifFollowing($person_user_id, $user_id);

                        $following_count = $this->Follower->countFollowing($person_user_id);

                        $video_count = $this->Video->getUserVideosCount($person_user_id);
                        $following[$key]['FollowingList']['following_count'] = $following_count;
                        $following[$key]['FollowingList']['video_count'] = $video_count;

                        if (count($follower_details) > 0 && count($follower_back_details) > 0) {

                            $following[$key]['FollowingList']['button'] = "Friends";
                        } else if (count($follower_details) > 0) {

                            $following[$key]['FollowingList']['button'] = "Following";

                        } else if (count($follower_back_details) > 0 && $follower_details < 0) {

                            $following[$key]['FollowingList']['button'] = "Follow Back";

                        } else {

                            $following[$key]['FollowingList']['button'] = "Follow";

                        }

                    }
                }
                $searchData = $following;

            }else if($type == "follower"){


                $followers = $this->Follower->searchFollower($keyword,$starting_point,$user_id);

                if (count($followers) > 0) {
                    foreach ($followers as $key => $follow) {

                        $person_user_id = $follow['FollowerList']['id'];


                        $follower_details = $this->Follower->ifFollowing($user_id, $person_user_id);
                        $follower_back_details = $this->Follower->ifFollowing($person_user_id, $user_id);

                        $followers_count = $this->Follower->countFollowers($person_user_id);

                        $video_count = $this->Video->getUserVideosCount($person_user_id);
                        $followers[$key]['FollowerList']['follower_count'] = $followers_count;
                        $followers[$key]['FollowerList']['video_count'] = $video_count;
                        if (count($follower_details) > 0 && count($follower_back_details) > 0) {

                            $followers[$key]['FollowerList']['button'] = "Friends";
                        } else if (count($follower_details) > 0) {

                            $followers[$key]['FollowerList']['button'] = "Following";

                        } else if (count($follower_back_details) > 0 && $follower_details < 0) {

                            $followers[$key]['FollowerList']['button'] = "Follow Back";

                        } else {

                            $followers[$key]['FollowerList']['button'] = "Follow";

                        }

                    }
                }
                $searchData = $followers;

            }else  if($type == "sound_favourite"){


                $sounds_fav = $this->SoundFavourite->searchFavouriteSound($keyword,$starting_point,$user_id);

                if(count($sounds_fav) > 0) {
                    foreach ($sounds_fav as $key => $val) {

                        if ($user_id > 0) {
                            $sound_data['user_id'] = $user_id;
                            $sound_data['sound_id'] = $val['Sound']['id'];

                            $sound_favourite_detail = $this->SoundFavourite->ifExist($sound_data);


                            if (count($sound_favourite_detail) > 0) {

                                $sounds_fav[$key]['Sound']['favourite'] = 1;

                            } else {

                                $sounds_fav[$key]['Sound']['favourite'] = 0;
                            }
                        } else {


                            $sounds_fav[$key]['Sound']['favourite'] = 0;


                        }
                    }
                }
                $searchData = $sounds_fav;


            }




            if(count($searchData) > 0) {

                $output['code'] = 200;

                $output['msg'] = $searchData;


                echo json_encode($output);

                die();

            }else{

                Message::EMPTYDATA();
                die();

            }










        }
    }
    public function searchProduct(){

        $this->loadModel('Product');
        $this->loadModel('ProductFavourite');





        if ($this->request->isPost()) {
            $json = file_get_contents('php://input');
            $data = json_decode($json, TRUE);


            $keyword = $data['keyword'];
            $starting_point = $data['starting_point'];
            $user_id = $data['user_id'];



            $products = $this->Product->searchProduct($keyword,$starting_point);
            if(count($products) > 0) {

                if(isset($data['user_id'])){


                    foreach ($products as $key => $product) {

                        $favourite = $this->ProductFavourite->ifProductFavourite($data['user_id'], $product['Product']['id']);
                        if (count($favourite) > 0) {

                            $products[$key]['Product']['favourite'] = "1";

                        } else {

                            $products[$key]['Product']['favourite'] = "0";

                        }


                    }
                }

                $output['code'] = 200;

                $output['msg'] = $products;


                echo json_encode($output);


                die();
            }else{


                Message::EMPTYDATA();
                die();
            }

        }


    }



    public function searchFollowingOrFollowUsers()
    {




        $this->loadModel("User");

        $this->loadModel("Follower");
        $this->loadModel("Video");



        if ($this->request->isPost()) {
            $json = file_get_contents('php://input');
            $data = json_decode($json, TRUE);

            $keyword = $data['keyword'];

            $starting_point = $data['starting_point'];


            $user_id = 0;

            if(isset($data['user_id'])){

                $user_id = $data['user_id'];
            }




            $new_array = array();
            $followers = $this->Follower->searchFollower($keyword,$starting_point,$user_id);
            $following = $this->Follower->searchFollowing($keyword,$starting_point,$user_id);


            $i=0;
            if (count($followers) > 0) {
                foreach ($followers as $key => $follow) {

                    $person_user_id = $follow['FollowerList']['id'];


                    $follower_details = $this->Follower->ifFollowing($user_id, $person_user_id);
                    $follower_back_details = $this->Follower->ifFollowing($person_user_id, $user_id);

                    $followers_count = $this->Follower->countFollowers($person_user_id);

                    $video_count = $this->Video->getUserVideosCount($person_user_id);
                    $new_array[$i]['User'] = $follow['FollowerList'];
                    $new_array[$i]['User']['follower_count'] = $followers_count;
                    $new_array[$i]['User']['video_count'] = $video_count;
                    if (count($follower_details) > 0 && count($follower_back_details) > 0) {

                        $new_array[$i]['User']['button'] = "Friends";
                    } else if (count($follower_details) > 0) {

                        $new_array[$i]['User']['button'] = "Following";

                    } else if (count($follower_back_details) > 0 && $follower_details < 0) {

                        $new_array[$i]['User']['button'] = "Follow Back";

                    } else {

                        $new_array[$i]['User']['button'] = "Follow";

                    }

                    $i++;
                }
            }

            if (count($following) > 0) {
                foreach ($following as $key => $follow) {

                    $person_user_id = $follow['FollowingList']['id'];



                    $follower_details = $this->Follower->ifFollowing($user_id, $person_user_id);
                    $follower_back_details = $this->Follower->ifFollowing($person_user_id, $user_id);

                    $following_count = $this->Follower->countFollowing($person_user_id);

                    $video_count = $this->Video->getUserVideosCount($person_user_id);
                    $new_array[$i]['User'] = $follow['FollowingList'];
                    $new_array[$i]['User']['following_count'] = $following_count;
                    $new_array[$i]['User']['video_count'] = $video_count;

                    if (count($follower_details) > 0 && count($follower_back_details) > 0) {

                        $new_array[$i]['User']['button'] = "Friends";
                    } else if (count($follower_details) > 0) {

                        $new_array[$i]['User']['button'] = "Following";

                    } else if (count($follower_back_details) > 0 && $follower_details < 0) {

                        $new_array[$i]['User']['button'] = "Follow Back";

                    } else {

                        $new_array[$i]['User']['button'] = "Follow";

                    }

                    $i++;
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

    public function deleteVideoComment(){

        $this->loadModel('VideoComment');
        $this->loadModel('Video');


        if ($this->request->isPost()) {
            $json = file_get_contents('php://input');
            $data = json_decode($json, TRUE);


            $id = $data['id'];

            $details = $this->VideoComment->getDetails($id);

            if(count($details) > 0 ) {
                $this->VideoComment->delete($id,true);
                $this->Video->id = $details['Video']['id'];
                $this->Video->saveField('pin_comment_id',0);


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


    public function showAddSettings(){

            $output['code'] = 200;

            $output['msg']['followers'] = FOLLOWER_PER_COIN;
            $output['msg']['website_visits'] = WEBSITE_VISITS_PER_COIN;
            $output['msg']['video_views'] = VIDEO_VIEWS_PER_COIN;


            echo json_encode($output);

            die();

    }



    function forgotPassword()
    {


        $this->loadModel('User');

        if ($this->request->isPost()) {


            $result = array();
            $json   = file_get_contents('php://input');

            $data = json_decode($json, TRUE);


            $email     = $data['email'];



            $code     = Utility::randomNumber(4);
            $user_info = $this->User->getUserDetailsAgainstEmail($email);


            if (count($user_info) > 0) {



                $user_id = $user_info['User']['id'];
                $email   = $user_info['User']['email'];
                $first_name   = $user_info['User']['first_name'];
                $last_name   = $user_info['User']['last_name'];
                $full_name   = $first_name. ' '.$last_name;

                $email_data['to'] = $email;
                $email_data['name'] = $full_name;
                $email_data['subject'] = "reset your password";
                $email_data['message'] = "You recently requested to reset your password for your ".APP_NAME." account  with the e-mail address (".$email."). 
Please enter this verification code to reset your password.<br><br>Confirmation code: <b></b>".$code."<b>";
                $response = Utility::sendMail($email_data);



                //  $response['ErrorCode']  = 0;
                if ($response['code'] == 200) {

                    $this->User->id = $user_id;

                    $savedField     = $this->User->saveField('token', $code);
                    $result['code'] = 200;
                    $result['msg']  = "An email has been sent to " . $email . ". You should receive it shortly.";
                } else {

                    $result['code'] = 201;
                    $result['msg']  = $response['msg'];


                }

            } else {

                $result['code'] = 201;
                $result['msg']  = "Email doesn't exist";
            }



            echo json_encode($result);
            die();
        }


    }


    public function verifyforgotPasswordCode()
    {
        $this->loadModel('User');


        if ($this->request->isPost()) {
            $json = file_get_contents('php://input');

            $data = json_decode($json, TRUE);
            $code = $data['code'];
            $email = $data['email'];

            $code_verify = $this->User->verifyToken($code,$email);
            $user_info = $this->User->getUserDetailsFromEmail($email);
            if (!empty($code_verify)) {
                $this->User->id = $user_info['User']['id'];
                $this->User->saveField('token',0);

                $user_info = $this->User->getUserDetailsFromEmail($email);
                $result['code'] = 200;
                $result['msg']  = $user_info;
                echo json_encode($result);
                die();
            } else {
                $result['code'] = 201;
                $result['msg']  = "invalid code";
                echo json_encode($result);
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
            $email          = $this->User->field('email');

            $old_password   = $data['old_password'];
            $new_password   = $data['new_password'];


            if ($this->User->verifyPassword($email, $old_password)) {

                $this->request->data['password'] = $new_password;
                $this->User->id                  = $user_id;


                if ($this->User->save($this->request->data)) {

                    echo Message::DATASUCCESSFULLYSAVED();

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


    public function updateOrderStatus()
    {


        $this->loadModel('Order');

        if ($this->request->isPost()) {


            $json = file_get_contents('php://input');
            $data = json_decode($json, TRUE);

            $order['status'] = $data['status'];




            $order_id = $data['order_id'];

            $order_details = $this->Order->getDetails($order_id);
            if(count($order_details) > 0) {


                $this->Order->id = $order_id;
                $this->Order->save($order);


                $order_details = $this->Order->getDetails($order_id);


                $output = array();

                $output['code'] = 200;
                $output['msg'] = $order_details;
                echo json_encode($output);
                die();
            }else{


                Message::EMPTYDATA();
                die();
            }




        }
    }


    public function updateProfile(){

        $this->loadModel('User');



        $users = $this->User->getUsersWhoHasProfilePic(1);
        pr($users);

        if(count($users) > 0){


           $user_data = array();
            foreach($users as $user){



               $profile_pic =  $user['User']['profile_pic'];
               $profile_pic_small =  $user['User']['profile_pic_small'];



                   $key = 'http';
                   if (strpos($profile_pic, $key) !== false) {




                   }else {

                       if (strlen($profile_pic) > 0) {
                           echo "big exist";
                           $result_big = Premium::profileImageToS3($profile_pic, "png");

                           pr($result_big);

                           if ($result_big['code'] == 200) {
                               echo "hello";
                               $user_data['profile_pic'] = Utility::getCloudFrontUrl($result_big['msg'], "/profile");
                               @unlink($profile_pic);
                           }

                       }

                       if (strlen($profile_pic_small) > 0) {
                           echo "small exist";
                           $result_small = Premium::profileImageToS3($profile_pic_small, "png");


                           if ($result_small['code'] == 200) {

                               $user_data['profile_pic_small'] = Utility::getCloudFrontUrl($result_small['msg'], "/profile");

                               @unlink($profile_pic_small);
                           }


                       }
                   }

                   if(count($user_data) > 0){

                       $this->User->id = $user['User']['id'];
                       $this->User->save($user_data);
                       $this->User->clear();
                   }
            }

        }

    }
    public function changeEmailAddress()
    {
        $this->loadModel('User');

        if ($this->request->isPost()) {
            $json = file_get_contents('php://input');
            //$json = $this->request->data('json');
            $data = json_decode($json, TRUE);


            $user_id        = $data['user_id'];
            $email        = $data['email'];


            $email_exist = $this->User->editIsEmailAlreadyExist($email, $user_id);

            $user_details = $this->User->getUserDetailsFromID($user_id);
            if(count($user_details) > 0) {

                $db_email = $user_details['User']['email'];

                if ($db_email == $email) {


                    $result['code'] = 200;
                    $result['msg'] = $user_details;
                    echo json_encode($result);
                    die();
                }

                if ($email_exist > 0) {

                    $result['code'] = 201;
                    $result['msg'] = "This email has already been registered";
                    echo json_encode($result);
                    die();
                }


                $code = Utility::randomNumber(4);


                $user_id = $user_details['User']['id'];
                $first_name = $user_details['User']['first_name'];
                $last_name = $user_details['User']['last_name'];
                $full_name = $first_name . ' ' . $last_name;

                $email_data['to'] = $email;
                $email_data['name'] = $full_name;
                $email_data['subject'] = "change your email address";
                $email_data['message'] = "You recently requested to update your email for your " . APP_NAME . " account. 
Please enter this verification code to reset your email.<br><br>Confirmation code: <b></b>" . $code . "<b>";
                $response = Utility::sendMail($email_data);


                //  $response['ErrorCode']  = 0;
                if ($response['code'] == 200) {

                    $this->User->id = $user_id;

                    $savedField = $this->User->saveField('token', $code);
                    $result['code'] = 200;
                    $result['msg'] = "An email has been sent to " . $email . ". You should receive it shortly.";
                } else {

                    $result['code'] = 201;
                    $result['msg'] = $response['msg'];


                }

                echo json_encode($result);
                die();
            }else{


                Message::EMPTYDATA();
                die();
            }

        }

    }




    public function verifyRegisterEmailCode()
    {
        $this->loadModel('EmailVerification');
        $this->loadModel('User');


        if ($this->request->isPost()) {
            $json = file_get_contents('php://input');

            $data = json_decode($json, TRUE);

            $email_count = $this->User->isEmailAlreadyExist($data['email']);
            if($email_count > 0){

                $user_details  = $this->User->getUserDetailsAgainstEmail($data['email']);
                $active = $user_details['User']['active'];

                if($active > 1){


                    $output['code'] = 201;
                    $output['msg'] = "You have been blocked by the admin. Contact support";
                    echo json_encode($output);
                    die();

                }

                $output['code'] = 201;
                $output['msg'] = "The account already exist with this email";
                echo json_encode($output);
                die();
            }
            if(!isset($data['code'])){


                $email = $data['email'];



                $code = Utility::randomNumber(4);
                $email_data['to'] = $email;
                $email_data['name'] = "";
                $email_data['subject'] = "verify your email address";
                $email_data['message'] = "Please enter this verification code to register your email.<br><br>Confirmation code: <b></b>" . $code . "<b>";

                if(APP_STATUS == "live"){

                    $response = Utility::sendMail($email_data);

                }else{

                    $code = 1234;
                }

                $email_verification['email'] = $email;
                $email_verification['code'] = $code;

                $this->EmailVerification->save($email_verification);
                $id = $this->EmailVerification->getInsertID();
                $details =  $this->EmailVerification->getDetails($id);

                $result['code'] = 200;
                $result['msg'] = $details;
                echo json_encode($result);
                die();
            }
            $code = $data['code'];
            $email = $data['email'];
            $details = $this->EmailVerification->verifyCode($email,$code);
            if(count($details) > 0) {

                $result['code'] = 200;
                $result['msg'] = $details;
                echo json_encode($result);
                die();

            }else{

                $result['code'] = 201;
                $result['msg'] = "invalid code";
                echo json_encode($result);
                die();
            }

        }
    }

    public function verifyChangeEmailCode()
    {
        $this->loadModel('User');


        if ($this->request->isPost()) {
            $json = file_get_contents('php://input');

            $data = json_decode($json, TRUE);
            $code = $data['code'];
            $email = $data['new_email'];
            $user_id = $data['user_id'];
            $user_details = $this->User->getUserDetailsFromID($user_id);
            if(count($user_details) > 0) {

                $db_email = $user_details['User']['email'];
                $code_verify = $this->User->verifyToken($code, $db_email);

                if (!empty($code_verify) && $code > 0) {
                    $email_change['email'] = $email;
                    $email_change['token'] = 0;
                    $email_change['active'] = 1;
                    $this->User->id = $user_id;
                    $this->User->save($email_change);

                    $user_details = $this->User->getUserDetailsFromEmail($email);
                    $result['code'] = 200;
                    $result['msg'] = $user_details;
                    echo json_encode($result);
                    die();
                } else {
                    $result['code'] = 201;
                    $result['msg'] = "invalid code";
                    echo json_encode($result);
                    die();
                }
            }else{

                $result['code'] = 201;
                $result['msg'] = "invalid code";
                echo json_encode($result);
                die();
            }

        }
    }

    public function changePhoneNo()
    {
        $this->loadModel('User');

        if ($this->request->isPost()) {
            $json = file_get_contents('php://input');
            //$json = $this->request->data('json');
            $data = json_decode($json, TRUE);


            $user_id        = $data['user_id'];
            $phone        = $data['phone'];


            $phone_exist = $this->User->editIsphoneNoAlreadyExist($phone, $user_id);

            $user_details = $this->User->getUserDetailsFromID($user_id);
            if(count($user_details) > 0) {

                $db_phone = $user_details['User']['phone'];

                if ($db_phone == $phone) {


                    $result['code'] = 200;
                    $result['msg'] = $user_details;
                    echo json_encode($result);
                    die();
                }

                if ($phone_exist > 0) {

                    $result['code'] = 201;
                    $result['msg'] = "This phone has already been registered";
                    echo json_encode($result);
                    die();
                }



                $response =  $this->verifyPhoneNo($phone,$user_id,0);


                echo json_encode($response);
                die();
            }

        }

    }

    public function verifyPhoneNo($phone_no = null,$user_id = null,$verify = null)
    {

        $this->loadModel('PhoneNoVerification');
        $this->loadModel('User');



        $json = file_get_contents('php://input');

        $data = json_decode($json, TRUE);


        if (!empty($phone_no)) {
            $phone_no = $phone_no;
            $verify = $verify;

        }else{

            $phone_no =  $data['phone'];
            $verify =  $data['verify'];
            // $code =  $data['code'];

            if(isset($data['user_id'])) {
                $user_id = $data['user_id'];

                $phone_exist = $this->User->editisphoneNoAlreadyExist($phone_no,$user_id);




                if ($phone_exist > 0) {

                    $result['code'] = 201;
                    $result['msg'] = "This phone has already been registered";
                    echo json_encode($result);
                    die();
                }
            }
        }


        $code     = Utility::randomNumber(4);

        if(APP_STATUS =="demo"){
            $code     = 1234;
        }


        $created                  = date('Y-m-d H:i:s', time() - 60 * 60 * 4);
        $phone_verify['phone_no'] = $phone_no;
        $phone_verify['code']     = $code;
        $phone_verify['created']  = $created;


        if ($verify == 0) {

            if(APP_STATUS =="demo"){
                $response['sid']= "";
            }else{

                $response = Utility::sendSmsVerificationCurl($phone_no, VERIFICATION_PHONENO_MESSAGE . ' ' . $code);

            }





            if (array_key_exists('code', $response)){


                $output['code'] = 201;
                $output['msg']  = $response['message'];



            }else{



                if (array_key_exists('sid', $response)){



                    $this->PhoneNoVerification->save($phone_verify);


                    $output['code'] = 200;

                    $output['msg']  = "code has been generated and sent to user's phone number";



                }

            }





        } else {
            $code_user = $data['code'];
            if ($this->PhoneNoVerification->verifyCode($phone_no, $code_user) > 0) {

                if (!empty($user_id)) {


                    $this->User->id = $user_id;
                    $this->User->saveField('phone',$phone_no);
                }
                $output['code'] = 200;
                $output['msg']  = "successfully code matched";
                /*$this->PhoneNoVerification->deleteAll(array(
                    'phone_no' => $phone_no
                ), false);*/



            } else {

                $output['code'] = 201;
                $output['msg']  = "invalid code";



            }

        }

        if (!empty($phone)) {


            return $output;
        }else{


            //it means post request from app
            echo json_encode($output);
            die();

        }

    }
    public function blockUser()
    {


        $this->loadModel('BlockUser');

        if ($this->request->isPost()) {


            $json = file_get_contents('php://input');
            $data = json_decode($json, TRUE);


            //$user['password'] = $data['password'];


            $user_id = $data['user_id'];
            $block_user_id = $data['block_user_id'];

            $block_user['created'] = date('Y-m-d H:i:s', time());
            $block_user['user_id'] = $user_id;
            $block_user['block_user_id'] = $block_user_id;

            $userDetails = $this->BlockUser->ifAlreadyBlocked($block_user);
            if(count($userDetails) < 1) {

                $this->BlockUser->save($block_user);

                $id = $this->BlockUser->getInsertID();
                $output = array();
                $userDetails = $this->BlockUser->getDetails($id);


                $output['code'] = 200;
                $output['msg'] = $userDetails;
                echo json_encode($output);
                die();

            }else{

                $id = $userDetails['BlockUser']['id'];
                $this->BlockUser->id = $id;
                $this->BlockUser->delete();


                $output['code'] = 201;
                $output['msg'] = "deleted";
                echo json_encode($output);
                die();

            }
        }
    }

    public function showBlockedUsers(){

        $this->loadModel('BlockUser');




        if ($this->request->isPost()) {
            $json = file_get_contents('php://input');
            $data = json_decode($json, TRUE);

            $user_id = $data['user_id'];

            $details = $this->BlockUser->getBlockUsers($user_id);


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

    public function changePasswordForgot()
    {
        $this->loadModel('User');

        if ($this->request->isPost()) {
            $json = file_get_contents('php://input');
            //$json = $this->request->data('json');
            $data = json_decode($json, TRUE);


            $email        = $data['email'];

            $new_password   = $data['password'];




            $this->request->data['password'] = $new_password;

            $email_details = $this->User->getUserDetailsAgainstEmail($email);


            $user_id = $email_details['User']['id'];
            $this->User->id = $user_id;
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


    public function sendLiveStreamPushNotfication()
    {
        $this->loadModel("User");
        $this->loadModel("PushNotification");
        $this->loadModel("Follower");
        $this->loadModel("Notification");

        if ($this->request->isPost()) {
            $json = file_get_contents('php://input');
            $data = json_decode($json, TRUE);


            $user_id = $data['user_id'];
            $live_streaming_id = $data['live_streaming_id'];






            $user_details =  $this->User->getUserDetailsFromID($user_id);
            $followers =  $this->Follower->getUserFollowersWithoutLimit($user_id);
            if(count($followers) > 0){
                foreach($followers as $follower){


                    $device_token = $follower['FollowerList']['device_token'];
                    $receiver_id = $follower['FollowerList']['id'];


                    $msg = $user_details['User']['username']." is live now";
                    if (strlen($device_token) > 8) {
                        $notification['to'] = $device_token;

                        $notification['notification']['title'] =  $user_details['User']['username'];
                        $notification['notification']['body'] = $msg;
                        $notification['notification']['badge'] = "1";
                        $notification['notification']['sound'] = "default";
                        $notification['notification']['icon'] = "";
                        $notification['notification']['type'] = "live";
                        $notification['notification']['user_id'] = $user_id;
                        $notification['notification']['name'] = $user_details['User']['first_name']." ".$user_details['User']['last_name'];
                        $notification['notification']['image'] = $user_details['User']['profile_pic'];

                        $notification['data']['title'] =$user_details['User']['username'];
                        $notification['data']['body'] = $msg;
                        $notification['data']['icon'] = "";
                        $notification['data']['badge'] = "1";
                        $notification['data']['sound'] = "default";
                        $notification['data']['type'] = "live";
                        $notification['data']['user_id'] = $user_id;
                        $notification['data']['name'] = $user_details['User']['first_name']." ".$user_details['User']['last_name'];
                        $notification['data']['image'] = $user_details['User']['profile_pic'];
                        $notification['data']['receiver_id'] = $receiver_id;
                        $notification['notification']['receiver_id'] = $receiver_id;




                        Utility::sendPushNotificationToMobileDevice(json_encode($notification));


                        $notification_data['sender_id'] = $user_id;
                        $notification_data['receiver_id'] = $receiver_id;
                        $notification_data['type'] = "live";
                        $notification_data['live_streaming_id'] = $live_streaming_id;
                        //$notification_data['video_id'] = $video_id;

                        $notification_data['string'] = $msg;
                        $notification_data['created'] = date('Y-m-d H:i:s', time());

                        $this->Notification->save($notification_data);
                    }
                }
            }




            $output['code'] = 200;
            $output['msg'] = "success";
            echo json_encode($output);


            die();
        }

    }

    public function deleteUserAccount(){

        $this->loadModel('User');
        $this->loadModel('Follower');

        if ($this->request->isPost()) {
            $json = file_get_contents('php://input');
            $data = json_decode($json, TRUE);

            $user_id = $data['user_id'];

            $details = $this->User->getUserDetailsFromID($user_id);


          /*  if(APP_STATUS == "demo") {



                $code =  201;
                $msg = "You cannot delete account in demo account. You need to contant qboxus for that";

                $output['code'] = $code;

                $output['msg'] = $msg;



                die();

            }*/
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

    public function deleteFollower(){

        $this->loadModel('User');
        $this->loadModel('Follower');

        if ($this->request->isPost()) {
            $json = file_get_contents('php://input');
            $data = json_decode($json, TRUE);


            $follower_id = $data['follower_id'];
            $user_id = $data['user_id'];

            $details = $this->Follower->ifFollowing($follower_id,$user_id);

            if(count($details) > 0 ) {
                $this->Follower->id = $details['Follower']['id'];
                $this->Follower->delete();


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

    public function sendMessageNotification()
    {
        $this->loadModel("User");
        $this->loadModel("PushNotification");

        if ($this->request->isPost()) {
            $json = file_get_contents('php://input');
            $data = json_decode($json, TRUE);


            $sender_id = $data['sender_id'];
            $title = $data['title'];
            $message = $data['message'];

            $sender_details =  $this->User->getUserDetailsFromID($sender_id);





            /*********************************START NOTIFICATION******************************/

            if(isset($data['receivers'])){


                $receivers = $data['receivers'];

                foreach($receivers as $receiver){

                    $receiver_id = $receiver['receiver_id'];
                    $receiver_details =  $this->User->getUserDetailsFromID($receiver_id);

                    $notification['to'] = $receiver_details['User']['device_token'];




                    $notification['notification']['title'] = $title;
                    $notification['notification']['body'] = $message;
                    $notification['notification']['user_id'] = $sender_details['User']['id'];
                    $notification['notification']['image'] = $sender_details['User']['profile_pic'];
                    $notification['notification']['name'] = $sender_details['User']['username'];
                    $notification['notification']['badge'] = "1";
                    $notification['notification']['sound'] = "default";
                    $notification['notification']['icon'] = "";
                    $notification['notification']['type'] = "message";

                    $notification['data']['title'] = $title;
                    $notification['data']['name'] = $sender_details['User']['username'];
                    $notification['data']['body'] = $message;
                    $notification['data']['icon'] = "";
                    $notification['data']['badge'] = "1";
                    $notification['data']['sound'] = "default";
                    $notification['data']['type'] = "message";
                    $notification['data']['user_id'] = $sender_details['User']['id'];
                    $notification['data']['image'] = $sender_details['User']['profile_pic'];
                    $notification['data']['receiver_id'] = $receiver_details['User']['id'];
                    $notification['notification']['receiver_id'] = $receiver_details['User']['id'];




                    $if_exist = $this->PushNotification->getDetails($receiver_details['User']['id']);
                    if(count($if_exist) > 0) {

                        $likes = $if_exist['PushNotification']['direct_messages'];
                        if($likes > 0) {
                            Utility::sendPushNotificationToMobileDevice(json_encode($notification));
                        }
                    }
                }
            }

            if(isset($data['receiver_id'])) {
                $receiver_id = $data['receiver_id'];


                $receiver_details =  $this->User->getUserDetailsFromID($receiver_id);
                $notification['to'] = $receiver_details['User']['device_token'];


                $notification['notification']['title'] = $title;
                $notification['notification']['body'] = $message;
                $notification['notification']['user_id'] = $sender_details['User']['id'];
                $notification['notification']['image'] = $sender_details['User']['profile_pic'];
                $notification['notification']['name'] = $sender_details['User']['username'];
                $notification['notification']['badge'] = "1";
                $notification['notification']['sound'] = "default";
                $notification['notification']['icon'] = "";
                $notification['notification']['type'] = "message";

                $notification['data']['title'] = $title;
                $notification['data']['name'] = $sender_details['User']['username'];
                $notification['data']['body'] = $message;
                $notification['data']['icon'] = "";
                $notification['data']['badge'] = "1";
                $notification['data']['sound'] = "default";
                $notification['data']['type'] = "message";
                $notification['data']['user_id'] = $sender_details['User']['id'];
                $notification['data']['image'] = $sender_details['User']['profile_pic'];
                $notification['data']['receiver_id'] = $receiver_details['User']['id'];
                $notification['notification']['receiver_id'] = $receiver_details['User']['id'];


                $if_exist = $this->PushNotification->getDetails($receiver_details['User']['id']);
                if (count($if_exist) > 0) {

                    $likes = $if_exist['PushNotification']['direct_messages'];
                    if ($likes > 0) {
                        Utility::sendPushNotificationToMobileDevice(json_encode($notification));
                    }
                }

                /*********************************END NOTIFICATION******************************/


            }


            $output['code'] = 200;
            $output['msg'] = "success";
            echo json_encode($output);


            die();
        }

    }













}








?>