<?php



class Audience extends AppModel
{
    public $useTable = 'audience';

    public $belongsTo = array(

        'User' => array(
            'className' => 'User',
            'foreignKey' => 'user_id',


        ),







    );



    public function getDetails($id)
    {
        return $this->find('first', array(
            'conditions' => array(

                'Audience.id' => $id





            )
        ));
    }


    public function getUserAudiences($user_id)
    {
        $this->Behaviors->attach('Containable');
        return $this->find('all', array(
            'conditions' => array(

                'Audience.user_id' => $user_id






            ),
            'contain'=> array('User')
        ));
    }










}