<?php
class Garden
{
    /*
    Сад представляет собой массив деревьев, созданный по условиям из файла garden_setup.json
    */

    public $garden_setup; //Массив с начальными настройками сада
    public $trees=[]; //Массив деревьев в саду
    public $harvest=[]; //Массив всех плодов с сада 
    public $harvest_amounts_by_types=[]; //Массив с количеством плодов по типам деревьев
    public $harvest_weights_by_types=[]; //Массив с весом по типам деревьев
    public $heavy_apple_data=[]; //Массив с данными тяжёлого яблока
    

    public function getGardenSetup() //Метод получения данных о саде из файла garden_setup.json
    {
        $garden_setup=file_get_contents($_SERVER["DOCUMENT_ROOT"]."/Oborot/src/garden_setup.json");
        $garden_setup=json_decode($garden_setup,true);
        $this->garden_setup=$garden_setup;
        return($this->garden_setup);
    }

    public function initialize($garden_source) //Метод создания деревьев в саду (внесение деревьев в массив)
    {
        //Заполнение массива деревьями по условию
        $id=0;
        foreach($garden_source["trees"] as $type=>$tree)
        {   
            for($i=0;$i<$tree["amount"];$i++)
            {
                $tree_obj=new Tree;
                $tree_obj->id=$id;
                $tree_obj->type=$type;
                $id++;
                $tree_row=$tree_obj->createTree();
                array_push($this->trees,$tree_row);
            }
        }

        return($this->trees);
    }

    public function collectHarvest($trees,$garden_source) //Метод сбора всех плодов в саду (внесение объёма плодов в массив)
    {
        foreach($trees as $tree)
        {
            $tree_obj=new Tree;
            $tree_obj->id=$tree["id"];
            $tree_obj->type=$tree["type"];
            $tree_obj->harvest_range=$garden_source["trees"][$tree["type"]]["harvest_range"];

            $tree_harvest=$tree_obj->collectTreeHarvest($garden_source); //Массив урожая с одного дерева

            //Заполнение массива урожая
            $this->harvest=array_merge_recursive($this->harvest,$tree_harvest);
        }

        return($this->harvest);
    }

    public function countHarverst($garden_source) //Метод вызова расчётов плодов
    {
        $counter=new Counter;

        //Расчёт количества фруктов по типам
        $this->harvest_amounts_by_types=$counter->countHarvestAmountByType($this->garden_setup,$this->harvest);

        //Расчёт общего веса фруктов по типам
        $this->harvest_weights_by_types=$counter->countHarvestWeightByType($this->garden_setup,$this->harvest);

        //Расчёт данных самого тяжёлого яблока
        $this->heavy_apple_data=$counter->getHeavyAppleData($this->harvest);

        var_dump($this->heavy_apple_data);

    }

    public function display($console=true)//Метод вывода сада и результатов расчётов
    { 
        if($console) //Вывод в консоль
        {
            //Вывод деревьев сада
            echo("\nДеревья: \n");
            foreach($this->trees as $tree)
            {
                if($tree["type"]=="apple"){$type="Яблоня";}
                else{$type="Груша";}

                echo("id: ".$tree["id"]."; Сорт: ".$type."\n");
            }

            //Вывод общего кол-ва собранных фруктов каждого вида
            echo("\nОбщее кол-во собранных фруктов каждого вида: \n");
            foreach($this->harvest_amounts_by_types as $type=>$amount)
            {
                if($type=="apple"){$type="Яблоня";}
                else{$type="Груша";}

                echo("Вид фрукта: ".$type.", Количество: ".$amount."; \n");
            }

            //Вывод общего кол-ва собранных фруктов каждого вида
            echo("\nОбщий вес собранных фруктов каждого вида: \n");
            foreach($this->harvest_weights_by_types as $type=>$weight)
            {
                if($type=="apple"){$type="Яблоня";}
                else{$type="Груша";}

                echo("Вид фрукта: ".$type.", Вес: ".$weight." г; \n");
            }

            //Вывод веса самого тяжёлого яблока и id дерева
            echo("\nВес самого тяжёлого яблока: ".$this->heavy_apple_data["weight"]." г\n");
            echo("id дерева: ".$this->heavy_apple_data["tree_id"]."\n");
        }
        else //Вывод на html страницу
        {
            //Вывод деревьев сада
            echo("<div>Деревья: </div>");
            foreach($this->trees as $tree)
            {
                if($tree["type"]=="apple"){$type="Яблоня";}
                else{$type="Груша";}

                echo("<div>id: ".$tree["id"]."; Сорт: ".$type."</div>");
            }

            //Вывод общего кол-ва собранных фруктов каждого вида
            echo("<br><div>Общее кол-во собранных фруктов каждого вида: </div>");
            foreach($this->harvest_amounts_by_types as $type=>$amount)
            {
                if($type=="apple"){$type="Яблоня";}
                else{$type="Груша";}

                echo("<div>Вид фрукта: ".$type.", Количество: ".$amount."; </div>");
            }

            //Вывод общего кол-ва собранных фруктов каждого вида
            echo("<br><div>Общий вес собранных фруктов каждого вида: </div>");
            foreach($this->harvest_weights_by_types as $type=>$weight)
            {
                if($type=="apple"){$type="Яблоня";}
                else{$type="Груша";}

                echo("<div>Вид фрукта: ".$type.", Вес: ".$weight." г; </div>");
            }

            //Вывод веса самого тяжёлого яблока и id дерева
            echo("<br><div>Вес самого тяжёлого яблока: ".$this->heavy_apple_data["weight"]." г</div>");
            echo("<div>id дерева: ".$this->heavy_apple_data["tree_id"]."</div>");
        }
    }
}

class Tree
{
    public $id; 
    public $type; //Сорт дерева: apple | peach
    public $harvest_range; //Диапазон возможного количества плодов с дерева
    public $harvest_amount; //Объём плодов с дерева
    public $fruits=[]; //Массив плодов с дерева

    public function createTree()  //Метод создания дерева
    {
        $tree_row=["id"=>$this->id,"type"=>$this->type];
        return($tree_row);
    }

    public function collectTreeHarvest($garden_source)//Метод сбора урожая с дерева (возвращает масив плодов с дерева)
    {
        $tree_harvest=[];
        $this->harvest_amount=rand($this->harvest_range["min"],$this->harvest_range["max"]);

        for($i=0;$i<$this->harvest_amount;$i++)
        {
            $fruit=new Fruit;
            $fruit->tree_id=$this->id;
            $fruit->tree_type=$this->type;
            $fruit->weight_range=$garden_source["trees"][$this->type]["harvest_weight_range"];

            $fruit_row=$fruit->collectFruit();
            array_push($tree_harvest,$fruit_row);
        }

        return($tree_harvest);
    }
}

class Fruit 
{
    public $tree_id;
    public $tree_type;
    public $weight;
    public $weight_range;

    public function collectFruit()//Метод сбора фрукта
    {
        $this->defineWeight();
        $fruit_row=["tree_id"=>$this->tree_id,"type"=>$this->tree_type,"weight"=>$this->weight];

        return($fruit_row);
    }

    public function defineWeight()//Метод определения веса фрукта
    {
        $this->weight=rand($this->weight_range["min"],$this->weight_range["max"]);

        return($this->weight);
    }

}

class Counter//Класс для расчётов урожая в саду
{
    public function countHarvestAmountByType($garden_source,$harvest)
    {
        $harvest_amounts_by_types=[];

        //Распределение по типам (для возможного добавления новых типов типов деревьев)
        foreach($garden_source["trees"] as $type=>$tree)
        {
            $harvest_amounts_by_types[$type]=0;

            foreach($harvest as $fruit)
            {
                if($fruit["type"]==$type)
                {
                    $harvest_amounts_by_types[$type]++;
                }
            }
        }

        return($harvest_amounts_by_types);
    }

    public function countHarvestWeightByType($garden_source,$harvest)
    {
        $harvest_weights_by_types=[];

        //Распределение по типам (для возможного добавления новых типов типов деревьев)
        foreach($garden_source["trees"] as $type=>$tree)
        {
            $harvest_weights_by_types[$type]=0;

            foreach($harvest as $fruit)
            {
                if($fruit["type"]==$type)
                {
                    $harvest_weights_by_types[$type]=$harvest_weights_by_types[$type]+$fruit["weight"];
                }
            }
        }

        return($harvest_weights_by_types);
    }

    public function getHeavyAppleData($harvest)
    {
        $heavy_apple_data["weight"]=0;
        foreach($harvest as $fruit)
        {
            if($fruit["type"]=="apple" && $fruit["weight"]>$heavy_apple_data["weight"])
            {
                $heavy_apple_data["weight"]=$fruit["weight"];
                $heavy_apple_data["tree_id"]=$fruit["tree_id"];
            }
        }

        return($heavy_apple_data);
    }

}
?>