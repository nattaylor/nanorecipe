<?php
declare(strict_types=1);
use PHPUnit\Framework\TestCase;

final class RecipeSearchTest extends TestCase
{
	public function testRecipeSearchGetResults(): void
	{
		$rc = new Recipes\RecipeSearch();
		$recipe = $rc->getResult("shrimp ceviche");
		$this->assertSame("Aguachile de Camarón (Shrimp Cooked in Lime and Chile)", $recipe['title']);
		$this->assertStringContainsString("ingredients-info", $recipe['contents']);
	}
}
