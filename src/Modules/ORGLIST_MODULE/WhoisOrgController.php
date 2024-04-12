<?php declare(strict_types=1);

namespace Nadybot\Modules\ORGLIST_MODULE;

use Nadybot\Core\{
	Attributes as NCA,
	CmdContext,
	Config\BotConfig,
	ModuleInstance,
	Modules\PLAYER_LOOKUP\Guild,
	Modules\PLAYER_LOOKUP\GuildManager,
	Modules\PLAYER_LOOKUP\PlayerManager,
	ParamClass\PCharacter,
	Text,
	Types\Profession,
};

/**
 * @author Tyrence (RK2)
 */
#[
	NCA\Instance,
	NCA\DefineCommand(
		command: 'whoisorg',
		accessLevel: 'guest',
		description: 'Display org info',
	)
]
class WhoisOrgController extends ModuleInstance {
	#[NCA\Inject]
	private BotConfig $config;

	#[NCA\Inject]
	private Text $text;

	#[NCA\Inject]
	private PlayerManager $playerManager;

	#[NCA\Inject]
	private GuildManager $guildManager;

	/** Show information about an organization */
	#[NCA\HandlesCommand('whoisorg')]
	public function whoisorgIdCommand(CmdContext $context, int $orgId, ?int $dimension): void {
		$dimension ??= $this->config->main->dimension;

		$guild = $this->guildManager->byId($orgId, $dimension);
		$msg = $this->getOrgInfo($guild);
		$context->reply($msg);
	}

	/** Show information about a character's org */
	#[NCA\HandlesCommand('whoisorg')]
	public function whoisorgCommand(CmdContext $context, PCharacter $char, ?int $dimension): void {
		$dimension ??= $this->config->main->dimension;
		$name = $char();

		$whois = $this->playerManager->byName($name, $dimension);
		if ($whois === null) {
			$msg = "Could not find character info for {$name}.";
			$context->reply($msg);
			return;
		} elseif (!isset($whois->guild_id) || $whois->guild_id === 0) {
			$msg = "Character <highlight>{$name}<end> does not seem to be in an org.";
			$context->reply($msg);
			return;
		}

		$guild = $this->guildManager->byId($whois->guild_id, $dimension);
		$msg = $this->getOrgInfo($guild);
		$context->reply($msg);
	}

	/** @return string|string[] */
	public function getOrgInfo(?Guild $org): string|array {
		if ($org === null) {
			$msg = 'Error in getting the org info. '.
				"Either the org does not exist or AO's server ".
				'was too slow to respond.';
			return $msg;
		}
		if (!isset($org->orgname)) {
			$msg = 'This is an illegal org id.';
			return $msg;
		}

		$countProfs = [];
		$minLevel = 220;
		$maxLevel = 1;

		$numMembers = count($org->members);
		$sumLevels = 0;
		$leader = null;
		$faction = '&lt;unknown&gt;';
		foreach ($org->members as $member) {
			if ($member->guild_rank_id === 0) {
				$leader = $member;
				$faction = $leader->faction->value;
			}
			$sumLevels += $member->level??0;

			$minLevel = min($member->level, $minLevel);
			$maxLevel = max($member->level, $maxLevel);

			if (isset($member->profession)) {
				$countProfs[$member->profession->value] ??= 0;
				$countProfs[$member->profession->value]++;
			}
		}
		$averageLevel = round($sumLevels/$numMembers);

		$link = "<header2>General Info<end>\n";
		$link .= '<tab>Faction: <' . strtolower($faction) . ">{$faction}<end>\n";
		$link .= "<tab>Lowest lvl: <highlight>{$minLevel}<end>\n";
		$link .= "<tab>Highest lvl: <highlight>{$maxLevel}<end>\n";
		$link .= "<tab>Average lvl: <highlight>{$averageLevel}<end>\n\n";

		if (isset($leader)) {
			$link .= "<header2>{$leader->guild_rank}<end>\n";
			$link .= "<tab>Name: <highlight>{$leader->name}<end>\n";
			$link .= "<tab>Profession: {$leader->profession?->inColor()}\n";
			$link .= "<tab>Level: <highlight>{$leader->level}<end>\n";
			$link .= "<tab>Gender: <highlight>{$leader->gender}<end>\n";
			$link .= "<tab>Breed: <highlight>{$leader->breed}<end>\n\n";
		}

		ksort($countProfs);
		$link .= "<header2>Members ({$numMembers})<end>\n";
		foreach ($countProfs as $prof => $profMembers) {
			$profession = Profession::from($prof);

			$link .= '<tab>'.
				Text::alignNumber($profMembers, 3, 'highlight').
				'  ('.
				Text::alignNumber(
					(int)round(($profMembers*100)/$numMembers, 1),
					(count($countProfs) > 1) ? 2 : 3
				).
				"%)  {$profession->toIcon()} {$prof}\n";
		}
		$msg = $this->text->makeBlob("Org Info for {$org->orgname}", $link);

		return $msg;
	}
}
