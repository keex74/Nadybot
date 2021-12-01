<?php declare(strict_types=1);

namespace Nadybot\Core\Channels;

use Nadybot\Core\AccessManager;
use Nadybot\Core\MessageHub;
use Nadybot\Core\MessageReceiver;
use Nadybot\Core\Modules\DISCORD\DiscordAPIClient;
use Nadybot\Core\Modules\DISCORD\DiscordController;
use Nadybot\Core\Nadybot;
use Nadybot\Core\Routing\Events\Online;
use Nadybot\Core\Routing\RoutableEvent;
use Nadybot\Core\Routing\Source;
use Nadybot\Core\SettingManager;
use Nadybot\Core\Text;

class DiscordMsg implements MessageReceiver {
	/** @Inject */
	public Nadybot $chatBot;

	/** @Inject */
	public DiscordAPIClient $discordAPIClient;

	/** @Inject */
	public MessageHub $messageHub;

	/** @Inject */
	public DiscordController $discordController;

	/** @Inject */
	public AccessManager $accessManager;

	/** @Inject */
	public SettingManager $settingManager;

	/** @Inject */
	public Text $text;

	protected string $channel;
	protected string $id;

	public function getChannelName(): string {
		return Source::DISCORD_MSG . "(*)";
	}

	public function receive(RoutableEvent $event, string $destination): bool {
		$renderPath = true;
		if ($event->getType() !== $event::TYPE_MESSAGE) {
			if (!is_object($event->data) || !is_string($event->data->message??null)) {
				return false;
			}
			$msg = $event->data->message;
			$renderPath = $event->data->renderPath;
			if (isset($msg) && $event->data->type === Online::TYPE) {
				$msg = $this->text->removePopups($msg);
			}
		} else {
			$msg = $event->getData();
		}
		$pathText = "";
		if ($renderPath) {
			$pathText = $this->messageHub->renderPath($event, $this->getChannelName());
		}
		if (isset($event->char)) {
			$pathText = preg_replace("/<a\s[^>]*href=['\"]?user.*?>(.+)<\/a>/s", '<highlight>$1<end>', $pathText);
			$pathText = preg_replace("/(\s)([^:\s]+): $/s", '$1<highlight>$2<end>: ', $pathText);
		}
		$message = $pathText.$msg;
		$discordMsg = $this->discordController->formatMessage($message);

		if (isset($event->char)) {
			$minRankForMentions = $this->settingManager->getString('discord_relay_mention_rank') ?? "superadmin";
			$sendersRank = $this->accessManager->getAccessLevelForCharacter($event->char->name);
			if ($this->accessManager->compareAccessLevels($sendersRank, $minRankForMentions) < 0) {
				$discordMsg->allowed_mentions = (object)[
					"parse" => ["users"]
				];
			}
		}

		//Relay the message to the discord channel
		if (preg_match("/^\d+$/", $destination)) {
			$this->discordAPIClient->queueToChannel($destination, $discordMsg->toJSON());
		} else {
			$this->discordAPIClient->sendToUser($destination, $discordMsg);
		}
		return true;
	}
}