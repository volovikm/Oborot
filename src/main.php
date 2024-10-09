<?php
require_once("classes.php");

//Создание сада с деревьями, вывод массива деревьев
$garden=new Garden();
$garden->initialize();

//Сбор урожая с сада
$garden->collectHarvest();

//Подсчёты урожая
$garden->countHarverst();

$garden->display();

?>