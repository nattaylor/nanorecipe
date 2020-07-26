<?php
declare(strict_types=1);
use PHPUnit\Framework\TestCase;
use GuzzleHttp\Client;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\Psr7\Response;

final class RecipeSearchTest extends TestCase
{
	protected $mockHandler;
	protected function setUp(): void {
		$this->mockHandler = new MockHandler();

		$httpClient = new Client([
				'handler' => $this->mockHandler,
		]);
	}

	public function testRecipeSearchGetResults(): void
	{
		$this->mockHandler->append(new Response(200, [], file_get_contents(__DIR__ . '/shrimp_ceviche_search')));
		$this->mockHandler->append(new Response(200, [], file_get_contents(__DIR__ . '/shrimp_ceviche')));
		$httpClient = new Client([
				'handler' => $this->mockHandler,
		]);
		$rc = new Recipes\RecipeSearch($httpClient);
		$recipe = $rc->getResult("shrimp ceviche");
		$this->assertSame("Aguachile de Camarón (Shrimp Cooked in Lime and Chile)", $recipe['title']);
		$this->assertStringContainsString("ingredients-info", $recipe['contents']);
	}
}
