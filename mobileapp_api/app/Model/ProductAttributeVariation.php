<?php



class ProductAttributeVariation extends AppModel
{
    public $useTable = 'product_attribute_variation';

    public $belongsTo = array(

        'ProductAttribute' => array(
            'className' => 'ProductAttribute',
            'foreignKey' => 'product_attribute_id',


        ),



    );

    public function getDetails($id)
    {
        return $this->find('first', array(
            'conditions' => array(

                'ProductAttributeVariation.id' => $id





            )
        ));
    }


    public function ifExist($data)
    {
        return $this->find('first', array(
            'conditions' => array(

                'ProductAttributeVariation.value' => $data['value'],
                'ProductAttributeVariation.price' => $data['price'],
                'ProductAttributeVariation.product_attribute_id' => $data['product_attribute_id'],





            )
        ));
    }


    public function deleteAllVariations($product_attribute_id){

        $this->deleteAll(
            [
                'ProductAttributeVariation.product_attribute_id' => $product_attribute_id,

            ],
            false # <- single delete statement please
        );


    }










}