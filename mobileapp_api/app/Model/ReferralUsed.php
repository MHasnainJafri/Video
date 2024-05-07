<?php



class ReferralUsed extends AppModel
{
    public $useTable = 'referral_used';






    public function getDetails($id)
    {
        return $this->find('first', array(
            'conditions' => array(

                'ReferralUsed.id' => $id





            )
        ));
    }

    public function ifReferralUsed($user_id,$usedby)
    {
        return $this->find('first', array(
            'conditions' => array(

                'ReferralUsed.referral_owner' => $user_id,
                'ReferralUsed.used_by' => $usedby,






            )
        ));
    }

    public function countReferralUsedByOthers($user_id)
    {
        return $this->find('count', array(
            'conditions' => array(

                'ReferralUsed.referral_owner' => $user_id,







            )
        ));
    }

    public function countReferralUsedByOthersAll($user_id)
    {
        return $this->find('count', array(
            'conditions' => array(

                'ReferralUsed.referral_owner' => $user_id,
                //'ReferralUsed.purchase' => 1,






            )
        ));
    }

    public function ifUserHasUsedReferral($user_id)
    {
        return $this->find('first', array(
            'conditions' => array(


                'ReferralUsed.used_by' => $user_id,
                'ReferralUsed.purchase' => 0,





            )
        ));
    }



    public function countReferralUsed($user_id,$datetime)
    {
        return $this->find('first', array(
            'conditions' => array(

                'ReferralUsed.referral_owner' => $user_id,
                'ReferralUsed.created >' => $datetime,





            ),
            'fields'=>array('SUM(ReferralUsed.amount) as total_amount','COUNT(ReferralUsed.id) as total_count'),

        ));
    }










}