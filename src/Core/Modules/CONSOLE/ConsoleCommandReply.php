<?php declare(strict_types=1);

namespace Nadybot\Core\Modules\CONSOLE;

use Nadybot\Core\{
	Attributes as NCA,
	Config\BotConfig,
	MessageHub,
	Modules\COLORS\ColorsController,
	Nadybot,
	Routing\Character,
	Routing\RoutableMessage,
	Routing\Source,
	Safe,
	Types\CommandReply,
	Types\MessageEmitter,
};

class ConsoleCommandReply implements CommandReply, MessageEmitter {
	#[NCA\Inject]
	private ConsoleController $consoleController;

	#[NCA\Inject]
	private ColorsController $colors;

	#[NCA\Inject]
	private MessageHub $messageHub;

	#[NCA\Inject]
	private BotConfig $config;

	public function __construct(
		private Nadybot $chatBot
	) {
	}

	public function getChannelName(): string {
		return Source::CONSOLE;
	}

	public function reply($msg): void {
		foreach ((array)$msg as $text) {
			$rMessage = new RoutableMessage($text);
			$rMessage->setCharacter(new Character($this->config->main->character, $this->chatBot->char?->id));
			$rMessage->prependPath(new Source(Source::CONSOLE, 'Console'));
			$this->messageHub->handle($rMessage);
			$text = $this->formatMsg($text);
			echo("{$this->config->main->character}: {$text}\n");
		}
	}

	/** @param string|string[] $msg */
	public function replyOnly(string|array $msg): void {
		foreach ((array)$msg as $text) {
			$text = $this->formatMsg($text);
			echo("{$this->config->main->character}: {$text}\n");
		}
	}

	/** Replace color names with hexcodes */
	public function replaceColorNamesWithCodes(string $text): string {
		$namesToHex = [
			'aliceblue' => '#f0f8ff',
			'antiquewhite' => '#faebd7',
			'aqua' => '#00ffff',
			'aquamarine' => '#7fffd4',
			'azure' => '#f0ffff',
			'beige' => '#f5f5dc',
			'bisque' => '#ffe4c4',
			'black' => '#000000',
			'blanchedalmond' => '#ffebcd',
			'blue' => '#0000ff',
			'blueviolet' => '#8a2be2',
			'brown' => '#a52a2a',
			'burlywood' => '#deb887',
			'cadetblue' => '#5f9ea0',
			'chartreuse' => '#7fff00',
			'chocolate' => '#d2691e',
			'coral' => '#ff7f50',
			'cornflowerblue' => '#6495ed',
			'cornsilk' => '#fff8dc',
			'crimson' => '#dc143c',
			'cyan' => '#00ffff',
			'darkblue' => '#00008b',
			'darkcyan' => '#008b8b',
			'darkgoldenrod' => '#b8860b',
			'darkgray' => '#a9a9a9',
			'darkgrey' => '#a9a9a9',
			'darkgreen' => '#006400',
			'darkkhaki' => '#bdb76b',
			'darkmagenta' => '#8b008b',
			'darkolivegreen' => '#556b2f',
			'darkorange' => '#ff8c00',
			'darkorchid' => '#9932cc',
			'darkred' => '#8b0000',
			'darksalmon' => '#e9967a',
			'darkseagreen' => '#8fbc8f',
			'darkslateblue' => '#483d8b',
			'darkslategray' => '#2f4f4f',
			'darkslategrey' => '#2f4f4f',
			'darkturquoise' => '#00ced1',
			'darkviolet' => '#9400d3',
			'deeppink' => '#ff1493',
			'deepskyblue' => '#00bfff',
			'dimgray' => '#696969',
			'dimgrey' => '#696969',
			'dodgerblue' => '#1e90ff',
			'firebrick' => '#b22222',
			'floralwhite' => '#fffaf0',
			'forestgreen' => '#228b22',
			'fuchsia' => '#ff00ff',
			'gainsboro' => '#dcdcdc',
			'ghostwhite' => '#f8f8ff',
			'gold' => '#ffd700',
			'goldenrod' => '#daa520',
			'gray' => '#808080',
			'grey' => '#808080',
			'green' => '#008000',
			'greenyellow' => '#adff2f',
			'honeydew' => '#f0fff0',
			'hotpink' => '#ff69b4',
			'indianred' => '#cd5c5c',
			'indigo' => '#4b0082',
			'ivory' => '#fffff0',
			'khaki' => '#f0e68c',
			'lavender' => '#e6e6fa',
			'lavenderblush' => '#fff0f5',
			'lawngreen' => '#7cfc00',
			'lemonchiffon' => '#fffacd',
			'lightblue' => '#add8e6',
			'lightcoral' => '#f08080',
			'lightcyan' => '#e0ffff',
			'lightgoldenrodyellow' => '#fafad2',
			'lightgray' => '#d3d3d3',
			'lightgrey' => '#d3d3d3',
			'lightgreen' => '#90ee90',
			'lightpink' => '#ffb6c1',
			'lightsalmon' => '#ffa07a',
			'lightseagreen' => '#20b2aa',
			'lightskyblue' => '#87cefa',
			'lightslategray' => '#778899',
			'lightslategrey' => '#778899',
			'lightsteelblue' => '#b0c4de',
			'lightyellow' => '#ffffe0',
			'lime' => '#00ff00',
			'limegreen' => '#32cd32',
			'linen' => '#faf0e6',
			'magenta' => '#ff00ff',
			'maroon' => '#800000',
			'mediumaquamarine' => '#66cdaa',
			'mediumblue' => '#0000cd',
			'mediumorchid' => '#ba55d3',
			'mediumpurple' => '#9370db',
			'mediumseagreen' => '#3cb371',
			'mediumslateblue' => '#7b68ee',
			'mediumspringgreen' => '#00fa9a',
			'mediumturquoise' => '#48d1cc',
			'mediumvioletred' => '#c71585',
			'midnightblue' => '#191970',
			'mintcream' => '#f5fffa',
			'mistyrose' => '#ffe4e1',
			'moccasin' => '#ffe4b5',
			'navajowhite' => '#ffdead',
			'navy' => '#000080',
			'oldlace' => '#fdf5e6',
			'olive' => '#808000',
			'olivedrab' => '#6b8e23',
			'orange' => '#ffa500',
			'orangered' => '#ff4500',
			'orchid' => '#da70d6',
			'palegoldenrod' => '#eee8aa',
			'palegreen' => '#98fb98',
			'paleturquoise' => '#afeeee',
			'palevioletred' => '#db7093',
			'papayawhip' => '#ffefd5',
			'peachpuff' => '#ffdab9',
			'peru' => '#cd853f',
			'pink' => '#ffc0cb',
			'plum' => '#dda0dd',
			'powderblue' => '#b0e0e6',
			'purple' => '#800080',
			'red' => '#ff0000',
			'rosybrown' => '#bc8f8f',
			'royalblue' => '#4169e1',
			'saddlebrown' => '#8b4513',
			'salmon' => '#fa8072',
			'sandybrown' => '#f4a460',
			'seagreen' => '#2e8b57',
			'seashell' => '#fff5ee',
			'sienna' => '#a0522d',
			'silver' => '#c0c0c0',
			'skyblue' => '#87ceeb',
			'slateblue' => '#6a5acd',
			'slategray' => '#708090',
			'slategrey' => '#708090',
			'snow' => '#fffafa',
			'springgreen' => '#00ff7f',
			'steelblue' => '#4682b4',
			'tan' => '#d2b48c',
			'teal' => '#008080',
			'thistle' => '#d8bfd8',
			'tomato' => '#ff6347',
			'turquoise' => '#40e0d0',
			'violet' => '#ee82ee',
			'wheat' => '#f5deb3',
			'white' => '#ffffff',
			'whitesmoke' => '#f5f5f5',
			'yellow' => '#ffff00',
			'yellowgreen' => '#9acd32',
		];
		return preg_replace_callback(
			"/(<font\s+color\s*=\s*)(['\"]?)(.+?)\\2>/s",
			static function (array $matches) use ($namesToHex): string {
				if (isset($namesToHex[$matches[3]])) {
					return $matches[1].
						$matches[2].
						$namesToHex[$matches[3]].
						$matches[2].
						'>';
				}
				return $matches[0];
			},
			$text
		);
	}

	public function formatMsg(string $message): string {
		$array = [
			'<myname>' => $this->config->main->character,
			'<myguild>' => $this->config->general->orgName,
			'<tab>' => '    ',
			'<symbol>' => '',
			'<center>' => '',
			'</center>' => '',
			'<u>' => "\e[4m",
			'</u>' => "\e[24m",
			'<i>' => "\e[3m",
			'</i>' => "\e[23m",
			'<br>' => "\n",
			'<br/>' => "\n",
			'<br />' => "\n",
		];

		$message = preg_replace_callback(
			'/<black>(.*?)<end>/',
			static function (array $matches): string {
				if (function_exists('mb_strlen')) {
					return str_repeat(' ', mb_strlen($matches[1]));
				}
				return str_repeat(' ', strlen($matches[1]));
			},
			$message
		);
		$message = str_ireplace(array_keys($array), array_values($array), $message);
		$message = Safe::pregReplace("/<a\s+href=['\"]?user:\/\/[^'\">]+['\"]?\s*>(.*?)<\/a>/s", '<link>$1</link>', $message);
		$message = Safe::pregReplace("/<a\s+href=['\"]?skillid:\/\/\d+['\"]?\s*>(.*?)<\/a>/s", '[skill:<link>$1</link>]', $message);
		$message = Safe::pregReplace("/<a\s+href=['\"]chatcmd:\/\/\/(.*?)['\"]\s*>(.*?)<\/a>/s", '<link>$2</link>', $message);
		$message = Safe::pregReplace("/<a\s+href=['\"]?itemref:\/\/\d+\/\d+\/\d+['\"]?\s*>(.*?)<\/a>/s", '[item:<link>$1</link>]', $message);
		$message = Safe::pregReplace("/<a\s+href=['\"]?itemid:\/\/53019\/\d+['\"]?\s*>(.*?)<\/a>/s", '[nano:<link>$1</link>]', $message);
		$message = Safe::pregReplace("/<p\s*>/is", "\n", $message);
		$message = Safe::pregReplace("/<\/p\s*>/is", '', $message);
		$message = Safe::pregReplace("/\n<img\s+src=['\"]?tdb:\/\/id:[A-Z0-9_]+['\"]?\s*>\n/s", "\n", $message);
		$message = Safe::pregReplace("/\n<img\s+src=['\"]?rdb:\/\/\d+['\"]?\s*>\n/s", "\n", $message);
		$message = Safe::pregReplace("/<img\s+src=['\"]?tdb:\/\/id:[A-Z0-9_]+['\"]?\s*>/s", '', $message);
		$message = Safe::pregReplace("/<img\s+src=['\"]?rdb:\/\/\d+['\"]?\s*>/s", '', $message);
		$message = Safe::pregReplace("/\n\[item:<link><\/link>]\n/s", "\n", $message);
		$message = str_replace("\n", "\n ", $this->handleColors($message, true));
		$parts = [];
		$message = html_entity_decode(
			preg_replace_callback(
				"/<a\s+href\s*=\s*([\"'])text:\/\/(.+?)\\1\s*>(.*?)<\/a>/s",
				function (array $matches) use (&$parts): string {
					$parts[] = html_entity_decode($this->handleColors($matches[2], true), \ENT_QUOTES);
					return $this->handleColors("<link>{$matches[3]}</link>", false);
				},
				$message
			),
			\ENT_QUOTES
		);
		if (count($parts)) {
			$message .= "\n\n" . implode("\n" . str_repeat('-', 75) . "\n", $parts);
		}

		return $message;
	}

	public function handleColors(string $text, bool $clearEOL): string {
		if (!$this->consoleController->consoleColor) {
			return $this->parseBasicAnsi($text);
		}
		$text = $this->parseAnsiColors($text);
		$stack = [];
		$text = preg_replace_callback(
			"/<(\/?font.*?)>/",
			function (array $matches) use (&$stack): string {
				$matches[1] = strtolower($matches[1]);
				if (substr($matches[1], 0, 1) === '/') {
					array_pop($stack);

					/** @psalm-suppress InvalidArrayOffset */
					$currentTag = $stack[count($stack)-1] ?? null;
					if ($currentTag === null) {
						return '';
					}
					$matches[1] = $currentTag;
				} else {
					$stack []= $matches[1];
				}
				if (count($colMatch = Safe::pregMatch("/font\s+color\s*=\s*[\"']?#(.{6})[\"']?/s", $matches[1]))) {
					return $this->fgHexToAnsi($colMatch[1]);
				}
				return $matches[0];
			},
			$text
		);
		if ($this->consoleController->consoleBGColor) {
			$text = $this->bgHexToAnsi('222222') . $text;
		}
		$text = str_replace("\r\n", "\n", $text);
		if ($clearEOL) {
			return str_replace("\n", "\e[K\n", $text) . "\e[K\e[0m";
		}
		return $text;
	}

	protected function parseBasicAnsi(string $text): string {
		$array = [
			'<header>' => "\e[1;4m",
			'<header2>' => "\e[4m",
			'<highlight>' => "\e[1m",
			'<link>' => "\e[4m",
			'</link>' => "\e[24m",
			'</font>' => '',
			'<black>' => '',
			'<white>' => '',
			'<yellow>' => '',
			'<blue>' => '',
			'<on>' => '',
			'<off>' => '',
			'<green>' => '',
			'<red>' => '',
			'<orange>' => '',
			'<grey>' => '',
			'<cyan>' => '',
			'<violet>' => '',

			'<neutral>' => '',
			'<omni>' => '',
			'<clan>' => '',
			'<unknown>' => '',
			'<end>' => "\e[22;24m",
		];
		$text = str_ireplace(array_keys($array), array_values($array), $text);
		$text = Safe::pregReplace("/<font\s+color\s*=\s*[\"']?#.{6}[\"']?>/is", '', $text);
		return $text;
	}

	protected function parseAnsiColors(string $text): string {
		$text = $this->replaceColorNamesWithCodes($text);
		$array = [
			'<header>' => str_replace("'", '', $this->colors->defaultHeaderColor),
			'<header2>' => str_replace("'", '', $this->colors->defaultHeader2Color),
			'<highlight>' => str_replace("'", '', $this->colors->defaultHighlightColor),
			'<on>' => str_replace("'", '', $this->colors->defaultEnabledColor),
			'<off>' => str_replace("'", '', $this->colors->defaultDisabledColor),
			'<link>' => "\e[4m<font color=#219CFF>",
			'</link>' => "</font>\e[24m",
			'<black>' => '<font color=#000000>',
			'<white>' => '<font color=#FFFFFF>',
			'<yellow>' => '<font color=#FFFF00>',
			'<blue>' => '<font color=#8CB5FF>',
			'<green>' => '<font color=#00DE42>',
			'<red>' => '<font color=#FF0000>',
			'<orange>' => '<font color=#FCA712>',
			'<grey>' => '<font color=#C3C3C3>',
			'<cyan>' => '<font color=#00FFFF>',
			'<violet>' => '<font color=#8F00FF>',

			'<neutral>' => $this->colors->defaultNeutColor,
			'<omni>' => $this->colors->defaultOmniColor,
			'<clan>' => $this->colors->defaultClanColor,
			'<unknown>' => $this->colors->defaultUnknownColor,

			'<end>' => '</font>',
		];
		$defaultColor = $this->colors->defaultPrivColor;
		$text = $defaultColor . str_ireplace(array_keys($array), array_values($array), $text);
		return $text;
	}

	protected function fgHexToAnsi(string $hexColor): string {
		$codes = array_map('hexdec', str_split($hexColor, 2));
		return "\e[38;2;" . implode(';', $codes) . 'm';
	}

	protected function bgHexToAnsi(string $hexColor): string {
		$codes = array_map('hexdec', str_split($hexColor, 2));
		return "\e[48;2;" . implode(';', $codes) . 'm';
	}
}
