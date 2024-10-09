<?php
use PHPUnit\Framework\TestCase;
require_once ('../vendor/autoload.php');
require_once("../src/classes.php");

class GardenTest extends TestCase //Для запуска: php ../vendor/bin/phpunit GardenTest.php
{
    /**
    * @dataProvider provider_initialize
    */

    public function test_initialize($trees_array,$garden_source)
    {
        $garden_source=json_decode($garden_source,true);

        $garden=new Garden;
        $this->assertEquals($trees_array,$garden->initialize($garden_source));
    }

    public function provider_initialize()
    {
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

        //Пример ожидаемого массива деревьев
        $trees_array=[];
        $id=0;
        for($i=0;$i<25;$i++)
        {
            if($i<10)
            {
                $type="apple";
            }
            else{
                $type="peach";
            }
            
            $trees_array[$i]=["id"=>$i,"type"=>$type];
            $id++;
        }

        $par_array=[
            array($trees_array,$garden_source)
        ];

        return($par_array);
    }


    /**
    * @dataProvider provider_collectHarvest
    */
    public function test_collectHarvest($trees_array,$garden_source)
    {
        $garden_source=json_decode($garden_source,true);

        $garden=new Garden; 
        $test_harvest=$garden->collectHarvest($trees_array,$garden_source);

        $fruit_example=$test_harvest[rand(0,count($test_harvest))];

        $this->assertEquals("integer",gettype($fruit_example["tree_id"])); //int (0 -24)
        $this->assertEquals("string",gettype($fruit_example["type"])); //string: apple | peach
        $this->assertEquals("integer",gettype($fruit_example["weight"])); //int

        $this->assertTrue($fruit_example["type"]=="apple" ||  $fruit_example["type"]=="peach");
    }

    public function provider_collectHarvest()
    {
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

        //Пример массива деревьев
        $trees_array=[];
        $id=0;
        for($i=0;$i<25;$i++)
        {
            if($i<10)
            {
                $type="apple";
            }
            else{
                $type="peach";
            }
            
            $trees_array[$i]=["id"=>$i,"type"=>$type];
            $id++;
        }

        $par_array=[
            array($trees_array,$garden_source)
        ];

        return($par_array);
    }
}


?>