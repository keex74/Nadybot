<?php declare(strict_types=1);

namespace Nadybot\Modules\WEBSERVER_MODULE;

use function Safe\json_encode;
use Exception;
use Nadybot\Modules\WEBSERVER_MODULE\Interfaces\{GaugeProvider, ValueProvider};

class Dataset {
	/** @var list<string> */
	private array $tags = [];

	/** @var list<ValueProvider> */
	private array $providers = [];

	public function __construct(
		private string $name,
		string ...$tags
	) {
		sort($tags);
		$this->tags = $tags;
	}

	public function registerProvider(ValueProvider $provider): void {
		$tags = $provider->getTags();
		$tagKeys = array_keys($tags);
		sort($tagKeys);
		if ($tagKeys !== $this->tags) {
			throw new Exception('Incompatible tag-sets provided');
		}
		$this->providers []= $provider;
	}

	/** @return list<string> */
	public function getValues(): array {
		if (!count($this->providers)) {
			return [];
		}
		$type = ($this->providers[0] instanceof GaugeProvider) ? 'gauge' : 'counter';
		$result = ["# TYPE {$this->name} {$type}"];
		foreach ($this->providers as $provider) {
			$line = $this->name;
			$tags = $provider->getTags();
			$attrs = implode(
				',',
				array_map(
					static function (string $tag) use ($tags): string {
						return "{$tag}=" . json_encode($tags[$tag]);
					},
					$this->tags
				)
			);
			if (count($tags)) {
				$line .= '{' . $attrs . '}';
			}
			$line .= ' ' . (string)$provider->getValue();
			$result []= $line;
		}
		return $result;
	}
}
