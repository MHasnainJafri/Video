
<?php

class ProductRating extends AppModel
{
    public $useTable = 'product_rating';

    public $belongsTo = array(

        'User' => array(
            'className' => 'User',
            'foreignKey' => 'user_id',


        ),
        'Product' => array(
            'className' => 'Product',
            'foreignKey' => 'product_id',


        ),

    );




    public function getAvgRatings($product_id)
    {
        return $this->find('first', array(
            'conditions' => array(
                'ProductRating.product_id' => $product_id,


            ),

            'fields'    => array(
                'AVG( ProductRating.star ) AS average',
                'COUNT(ProductRating.id) AS total_ratings'


            ),
            'group' => 'ProductRating.product_id'
        ));


    }

    public function getComments($product_id)
    {
        $this->Behaviors->attach('Containable');
        return $this->find('all', array(
            'conditions' => array(
                'ProductRating.product_id' => $product_id,


            ),


            'fields'    => array(
                'ProductRating.*',

                'User.*',



            ),

        ));


    }

    public function getDetails($id)
    {
        return $this->find('first', array(
            'conditions' => array(
                'ProductRating.id' => $id,


            ),



        ));


    }

    public function ifExist($user_id,$product_id,$order_id)
    {
        return $this->find('first', array(
            'conditions' => array(
                'ProductRating.user_id' => $user_id,
                'ProductRating.product_id' => $product_id,
                'ProductRating.order_id' => $order_id,


            ),
            'recursive'=>-1



        ));


    }

}



?>