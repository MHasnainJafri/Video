<?php


class RoomMember extends AppModel
{
    public $useTable = 'room_member';

    public $belongsTo = array(
        'Room' => array(
            'className' => 'Room',
            'foreignKey' => 'room_id',

        ),

        'User' => array(
            'className' => 'User',
            'foreignKey' => 'user_id',
           
        ),


    );

   
    public function getDetails($id)
    {

        return $this->find('first', array(
            'conditions' => array('RoomMember.id' => $id)
        ));

    }




    public function getUserRooms($user_id)
    {

        return $this->find('all',  array(
            'conditions' => array(
                'RoomMember.user_id' => $user_id,



            ),




        ));

    }

    public function getRoomModerators($room_id)
    {
        $this->Behaviors->attach('Containable');
        return $this->find('all',  array(
            'conditions' => array(
                'RoomMember.room_id' => $room_id,
                'RoomMember.moderator !=' => 0,



            ),
            'contain'=>array('User'),



        ));

    }



    public function getRoomMembers($room_id)
    {



        return $this->find('all',  array(
            'conditions' => array(
                'RoomMember.room_id' => $room_id,



            ),






        ));

    }

    public function getRecentListeners($user_id,$starting_point)
    {

        return $this->find('all',  array(
            'conditions' => array(//'not exists '.
               // '(SELECT id FROM block_user as BlockUser WHERE RoomMember.user_id = BlockUser.block_user_id AND BlockUser.user_id ='.$user_id.' OR RoomMember.user_id = BlockUser.user_id AND BlockUser.block_user_id ='.$user_id.')',
               // '(SELECT id FROM follower as Follower WHERE Follower.sender_id = RoomMember.user_id AND Follower.sender_id ='.$user_id.')',




                'RoomMember.user_id !=' => $user_id,
                'RoomMember.moderator' => array(1,2),



            ),

            'limit' => 10,
            'offset' => $starting_point*10,




        ));

    }
    public function getMembersCount($room_id)
    {

        return $this->find('count', array(
            'conditions' => array('RoomMember.room_id' => $room_id)
        ));

    }

    public function ifExist($room_id,$user_id)
    {

        return $this->find('first',  array(
            'conditions' => array(
                'RoomMember.user_id' => $user_id,
                'RoomMember.room_id' => $room_id,



            ),




        ));

    }



    public function getAll()
    {

        return $this->find('all');

    }





}

?>