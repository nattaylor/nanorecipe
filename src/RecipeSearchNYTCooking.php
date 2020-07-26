<?php

namespace Recipes;

use Symfony\Component\CssSelector\CssSelectorConverter;

/** Search recipe(s) from a recipe source */
class RecipeSearchNYTCooking extends RecipeSearch {
	public function __construct() {
		$this->recipe['foo'] = 'foo';
	}
}
