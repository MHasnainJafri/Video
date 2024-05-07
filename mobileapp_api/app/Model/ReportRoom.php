<?php



class ReportRoom extends AppModel
{
    public $useTable = 'report_room';

    public $belongsTo = array(

        'User' => array(
            'className' => 'User',
            'foreignKey' => 'user_id',


        ),



        'Room' => array(
            'className' => 'Room',
            'foreignKey' => 'room_id',


        ),

        'ReportReason' => array(
            'className' => 'ReportReason',
            'foreignKey' => 'report_reason_id',


        ),

    );

    public function getDetails($id)
    {
        return $this->find('first', array(
            'conditions' => array(

                'ReportRoom.id' => $id





            )
        ));
    }

    public function ifExist($user_id,$room_id)
    {
        return $this->find('first', array(
            'conditions' => array(

                'ReportRoom.user_id' => $user_id,
                'ReportRoom.room_id' => $room_id,





            )
        ));
    }




    public function getAll()
    {
        return $this->find('all', array(
            'order' => 'ReportUser.id DESC',
        ));

    }






}