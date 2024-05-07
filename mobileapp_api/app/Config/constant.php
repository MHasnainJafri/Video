<?php

define('API_KEY', '156c4675-9608-4591-1111-00000');
define('ADMIN_API_KEY', '156c4675-9608-4591-1111-00000');



date_default_timezone_set('GMT');
define('BASE_URL', 'http://quickies.xoblack.com/mobileapp_api');
define('APP_STATUS', 'live');///demo/live
define('APP_NAME', 'Quickies');

define('ADMIN_RECORDS_PER_PAGE',30);
define('APP_RECORDS_PER_PAGE',20);

//Add settings

define('STRIPE_API_KEY','');
define('STRIPE_CURRENCY', 'usd');

define('DATABASE_HOST', 'localhost');
define('DATABASE_USER', 'quickies_xoblack');
define('DATABASE_PASSWORD', 'quickies_xoblack');
define('DATABASE_NAME', 'quickies_xoblack');


define('VIDEO_COMPRESSION', '2500');






define('UPLOADS_FOLDER_URI', 'app/webroot/uploads');
define('TEMP_UPLOADS_FOLDER_URI', 'app/webroot/temp_uploads');
define('FONT_FOLDER_URI', 'app/webroot/font');


define('WATERMARK_IMAGE_URI', 'app/webroot/img/watermark.png'); //the size should be 100x30
define('WATERMARK_VIDEO_URI', 'app/webroot/video/watermark.mp4');



define('IMAGE_THUMB_SIZE', '150');
define('FIREBASE_PUSH_NOTIFICATION_KEY', '');

//Twilio
define('TWILIO_ACCOUNTSID', '');
define('TWILIO_AUTHTOKEN', '');
define('TWILIO_NUMBER', '');
define('VERIFICATION_PHONENO_MESSAGE', 'Your verification code is');


define('GOOGLE_MAPS_KEY', '');



//Facebook
define('FACEBOOK_APP_ID', '');
define('FACEBOOK_APP_SECRET', '');
define('FACEBOOK_GRAPH_VERSION', 'v2.10');



define('DEEPENGIN_KEY', '');
define('DEEPENGIN_VIDEO_MODERATION_URL', 'https://videos.deepengin.com/v1/videoModeration');
define('DEEPENGIN_IMAGE_MODERATION_URL', 'https://images.deepengin.com/v1/imageModeration');


//Google
define('GOOGLE_CLIENT_ID', '');

//FFMPEG

//email send
define('MAIL_HOST', 'smtp.gmail.com');
define('MAIL_USERNAME', 'XOblack.us@gmail.com');
define('MAIL_PASSWORD', 'soywnqrztdjsagps');
define('MAIL_FROM', 'XOblack.us@gmail.com');
define('MAIL_NAME', 'Quickies');
define('MAIL_REPLYTO', 'XOblack.us@gmail.com');


define("MEDIA_STORAGE","local");  // if you want to enable AWS s3 then you have to put the value "s3" and if you put "local" videos will be stored in your local server/hosting


/**********************CDN SERVICE**********************/

//you can fill either AWS/GOOGLE/BUNNY
//AWS
define("IAM_KEY","");
define("IAM_SECRET","");
define("BUCKET_NAME","");
define("S3_REGION","us-east-1");
define("CLOUDFRONT_URL","");




//add settings

define('FOLLOWER_PER_COIN', 1.9);
define('VIDEO_VIEWS_PER_COIN', 2.3);
define('WEBSITE_VISITS_PER_COIN', 1.7);





?>



