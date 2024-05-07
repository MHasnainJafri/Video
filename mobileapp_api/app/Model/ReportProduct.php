<?php



class ReportProduct extends AppModel
{
    public $useTable = 'report_product';

    public $belongsTo = array(

        'User' => array(
            'className' => 'User',
            'foreignKey' => 'user_id',


        ),

        'Product' => array(
            'className' => 'Product',
            'foreignKey' => 'product_id',


        ),

        'ReportReason' => array(
            'className' => 'ReportReason',
            'foreignKey' => 'report_reason_id',


        ),

    );

    public function getDetails($id)
    {
        return $this->find('first', array(
            'conditions' => array(

                'ReportProduct.id' => $id





            )
        ));
    }


    public function getReportsAgainstProduct($product_id)
    {
        return $this->find('first', array(
            'conditions' => array(

                'ReportProduct.product_id' => $product_id





            )
        ));

    }

    public function getAll()
    {
        return $this->find('all', array(
            'order' => 'ReportProduct.id DESC',
        ));

    }






}