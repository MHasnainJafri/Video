<?php


class RepostVideo extends AppModel
{

    public $useTable = 'repost_video';

    public $belongsTo = array(
        'Video' => array(
            'className' => 'Video',
            'foreignKey' => 'video_id',



        ),

        'User' => array(
            'className' => 'User',
            'foreignKey' => 'user_id',



        ),

    );

    public function getDetails($id)
    {
        return $this->find('first', array(
            'conditions' => array(



                'RepostVideo.id'=> $id,




            )
        ));
    }

    public function getAllRespostedVideos($user_id,$starting_point)
    {
        $this->Behaviors->attach('Containable');
        return $this->find('all', array(
            'contain' => array('Video.Sound','Video.User.PrivacySetting','Video.User.PushNotification'),
            'conditions' => array(



                'RepostVideo.user_id'=> $user_id,
                'NOT' => array('Video.id' => null),
                'RepostVideo.video_id = Video.id',



            ),
            'limit'=>APP_RECORDS_PER_PAGE,
            'offset' => $starting_point*APP_RECORDS_PER_PAGE,
            'order' => 'RepostVideo.id DESC',
        ));
    }
    public function countRepost($video_id)
    {
        return $this->find('count', array(
            'conditions' => array(




                'RepostVideo.video_id'=> $video_id,




            )
        ));
    }


    public function ifExist($data)
    {
        $this->Behaviors->attach('Containable');
        return $this->find('first', array(
            'conditions' => array(



                'RepostVideo.video_id'=> $data['video_id'],
                'RepostVideo.user_id'=> $data['user_id'],




            ),
            'recursive'=>-1
            //'contain' => array('Video.User','User')
        ));
    }




    public function getAll()
    {
        return $this->find('all');
    }






}
?>