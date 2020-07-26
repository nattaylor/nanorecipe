<?php

//https://www.foodnetwork.com/search/shrimp-ceviche-
//https://www.epicurious.com/search/shrimp%20ceviche?content=recipe

require __DIR__ . '/vendor/autoload.php';

$recipe = new Recipes\RecipeSearch();
if (empty($_POST['keyword'])) {
	echo $recipe->renderIndex();
} else {
	$result = $recipe->getResult($_POST['keyword']);
	echo $recipe->renderResult($result);
}
