<?php



class Category extends AppModel
{
    public $useTable = 'category';

    public $belongsTo = array(

        'Parent' => array(
            'className' => 'Category',
            'foreignKey' => 'parent_id',


        ),
    );
    public $hasMany = array(

        'Product' => array(
            'className' => 'Product',
            'foreignKey' => 'category_id',
            'dependent' =>true


        ),
        'Children' => array(
            'className' => 'Category',
            'foreignKey' => 'parent_id',
            'dependent' =>true


        ),
    );






    public function getDetails($id)
    {
        return $this->find('first', array(
            'conditions' => array(

                'Category.id' => $id





            )
        ));
    }

    public function getAllMostOccurance()
    {
        $this->Behaviors->attach('Containable');
        return $this->find('all',array(

            'contain' => array('Product'=> array(
                'limit' => 10,
            ),'Product.ProductImage','Product.ProductAttribute.ProductAttributeVariation'),
            'joins' => array(
                array(
                    'table' => 'product',
                    'alias' => 'Product',
                    'type' => 'INNER',
                    'conditions' => array(
                        'Product.category_id = Category.id'
                    )
                )
            ),
            'fields' => array(
                'Category.*',

                'COUNT(Product.id) AS product_count'
            ),

            'limit' => 10,

            'group' => 'Category.id',
            'order' => 'product_count DESC'
        ));

    }
    public function getProfileVisitors($user_id,$date,$starting_point)
    {
        return $this->find('all', array(
            'conditions' => array(

                'ProfileVisit.user_id' => $user_id,
                'DATE(ProfileVisit.created) >' => $date,
                'ProfileVisit.visitor_id !=' => $user_id,
                'Visitor.id >' => 0,


            ),
            'group'=>'ProfileVisit.visitor_id',
            'limit' => 10,
            'offset' => $starting_point*10,

        ));
    }

    public function getAll($parent)
    {
        $this->Behaviors->attach('Containable');
        return $this->find('all', array(
            'conditions' => array(

                'Category.parent_id' => $parent,




            ),
            'contain'=>array('Parent','Children'),





        ));
    }

    public function updateReadCount($user_id){


        $this->updateAll(
            array('ProfileVisit.read' => 1),
            array('ProfileVisit.user_id' => $user_id)
        );
    }





}