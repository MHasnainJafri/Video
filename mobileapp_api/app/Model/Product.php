<?php


class Product extends AppModel
{
    public $useTable = 'product';

    public $belongsTo = array(
        'Category' => array(
            'className' => 'Category',
            'foreignKey' => 'category_id',

        ),

        'User' => array(
            'className' => 'User',
            'foreignKey' => 'user_id',

        ),


    );


    public $hasMany = array(
        'ProductImage' => array(
            'className' => 'ProductImage',
            'foreignKey' => 'product_id',
            'dependent'=> true,

        ),

        'ProductAttribute' => array(
            'className' => 'ProductAttribute',
            'foreignKey' => 'product_id',
            'dependent'=> true,

        ),

        'ProductAttributeCombination' => array(
            'className' => 'ProductAttributeCombination',
            'foreignKey' => 'product_id',
            'dependent'=> true,

        ),
        'OrderProduct' => array(
            'className' => 'OrderProduct',
            'foreignKey' => 'product_id',
            'dependent'=> true,

        ),


    );





    public function getDetails($id)
    {
        $this->Behaviors->attach('Containable');
        return $this->find('first', array(
            'conditions' => array('Product.id' => $id),
            //'recursive'=>-1
            'contain' => array('ProductImage'),
        ));

    }

    public function getDetailsWithAttributes($id)
    {
        $this->Behaviors->attach('Containable');
        return $this->find('first', array(
            'conditions' => array('Product.id' => $id),
            'contain' => array('Category','User','ProductImage','ProductAttribute.ProductAttributeVariation','ProductAttributeCombination'),

            //'contain' => array(' Category','ProductImage'),
        ));

    }

    public function getDetailsWithUser($id)
    {
        $this->Behaviors->attach('Containable');
        return $this->find('first', array(
            'conditions' => array('Product.id' => $id),
            'contain' => array('User'),

            //'contain' => array(' Category','ProductImage'),
        ));

    }



    public function getAll()
    {
        $this->Behaviors->attach('Containable');
        return $this->find('all',array(

            'contain' => array('Category','ProductImage','Store.StoreLocation.Country'),
            'order' => array('Product.id DESC'),
        ));

    }

    public function getProductsAgainstCategory($category_id,$starting_point)
    {
        $this->Behaviors->attach('Containable');
        return $this->find('all',array(

            'contain' => array('Category','ProductImage','ProductAttribute.ProductAttributeVariation'),
            'conditions' => array('Product.category_id' => $category_id),
              'limit' => 10,
            'offset' => $starting_point*10,
            'order' => array('Product.id DESC'),
        ));

    }

    public function getProductsAgainstUser($user_id,$starting_point)
    {
        $this->Behaviors->attach('Containable');
        return $this->find('all',array(

            'contain' => array('Category','ProductImage','ProductAttribute.ProductAttributeVariation'),
            'conditions' => array('Product.user_id' => $user_id),
            'limit' => 10,
            'offset' => $starting_point*10,
            'order' => array('Product.id DESC'),
        ));

    }

    public function getPromotedProducts($starting_point)
    {
        $this->Behaviors->attach('Containable');
        return $this->find('all',array(

            'contain' => array('ProductImage','ProductAttribute.ProductAttributeVariation'),
            'conditions' => array('Product.promote' => 1),
            'limit' => 10,
            'offset' => $starting_point*10,
            'order' => array('Product.id DESC'),
        ));

    }

    public function getTopViewedProducts($starting_point)
    {
        $this->Behaviors->attach('Containable');
        return $this->find('all',array(

            'contain' => array('ProductImage','ProductAttribute.ProductAttributeVariation'),

            'limit' => 10,
            'offset' => $starting_point*10,
            'order' => array('Product.view DESC'),
        ));

    }

    public function searchProduct($keyword,$starting_point)
    {
        $this->Behaviors->attach('Containable');
        return $this->find('all',array(

            'contain' => array('Category','ProductImage','ProductAttribute.ProductAttributeVariation'),


            'conditions' => array(


                'OR' => array(
                    array('Product.title Like' => "%$keyword%"),
                    array('Product.description Like' => "%$keyword%"),

                ),


            ),
            'limit' => 10,
            'offset' => $starting_point*10,
            'order' => array('Product.view DESC'),

            ));

    }


    public function filterProducts($min_price=null,$max_price=null,$keyword=null)
    {
        $this->Behaviors->attach('Containable');
        return $this->find('all',array(

            'contain' => array('Category','ProductImage','Store.StoreLocation.Country'),
            'conditions' => array(
                'Product.price >=' => $min_price,
                'Product.price <=' => $max_price,

                'Product.title LIKE' => '%'.$keyword.'%'
            ),

        ));

    }

    public function filterProductsWithCategory($min_price=null,$max_price=null,$keyword=null,$category_id)
    {
        $this->Behaviors->attach('Containable');
        return $this->find('all',array(

            'contain' => array('Category','ProductImage','Store.StoreLocation.Country'),
            'conditions' => array(
                'Product.price >=' => $min_price,
                'Product.price <=' => $max_price,
                'Product.category_id ' => $category_id,

                'Product.title LIKE' => '%'.$keyword.'%'
            ),

        ));

    }

    public function filterProductsWithCategoryAndStore($min_price=null,$max_price=null,$category_id,$store_id)
    {
        $this->Behaviors->attach('Containable');
        return $this->find('all',array(

            'contain' => array('Category','ProductImage','Store.StoreLocation.Country'),
            'conditions' => array(
                'Product.price >=' => $min_price,
                'Product.price <=' => $max_price,
                'Product.category_id ' => $category_id,
                'Product.store_id ' => $store_id,


            ),

        ));

    }


    public function filterProductsWithStore($min_price=null,$max_price=null,$store_id)
    {
        $this->Behaviors->attach('Containable');
        return $this->find('all',array(

            'contain' => array('Category','ProductImage','Store.StoreLocation.Country'),
            'conditions' => array(
                'Product.price >=' => $min_price,
                'Product.price <=' => $max_price,
                'Product.store_id ' => $store_id,


            ),

        ));

    }

    public function filterProductsWithHighestPrice($min_price=null,$max_price=null,$keyword,$highest_price)
    {
        $this->Behaviors->attach('Containable');
        return $this->find('all',array(

            'contain' => array('Category','ProductImage','Store.StoreLocation.Country'),
            'conditions' => array(
                'Product.price >=' => $min_price,
                'Product.price <=' => $max_price,
                'Product.title LIKE' => '%'.$keyword.'%'


            ),

            'order' => array('Product.price DESC'),

        ));

    }

    public function filterProductsWithLowestPrice($min_price=null,$max_price=null,$keyword,$lowest_price)
    {
        $this->Behaviors->attach('Containable');
        return $this->find('all',array(

            'contain' => array('Category','ProductImage','Store.StoreLocation.Country'),
            'conditions' => array(
                'Product.price >=' => $min_price,
                'Product.price <=' => $max_price,
                'Product.title LIKE' => '%'.$keyword.'%'


            ),

            'order' => array('Product.price ASC'),

        ));

    }













}

?>