<?php
class Garden
{
    public $garden_setup; //Массив с начальными настройками сада
    public $trees=[]; //Массив деревьев в саду
    public $harvest_arr=[]; //Массив всех плодов с сада (с разделением по деревьям)
    public $harvest_amounts_by_types=[]; //Массив с количеством плодов по типам деревьев
    public $harvest_weights_by_types=[]; //Массив с весом по типам деревьев
    public $heavy_apple_data=[]; //Массив с данными тяжёлого яблока
    

    public function getGardenSetup() //Метод получения общих данных о саде из файла garden_setup.json
    {
        $garden_setup=file_get_contents("garden_setup.json");
        $garden_setup=json_decode($garden_setup,true);
        $this->garden_setup=$garden_setup;
    }

    public function initialize() //Метод создания деревьев в саду (внесение деревьев в массив)
    {
        //Заполнение массива деревьями по условию
        $this->getGardenSetup();
        $id=0;
        foreach($this->garden_setup["trees"] as $type=>$tree)
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
    }

    public function collectHarvest() //Метод сбора всех плодов в саду (внесение объёма плодов в массив)
    {
        foreach($this->trees as $tree)
        {
            $tree_obj=new Tree;
            $tree_obj->id=$tree["id"];
            $tree_obj->type=$tree["type"];
            $tree_obj->harvest_range=$this->garden_setup["trees"][$tree["type"]]["harvest_range"];

            $tree_harvest_arr=$tree_obj->collectTreeHarvest();

            //Заполнение массива урожая
            array_push($this->harvest_arr,$tree_harvest_arr); 
        }
    }

    public function countHarverst() //Метод вызова расчётов плодов
    {
        $counter=new Counter;

        //Расчёт количества фруктов по типам
        $this->harvest_amounts_by_types=$counter->countHarvestAmountByType($this->harvest_arr);

        //Расчёт общего веса фруктов по типам
        $this->harvest_weights_by_types=$counter->countHarvestWeightByType($this->harvest_arr);

        //Расчёт данных самого тяжёлого яблока
        $this->heavy_apple_data=$counter->getHeavyAppleData($this->harvest_arr);
    }

    public function display()//Метод вывода сада и результатов расчётов
    {
        //Вывод деревьев сада
        echo("<div>Деревья:  </div>");
        foreach($this->trees as $tree)
        {
            if($tree["type"]=="apple"){$type="Яблоня";}
            else{$type="Груша";}

            echo("<div>id: ".$tree["id"]."; Сорт: ".$type."</div>");
        }

        /*
        //Вывод всех собранных плодов
        echo("<br><div>Собранные плоды: </div>");
        foreach($this->harvest_arr as $tree_harvest)
        {
            foreach($tree_harvest as $fruit)
            {
                echo("<div>id дерева: ".$fruit["tree_id"]."; Тип плода: ".$fruit["type"]."; Вес плода: ".$fruit["weight"]." г </div>");
            }
        }
        */

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

class Tree extends Garden
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

    public function collectTreeHarvest()//Метод сбора урожая с дерева
    {
        $this->getGardenSetup();

        $tree_harvest_arr=[];
        $this->harvest_amount=rand($this->harvest_range["min"],$this->harvest_range["max"]);

        for($i=0;$i<$this->harvest_amount;$i++)
        {
            $fruit=new Fruit;
            $fruit->tree_id=$this->id;
            $fruit->tree_type=$this->type;
            $fruit->weight_range=$this->garden_setup["trees"][$this->type]["harvest_weight_range"];

            $fruit_row=$fruit->collectFruit();
            array_push($tree_harvest_arr,$fruit_row);
        }

        return($tree_harvest_arr);
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
    }

}

class Counter extends Garden //Класс для расчётов урожая в саду
{
    public $full_harvest_arr=[];

    public function fullHarvestArr($harvest_arr) //Метод сборки общего массива из всех плодов
    {
        foreach($harvest_arr as $tree_harvest)
        {
            foreach($tree_harvest as $fruit)
            {
                array_push($this->full_harvest_arr,$fruit);
            }
        }
    }

    public function countHarvestAmountByType($harvest_arr)
    {
        $this->getGardenSetup();
        $this->fullHarvestArr($harvest_arr);

        //Инициализация массива по типам (для возможного добавления новых типов типов деревьев)
        foreach($this->garden_setup["trees"] as $type=>$tree)
        {
            $this->harvest_amounts_by_types[$type]=0;

            foreach($this->full_harvest_arr as $fruit)
            {
                if($fruit["type"]==$type)
                {
                    $this->harvest_amounts_by_types[$type]++;
                }
            }
        }

        return($this->harvest_amounts_by_types);
    }

    public function countHarvestWeightByType($harvest_arr)
    {
        $this->getGardenSetup();
        $this->fullHarvestArr($harvest_arr);

        //Инициализация массива по типам (для возможного добавления новых типов типов деревьев)
        foreach($this->garden_setup["trees"] as $type=>$tree)
        {
            $this->harvest_weights_by_types[$type]=0;

            foreach($this->full_harvest_arr as $fruit)
            {
                if($fruit["type"]==$type)
                {
                    $this->harvest_weights_by_types[$type]=$this->harvest_weights_by_types[$type]+$fruit["weight"];
                }
            }
        }

        return($this->harvest_weights_by_types);
    }

    public function getHeavyAppleData($harvest_arr)
    {
        $this->fullHarvestArr($harvest_arr);

        $this->heavy_apple_data["weight"]=0;
        foreach($this->full_harvest_arr as $fruit)
        {
            if($fruit["type"]=="apple" && $fruit["weight"]>$this->heavy_apple_data["weight"])
            {
                $this->heavy_apple_data["weight"]=$fruit["weight"];
                $this->heavy_apple_data["tree_id"]=$fruit["tree_id"];
            }
        }

        return($this->heavy_apple_data);
    }

}
?>