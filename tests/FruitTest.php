<?php
use PHPUnit\Framework\TestCase;
require_once ('../vendor/autoload.php');
require_once("../src/classes.php");

class FruitTest extends TestCase //Для запуска: php ../vendor/bin/phpunit FruitTest.php
{
    /**
    * @dataProvider provider_collectFruit
    */

    public function test_collectFruit($garden_source,$tree_row,$fruit_row)
    {
        $garden_source=json_decode($garden_source,true);

        $fruit=new Fruit;

        $fruit->tree_id=$tree_row["id"];
        $fruit->tree_type=$tree_row["type"];
        $fruit->weight_range=$garden_source["trees"][$tree_row["type"]]["harvest_weight_range"];

        $min_weight=$garden_source["trees"][$tree_row["type"]]["harvest_weight_range"]["min"];
        $max_weight=$garden_source["trees"][$tree_row["type"]]["harvest_weight_range"]["max"];

        $fruit_example=$fruit->collectFruit();
        $this->assertEquals($fruit_row["tree_id"],$fruit_example["tree_id"]);
        $this->assertEquals($fruit_row["type"],$fruit_example["type"]);
        $this->assertTrue($fruit_example["weight"]>=$min_weight && $fruit_example["weight"]<=$max_weight);
    }

    public function provider_collectFruit()
    {
        //Ожидаемые данные одного плода
        $fruit_row=["tree_id"=>"0","type"=>"apple"];

        //Данные одного дерева
        $tree_row=["id"=>"0","type"=>"apple"];

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
            array($garden_source,$tree_row,$fruit_row)
        ];

        return($par_array);
    }
}


?>