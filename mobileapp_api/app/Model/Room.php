<?php


class Room extends AppModel
{
    public $useTable = 'room';

    public $belongsTo = array(
        'Topic' => array(
            'className' => 'Topic',
            'foreignKey' => 'topic_id',
            'dependent'=>true

        ),
    );

    public $hasMany = array(
        'RoomMember' => array(
            'className' => 'RoomMember',
            'foreignKey' => 'room_id',
            'dependent'=>true

        ),
    );


    public function getDetails($id)
    {
        $this->Behaviors->attach('Containable');


        return $this->find('first', array(
            'conditions' => array('Room.id' => $id),
            'contain' => array('RoomMember.User','Topic')

        ));

    }

    public function getRoomsAgainstClub($club_id)
    {
        $this->Behaviors->attach('Containable');


        return $this->find('all', array(
            'conditions' => array(

                'Room.club_id' => $club_id,
                'Room.privacy' => 0


            ),
            'contain' => array('RoomMember.User')

        ));

    }


    public function getSearchResults($keyword,$starting_point){


       $this->Behaviors->attach('Containable');
        return $this->find('all', array(

            'conditions' => array(
                'Room.title Like' => "$keyword%",
                'Room.delete' => 0),
            'contain' => array('RoomMember.User','Topic'),

            'limit' => 10,
            'offset' => $starting_point*10,






            'recursive' => 0


        ));

    }

    public function getRoomsCreatedAgainstUser()
    {
        $this->Behaviors->attach('Containable');
        return $this->find('all',  array(
            'conditions' => array(
                'Room.club_id' => 0,
                'Room.delete' => 0,



            ),
            'contain' => array('RoomMember.User')



        ));

    }

    public function getUserRooms($user_id)
    {
        $this->Behaviors->attach('Containable');
        return $this->find('all',  array(
            'conditions' => array(
                'Room.user_id' => $user_id,
                'Room.delete' => 0,



            ),
            'contain' => array('RoomMember.User','Topic')



        ));

    }



    public function getAll()
    {
        $this->Behaviors->attach('Containable');
        return $this->find('all', array(
            'contain' => array('RoomMember.User', 'Topic'),
            'joins' => array(
                array(
                    'table' => 'room_member',
                    'alias' => 'RoomMember',
                    'type' => 'LEFT',
                    'conditions' => array(
                        'Room.id = RoomMember.room_id',
                    ),
                ),
                array(
                    'table' => 'user',
                    'alias' => 'User',
                    'type' => 'INNER',
                    'conditions' => array(
                        'RoomMember.user_id = User.id',
                    ),
                ),
            ),
            //'fields' => array('Room.*', 'RoomMember.*'),
            'conditions' => array(
                'OR' => array(
                    'User.id IS NOT NULL',
                    'RoomMember.user_id IS NULL',
                ),

            ),
            'group'=>'Room.id'
        ));

    }





}

?>