<?php



class Order extends AppModel
{
    public $useTable = 'order';



    public $belongsTo = array(

        /*'PaymentMethod' => array(
            'className' => 'PaymentMethod',
            'foreignKey' => 'payment_method_id',


        ),*/

        'User' => array(
            'className' => 'User',
            'foreignKey' => 'user_id',


        ),

        'DeliveryAddress' => array(
            'className' => 'DeliveryAddress',
            'foreignKey' => 'delivery_address_id',


        ),

        'Store' => array(
            'className' => 'User',
            'foreignKey' => 'store_user_id',


        ),




    );
    public $hasMany = array(
        'OrderProduct' => array(
            'className' => 'OrderProduct',
            'foreignKey' => 'order_id',



        ),
    );

    public $hasOne = array(
        'CouponUsed' => array(
            'className' => 'CouponUsed',
            'foreignKey' => 'order_id',



        ),


    );


    public function getDetails($id)
    {

        $this->Behaviors->attach('Containable');
        return $this->find('first',array(

            'contain' => array('User','DeliveryAddress.Country','OrderProduct','CouponUsed','Store'),
            'conditions' => array(

                'Order.id' => $id

            )

        ));



    }



    public function getOrdersAccordingToStatus($status)
    {

        $this->Behaviors->attach('Containable');
        return $this->find('all',array(

            'contain' => array('User','DeliveryAddress','OrderStoreProduct','CouponUsed','Store.StoreLocation.Country','Store.User'),
            'conditions' => array(

                'Order.status' => $status

            )

        ));



    }

    public function getTotalCoins($user_id)
    {


        return $this->find('first',array(

            //'contain' => array('User','DeliveryAddress','OrderStoreProduct','CouponUsed','Store.StoreLocation.Country','Store.User'),
            'conditions' => array(

                'Order.status <' => 3,
                'Order.user_id' => $user_id,

            ),
            'fields'=>array('SUM(Order.total) as total_amount' )

        ));



    }

    public function getPendingSellerBalance($user_id,$older_date)
    {


        return $this->find('first',array(

            //'contain' => array('User','DeliveryAddress','OrderStoreProduct','CouponUsed','Store.StoreLocation.Country','Store.User'),
            'conditions' => array(
                'Order.store_user_id' => $user_id,
                'Order.status' => 2,
                'Order.completed_datetime !=' => "0000-00-00 00:00:00",
                'Order.completed_datetime >=' => $older_date,

            ),
            'fields'=>array('SUM(Order.total) as total_amount' )

        ));



    }

    public function getSellerBalance($user_id,$older_date)
    {


        return $this->find('first',array(

            //'contain' => array('User','DeliveryAddress','OrderStoreProduct','CouponUsed','Store.StoreLocation.Country','Store.User'),
            'conditions' => array(
                'Order.store_user_id' => $user_id,
                'Order.status' => 2,
                'Order.completed_datetime !=' => "0000-00-00 00:00:00",
                'Order.completed_datetime <=' => $older_date,

            ),
            'fields'=>array('SUM(Order.total) as total_amount' )

        ));



    }



    public function getCountStoreUserOrdersAccordingToStatus($status,$user_id)
    {

        $this->Behaviors->attach('Containable');
        return $this->find('count',array(

            // 'contain' => array('User','DeliveryAddress','OrderStoreProduct','CouponUsed','Store.StoreLocation.Country'),
            'conditions' => array(

                'Order.status' => $status,
                'Order.store_user_id' => $user_id


            )

        ));



    }

    public function getStoreUserOrdersAccordingToStatus($status,$user_id,$starting_id)
    {

        $this->Behaviors->attach('Containable');
        return $this->find('all',array(

            'contain' => array('User','DeliveryAddress','OrderStoreProduct','CouponUsed','Store.StoreLocation.Country','Store.User'),
            'conditions' => array(

                'Order.status' => $status,
                'Order.store_user_id' => $user_id

            ),

            'limit' => 10,
            'offset' => $starting_id*10,
            'order' => 'Order.id DESC',

        ));



    }

    public function getStoreOrdersAccordingToStatus($status,$store_id,$starting_id)
    {

        $this->Behaviors->attach('Containable');
        return $this->find('all',array(

            'contain' => array('User','DeliveryAddress','OrderStoreProduct','CouponUsed','Store.StoreLocation.Country','Store.User'),
            'conditions' => array(

                'Order.status' => $status,
                'Order.store_id' => $store_id

            ),
            'limit' => 10,
            'offset' => $starting_id*10,

        ));



    }

    public function getStoreOrdersAccordingToStatusStorePortal($status,$store_id)
    {

        $this->Behaviors->attach('Containable');
        return $this->find('all',array(

            'contain' => array('User','DeliveryAddress','OrderStoreProduct','CouponUsed','Store.StoreLocation.Country','Store.User'),
            'conditions' => array(

                'Order.status' => $status,
                'Order.store_id' => $store_id

            ),




        ));



    }
    public function getUserOrders($user_id,$status,$starting_point)
    {

        $this->Behaviors->attach('Containable');

        $conditions = array(

            'Order.user_id' => $user_id

        );

        if ($status != 5) {
            $conditions['Order.status'] = $status;
        }

        return $this->find('all', array(
            'conditions' => $conditions,
            'limit' => 10,
            'offset' => $starting_point*10,
            'order' => 'Order.id DESC',

            'contain' => array('OrderProduct','DeliveryAddress','Store'),


        ));



    }

    public function getStoreOrders($user_id,$status,$starting_point)
    {

        $this->Behaviors->attach('Containable');

        $conditions = array(

            'Order.store_user_id' => $user_id

        );

        if ($status != 5) {
            $conditions['Order.status'] = $status;
        }


        return $this->find('all', array(
            'conditions' => $conditions,
            'limit' => 10,
            'offset' => $starting_point*10,
            'order' => 'Order.id DESC',

            'contain' => array('OrderProduct','User','Store'),


        ));



    }





}