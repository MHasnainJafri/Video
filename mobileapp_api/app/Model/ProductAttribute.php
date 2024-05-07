<?php



class ProductAttribute extends AppModel
{
    public $useTable = 'product_attribute';

    public $belongsTo = array(

        'Product' => array(
            'className' => 'Product',
            'foreignKey' => 'product_id',


        ),



    );

    public $hasMany = array(

        'ProductAttributeVariation' => array(
            'className' => 'ProductAttributeVariation',
            'foreignKey' => 'product_attribute_id',
            'dependent'=>true


        ),



    );

    public function getDetails($id)
    {
        $this->Behaviors->attach('Containable');
        return $this->find('first', array(
            'conditions' => array(

                'ProductAttribute.id' => $id





            ),
            'contain' => array('ProductAttributeVariation'),
        ));
    }

    public function ifExist($data)
    {
        return $this->find('first', array(
            'conditions' => array(

                'ProductAttribute.id' => $data['name'],
                'ProductAttribute.product_id' => $data['product_id'],





            )
        ));
    }











}