<?php

namespace Recipes;

use Symfony\Component\CssSelector\CssSelectorConverter;

/** Search recipe(s) from a recipe source */
class RecipeSearch {

	protected $sources = [
		"epicurious" => [
			"search" => "/search/%s-?content=recipe",
			"base" => "https://www.epicurious.com"
		],
		"nytcooking" => [
			"base" => "https://cooking.nytimes.com/",
			"search" => "/search?q=%s",
			"class" => "\Recipes\RecipeSearchNYTCooking"
		]
	];
	protected $recipe = [];
	private $client;
	private $start = 0;

	public function __construct($client = null) {
		$this->start = time();
		if (is_null($client)) {
			$client = new \GuzzleHttp\Client([
				'headers' => [
					'User-Agent' => 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_4) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/84.0.4147.89 Safari/537.36'
				]
			]);
		}
		$this->client = $client;
	}

	/**
	 * Search a recipe source for a keyword and return the first result
	 * @param  string $keyword the search keyword
	 * @param  string $source  the source to search
	 * @return Array           A associative array contain the recipe details
	 */
	public function getResult($keyword, $source = 'epicurious') {
		if (!array_key_exists($source, $this->sources)) {
			throw new \Exception("$source does not exist");
		}
		$this->{"getResult$source"}($keyword);
		return $this->recipe;
	}

	private function getResultnytcooking($keyword) {
		$this->recipe['search'] = $this->sources['nytcooking']['base'].sprintf($this->sources['nytcooking']['search'], str_replace(' ', '-', $keyword, ));
		$client = $this->client;
		$response = $client->request('GET', $this->recipe['search']);
		$dom = new \DOMDocument();
		@$dom->loadHTML(mb_convert_encoding($response->getBody(), 'HTML-ENTITIES', 'UTF-8'));
		$converter = new CssSelectorConverter();
		$xpath = new \DOMXPath($dom);
		try {
			$this->recipe['url'] = $this->sources['nytcooking']['base'].$xpath->query($converter->toXPath(".recipe-card-list .recipe-card a"))[0]->getAttribute('href');
		} catch (Exception $e) {
			//
		}
		$response = $client->request('GET', $this->recipe['url']);
		$dom = new \DOMDocument();
		@$dom->loadHTML(mb_convert_encoding($response->getBody(), 'HTML-ENTITIES', 'UTF-8'));
		$converter = new CssSelectorConverter();
		$xpath = new \DOMXPath($dom);
		try {
			$recipe = $xpath->query($converter->toXPath(".recipe-instructions"))[0];
			$title = $xpath->query($converter->toXPath(".recipe-title"))[0]->textContent;
			$img = $xpath->query($converter->toXPath("head > meta[property='og:image']"))[0]->getAttribute('content');
		} catch (Exception $e) {
			//
		}
		$this->recipe['title'] = trim($title);
		$this->recipe['contents'] = implode(array_map([$recipe->ownerDocument,"saveHTML"], iterator_to_array($recipe->childNodes)));
		$this->recipe['img'] = $img;
		if (empty($this->recipe['title']) OR empty($this->recipe['contents'])) {
			throw new \Exception('Unable ');
		}
	}

	private function getResultepicurious($keyword) {
		$this->recipe['search'] = $this->sources['epicurious']['base'].sprintf($this->sources['epicurious']['search'], str_replace(' ', '-', $keyword, ));
		$client = $this->client;
		$response = $client->request('GET', $this->recipe['search']);
		$dom = new \DOMDocument();
		@$dom->loadHTML(mb_convert_encoding($response->getBody(), 'HTML-ENTITIES', 'UTF-8'));
		$converter = new CssSelectorConverter();
		$xpath = new \DOMXPath($dom);
		try {
			$this->recipe['url'] = $this->sources['epicurious']['base'].$xpath->query($converter->toXPath(".results-group .recipe-panel > a"))[0]->getAttribute('href');
		} catch (Exception $e) {
			//
		}
		$response = $client->request('GET', $this->recipe['url']);
		$dom = new \DOMDocument();
		@$dom->loadHTML(mb_convert_encoding($response->getBody(), 'HTML-ENTITIES', 'UTF-8'));
		$converter = new CssSelectorConverter();
		$xpath = new \DOMXPath($dom);
		try {
			$recipe = $xpath->query($converter->toXPath("div.recipe-content"))[0];
			$title = $xpath->query($converter->toXPath(".recipe-title-wrapper h1"))[0]->textContent;
			$img = $xpath->query($converter->toXPath(".recipe-image picture > img"))[0]->getAttribute('srcset');
		} catch (Exception $e) {
			//
		}
		$this->recipe['title'] = trim($title);
		$this->recipe['contents'] = implode(array_map([$recipe->ownerDocument,"saveHTML"], iterator_to_array($recipe->childNodes)));
		$this->recipe['img'] = $img;
		if (empty($this->recipe['title']) OR empty($this->recipe['contents'])) {
			throw new \Exception('Unable ');
		}
	}

	/**
	 * Renders HTML of a recipe search result
	 * @return string the rendered HTML
	 */
	public function renderResult() {
		$time = time() - $this->start;
		return <<<HTML
<!DOCTYPE html>
<html>
<head>
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<title>{$this->recipe['title']}</title>
	<style>
	.preparation-groups, .ingredient-groups {
			list-style-type:none;
			margin:0;
			padding:0;
	}

	.recipe-video-wrap {
			display:none;
	}

	dt {
		display:none;
	}
	dd {
		display: inline;
		margin: 0;
	}

	h1 {
		font-size:3ch;
	}
	.links {
		position: absolute;
		top:1vw;
		right:1vw;
	}
	.recipe-content > div {
		display:none;
	}

	.recipe-content .recipe-summary, .recipe-content .ingredients-info, .recipe-content .instructions, .recipe-content .recipe-notes {
		display:block;
	}

	</style>
</head>
<body>
	<h1><a href="{$this->recipe['url']}">{$this->recipe['title']}</a></h1>
	<div class="links"><span id="time">{$time}</span> | <a href="./">New</a> | <a href="{$this->recipe['search']}">More</a></div>
	<div class="recipe-content">{$this->recipe['contents']}</div>
	<img src="{$this->recipe['img']}" />
	<script type="text/javascript">
		document.querySelector("#time").innerText = (parseFloat(document.querySelector("#time").innerText)+parseFloat(performance.getEntriesByType("navigation").pop().loadEventStart/1000)).toFixed(1)+"s";
	</script>
</body>
</html>
HTML;
	}

	/**
	 * Renders the HTML for the index page
	 * @return string the html
	 */
	public function renderIndex() {
		return <<<HTML
<!DOCTYPE html>
<html>
<head>
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<title>NanoRecipe</title>
	<style>
		body {
			text-align: center;
			min-height: 100vh;
		}
	</style>
</head>
<body>
	<h1>NanoRecipe</h1>
	<form method="get" action="./">
		<p>Search for a recipe and get a plaintext result back in a couple of seconds.</p>
		<input type="text" name="keyword" style="font-size:large" />
		<br>
		<select name="source">
			<option selected>epicurious</option>
			<option>nytcooking</option>
		</select>
	</form>
	<script>
	/** Help mobile users with focus */
	document.body.addEventListener("click", function(e) {
		e.preventDefault()
		document.querySelector("input[name='keyword']").focus()
	})
	/** Bind submit to enter key */
	document.querySelector("form").addEventListener("keydown",function(event) {
		if (event.keyCode === 13) {
			this.submit();
		}
	})
	</script>
</body>
</html>
HTML;
	}
}
