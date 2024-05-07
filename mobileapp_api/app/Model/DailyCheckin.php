<?php


class DailyCheckin extends AppModel
{

    public $useTable = 'daily_checkin';



    public function getDetails($id)
    {
        return $this->find('first', array(
            'conditions' => array(



                'DailyCheckin.id'=> $id,




            )
        ));
    }

    public function counCoins($user_id)
    {
        return $this->find('first', array(
            'conditions' => array(



                'DailyCheckin.user_id'=> $user_id,





            ),

            'fields'=>array('SUM(DailyCheckin.coin) as total_coin' )
        ));
    }

    public function ifExist($user_id,$date)
    {
        return $this->find('first', array(
            'conditions' => array(



                'DATE(DailyCheckin.created)'=> $date,
                'DailyCheckin.user_id'=> $user_id,




            )
        ));
    }

    public function getRecentCheckins($user_id,$date)
    {
        return $this->find('all', array(
            'conditions' => array(



                'DATE(DailyCheckin.created) >='=> $date,
                'DailyCheckin.user_id'=> $user_id,




            )
        ));
    }









}
?>