<?php declare(strict_types=1);

namespace Nadybot\Modules\PVP_MODULE\Handlers;

use Nadybot\Core\ParamClass\PTowerSite;
use Nadybot\Core\{Playfield, UserException};
use Nadybot\Modules\PVP_MODULE\Attributes\Argument;
use Nadybot\Modules\PVP_MODULE\FeedMessage\SiteUpdate;

#[Argument(
	names: ['site'],
	description: 'Only match the given site. A site has a playfield name, and '.
		'a site-number, e.g. "PW 12" or PW12.',
	type: 'site-name with, or without spaces',
	examples: ['AEG3', '"GOF 6"'],
)]
class Site extends Base {
	private ?Playfield $pf=null;
	private ?int $siteId=null;

	public function matches(SiteUpdate $site): bool {
		if (!isset($this->pf) || !isset($this->siteId)) {
			return false;
		}
		return $this->pf === $site->playfield && $this->siteId === $site->site_id;
	}

	protected function validateValue(): void {
		if (!PTowerSite::matches($this->value)) {
			throw new UserException("'<highlight>{$this->value}<end>' is not a tower site format");
		}
		$site = new PTowerSite($this->value);

		$this->pf = Playfield::tryByName($site->pf);
		if (!isset($this->pf)) {
			throw new UserException("'<highlight>{$this->value}<end>' is not a known playfield.");
		}
		$this->siteId = $site->site;
	}
}
