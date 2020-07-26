<?php

namespace Recipes;

use Curl\Curl;
use Symfony\Component\CssSelector\CssSelectorConverter;

/** Search recipe(s) from a recipe source */
class RecipeSearch {

	private $sources = [
		"epicurious" => [
			"search" => "/search/%s-?content=recipe",
			"base" => "https://www.epicurious.com"
		]
	];
	private $recipe = [];

	/**
	 * Search a recipe source for a keyword and return the first result
	 * @param  string $keyword the search keyword
	 * @param  string $source  the source to search
	 * @return Array           A associative array contain the recipe details
	 */
	public function getResult($keyword, $source = 'epicurious') {
		$this->recipe['search'] = $this->sources[$source]['base'].sprintf($this->sources[$source]['search'], str_replace(' ', '-', $keyword, ));
		$curl = new Curl();
		$curl->setUserAgent('Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_4) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/84.0.4147.89 Safari/537.36');
		$curl->get($this->recipe['search']);
		if ($curl->error) {
			throw new \Exception($curl->error_code);
		}
		$dom = new \DOMDocument();
		@$dom->loadHTML(mb_convert_encoding($curl->response, 'HTML-ENTITIES', 'UTF-8'));
		$converter = new CssSelectorConverter();
		$xpath = new \DOMXPath($dom);
		try {
			$this->recipe['url'] = $this->sources[$source]['base'].$xpath->query($converter->toXPath(".results-group .recipe-panel > a"))[0]->getAttribute('href');
		} catch (Exception $e) {
			//
		}
		$curl->get($this->recipe['url']);
		if ($curl->error) {
			throw new \Exception($curl->error_code);
		}
		$dom = new \DOMDocument();
		@$dom->loadHTML(mb_convert_encoding($curl->response, 'HTML-ENTITIES', 'UTF-8'));
		$converter = new CssSelectorConverter();
		$xpath = new \DOMXPath($dom);
		try {
			$recipe = $xpath->query($converter->toXPath("div.recipe-content"))[0];
			$title = $xpath->query($converter->toXPath(".recipe-title-wrapper h1"))[0]->textContent;
		} catch (Exception $e) {
			//
		}
		$curl->close();
		$this->recipe['title'] = trim($title);
		$this->recipe['contents'] = implode(array_map([$recipe->ownerDocument,"saveHTML"], iterator_to_array($recipe->childNodes)));
		if (empty($this->recipe['title']) OR empty($this->recipe['contents'])) {
			throw new \Exception('Unable ');
		}
		return $this->recipe;
	}

	/**
	 * Renders HTML of a recipe search result
	 * @return string the rendered HTML
	 */
	public function renderResult() {
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

	</style>
</head>
<body>
	<h1><a href="{$this->recipe['url']}">{$this->recipe['title']}</a></h1>
	<div class="links"><a href="./">New</a> | <a href="{$this->recipe['search']}">More</a></div>
	<div class="recipe-content">{$this->recipe['contents']}</div>
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
	<form method="post" action="./">
		<input type="text" name="keyword" />
		<input type="submit" value="Search" />
	</form>
	<script>
	document.body.addEventListener("click", function(e) {
		e.preventDefault()
		document.querySelector("input[name='keyword']").focus()
	})
	document.querySelector("form").addEventListener("keydown",function() {
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
