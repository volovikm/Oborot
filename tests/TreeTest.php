<?php
use PHPUnit\Framework\TestCase;
require_once ('../vendor/autoload.php');
require_once("../src/classes.php");

class TreeTest extends TestCase ////Для запуска: php ../vendor/bin/phpunit TreeTest.php
{
    /**
    * @dataProvider provider_createTree
    */

    public function test_createTree($tree_row)
    {
        $tree=new Tree;
        $tree->id=$tree_row["id"];
        $tree->type=$tree_row["type"];
        $this->assertEquals($tree_row,$tree->createTree());
    }

    public function provider_createTree()
    {
        //Ожидаемые данные одного дерева
        $par_array=[
            array(["id"=>"0","type"=>"apple"]),
            array(["id"=>"10","type"=>"peach"]),
            array(["id"=>"100","type"=>"apple"]),
            array(["id"=>"25","type"=>"peach"]),
        ];

        return($par_array);
    }



    /**
    * @dataProvider provider_collectTreeHarvest
    */

    public function test_collectTreeHarvest($garden_source,$tree_row)
    {
        $garden_source=json_decode($garden_source,true);

        $tree=new Tree;
        $tree->id=$tree_row["id"];
        $tree->type=$tree_row["type"];
        $tree->harvest_range=$garden_source["trees"][$tree_row["type"]]["harvest_range"];

        $tree_harvest_example=$tree->collectTreeHarvest($garden_source);
        $fruit_example=$tree_harvest_example[rand(0,count($tree_harvest_example))]; //Случайный плод из массива

        $this->assertTrue($fruit_example["tree_id"]==$tree_row["id"]);
        $this->assertTrue($fruit_example["type"]==$tree_row["type"]);
        $this->assertEquals("integer",gettype($fruit_example["weight"]));
    }

    public function provider_collectTreeHarvest()
    {
        //Пример данных одного дерева
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
            array($garden_source,$tree_row)
        ];

        return($par_array);
    }
}


?>