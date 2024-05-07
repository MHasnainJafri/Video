<?php



class DeliveryAddress extends AppModel
{

    public $useTable = 'delivery_address';

    public $belongsTo = array(
        'Country' => array(
            'className' => 'Country',
            'foreignKey' => 'country_id',
            'dependent' =>true



        ),

    );
    public function getDetails($id)
    {
        return $this->find('first', array(
            'conditions' => array(

                'DeliveryAddress.id' => $id,




            )

        ));


    }


    public function getUserDeliveryAddresses($user_id)
    {
        $this->Behaviors->attach('Containable');
        return $this->find('all', array(
            'conditions' => array(

                'DeliveryAddress.user_id' => $user_id,




            ),
            'contain' => array('Country.ShippingRate'),
            'order' => 'DeliveryAddress.id DESC',

        ));


    }

    public function getUserDefaultDeliveryAddresses($user_id)
    {
        $this->Behaviors->attach('Containable');
        return $this->find('first', array(
            'conditions' => array(

                'DeliveryAddress.user_id' => $user_id,
                'DeliveryAddress.default' => 1,




            ),
            'contain' => array('Country.ShippingRate'),
            'order' => 'DeliveryAddress.id DESC',

        ));


    }

    public function getAddressDetail($address_id)
    {
        return $this->find('all', array(
            'conditions' => array(

                'DeliveryAddress.id' => $address_id,




            )

        ));


    }

    public function isDuplicateRecord($user_id,$street,$city,$apartment,$state,$country)
    {
        return $this->find('count', array(
            'conditions' => array(

                'DeliveryAddress.user_id' => $user_id,
                'DeliveryAddress.street'=> $street,
                'DeliveryAddress.city'=> $city,
                'DeliveryAddress.apartment' => $apartment,
                'DeliveryAddress.state'=> $state,
                'DeliveryAddress.country'=> $country,



            )
        ));
    }

    public function beforeSave($options = array())
    {



        if (isset($this->data[$this->alias]['street']) && isset($this->data[$this->alias]['city'])
            && isset($this->data[$this->alias]['apartment']) && isset($this->data[$this->alias]['state']) && isset($this->data[$this->alias]['country'])) {

            $name = strtolower($this->data[$this->alias]['street']);
            $city = strtolower($this->data[$this->alias]['city']);
            $apartment = strtolower($this->data[$this->alias]['apartment']);
            $state = strtolower($this->data[$this->alias]['state']);
            $country = strtolower($this->data[$this->alias]['country']);





            $this->data['DeliveryAddress']['name'] = ucwords($name);
            $this->data['DeliveryAddress']['city'] = ucwords($city);
            $this->data['DeliveryAddress']['apartment'] = ucwords($apartment);
            $this->data['DeliveryAddress']['state'] = ucwords($state);
            $this->data['DeliveryAddress']['country'] = ucwords($country);

        }
        return true;
    }
}


?>