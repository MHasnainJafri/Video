<?php


class Gift extends AppModel
{

    public $useTable = 'gift';



    public function getDetails($id)
    {
        return $this->find('first', array(
            'conditions' => array(



                'Gift.id'=> $id,




            )
        ));
    }

    public function ifExist($title)
    {
        return $this->find('first', array(
            'conditions' => array(



                'Gift.title'=> $title,




            )
        ));
    }

    public function getAllAgainstType($type)
    {
        return $this->find('all', array(
            'conditions' => array(



                'Gift.type'=> $type,




            )
        ));
    }

   
    public function getAll()
    {
        return $this->find('all', array(

            'order'=>'Gift.featured DESC'

        ));
    }







}
?>