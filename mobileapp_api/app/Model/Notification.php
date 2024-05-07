<?php


class Notification extends AppModel
{
    public $useTable = 'notification';


    public $belongsTo = array(
        'Video' => array(
            'className' => 'Video',
            'foreignKey' => 'video_id',



        ),

        'Sender' => array(
            'className' => 'User',
            'foreignKey' => 'sender_id',



        ),

        'Receiver' => array(
            'className' => 'User',
            'foreignKey' => 'receiver_id',



        ),


    );


    public function getDetails($id)
    {

        return $this->find('first', array(
            'conditions' => array('Notification.id' => $id)
        ));

    }

    public function ifStreamingNotificationExist($sender_id,$receiver_id,$streaming_id)
    {

        return $this->find('count', array(
            'conditions' => array(

                'Notification.sender_id' => $sender_id,
                'Notification.receiver_id' => $receiver_id,
                'Notification.live_streaming_id' => $streaming_id,

                )
        ));

    }



    public function getAll()
    {

        return $this->find('all');

    }


    public function getUserNotifications($user_id,$starting_point)
    {

        return $this->find('all', array(
            'conditions' => array(
                'Notification.receiver_id' => $user_id


            ),
            'order' => 'Notification.id DESC',
            'limit'=>10,
            'offset' => $starting_point*10,

        ));

    }

    public function getUserUnreadNotification($user_id)
    {

        return $this->find('count', array(
            'conditions' => array(
                'Notification.receiver_id' => $user_id,
                'Notification.read' => 0,


            ),


        ));

    }



    public function readNotification($user_id){

        $this->updateAll(
            array('Notification.read' => 1),
            array('Notification.receiver_id' => $user_id)
        );

    }











}

?>