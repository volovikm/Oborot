<?php
require_once("classes.php");

//Создание сада с деревьями, вывод массива деревьев
$garden=new Garden();
$garden->initialize($garden->getGardenSetup());

//Сбор урожая с сада
$garden->collectHarvest($garden->trees,$garden->garden_setup);

//Подсчёты урожая
$garden->countHarverst($garden->garden_setup);

//Вывод результатов
$garden->display();
?>