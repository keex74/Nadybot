<?php declare(strict_types=1);

namespace Nadybot\Modules\SKILLS_MODULE;

use Illuminate\Support\Collection;
use Nadybot\Core\{
	Attributes as NCA,
	CmdContext,
	DB,
	ModuleInstance,
	ParamClass\PItem,
	ParamClass\PNonNumber,
	Text,
	Util,
};
use Nadybot\Modules\ITEMS_MODULE\{
	AODBEntry,
	ItemSearchResult,
	ItemsController,
};

/**
 * @author Tyrence (RK2)
 * @author Nadyita
 */
#[
	NCA\Instance,
	NCA\HasMigrations('Migrations/Weapons'),
	NCA\DefineCommand(
		command: 'aggdef',
		accessLevel: 'guest',
		description: 'Agg/Def: Calculates weapon inits for your Agg/Def bar',
	),
	NCA\DefineCommand(
		command: 'aimshot',
		accessLevel: 'guest',
		description: 'Aim Shot: Calculates Aimed Shot',
		alias: ['as', 'aimedshot'],
	),
	NCA\DefineCommand(
		command: 'nanoinit',
		accessLevel: 'guest',
		description: 'Nanoinit: Calculates Nano Init',
	),
	NCA\DefineCommand(
		command: 'fullauto',
		accessLevel: 'guest',
		description: 'Fullauto: Calculates Full Auto recharge',
	),
	NCA\DefineCommand(
		command: 'burst',
		accessLevel: 'guest',
		description: 'Burst: Calculates Burst',
	),
	NCA\DefineCommand(
		command: 'fling',
		accessLevel: 'guest',
		description: 'Fling: Calculates Fling',
	),
	NCA\DefineCommand(
		command: 'mafist',
		accessLevel: 'guest',
		description: 'MA Fist: Calculates your fist speed',
	),
	NCA\DefineCommand(
		command: 'dimach',
		accessLevel: 'guest',
		description: 'Dimach: Calculates dimach facts',
	),
	NCA\DefineCommand(
		command: 'brawl',
		accessLevel: 'guest',
		description: 'Brawl: Calculates brawl facts',
	),
	NCA\DefineCommand(
		command: 'fastattack',
		accessLevel: 'guest',
		description: 'Fastattack: Calculates Fast Attack recharge',
	),
	NCA\DefineCommand(
		command: 'weapon',
		accessLevel: 'guest',
		description: 'Shows weapon info (skill cap specials recycle and aggdef positions)',
		alias: ['specials', 'inits'],
	)
]
class SkillsController extends ModuleInstance {
	#[NCA\Inject]
	private DB $db;

	#[NCA\Inject]
	private Text $text;

	#[NCA\Inject]
	private ItemsController $itemsController;

	#[NCA\Setup]
	public function setup(): void {
		$this->db->loadCSVFile($this->moduleName, __DIR__ . '/weapon_attributes.csv');
	}

	/** Find out where to set your aggdef slider to be 1/1 */
	#[NCA\HandlesCommand('aggdef')]
	#[NCA\Help\Epilogue(
		"If you are lazy, just use\n".
		"<tab><highlight><symbol>weapon &lt;drop weapon into chat&gt;<end>\n\n".
		"Example:\n".
		"<tab>Your weapon has an attack time of <highlight>1.2<end> seconds and a recharge time of <highlight>1.5<end> seconds.\n".
		"<tab>Your melee/ranged init is <highlight>1200<end>:\n".
		"<tab><a href='chatcmd:///tell <myname> <symbol>aggdef 1.2 1.5 1200'>/tell <myname> <symbol>aggdef 1.2 1.5 1200</a>"
	)]
	public function aggdefCommand(CmdContext $context, float $attackTime, float $rechargeTime, int $initValue): void {
		$blob = $this->getAggDefOutput($attackTime, $rechargeTime, $initValue);

		$msg = $this->text->makeBlob('Agg/Def Results', $blob);
		$context->reply($msg);
	}

	public function getAggDefOutput(float $AttTim, float $RechT, int $InitS): string {
		if ($InitS < 1_200) {
			$AttCalc	= round(((($AttTim - ($InitS / 600)) - 1)/0.02) + 87.5, 2);
			$RechCalc	= round(((($RechT - ($InitS / 300)) - 1)/0.02) + 87.5, 2);
		} else {
			$InitSk = $InitS - 1_200;
			$AttCalc = round(((($AttTim - (1_200/600) - ($InitSk / 600 / 3)) - 1)/0.02) + 87.5, 2);
			$RechCalc = round(((($RechT - (1_200/300) - ($InitSk / 300 / 3)) - 1)/0.02) + 87.5, 2);
		}

		if ($AttCalc < $RechCalc) {
			$InitResult = $RechCalc;
		} else {
			$InitResult = $AttCalc;
		}
		if ($InitResult < 0) {
			$InitResult = 0;
		} elseif ($InitResult > 100) {
			$InitResult = 100;
		}

		$initsFullAgg = $this->getInitsNeededFullAgg($AttTim, $RechT);
		$initsNeutral = $this->getInitsNeededNeutral($AttTim, $RechT);
		$initsFullDef = $this->getInitsNeededFullDef($AttTim, $RechT);

		$blob = 'Attack:    <highlight>'. $AttTim ." <end>second(s).\n";
		$blob .= 'Recharge: <highlight>'. $RechT ." <end>second(s).\n";
		$blob .= 'Init Skill:   <highlight>'. $InitS ."<end>.\n\n";
		$blob .= 'You must set your AGG bar at <highlight>'. (int)round($InitResult, 0) .'% ('. (int)round($InitResult*8/100, 0) .") <end>to wield your weapon at <highlight>1/1<end>.\n";
		$blob .= '(<a href=skillid://51>Agg/def-Slider</a> should read <highlight>'.(int)round($InitResult*2-100, 0)."<end>).\n\n";
		$blob .= "Init needed for max speed at:\n";
		$blob .= '  Full Agg (100%): <highlight>'. $initsFullAgg ." <end>inits\n";
		$blob .= '  Neutral (87.5%): <highlight>'. $initsNeutral ." <end>inits\n";
		$blob .= '  Full Def (0%):     <highlight>'. $initsFullDef ." <end>inits\n\n";
		$blob .= "<highlight>{$initsFullDef}<end> DEF ";
		$blob .= $this->getAggdefBar($InitResult);
		$blob .= " AGG <highlight>{$initsFullAgg}<end>\n";
		$blob .= "                         You: <highlight>{$InitS}<end>\n\n";
		$blob .= 'Note that at the neutral position (87.5%), your attack and recharge time will match that of the weapon you are using.';
		$blob .= "\n\nBased upon a RINGBOT module made by NoGoal(RK2)\n";
		$blob .= 'Modified for Budabot by Healnjoo and Nadyita';

		return $blob;
	}

	public function getInitsForPercent(float $percent, float $attackTime, float $rechargeTime): int {
		$initAttack   = ($attackTime   - ($percent - 87.5) / 50 - 1) * 600;
		$initRecharge = ($rechargeTime - ($percent - 87.5) / 50 - 1) * 300;

		if ($initAttack > 1_200) {
			$initAttack = ($attackTime - ($percent - 37.5) / 50 - 2) * 1_800 + 1_200;
		}
		if ($initRecharge > 1_200) {
			$initRecharge = ($rechargeTime - ($percent - 37.5) / 50 - 4) * 900 + 1_200;
		}
		return (int)round(max(max($initAttack, $initRecharge), 0), 0);
	}

	public function getInitsNeededFullAgg(float $attackTime, float $rechargeTime): int {
		return $this->getInitsForPercent(100, $attackTime, $rechargeTime);
	}

	public function getInitsNeededNeutral(float $attackTime, float $rechargeTime): int {
		return $this->getInitsForPercent(87.5, $attackTime, $rechargeTime);
	}

	public function getInitsNeededFullDef(float $attackTime, float $rechargeTime): int {
		return $this->getInitsForPercent(0, $attackTime, $rechargeTime);
	}

	/** Calculate your aimed shot recharge */
	#[NCA\HandlesCommand('aimshot')]
	#[NCA\Help\Epilogue(
		"If you are lazy, just use\n".
		"<tab><highlight><symbol>weapon &lt;drop weapon into chat&gt;<end>\n\n".
		"Example:\n".
		"<tab>Your weapon has an attack time of <highlight>1.2<end> seconds and a recharge time of <highlight>1.5<end> seconds.\n".
		"<tab>You have <highlight>1200<end> Aimed Shot skill:\n".
		"<tab><a href='chatcmd:///tell <myname> <symbol>aimshot 1.2 1.5 1200'>/tell <myname> <symbol>aimshot 1.2 1.5 1200</a>"
	)]
	public function aimshotCommand(CmdContext $context, float $attackTime, float $rechargeTime, int $aimedShot): void {
		[$cap, $ASCap] = $this->capAimedShot($attackTime, $rechargeTime);

		$ASRecharge	= (int)ceil(($rechargeTime * 40) - ($aimedShot * 3 / 100) + $attackTime - 1);
		if ($ASRecharge < $cap) {
			$ASRecharge = $cap;
		}
		$ASMultiplier	= (int)round($aimedShot / 95, 0);

		$blob = "Attack:       <highlight>{$attackTime}<end> second(s)\n";
		$blob .= "Recharge:    <highlight>{$rechargeTime}<end> second(s)\n";
		$blob .= "Aimed Shot: <highlight>{$aimedShot}<end>\n\n";
		$blob .= "Aimed Shot Multiplier: <highlight>1-{$ASMultiplier}x<end>\n";
		$blob .= "Aimed Shot Recharge: <highlight>{$ASRecharge}<end> seconds\n";
		$blob .= "With your weapon, your Aimed Shot recharge will cap at <highlight>{$cap}<end>s.\n";
		$blob .= "You need <highlight>{$ASCap}<end> Aimed Shot skill to cap your recharge.";

		$msg = $this->text->makeBlob('Aimed Shot Results', $blob);
		$context->reply($msg);
	}

	/** Calculate your brawl recharge and damage */
	#[NCA\HandlesCommand('brawl')]
	public function brawlCommand(CmdContext $context, int $brawlSkill): void {
		$skillList  = [1, 1_000, 1_001, 2_000, 2_001, 3_000];
		$minList	= [1,  100,  101,  170,  171,  235];
		$maxList	= [2,  500,  501,  850,  851, 1_145];
		$critList	= [3,  500,  501,  600,  601,  725];

		if ($brawlSkill < 1_001) {
			$i = 0;
		} elseif ($brawlSkill < 2_001) {
			$i = 2;
		} else {
			$i = 4;
		}

		$minDamage  = Util::interpolate($skillList[$i], $skillList[($i+1)], $minList[$i], $minList[($i+1)], $brawlSkill);
		$maxDamage  = Util::interpolate($skillList[$i], $skillList[($i+1)], $maxList[$i], $maxList[($i+1)], $brawlSkill);
		$critBonus = Util::interpolate($skillList[$i], $skillList[($i+1)], $critList[$i], $critList[($i+1)], $brawlSkill);
		$stunChance = '<highlight>20<end>%';
		if ($brawlSkill < 1_000) {
			$stunChance = '<highlight>10<end>%, (will become 20% above 1000 brawl skill)';
		}
		$stunDuration = '<highlight>4<end>s';
		if ($brawlSkill < 2_001) {
			$stunDuration = '<highlight>3<end>s, (will become 4s above 2001 brawl skill)';
		}

		$blob = 'Brawl Skill: <highlight>'.$brawlSkill."<end>\n";
		$blob .= "Brawl recharge: <highlight>15<end> seconds (constant)\n";
		$blob .= 'Damage: <highlight>'.$minDamage.'<end>-<highlight>'.$maxDamage.'<end> (<highlight>'.$critBonus."<end>)\n";
		$blob .= 'Stun chance: '.$stunChance."\n";
		$blob .= 'Stun duration: '.$stunDuration."\n";
		$blob .= "\n\nby Imoutochan, RK1";

		$msg = $this->text->makeBlob('Brawl Results', $blob);
		$context->reply($msg);
	}

	/** Calculate your burst recharge */
	#[NCA\HandlesCommand('burst')]
	#[NCA\Help\Epilogue(
		"If you are lazy, just use\n".
		"<tab><highlight><symbol>weapon &lt;drop weapon into chat&gt;<end>\n\n".
		"Example:\n".
		"<tab>Your weapon has an attack time of <highlight>1.2<end> seconds and a recharge time of <highlight>1.5<end> seconds.\n".
		"<tab>Your weapon has a Burst Delay of <highlight>1600<end>.\n".
		"<tab>You have <highlight>900<end> Burst skill.\n".
		"<tab><a href='chatcmd:///tell <myname> burst 1.2 1.5 1600 900'><symbol>burst 1.2 1.5 1600 900</a>\n\n".
		'<i>Your Burst Delay value (1600) can be found by using the '.
		"<a href='chatcmd:///tell <myname> help specials'>specials</a> command or on ".
		"<a href='chatcmd:///start http://www.auno.org'>auno.org</a> as Burst Cycle.</i>"
	)]
	public function burstCommand(CmdContext $context, float $attackTime, float $rechargeTime, int $burstDelay, int $burstSkill): void {
		[$burstWeaponCap, $burstSkillCap] = $this->capBurst($attackTime, $rechargeTime, $burstDelay);

		$burstRecharge = (int)floor(($rechargeTime * 20) + ($burstDelay / 100) - ($burstSkill / 25) + $attackTime);
		$burstRecharge = max($burstRecharge, $burstWeaponCap);

		$blob = "Attack:       <highlight>{$attackTime}<end> second(s)\n";
		$blob .= "Recharge:    <highlight>{$rechargeTime}<end> second(s)\n";
		$blob .= "Burst Delay: <highlight>{$burstDelay}<end>\n";
		$blob .= "Burst Skill:   <highlight>{$burstSkill}<end>\n\n";
		$blob .= "Your burst recharge: <highlight>{$burstRecharge}<end>s\n\n";
		$blob .= "You need <highlight>{$burstSkillCap}<end> ".
			'burst skill to cap your recharge at the minimum of '.
			"<highlight>{$burstWeaponCap}<end>s.";

		$msg = $this->text->makeBlob('Burst Results', $blob);
		$context->reply($msg);
	}

	/** Calculate your dimach recharge and damage */
	#[NCA\HandlesCommand('dimach')]
	public function dimachCommand(CmdContext $context, int $dimachSkill): void {
		$skillList	        = [1, 1_000, 1_001, 2_000, 2_001, 3_000];
		$generalDamageList	= [1, 2_000, 2_001, 2_500, 2_501, 2_850];
		$maRechargeList  	= [1_800, 1_800, 1_188,  600,  600,  300];
		$maDamageList	    = [1, 2_000, 2_001, 2_340, 2_341, 2_550];
		$shadeRechargeList  = [300,  300,  300,  300,  240,  200];
		$shadeDamageList	= [1,  920,  921, 1_872, 1_873, 2_750];
		$shadeHPDrainList	= [70,   70,   70,   75,   75,   80];
		$keeperHealList     = [1, 3_000, 3_001, 10_500, 10_501, 15_000];

		if ($dimachSkill < 1_001) {
			$i = 0;
		} elseif ($dimachSkill < 2_001) {
			$i = 2;
		} else {
			$i = 4;
		}

		$blob = "Dimach Skill: <highlight>{$dimachSkill}<end>\n\n";

		$maDamage = Util::interpolate($skillList[$i], $skillList[($i+1)], $maDamageList[$i], $maDamageList[($i+1)], $dimachSkill);
		$maDimachRecharge = Util::interpolate($skillList[$i], $skillList[($i+1)], $maRechargeList[$i], $maRechargeList[($i+1)], $dimachSkill);
		$blob .= "<header2>Martial Artist<end>\n";
		$blob .= "<tab>Damage: <highlight>{$maDamage}<end> (<highlight>1<end>)\n";
		$blob .= '<tab>Recharge <highlight>'.Util::unixtimeToReadable($maDimachRecharge)."<end>\n\n";

		$keeperHeal	= Util::interpolate($skillList[$i], $skillList[($i+1)], $keeperHealList[$i], $keeperHealList[($i+1)], $dimachSkill);
		$blob .= "<header2>Keeper<end>\n";
		$blob .= '<tab>Self heal: <highlight>'.$keeperHeal."<end> HP\n";
		$blob .= "<tab>Recharge: <highlight>5 mins<end> (constant)\n\n";

		$shadeDamage	= Util::interpolate($skillList[$i], $skillList[($i+1)], $shadeDamageList[$i], $shadeDamageList[($i+1)], $dimachSkill);
		$shadeHPDrainPercent  = Util::interpolate($skillList[$i], $skillList[($i+1)], $shadeHPDrainList[$i], $shadeHPDrainList[($i+1)], $dimachSkill);
		$shadeDimacheRecharge = Util::interpolate($skillList[$i], $skillList[($i+1)], $shadeRechargeList[$i], $shadeRechargeList[($i+1)], $dimachSkill);
		$blob .= "<header2>Shade<end>\n";
		$blob .= '<tab>Damage: <highlight>'.$shadeDamage."<end> (<highlight>1<end>)\n";
		$blob .= '<tab>HP drain: <highlight>'.$shadeHPDrainPercent."<end>%\n";
		$blob .= '<tab>Recharge: <highlight>'.Util::unixtimeToReadable($shadeDimacheRecharge)."<end>\n\n";

		$damageOthers = Util::interpolate($skillList[$i], $skillList[($i+1)], $generalDamageList[$i], $generalDamageList[($i+1)], $dimachSkill);
		$blob .= "<header2>All other professions<end>\n";
		$blob .= "<tab>Damage: <highlight>{$damageOthers}<end> (<highlight>1<end>)\n";
		$blob .= "<tab>Recharge: <highlight>30 mins<end> (constant)\n\n";

		$blob .= 'by Imoutochan, RK1';

		$msg = $this->text->makeBlob('Dimach Results', $blob);
		$context->reply($msg);
	}

	/** Calculate your fast attack recharge */
	#[NCA\HandlesCommand('fastattack')]
	#[NCA\Help\Epilogue(
		"If you are lazy, just use\n".
		"<tab><highlight><symbol>weapon &lt;drop weapon into chat&gt;<end>\n\n".
		"Example:\n".
		"<tab>Your weapon has an attack time of <highlight>1.2<end> seconds.\n".
		"<tab>You have <highlight>900<end> Fast Attack skill:\n".
		"<tab><a href='chatcmd:///tell <myname> <symbol>fastattack 1.2 900'>/tell <myname> <symbol>fastattack 1.2 900</a>"
	)]
	public function fastAttackCommand(CmdContext $context, float $attackTime, int $fastAttack): void {
		[$weaponCap, $skillNeededForCap] = $this->capFastAttack($attackTime);

		$recharge = (int)round(($attackTime * 16) - ($fastAttack / 100));

		if ($recharge < $weaponCap) {
			$recharge = $weaponCap;
		} else {
			$recharge = ceil($recharge);
		}

		$blob  = "Attack:           <highlight>{$attackTime}<end>s\n";
		$blob .= "Fast Attack:    <highlight>{$fastAttack}<end>\n";
		$blob .= "Your Recharge: <highlight>{$recharge}<end>s\n\n";
		$blob .= "You need <highlight>{$skillNeededForCap}<end> Fast Attack Skill to cap your fast attack at <highlight>{$weaponCap}<end>s.\n";
		$blob .= 'Every 100 points in Fast Attack skill less than this will increase the recharge by 1s.';

		$msg = $this->text->makeBlob('Fast Attack Results', $blob);
		$context->reply($msg);
	}

	/** Calculate your fling shot recharge */
	#[NCA\HandlesCommand('fling')]
	#[NCA\Help\Epilogue(
		"If you are lazy, just use\n".
		"<tab><highlight><symbol>weapon &lt;drop weapon into chat&gt;<end>\n\n".
		"Example:\n".
		"<tab>Your weapon has an attack of <highlight>1.2<end> seconds.\n".
		"<tab>You have <highlight>900<end> Fling skill.\n".
		"<tab><a href='chatcmd:///tell <myname> <symbol>fling 1.2 900'>/tell <myname> <symbol>fling 1.2 900</a>"
	)]
	public function flingShotCommand(CmdContext $context, float $attackTime, int $flingShot): void {
		[$weaponCap, $skillCap] = $this->capFlingShot($attackTime);

		$recharge =  round(($attackTime * 16) - ($flingShot / 100));

		$recharge = max($weaponCap, $recharge);

		$blob = "Attack:           <highlight>{$attackTime}<end>s\n";
		$blob .= "Fling Shot:       <highlight>{$flingShot}<end>\n";
		$blob .= "Your Recharge: <highlight>{$recharge}<end>s\n\n";
		$blob .= "You need <highlight>{$skillCap}<end> Fling Shot skill to cap your fling at <highlight>{$weaponCap}<end>s.";

		$msg = $this->text->makeBlob('Fling Results', $blob);
		$context->reply($msg);
	}

	/** Calculate your full auto recharge */
	#[NCA\HandlesCommand('fullauto')]
	#[NCA\Help\Epilogue(
		"If you are lazy, just use\n".
		"<tab><highlight><symbol>weapon &lt;drop weapon into chat&gt;<end>\n\n".
		"Example:\n".
		"<tab>Your weapon has an attack and recharge time of <highlight>1<end> second\n".
		"<tab>and a Full Auto recharge value of <highlight>5000<end>.\n".
		"<tab>You have <highlight>1200<end> Full Auto skill:\n".
		"<tab><a href='chatcmd:///tell <myname> fullauto 1 1 5000 1200'><symbol>fullauto 1 1 5000 1200</a>\n\n".
		'<i>Your Full Auto recharge value (5000) can be found by using the '.
		"<a href='chatcmd:///tell <myname> help weapon'>weapon</a> command or on ".
		"<a href='chatcmd:///start http://www.auno.org'>auno.org</a> as FullAuto Cycle.</i>"
	)]
	public function fullAutoCommand(CmdContext $context, float $attackTime, float $rechargeTime, int $faRecharge, int $faSkill): void {
		[$faWeaponCap, $faSkillCap] = $this->capFullAuto($attackTime, $rechargeTime, $faRecharge);

		$myFullAutoRecharge = (int)round(($rechargeTime * 40) + ($faRecharge / 100) - ($faSkill / 25) + round($attackTime - 1));
		$myFullAutoRecharge = max($myFullAutoRecharge, $faWeaponCap);

		$maxBullets = 5 + (int)floor($faSkill / 100);

		$blob = "Weapon Attack: <highlight>{$attackTime}<end>s\n";
		$blob .= "Weapon Recharge: <highlight>{$rechargeTime}<end>s\n";
		$blob .= "Full Auto Recharge value: <highlight>{$faRecharge}<end>\n";
		$blob .= "FA Skill: <highlight>{$faSkill}<end>\n\n";
		$blob .= "Your Full Auto recharge: <highlight>{$myFullAutoRecharge}<end>s\n";
		$blob .= "Your Full Auto can fire a maximum of <highlight>{$maxBullets}<end> bullets.\n";
		$blob .= "Full Auto recharge always caps at <highlight>{$faWeaponCap}<end>s.\n";
		$blob .= "You will need at least <highlight>{$faSkillCap}<end> Full Auto skill to cap your recharge.\n\n";
		$blob .= "From <black>0<end><highlight>0<end><black>K<end><highlight> to 10.0K<end> damage, the bullet damage is unchanged.\n";
		$blob .= "From <highlight>10K to 11.5K<end> damage, each bullet damage is halved.\n";
		$blob .= "From <highlight>11K to 15.0K<end> damage, each bullet damage is halved again.\n";
		$blob .= '<highlight>15K<end> is the damage cap.';

		$msg = $this->text->makeBlob('Full Auto Results', $blob);
		$context->reply($msg);
	}

	/** Calculate the damage of your fist attacks */
	#[NCA\HandlesCommand('mafist')]
	public function maFistCommand(CmdContext $context, int $maSkill): void {
		// MA templates
		$skillList =     [1,    200,   1_000,   1_001,   2_000,   2_001,   3_000];

		$maMinList =     [4,     45,    125,    130,    220,    225,    450];
		$maMaxList =     [8,     75,    400,    405,    830,    831,   1_300];
		$maCritList =    [3,     50,    500,    501,    560,    561,    800];
		$maFistSpeed =   [1.15,    1.2,   1.25,   1.30,   1.35,   1.45,   1.50];
		$maAOID =        [211_352, 211_353, 211_354, 211_357, 211_358, 211_363, 211_364];

		$shadeMinList =  [3,     25,     55,     56,    130, 131,    280];
		$shadeMaxList =  [5,     60,    258,    259,    682, 683,    890];
		$shadeCritList = [3,     50,    250,    251,    275, 276,    300];
		$shadeAOID =     [211_349, 211_350, 211_351, 211_359, 211_360, 211_365, 211_366];

		$otherMinList =  [3,     25,     65,     66,    140,    204,    300];
		$otherMaxList =  [5,     60,    280,    281,    715,    831,    990];
		$otherCritList = [3,     50,    500,    501,    605,    605,    630];
		$otherAOID =     [43_712, 144_745,  43_713, 211_355, 211_356, 211_361, 211_362];

		if ($maSkill < 200) {
			$i = 0;
		} elseif ($maSkill < 1_001) {
			$i = 1;
		} elseif ($maSkill < 2_001) {
			$i = 3;
		} else {
			$i = 5;
		}

		$aoidQL = (((int)ceil($maSkill / 2) - 1) % 500 + 1);

		$fistQL = min(1_500, (int)round($maSkill / 2, 0));
		if ($fistQL <= 200) {
			$speed = 1.25;
		} elseif ($fistQL <= 500) {
			$speed = 1.25 + (0.2 * (($fistQL - 200) / 300));
		} elseif ($fistQL <= 1_000) {
			$speed = 1.45 + (0.2 * (($fistQL - 500) / 500));
		} else {
			$speed = 1.65 + (0.2 * (($fistQL - 1_000) / 500));
		}
		$speed = round($speed, 2);

		$blob = "MA Skill: <highlight>{$maSkill}<end>\n\n";
		$maSkill = min(3_000, $maSkill);

		$min = Util::interpolate($skillList[$i], $skillList[($i + 1)], $maMinList[$i], $maMinList[($i + 1)], $maSkill);
		$max = Util::interpolate($skillList[$i], $skillList[($i + 1)], $maMaxList[$i], $maMaxList[($i + 1)], $maSkill);
		$crit = Util::interpolate($skillList[$i], $skillList[($i + 1)], $maCritList[$i], $maCritList[($i + 1)], $maSkill);
		$maBaseSpeed = (($maSkill - $skillList[$i]) * ($maFistSpeed[($i + 1)] - $maFistSpeed[$i])) / ($skillList[($i + 1)] - $skillList[$i]) + $maFistSpeed[$i]; // @phpstan-ignore-line
		$maFistSpeed = round($maBaseSpeed, 2);
		$dmg = "<highlight>{$min}<end>-<highlight>{$max}<end> (<highlight>{$crit}<end>)";
		$blob .= '<header2>Martial Artist<end> ('.  Text::makeItem($maAOID[$i], $maAOID[$i+1], $aoidQL, 'item') . ")\n";
		$blob .= "<tab>Fist speed:   <highlight>{$maFistSpeed}<end>s/<highlight>{$maFistSpeed}<end>s\n";
		$blob .= "<tab>Fist damage: {$dmg}\n\n";

		$min = Util::interpolate($skillList[$i], $skillList[($i + 1)], $shadeMinList[$i], $shadeMinList[($i + 1)], $maSkill);
		$max = Util::interpolate($skillList[$i], $skillList[($i + 1)], $shadeMaxList[$i], $shadeMaxList[($i + 1)], $maSkill);
		$crit = Util::interpolate($skillList[$i], $skillList[($i + 1)], $shadeCritList[$i], $shadeCritList[($i + 1)], $maSkill);
		$dmg = '<highlight>'.$min.'<end>-<highlight>'.$max.'<end> (<highlight>'.$crit.'<end>)';
		$blob .= '<header2>Shade<end> ('.  Text::makeItem($shadeAOID[$i], $shadeAOID[$i+1], $aoidQL, 'item') . ")\n";
		$blob .= "<tab>Fist speed:   <highlight>{$speed}<end>s/<highlight>{$speed}<end>s\n";
		$blob .= "<tab>Fist damage: {$dmg}\n\n";

		$min = Util::interpolate($skillList[$i], $skillList[($i + 1)], $otherMinList[$i], $otherMinList[($i + 1)], $maSkill);
		$max = Util::interpolate($skillList[$i], $skillList[($i + 1)], $otherMaxList[$i], $otherMaxList[($i + 1)], $maSkill);
		$crit = Util::interpolate($skillList[$i], $skillList[($i + 1)], $otherCritList[$i], $otherCritList[($i + 1)], $maSkill);
		$dmg = '<highlight>'.$min.'<end>-<highlight>'.$max.'<end> (<highlight>'.$crit.'<end>)';
		$blob .= '<header2>All other professions<end> ('.  Text::makeItem($otherAOID[$i], $otherAOID[$i+1], $aoidQL, 'item') . ")\n";
		$blob .= "<tab>Fist speed:   <highlight>{$speed}<end>s/<highlight>{$speed}<end>s\n";
		$blob .= "<tab>Fist damage: {$dmg}\n\n";

		$msg = $this->text->makeBlob('Martial Arts Results', $blob);
		$context->reply($msg);
	}

	/** Calculate the effective casting time of a nano */
	#[NCA\HandlesCommand('nanoinit')]
	#[NCA\Help\Example(
		command: '<symbol>nanoinit 3.72 400',
		description: 'Casting Volcanic Eruption with 400 nano init'
	)]
	public function nanoInitCommand(CmdContext $context, float $castingTime, int $initSkill): void {
		$castingTimeReduction = $this->calcAttackTimeReduction($initSkill);
		$effectiveCastingTime = $castingTime - $castingTimeReduction;

		$barSetting = $this->calcBarSetting($effectiveCastingTime);
		if ($barSetting < 0) {
			$barSetting = 0;
		}
		if ($barSetting > 100) {
			$barSetting = 100;
		}

		$fullAggInits = $this->calcInits($castingTime - 1);
		$neutralInits = $this->calcInits($castingTime);
		$fulldefInits = $this->calcInits($castingTime + 1);

		$blob = "Attack:    <highlight>{$castingTime}<end> second(s)\n";
		$blob .= "Init Skill:  <highlight>{$initSkill}<end>\n";
		$blob .= 'Def/Agg:  <highlight>' . round($barSetting, 0) . "%<end>\n";
		$blob .= 'You must set your AGG bar at <highlight>' . round($barSetting, 0) .'% ('. round($barSetting * 8 / 100, 2) .") <end>to instacast your nano.\n\n";
		$blob .= '(<a href=skillid://51>Agg/def-Slider</a> should read <highlight>' . round($barSetting*2-100, 0) . "<end>).\n\n";
		$blob .= "Init needed to instacast at:\n";
		$blob .= "  Full Agg (100%): <highlight>{$fullAggInits}<end> inits\n";
		$blob .= "  Neutral (87.5%): <highlight>{$neutralInits}<end> inits\n";
		$blob .= "  Full Def (0%):     <highlight>{$fulldefInits}<end> inits\n\n";

		$bar = 'llllllllllllllllllllllllllllllllllllllllllllllllll';
		$markerPos = (int)round($barSetting/100*strlen($bar), 0);
		$leftBar    = substr($bar, 0, $markerPos);
		$rightBar   = substr($bar, $markerPos+1);
		$blob .= "<highlight>{$fulldefInits}<end> DEF <green>{$leftBar}<end><red>│<end><green>{$rightBar}<end> AGG <highlight>{$fullAggInits}<end>\n";
		$blob .= "                         You: <highlight>{$initSkill}<end>\n";
		$blob .= "\n";
		$blob .= "Current casting times:\n";
		$blob .= '  Full Agg (100%): <highlight>' . round(max(0, $effectiveCastingTime-1), 1) . "s<end>\n";
		$blob .= '  Neutral (87.5%): <highlight>' . round(max(0, $effectiveCastingTime), 1) . "s<end>\n";
		$blob .= '  Full Def (0%):     <highlight>' . round(max(0, $effectiveCastingTime+1), 1) . "s<end>\n";

		$msg = $this->text->makeBlob('Nano Init Results', $blob);
		$context->reply($msg);
	}

	/**
	 * See weapon info, including how much skills you need to cap your weapon specials
	 * and attack speed at different aggdef positions
	 */
	#[NCA\HandlesCommand('weapon')]
	#[NCA\Help\Example("<symbol>weapon <a href='itemref://30190/30190/300'>Perfected Diamondine Kick Pistol</a>")]
	public function weaponCommandWithDrop(CmdContext $context, PItem $item): void {
		$this->weaponCommand($context, $item->highID, $item->ql);
	}

	/**
	 * See weapon info, including how much skills you need to cap your weapon specials
	 * and attack speed at different aggdef positions
	 */
	#[NCA\HandlesCommand('weapon')]
	#[NCA\Help\Example('<symbol>weapon 30190 300')]
	public function weaponCommand(CmdContext $context, int $highid, int $ql): void {
		// this is a hack since Worn Soft Pepper Pistol has its high and low ids reversed in-game
		// there may be others
		/** @var ?AODBEntry */
		$row = $this->itemsController->getByIDs($highid)
			->where('lowql', '<=', $ql)
			->where('highql', '>=', $ql)
			->sort(static function (AODBEntry $i1, AODBEntry $i2) use ($highid): int {
				return ($i1->highid === $highid) ? 1 : 2;
			})->first();

		if ($row === null) {
			$msg = 'Item does not exist in the items database.';
			$context->reply($msg);
			return;
		}

		/** @var ?WeaponAttribute */
		$lowAttributes = $this->db->table(WeaponAttribute::getTable())
			->where('id', $row->lowid)
			->asObj(WeaponAttribute::class)
			->first();

		/** @var ?WeaponAttribute */
		$highAttributes = $this->db->table(WeaponAttribute::getTable())
			->where('id', $row->highid)
			->asObj(WeaponAttribute::class)
			->first();

		if ($lowAttributes === null || $highAttributes === null) {
			$msg = 'Could not find any weapon info for this item.';
			$context->reply($msg);
			return;
		}

		$name = $row->name;
		$attackTime = Util::interpolate($row->lowql, $row->highql, $lowAttributes->attack_time, $highAttributes->attack_time, $ql);
		$rechargeTime = Util::interpolate($row->lowql, $row->highql, $lowAttributes->recharge_time, $highAttributes->recharge_time, $ql);
		$rechargeTime /= 100;
		$attackTime /= 100;
		$itemLink = $row->getLink(ql: $ql);

		$blob = '';

		$blob .= "<header2>Stats<end>\n";
		$blob .= "<tab>Item:       {$itemLink}\n";
		$blob .= '<tab>Attack:    <highlight>' . sprintf('%.2f', $attackTime) . "<end>s\n";
		$blob .= '<tab>Recharge: <highlight>' . sprintf('%.2f', $rechargeTime) . "<end>s\n\n";

		// inits
		$blob .= "<header2>Agg/Def<end>\n";
		$blob .= $this->getInitDisplay($attackTime, $rechargeTime);
		$blob .= "\n";

		$found = false;
		if ($highAttributes->full_auto !== null && $lowAttributes->full_auto !== null) {
			$full_auto_recharge = Util::interpolate($row->lowql, $row->highql, $lowAttributes->full_auto, $highAttributes->full_auto, $ql);
			[$weaponCap, $skillCap] = $this->capFullAuto($attackTime, $rechargeTime, $full_auto_recharge);
			$blob .= "<header2>Full Auto<end>\n";
			$blob .= '<tab>You need <highlight>'.$skillCap.'<end> Full Auto skill to cap your recharge at <highlight>'.$weaponCap."<end>s.\n\n";
			$found = true;
		}
		if ($highAttributes->burst !== null && $lowAttributes->burst !== null) {
			$burst_recharge = Util::interpolate($row->lowql, $row->highql, $lowAttributes->burst, $highAttributes->burst, $ql);
			[$weaponCap, $skillCap] = $this->capBurst($attackTime, $rechargeTime, $burst_recharge);
			$blob .= "<header2>Burst<end>\n";
			$blob .= '<tab>You need <highlight>'.$skillCap.'<end> Burst skill to cap your recharge at <highlight>'.$weaponCap."<end>s.\n\n";
			$found = true;
		}
		if ($highAttributes->fling_shot) {
			[$weaponCap, $skillCap] = $this->capFlingShot($attackTime);
			$blob .= "<header2>Fling Shot<end>\n";
			$blob .= '<tab>You need <highlight>'.$skillCap.'<end> Fling Shot skill to cap your recharge at <highlight>'.$weaponCap."<end>s.\n\n";
			$found = true;
		}
		if ($highAttributes->fast_attack) {
			[$weaponCap, $skillCap] = $this->capFastAttack($attackTime);
			$blob .= "<header2>Fast Attack<end>\n";
			$blob .= '<tab>You need <highlight>'.$skillCap.'<end> Fast Attack skill to cap your recharge at <highlight>'.$weaponCap."<end>s.\n\n";
			$found = true;
		}
		if ($highAttributes->aimed_shot) {
			[$weaponCap, $skillCap] = $this->capAimedShot($attackTime, $rechargeTime);
			$blob .= "<header2>Aimed Shot<end>\n";
			$blob .= '<tab>You need <highlight>'.$skillCap.'<end> Aimed Shot skill to cap your recharge at <highlight>'.$weaponCap."<end>s.\n\n";
			$found = true;
		}
		if ($highAttributes->brawl) {
			$blob .= "<header2>Brawl<end>\n";
			$blob .= "<tab>This weapon supports 1 brawl attack every <highlight>15s<end> (constant).\n\n";
			$found = true;
		}
		if ($highAttributes->sneak_attack) {
			$blob .= "<header2>Sneak Attack<end>\n";
			$blob .= "<tab>This weapon supports sneak attacks.\n";
			$blob .= "<tab>The recharge depends solely on your Sneak Attack Skill:\n";
			$blob .= "<tab>40 - (Sneak Attack skill) / 150\n\n";
			$found = true;
		}

		if (!$found) {
			$blob .= "There are no specials on this weapon that could be calculated.\n\n";
		}

		$blob .= "\nRewritten by Nadyita (RK5)";
		$msg = $this->text->makeBlob("Weapon Info for {$name}", $blob);

		$context->reply($msg);
	}

	/**
	 * See weapon info, including how much skills you need to cap your weapon specials
	 * and attack speed at different aggdef positions
	 */
	#[NCA\HandlesCommand('weapon')]
	#[NCA\Help\Example('<symbol>weapon perf diamondine')]
	#[NCA\Help\Example('<symbol>weapon 144 nippy')]
	public function weaponSearchCommand(CmdContext $context, ?int $ql, PNonNumber $search): void {
		$search = $search();
		$data = $this->itemsController->findItemsFromLocal($search, $ql);
		$kept = [];
		$data = array_values(
			array_filter(
				$data,
				function (ItemSearchResult $item) use ($ql, &$kept): bool {
					if (isset($ql) && ($ql < $item->lowql || $ql > $item->highql)) {
						return false;
					}
					if (isset($kept[$item->lowid])) {
						return false;
					}
					if (!$this->db->table(WeaponAttribute::getTable())
							->where('id', $item->lowid)
							->orWhere('id', $item->highid)
							->exists()
					) {
						return false;
					}
					$kept[$item->lowid] = true;
					return true;
				}
			)
		);
		if (count($data) === 0) {
			if ($ql !== null) {
				$msg = "No QL <highlight>{$ql}<end> items found matching <highlight>{$search}<end>.";
			} else {
				$msg = "No items found matching <highlight>{$search}<end>.";
			}
			$context->reply($msg);
			return;
		}
		if (count($data) === 1) {
			$this->weaponCommand($context, $data[0]->lowid, $ql ?? $data[0]->ql);
			return;
		}

		$blob = "<header2>Weapons matching {$search}<end>\n";
		foreach ($data as $item) {
			$useQL = $ql ?? $item->ql;
			$itemLink = $item->getLink(ql: $useQL);
			$statsLink = Text::makeChatcmd('stats', "/tell <myname> weapon {$item->lowid} {$useQL}");
			$blob .= "<tab>[{$statsLink}] {$itemLink} (QL {$useQL})\n";
		}
		$msg = $this->text->makeBlob('Weapons (' . count($data) .')', $blob);
		$context->reply($msg);
	}

	public function calcAttackTimeReduction(int $initSkill): float {
		if ($initSkill > 1_200) {
			$highRecharge = $initSkill - 1_200;
			$attackTimeReduction = ($highRecharge / 600) + 6;
		} else {
			$attackTimeReduction = ($initSkill / 200);
		}

		return $attackTimeReduction;
	}

	public function calcBarSetting(float $effectiveAttackTime): float {
		if ($effectiveAttackTime < 0) {
			return 87.5 + (87.5 * $effectiveAttackTime);
		} elseif ($effectiveAttackTime > 0) {
			return 87.5 + (12 * $effectiveAttackTime);
		}
		return 87.5;
	}

	public function calcInits(float $attackTime): float {
		if ($attackTime < 0) {
			return 0;
		} elseif ($attackTime < 6) {
			return round($attackTime * 200, 2);
		}
		return round(1_200 + ($attackTime - 6) * 600, 2);
	}

	/**
	 * @return list<float>
	 *
	 * @psalm-return list{float,float}
	 */
	public function capFullAuto(float $attackTime, float $rechargeTime, int $fullAutoRecharge): array {
		$weaponCap = floor(10 + $attackTime);
		$skillCap = ((40 * $rechargeTime) + ($fullAutoRecharge / 100) - 11) * 25;

		return [$weaponCap, $skillCap];
	}

	/**
	 * @return list<int>
	 *
	 * @psalm-return list{int,int}
	 */
	public function capBurst(float $attackTime, float $rechargeTime, int $burstRecharge): array {
		$hard_cap = (int)round($attackTime + 8, 0);
		$skill_cap = (int)floor((($rechargeTime * 20) + ($burstRecharge / 100) - 8) * 25);

		return [$hard_cap, $skill_cap];
	}

	/**
	 * @return list<float>
	 *
	 * @psalm-return list{float,float}
	 */
	public function capFlingShot(float $attackTime): array {
		$weaponCap = 5 + $attackTime;
		$skillCap = (($attackTime * 16) - $weaponCap) * 100;

		return [$weaponCap, $skillCap];
	}

	/**
	 * @return list<float>
	 *
	 * @psalm-return list{float,float}
	 */
	public function capFastAttack(float $attackTime): array {
		$weaponCap = (int)floor(5 + $attackTime);
		$skillCap = (($attackTime * 16) - $weaponCap) * 100;

		return [$weaponCap, $skillCap];
	}

	/**
	 * @return list<int>
	 *
	 * @psalm-return list{int,int}
	 */
	public function capAimedShot(float $attackTime, float $rechargeTime): array {
		$hardCap = (int)floor($attackTime + 10);
		$skillCap = (int)ceil((4_000 * $rechargeTime - 1_100) / 3);

		return [$hardCap, $skillCap];
	}

	public function getInitDisplay(float $attack, float $recharge): string {
		$blob = '';
		for ($percent = 100; $percent >= 0; $percent -= 10) {
			$init = $this->getInitsForPercent($percent, $attack, $recharge);

			$blob .= '<tab>DEF ';
			$blob .= $this->getAggdefBar($percent);
			$blob .= ' AGG ';
			$blob .= Text::alignNumber($init, 4, 'highlight');
			$blob .= ' (' . Text::alignNumber($percent, 3) . "%)\n";
		}
		return $blob;
	}

	/**
	 * @param null|int|list<int> $aoid
	 *
	 * @return Collection<int,WeaponAttribute>
	 */
	public function getWeaponAttributes(null|int|array $aoid): Collection {
		$query = $this->db->table(WeaponAttribute::getTable());
		if (is_int($aoid)) {
			$query->where('id', $aoid);
		} elseif (is_array($aoid)) {
			$query->whereIn('id', $aoid);
		}

		return $query->asObj(WeaponAttribute::class);
	}

	protected function getAggdefBar(float $percent, int $length=50): string {
		$bar = str_repeat('l', $length);
		$markerPos = (int)round($percent / 100 * $length, 0);
		$leftBar   = substr($bar, 0, $markerPos);
		$rightBar  = substr($bar, $markerPos + 1);
		$fancyBar = "<green>{$leftBar}<end><red>│<end><green>{$rightBar}<end>";
		if ($percent < 100.0) {
			$fancyBar .= '<black>l<end>';
		}
		return $fancyBar;
	}
}
