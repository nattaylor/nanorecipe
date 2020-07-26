<?php
declare(strict_types=1);
use PHPUnit\Framework\TestCase;
use GuzzleHttp\Client;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\Psr7\Response;
use Recipes\RecipeSearch;

final class RecipeSearchTest extends TestCase
{
	protected $mockHandler;
	protected function setUp(): void {
		$this->mockHandler = new MockHandler();
	}

	public function testRecipeSearchGetResultsEpicurious(): void
	{
		$this->mockHandler->append(new Response(200, [], file_get_contents(__DIR__ . '/epicurious_shrimp_ceviche_search')));
		$this->mockHandler->append(new Response(200, [], file_get_contents(__DIR__ . '/epicurious_shrimp_ceviche')));
		$httpClient = new Client(['handler' => $this->mockHandler,]);

		$recipeSearch = new RecipeSearch($httpClient);
		$recipe = $recipeSearch->getResult("shrimp ceviche");

		$this->assertSame(
			"Aguachile de Camarón (Shrimp Cooked in Lime and Chile)",
			$recipe['title']
		);

		$this->assertSame(
			"https://assets.epicurious.com/photos/578ce500f84bcc2e07511d34/6:4/w_274%2Ch_169/AGUACHILE-DE-CAMARO%CC%81N-18072016.jpg",
			$recipe['img'], "Problem"
		);
		$this->assertStringContainsString(
			"ingredients-info",
			$recipe['contents']
		);
	}

	public function testRecipeSearchGetResultsNytcooking(): void
	{
		$this->mockHandler->append(new Response(200, [], file_get_contents(__DIR__ . '/nytcooking_pizza_search')));
		$this->mockHandler->append(new Response(200, [], file_get_contents(__DIR__ . '/nytcooking_pizza')));
		$httpClient = new Client(['handler' => $this->mockHandler,]);

		$recipeSearch = new RecipeSearch($httpClient);
		$recipe = $recipeSearch->getResult("pizza", 'nytcooking');

		$this->assertSame(
			"Pizza Margherita",
			$recipe['title']
		);

		$this->assertSame(
			"https://static01.nyt.com/images/2014/04/09/dining/09JPPIZZA2/09JPPIZZA2-articleLarge.jpg",
			$recipe['img'], "Problem"
		);
		$this->assertStringContainsString(
			"recipe-ingredients",
			$recipe['contents']
		);
	}
}
