<?php


class ProductFavourite extends AppModel
{
    public $useTable = 'product_favourite';


    public $belongsTo = array(
        'Product' => array(
            'className' => 'Product',
            'foreignKey' => 'product_id',

        ),

        'User' => array(
            'className' => 'User',
            'foreignKey' => 'user_id',

        )
    );


    public function getDetails($id)
    {

        return $this->find('first', array(
            'conditions' => array('ProductFavourite.id' => $id)
        ));

    }

    public function ifProductFavourite($user_id,$product_id)
    {

        return $this->find('first', array(
            'conditions' => array(
                'ProductFavourite.product_id' => $product_id,
                'ProductFavourite.user_id' => $user_id


            )
        ));

    }

    public function productFavouriteCount($product_id)
    {

        return $this->find('count', array(
            'conditions' => array(
                'ProductFavourite.product_id' => $product_id,



            )
        ));

    }


    public function getUserFavouriteProducts($user_id)
    {
        $this->Behaviors->attach('Containable');
        return $this->find('all', array(
            'contain'=>array('Product.ProductImage','User','Product.Store.StoreLocation.Country'),
            'conditions' => array(


                'Favourite.user_id' => $user_id

            )
        ));

    }


    public function deleteFavourite($user_id,$product_id){


        $this->deleteAll(
            [
                'ProductFavourite.user_id' => $user_id,
                'ProductFavourite.product_id' => $product_id
            ],
            false # <- single delete statement please
        );
    }




}

?>