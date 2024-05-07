<?php



class ProductAttributeCombination extends AppModel
{
    public $useTable = 'product_attribute_combination';

    public $belongsTo = array(

        'Product' => array(
            'className' => 'Product',
            'foreignKey' => 'product_id',


        ),



    );

    public function getDetails($id)
    {
        return $this->find('first', array(
            'conditions' => array(

                'ProductAttributeCombination.id' => $id





            )
        ));
    }

    public function deleteAllCombinations($product_id){

        $this->deleteAll(
            [
                'ProductAttributeCombination.product_id' => $product_id,

            ],
            false # <- single delete statement please
        );


    }











}