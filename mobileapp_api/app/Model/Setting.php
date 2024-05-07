<?php


class Setting extends AppModel
{

    public $useTable = 'setting';



    public function getDetails($id)
    {
        return $this->find('first', array(
            'conditions' => array(



                'Setting.id'=> $id,




            )
        ));
    }

    public function getDetailsAgainstType($type)
    {
        return $this->find('first', array(
            'conditions' => array(



                'Setting.type'=> $type,




            )
        ));
    }

    public function checkDuplicate($type)
    {
        return $this->find('first', array(
            'conditions' => array(



                'Setting.type'=> $type,




            )
        ));
    }




    public function getAll()
    {
        return $this->find('all');
    }







}
?>