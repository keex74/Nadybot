<?php declare(strict_types=1);

namespace Nadybot\Modules\PRIVATE_CHANNEL_MODULE\Migrations;

use Nadybot\Core\{
	Attributes as NCA,
	Config\BotConfig,
	DB,
	DBSchema\Route,
	DBSchema\RouteModifier,
	DBSchema\RouteModifierArgument,
	DBSchema\Setting,
	MessageHub,
	Routing\Source,
	SchemaMigration,
	SettingManager,
};
use Psr\Log\LoggerInterface;

#[NCA\Migration(order: 20_210_812_052_246)]
class MoveSettingsToRoutes implements SchemaMigration {
	#[NCA\Inject]
	private BotConfig $config;

	public function migrate(LoggerInterface $logger, DB $db): void {
		$guestRelay = $this->getSetting($db, 'guest_relay');
		if (isset($guestRelay) && $guestRelay->value !== '1') {
			return;
		}
		$relayCommands = $this->getSetting($db, 'guest_relay_commands');
		$ignoreSenders = $this->getSetting($db, 'guest_relay_ignore');
		$relayFilter = $this->getSetting($db, 'guest_relay_filter');

		$unfiltered = (!isset($ignoreSenders) || !strlen($ignoreSenders->value??''))
			&& (!isset($relayFilter) || !strlen($relayFilter->value??''));

		$route = new Route(
			source: Source::PRIV . "({$this->config->main->character})",
			destination: Source::ORG,
			two_way: $unfiltered,
		);
		$route->id = $db->insert(MessageHub::DB_TABLE_ROUTES, $route);
		$this->addCommandFilter($db, $relayCommands, $route->id);
		if ($unfiltered) {
			return;
		}
		$route = new Route(
			source: Source::ORG,
			destination: Source::PRIV . "({$this->config->main->character})",
			two_way: false,
		);
		$route->id = $db->insert(MessageHub::DB_TABLE_ROUTES, $route);
		$this->addCommandFilter($db, $relayCommands, $route->id);

		if (isset($ignoreSenders) && strlen($ignoreSenders->value??'') > 0) {
			$toIgnore = explode(',', $ignoreSenders->value??'');
			$this->ignoreSenders($db, $route->id, ...$toIgnore);
		}

		if (isset($relayFilter) && strlen($relayFilter->value??'') > 0) {
			$this->addRegExpFilter($db, $route->id, $relayFilter->value??'');
		}
	}

	protected function getSetting(DB $db, string $name): ?Setting {
		return $db->table(SettingManager::DB_TABLE)
			->where('name', $name)
			->asObj(Setting::class)
			->first();
	}

	protected function addCommandFilter(DB $db, ?Setting $relayCommands, int $routeId): void {
		if (!isset($relayCommands) || $relayCommands->value === '1') {
			return;
		}
		$mod = new RouteModifier(
			modifier: 'if-not-command',
			route_id: $routeId,
		);
		$mod->id = $db->insert(MessageHub::DB_TABLE_ROUTE_MODIFIER, $mod);
	}

	protected function ignoreSenders(DB $db, int $routeId, string ...$senders): void {
		foreach ($senders as $sender) {
			$mod = new RouteModifier(
				modifier: 'if-not-by',
				route_id: $routeId,
			);
			$mod->id = $db->insert(MessageHub::DB_TABLE_ROUTE_MODIFIER, $mod);

			$arg = new RouteModifierArgument(
				name: 'sender',
				value: $sender,
				route_modifier_id: $mod->id,
			);

			$mod->id = $db->insert(MessageHub::DB_TABLE_ROUTE_MODIFIER_ARGUMENT, $arg);
		}
	}

	protected function addRegExpFilter(DB $db, int $routeId, string $filter): void {
		$mod = new RouteModifier(
			modifier: 'if-matches',
			route_id: $routeId,
		);
		$mod->id = $db->insert(MessageHub::DB_TABLE_ROUTE_MODIFIER, $mod);

		$arg = new RouteModifierArgument(
			name: 'text',
			value: $filter,
			route_modifier_id: $mod->id,
		);

		$mod->id = $db->insert(MessageHub::DB_TABLE_ROUTE_MODIFIER_ARGUMENT, $arg);

		$arg = new RouteModifierArgument(
			name: 'regexp',
			value: 'true',
			route_modifier_id: $mod->id,
		);

		$mod->id = $db->insert(MessageHub::DB_TABLE_ROUTE_MODIFIER_ARGUMENT, $arg);
	}
}
