<?php

//https://www.foodnetwork.com/search/shrimp-ceviche-
//https://www.epicurious.com/search/shrimp%20ceviche?content=recipe

require __DIR__ . '/vendor/autoload.php';

$recipe = new Recipes\RecipeSearch();
if (empty($_GET['keyword'])) {
	echo $recipe->renderIndex();
} else {
	$result = $recipe->getResult($_GET['keyword'], $_GET['source']);
	echo $recipe->renderResult($result);
}
