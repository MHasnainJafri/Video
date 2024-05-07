<?php


class GiftSend extends AppModel
{

    public $useTable = 'gift_send';


    public $belongsTo = array(

        'Gift' => array(
            'className' => 'Gift',
            'foreignKey' => 'gift_id',


        ),

        'User' => array(
            'className' => 'User',
            'foreignKey' => 'sender_id',


        ),
    );
    public function getDetails($id)
    {
        return $this->find('first', array(
            'conditions' => array(



                'GiftSend.id'=> $id,




            )
        ));
    }

    public function countGiftSendByUser($user_id)
    {
        return $this->find('first', array(
            'conditions' => array(



                'GiftSend.sender_id'=> $user_id,





            ),


            'fields'=>array('SUM(GiftSend.coin) as total_amount' )
        ));
    }

    public function getDailySenders($date,$starting_point){


        return $this->find('all', array(
            'conditions' => array(



                'DATE(GiftSend.created)'=> $date,





            ),

            'fields' => array('User.*','GiftSend.*','MAX(GiftSend.coin) as max_coin'),

            'group' => 'GiftSend.sender_id',
            'order' => 'max_coin DESC',
            'limit'=>10,
            'offset' => $starting_point*10,

        ));
    }


    public function getDailyReceivers($date,$starting_point){


        return $this->find('all', array(
            'conditions' => array(



                'DATE(GiftSend.created)'=> $date,





            ),

            'fields' => array('User.*','GiftSend.*','MAX(GiftSend.coin) as max_coin'),

            'group' => 'GiftSend.receiver_id',
            'order' => 'max_coin DESC',
            'limit'=>10,
            'offset' => $starting_point*10,

        ));
    }

    public function getHourlySenders($date,$starting_point){


        return $this->find('all', array(
            'conditions' => array(



                'HOUR(GiftSend.created)'=> $date,





            ),

            'fields' => array('User.*','GiftSend.*','MAX(GiftSend.coin) as max_coin'),

            'group' => 'GiftSend.sender_id',
            'order' => 'max_coin DESC',
            'limit'=>10,
            'offset' => $starting_point*10,

        ));
    }

    public function getHourlyReceivers($date,$starting_point){


        return $this->find('all', array(
            'conditions' => array(



                'HOUR(GiftSend.created)'=> $date,





            ),

            'fields' => array('User.*','GiftSend.*','MAX(GiftSend.coin) as max_coin'),

            'group' => 'GiftSend.receiver_id',
            'order' => 'max_coin DESC',
            'limit'=>10,
            'offset' => $starting_point*10,

        ));
    }

    public function getTopGiftsSendByUser($user_id){

        return $this->find('all', array(
            'conditions' => array(



                'GiftSend.sender_id'=> $user_id,





            ),

            'fields' => array('Gift.*','COUNT(*) AS total'),

            'group' => 'GiftSend.gift_id',
            'order' => 'total DESC',
            'limit'=>2,


        ));
    }

    public function countGiftReceiveByUser($user_id)
    {
        return $this->find('first', array(
            'conditions' => array(



                'GiftSend.receiver_id'=> $user_id,





            ),

            'fields'=>array('SUM(GiftSend.coin) as total_amount' )
        ));
    }




    public function getAll()
    {
        return $this->find('all');
    }







}
?>