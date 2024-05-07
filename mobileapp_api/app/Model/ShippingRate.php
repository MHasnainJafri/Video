<?php


class ShippingRate extends AppModel
{
    public $useTable = 'shipping_rate';

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
            'conditions' => array('ShippingRate.id' => $id)
        ));

    }

    public function getShippingRateAgainstCountry($country_id)
    {

        return $this->find('first', array(
            'conditions' => array('ShippingRate.country_id' => $country_id)
        ));

    }

    public function getCountryAgainstShortName($name)
    {

        return $this->find('first', array(
            'conditions' => array('Country.short_name' => $name)
        ));

    }

    public function getCountriesAgainstKeyword($keyword)
    {

        /*return $this->find('all',  array(
            'conditions' => array(
                'Country.active' => 1,
                'Country.name Like' => "$keyword%"
            ),


            'order'=>'Country.name ASC',
            'limit' => 5,
        ));
*/


        return $this->query("SELECT id, name, 'country' as type FROM countries WHERE name LIKE '$keyword%' 
       
           UNION
           (SELECT id, name, 'city' as type FROM cities WHERE name LIKE '$keyword%') 
           UNION
           (SELECT id,name, 'state' as type FROM states WHERE name LIKE '$keyword%') LIMIT 5");
    }

    public function getAll()
    {

        return $this->find('all',  array(



            'order'=>'Country.name ASC',

        ));

    }


    public function setDefaultToZero(){

        $this->updateAll(
            array('Country.default' => 0)
        );

    }



}

?>