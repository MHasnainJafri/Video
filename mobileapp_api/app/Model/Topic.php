<?php


class Topic extends AppModel
{
    public $useTable = 'topic';

   

    public function getDetails($id)
    {

        return $this->find('first', array(
            'conditions' => array('Topic.id' => $id)
        ));

    }




    public function checkDuplicate($name)
    {

        return $this->find('first',  array(
            'conditions' => array(
                'Topic.title' => $name,



            ),




        ));

    }

    public function getAll()
    {

        return $this->find('all');

    }





    public function getPopularTopics($user_topics_ids)
    {
       

        if(count($user_topics_ids) > 0) {
            return $this->query("SELECT * FROM `topic`as Topic LEFT JOIN user_topic as UserTopic On Topic.id = UserTopic.topic_id WHERE Topic.id NOT IN ($user_topics_ids) GROUP BY UserTopic.topic_id ORDER BY COUNT(*) DESC");

        }else{

            return  $this->query("SELECT * FROM `topic`as Topic LEFT JOIN user_topic as UserTopic On Topic.id = UserTopic.topic_id  GROUP BY UserTopic.topic_id ORDER BY COUNT(*) DESC");


        }
    }




    public function getSearchResults($keyword,$starting_point){


        $this->Behaviors->attach('Containable');
        return $this->find('all', array(

            'conditions' => array(
                'Topic.title Like' => "$keyword%",
             ),


            'limit' => 10,
            'offset' => $starting_point*10,






            'recursive' => 0


        ));

    }

    public function setDefaultToZero(){

        $this->updateAll(
            array('Country.default' => 0),
            array('Country.default' => 1)
        );

    }




}

?>