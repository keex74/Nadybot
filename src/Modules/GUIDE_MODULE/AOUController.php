<?php declare(strict_types=1);

namespace Nadybot\Modules\GUIDE_MODULE;

use function Safe\preg_split;

use Amp\File\{FileCache};
use Amp\Http\Client\{HttpClientBuilder, Request};
use Amp\Sync\LocalKeyedMutex;
use DOMDocument;
use DOMElement;
use Exception;
use Nadybot\Core\{
	Attributes as NCA,
	CmdContext,
	Config\BotConfig,
	Filesystem,
	ModuleInstance,
	Safe,
	Text,
	Types\AOIcon,
	Types\AOItemSpec,
};
use Nadybot\Modules\ITEMS_MODULE\{
	ItemsController,
};

use Throwable;

/**
 * @author Tyrence (RK2)
 */
#[
	NCA\Instance,
	NCA\DefineCommand(
		command: 'aou',
		accessLevel: 'guest',
		description: 'Search for or view a guide from AO-Universe',
	)
]
class AOUController extends ModuleInstance {
	public const AOU_URL = 'https://www.ao-universe.com/mobile/parser.php?bot=nadybot';
	#[NCA\Inject]
	private HttpClientBuilder $builder;

	#[NCA\Inject]
	private BotConfig $config;

	#[NCA\Inject]
	private Text $text;

	#[NCA\Inject]
	private ItemsController $itemsController;

	#[NCA\Inject]
	private Filesystem $fs;

	#[NCA\Setup]
	public function setup(): void {
		$cacheFolder = $this->config->paths->cache . '/guide';
		if (!$this->fs->exists($cacheFolder)) {
			$this->fs->createDirectory($cacheFolder, 0700);
		}
	}

	public function isValidXML(?string $data): bool {
		if (!isset($data) || !strlen($data)) {
			return false;
		}

		/** @phpstan-var non-empty-string $data */
		try {
			$dom = new DOMDocument();
			return $dom->loadXML($data) !== false;
		} catch (Throwable $e) {
			return false;
		}
	}

	/** View a specific guide on AO-Universe */
	#[NCA\HandlesCommand('aou')]
	public function aouView(CmdContext $context, int $guideId): void {
		$params = [
			'mode' => 'view',
			'id' => $guideId,
		];

		$cache = new FileCache(
			$this->config->paths->cache . '/guide',
			new LocalKeyedMutex(),
			$this->fs->getFilesystem(),
		);
		$cacheKey = (string)$guideId;
		$body = $cache->get($cacheKey);

		if ($body === null) {
			$client = $this->builder->build();

			$response = $client->request(new Request(
				self::AOU_URL . '&' . http_build_query($params)
			));
			$body = $response->getBody()->buffer();
			if ($response->getStatus() !== 200 || $body === '' || !$this->isValidXML($body)) {
				$msg = "An error occurred while trying to retrieve AOU guide with id <highlight>{$guideId}<end>.";
				$context->reply($msg);
			}
			$cache->set($cacheKey, $body, 3_600*24);
		}
		try {
			/** @phpstan-var non-empty-string $body */
			$msg = $this->renderAOUGuide($body, $guideId);
		} catch (Exception $e) {
			$context->reply("Error with AOU guide <highlight>{$guideId}<end>: ".
				$e->getMessage());
			return;
		}
		$context->reply($msg);
	}

	/**
	 * @phpstan-param non-empty-string $body
	 *
	 * @return string|list<string>
	 */
	public function renderAOUGuide(string $body, int $guideId): array|string {
		$dom = new DOMDocument();
		$dom->loadXML($body);

		if ($dom->getElementsByTagName('error')->length > 0) {
			throw new Exception(
				$dom->getElementsByTagName('text')->item(0)->nodeValue
			);
		}

		$content = $dom->getElementsByTagName('content')->item(0);
		if (!isset($content) || !($content instanceof DOMElement)) { // @phpstan-ignore-line
			throw new Exception('Invalid XML structure');
		}
		$title = $content->getElementsByTagName('name')->item(0)->nodeValue;

		$blob = Text::makeChatcmd('Guide on AO-Universe', "/start https://www.ao-universe.com/main.php?site=knowledge&id={$guideId}") . "\n\n";

		$blob .= 'Updated: <highlight>' . $content->getElementsByTagName('update')->item(0)->nodeValue . "<end>\n";
		$blob .= 'Profession: <highlight>' . $content->getElementsByTagName('class')->item(0)->nodeValue . "<end>\n";
		$blob .= 'Faction: <highlight>' . $content->getElementsByTagName('faction')->item(0)->nodeValue . "<end>\n";
		$blob .= 'Level: <highlight>' . $content->getElementsByTagName('level')->item(0)->nodeValue . "<end>\n";
		$blob .= 'Author: <highlight>' . $this->processInput($content->getElementsByTagName('author')->item(0)->nodeValue) . "<end>\n\n";

		$blob .= $this->processInput($content->getElementsByTagName('text')->item(0)->nodeValue);

		$blob .= "\n\n<i>Powered by " . Text::makeChatcmd('AO-Universe', '/start https://www.ao-universe.com') . '</i>';

		$msg = $this->text->makeBlob($title, $blob);
		return $msg;
	}

	/**
	 * Search for an AO-Universe guide and include guides that have the search terms in the guide text
	 *
	 * Note: this will search the name, category, and description as well as the guide body for matches.
	 */
	#[NCA\HandlesCommand('aou')]
	public function aouAllSearch(CmdContext $context, #[NCA\Str('all')] string $action, string $search): void {
		$msg = $this->searchAndGetAOUGuide($search, true);
		$context->reply($msg);
	}

	/**
	 * Search for an AO-Universe guide
	 *
	 * Note: this will search the name, category, and description for matches
	 */
	#[NCA\HandlesCommand('aou')]
	public function aouSearch(CmdContext $context, string $search): void {
		$msg = $this->searchAndGetAOUGuide($search, false);
		$context->reply($msg);
	}

	/** @return string|list<string> */
	private function searchAndGetAOUGuide(string $search, bool $searchGuideText): string|array {
		$params = [
			'mode' => 'search',
			'search' => $search,
		];
		$client = $this->builder->build();

		$response = $client->request(new Request(
			self::AOU_URL . '&' . http_build_query($params)
		));
		$body = $response->getBody()->buffer();
		if ($response->getStatus() !== 200 || $body === '' || !$this->isValidXML($body)) {
			return 'An error occurred while trying to search the AOU guides.';
		}

		/** @phpstan-var non-empty-string $body */
		return $this->renderAOUGuideList($body, $searchGuideText, $search);
	}

	/**
	 * @phpstan-param non-empty-string $body
	 *
	 * @return string|list<string>
	 */
	private function renderAOUGuideList(string $body, bool $searchGuideText, string $search): array|string {
		$searchTerms = explode(' ', $search);

		$dom = new DOMDocument();
		$dom->loadXML($body);

		$sections = $dom->getElementsByTagName('section');
		$blob = '';
		$count = 0;
		foreach ($sections as $section) {
			$category = $this->getSearchResultCategory($section);

			$guides = $section->getElementsByTagName('guide');
			$tempBlob = '';
			$found = false;
			foreach ($guides as $guide) {
				$guideObj = $this->getGuideObject($guide);
				// since aou returns guides that have keywords in the guide body, we filter the results again
				// to only include guides that contain the keywords in the category, name, or description
				if ($searchGuideText || $this->striposarray($category . ' ' . $guideObj->name . ' ' . $guideObj->description, $searchTerms)) {
					$count++;
					$tempBlob .= '  ' . Text::makeChatcmd("{$guideObj->name}", "/tell <myname> aou {$guideObj->id}") . ' - ' . $guideObj->description . "\n";
					$found = true;
				}
			}

			if ($found) {
				$blob .= '<pagebreak><header2>' . $category . "<end>\n";
				$blob .= $tempBlob;
				$blob .= "\n";
			}
		}

		$blob .= "\n<i>Powered by " . Text::makeChatcmd('AO-Universe.com', '/start https://www.ao-universe.com') . '</i>';

		if ($count > 0) {
			if ($searchGuideText) {
				$title = "All AO-Universe Guides containing '{$search}' ({$count})";
			} else {
				$title = "AO-Universe Guides containing '{$search}' ({$count})";
			}
			$msg = $this->text->makeBlob($title, $blob);
		} else {
			$msg = "Could not find any guides containing: '{$search}'.";
			if (!$searchGuideText) {
				$msg .= " Try including all results with <highlight>!aou all {$search}<end>.";
			}
		}
		return $msg;
	}

	/** @param iterable<string> $needles */
	private function striposarray(string $haystack, iterable $needles): bool {
		foreach ($needles as $needle) {
			if (stripos($haystack, $needle) === false) {
				return false;
			}
		}
		return true;
	}

	private function getSearchResultCategory(DOMElement $section): string {
		$folders = $section->getElementsByTagName('folder');
		$output = [];
		foreach ($folders as $folder) {
			$output []= $folder->getElementsByTagName('name')->item(0)->nodeValue;
		}
		return implode(' - ', array_reverse($output));
	}

	private function getGuideObject(DOMElement $guide): AOUGuide {
		return new AOUGuide(
			id: (int)$guide->getElementsByTagName('id')->item(0)->nodeValue,
			name: $guide->getElementsByTagName('name')->item(0)->nodeValue,
			description: $guide->getElementsByTagName('desc')->item(0)->nodeValue,
		);
	}

	/**
	 * @param string[] $arr
	 *
	 * @psalm-assert list{string,string,string,numeric-string,string} $arr
	 */
	private function replaceItem(array $arr): string {
		$type = $arr[1];
		$id = (int)$arr[3];

		$output = '';

		$row = $this->itemsController->findById($id);
		if ($row !== null) {
			$output = $this->generateItemMarkup($type, $row);
		} else {
			$output = (string)$id;
		}
		return $output;
	}

	/**
	 * @param string[] $arr
	 *
	 * @psalm-assert list{string,string,string} $arr
	 */
	private function replaceWaypoint(array $arr): string {
		$label = $arr[2];
		$params = explode(' ', $arr[1]);
		$wp = [];
		foreach ($params as $param) {
			[$name, $value] = explode('=', $param);
			$wp[$name] = $value;
		}

		return Text::makeChatcmd($label . " ({$wp['x']}x{$wp['y']})", "/waypoint {$wp['x']} {$wp['y']} {$wp['pf']}");
	}

	/**
	 * @param string[] $arr
	 *
	 * @psalm-assert list{string,string,string,string} $arr
	 */
	private function replaceGuideLinks(array $arr): string {
		$url = $arr[2];
		$label = $arr[3];

		if (count($idArray = Safe::pregMatch('/pid=(\\d+)/', $url))) {
			return Text::makeChatcmd($label, '/tell <myname> aou ' . $idArray[1]);
		}
		return Text::makeChatcmd($label, "/start {$url}");
	}

	private function processInput(string $input): string {
		$input = Safe::pregReplace("/(\[size.+?\])\[b\]/i", '[b]$1', $input);
		$input = Safe::pregReplace("/(\[color.+?\])\[b\]/i", '[b]$1', $input);
		$input = preg_replace_callback("/\[(item|itemname|itemicon)( nolink)?\](\d+)\[\/(item|itemname|itemicon)\]/i", $this->replaceItem(...), $input);
		$input = preg_replace_callback("/\[waypoint ([^\]]+)\]([^\]]*)\[\/waypoint\]/", $this->replaceWaypoint(...), $input);
		$input = preg_replace_callback("/\[(localurl|url)=([^ \]]+)\]([^\[]+)\[\/(localurl|url)\]/", $this->replaceGuideLinks(...), $input);
		$input = Safe::pregReplace("/\[img\](.*?)\[\/img\]/", '-image-', $input);
		$input = Safe::pregReplace("/\[color=#([0-9A-F]+)\]/", '<font color=#$1>', $input);
		$input = Safe::pregReplace("/\[color=(.+?)\]/", '<$1>', $input);
		$input = Safe::pregReplace("/\[\/color\]/", '<end>', $input);
		$input = str_replace(['[center]', '[/center]'], ['<center>', '</center>'], $input);
		$input = str_replace(['[i]', '[/i]'], ['<i>', '</i>'], $input);
		$input = str_replace(['[b]', '[/b]'], ['<highlight>', '<end>'], $input);

		$pattern = "/(\[.+?\])/";
		$matches =preg_split($pattern, $input, -1, \PREG_SPLIT_DELIM_CAPTURE | \PREG_SPLIT_NO_EMPTY);

		$output = '';
		foreach ($matches as $match) {
			$output .= $this->processTag($match);
		}

		return $output;
	}

	private function processTag(string $tag): string {
		switch ($tag) {
			case '[ts_ts]':
				return ' + ';
			case '[ts_ts2]':
				return ' = ';
			case '[cttd]':
				return ' | ';
			case '[cttr]':
			case '[br]':
				return "\n";
		}

		if ($tag[0] === '[') {
			return '';
		}

		return $tag;
	}

	private function generateItemMarkup(string $type, AOItemSpec&AOIcon $obj): string {
		$output = '';
		if ($type === 'item' || $type === 'itemicon') {
			$output .= $obj->getIcon();
		}

		if ($type === 'item' || $type === 'itemname') {
			$output .= $obj->getLink($obj->getHighQL());
		}

		return $output;
	}
}
