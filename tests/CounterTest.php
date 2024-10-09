<?php
use PHPUnit\Framework\TestCase;
require_once ('../vendor/autoload.php');
require_once("../src/classes.php");

class CounterTest extends TestCase //Для запуска: php ../vendor/bin/phpunit CounterTest.php
{
    /**
    * @dataProvider provider_counter
    */

    public function test_countHarvestAmountByType($garden_source,$harvest)
    {
        $garden_source=json_decode($garden_source,true);

        $counter=new Counter;

        $harvest_amounts_by_types=$counter->countHarvestAmountByType($garden_source,$harvest);

        $array_format_match=(
            isset($harvest_amounts_by_types["apple"]) && 
            isset($harvest_amounts_by_types["peach"]) &&
            gettype($harvest_amounts_by_types["apple"])=="integer" &&
            gettype($harvest_amounts_by_types["peach"])=="integer"
        );

        $this->assertTrue($array_format_match);
    }



    /**
    * @dataProvider provider_counter
    */

    public function test_countHarvestWeightByType($garden_source,$harvest)
    {
        $garden_source=json_decode($garden_source,true);

        $counter=new Counter;

        $harvest_amounts_by_types=$counter->countHarvestWeightByType($garden_source,$harvest);

        $array_format_match=(
            isset($harvest_amounts_by_types["apple"]) && 
            isset($harvest_amounts_by_types["peach"]) &&
            gettype($harvest_amounts_by_types["apple"])=="integer" &&
            gettype($harvest_amounts_by_types["peach"])=="integer"
        );

        $this->assertTrue($array_format_match);
    }



    /**
    * @dataProvider provider_counter
    */

    public function test_getHeavyAppleData($garden_source,$harvest)
    {
        $counter=new Counter;

        $heavy_apple_data=$counter->getHeavyAppleData($harvest);

        $array_format_match=(
            isset($heavy_apple_data["weight"]) && 
            isset($heavy_apple_data["tree_id"]) &&
            gettype($heavy_apple_data["weight"])=="integer" &&
            gettype($heavy_apple_data["tree_id"])=="integer"
        );

        $this->assertTrue($array_format_match);
    }

    public function provider_counter()
    {
        //Данные урожая
        $harvest=[];
        $harvest[0]=["tree_id"=>22,"type"=>"peach","weight"=>156];
        $harvest[1]=["tree_id"=>10,"type"=>"apple","weight"=>150];
        $harvest[2]=["tree_id"=>15,"type"=>"apple","weight"=>154];
        $harvest[3]=["tree_id"=>18,"type"=>"peach","weight"=>152];
        $harvest[4]=["tree_id"=>10,"type"=>"apple","weight"=>151];
        
        //Условия создания сада
        $garden_source='{
            "trees": {
            "apple":
            {
                "amount": 10,
                "harvest_range": {"min": 40,"max": 50},
                "harvest_weight_range": {"min": 150,"max": 180}
            },
            "peach":
            {
                "amount": 15,
                "harvest_range": {"min": 0,"max": 20},
                "harvest_weight_range": {"min": 130,"max": 170}
            }
            }
        }';

        $par_array=[
            array($garden_source,$harvest)
        ];

        return($par_array);
    }

}


?>