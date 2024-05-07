<?php



class ShopSlider extends AppModel
{

    public $useTable = 'shop_slider';


    public function getImages()
    {
        return $this->find('all');


    }


    public function getDetails($id)
    {
        return $this->find('first', array(


            // 'contain' => array('OrderMenuItem', 'Restaurant', 'OrderMenuItem.OrderMenuExtraItem', 'PaymentMethod', 'Address','UserInfo','RiderOrder.Rider'),

            'conditions' => array(



                'ShopSlider.id' => $id


            ),
        ));


    }

    public function getAll()
    {
        return $this->find('all');


    }

    public function getAppSlidersCount()
    {
        return $this->find('count');
    }

    public function deleteAppSlider($id)
    {
        return $this->deleteAll(
            [
                'ShopSlider.id' => $id

            ],
            false # <- single delete statement please
        );
    }
}

?>