<?php declare(strict_types=1);

namespace Nadybot\Modules\SKILLS_MODULE;

/*
 * Implements functions to calculate the cap times, current recharge times and skill needed to reach the cap
 * for the various special attacks in AO.
 * Brawl recharge time is fixed, sneak attack is currently not available, but has a recharge of around 40s + weapon attack.
 * It shows a very small reduction with sneak attack skill but only around 1s per 2000. It's hard to test, but close enough.
 */
class SkillRechargeInfo
{
	public function __construct(int $skillToCap, int $currentRechargeTime, int $capTime)
	{
		$this->SkillToCap = $skillToCap;
		$this->CurrentRechargeTime = $currentRechargeTime;
		$this->HardCapTime = $capTime;
	}

	/**
	 * The skill required to reach the lowest recharge time.
	 */
	public int $SkillToCap;

	/**
	 * The recharge time for the given special skill.
	 */
	public int $CurrentRechargeTime;

	/**
	 * The lowest possible special recharge time for the given weapon..
	 */
	public int $HardCapTime;
	
	/**
	 * Calculate the Burst recharge time.
	 *
	 * @param int $currentSkill The current Burst skill.
	 * @param float $weaponAttack The weapon attack time.
	 * @param float $weaponRecharge The weapon recharge time.
	 * @param int $burstDelay The weapon burst delay time.
	 *
	 * @return SkillRechargeInfo The recharge information as a SkillRechargeInfo object.
	 */
	public static function FromBurst(int $currentSkill, float $weaponAttack, float $weaponRecharge, int $burstDelay) : SkillRechargeInfo
	{
		// Comment: KelticDanor: Exquisite Mausser Chemical Streamer burst is confirmed 776 for 11 seconds, 775 is 12 seconds
		if ($burstDelay === 0)
		{
			$burstDelay = 1_000;
		}

		$hardCapTime = (int)floor( $weaponAttack + 8 );
		$skillToCap = (int)floor( (($weaponRecharge * 20) + ($burstDelay / 100) + $weaponAttack - ($hardCapTime + 1)) * 25 + 1 );
		$skillRecharge = (int)floor( ($weaponRecharge * 20) + ($burstDelay / 100) - ($currentSkill / 25) + $weaponAttack );
		$skillRecharge = max($skillRecharge, $hardCapTime);
		return new SkillRechargeInfo($skillToCap, $skillRecharge, $hardCapTime);
	}
	
	/**
	 * Calculate the Full Auto recharge time.
	 *
	 * @param int $currentSkill The current Full Auto skill.
	 * @param float $weaponAttack The weapon attack time.
	 * @param float $weaponRecharge The weapon recharge time.
	 * @param int $burstDelay The weapon full auto delay time.
	 *
	 * @return SkillRechargeInfo The recharge information as a SkillRechargeInfo object.
	 */
	public static function FromFullAuto(int $currentSkill, float $weaponAttack, float $weaponRecharge, int $fullAutoDelay) : SkillRechargeInfo
	{
		// Comment: KelticDanor: Full auto looks good, matches the 2431 on envy and 1583 on bigburger as expected and 976 on hellfury
		if ($fullAutoDelay === 0) {
			$fullAutoDelay = 1_000;
		}
		
		$hardCapTime = (int)floor($weaponAttack + 10);
		$skillToCap = (int)floor( (($weaponRecharge * 40) + ($fullAutoDelay / 100) + $weaponAttack - ($hardCapTime + 1)) * 25 + 1 );
		$skillRecharge = (int)floor( ($weaponRecharge * 40) + ($fullAutoDelay / 100) - ($currentSkill / 25) + $weaponAttack );
		$skillRecharge = max($skillRecharge, $hardCapTime);
		return new SkillRechargeInfo($skillToCap, $skillRecharge, $hardCapTime);
	}
	
	/**
	 * Calculate the Fling Shot recharge time.
	 *
	 * @param int $currentSkill The current Fling Shot skill.
	 * @param float $weaponAttack The weapon attack time.
	 *
	 * @return SkillRechargeInfo The recharge information as a SkillRechargeInfo object.
	 */
	public static function FromFlingShot(int $currentSkill, float $weaponAttack) : SkillRechargeInfo
	{
		// Comment: KelticDanor: Fast attack is confirmed, 1701 to cap fast attack of tonfa, that is correct. 1700 is 7 seconds, 1701 is 6 seconds.
		$hardCapTime = (int)floor($weaponAttack + 5);
		$skillToCap = (int)floor( ($weaponAttack * 1600) - ($hardCapTime + 1) * 100 + 1 );
		$skillRecharge = (int)floor( ($weaponAttack * 16) - ($currentSkill / 100) );
		$skillRecharge = max($skillRecharge, $hardCapTime);
		return new SkillRechargeInfo($skillToCap, $skillRecharge, $hardCapTime);
	}	
	
	/**
	 * Calculate the Fast Attack recharge time.
	 *
	 * @param int $currentSkill The current Fast Attack skill.
	 * @param float $weaponAttack The weapon attack time.
	 *
	 * @return SkillRechargeInfo The recharge information as a SkillRechargeInfo object.
	 */
	public static function FromFastAttack(int $currentSkill, float $weaponAttack) : SkillRechargeInfo
	{
		// Fast attack works the same as fling shot
		return SkillRechargeInfo::FromFlingShot($currentSkill, $weaponAttack);
	}
	
	/**
	 * Calculate the Aimed Shot recharge time.
	 *
	 * @param int $currentSkill The current Aimed Shot skill.
	 * @param float $weaponAttack The weapon attack time.
	 * @param float $weaponRecharge The weapon recharge time.
	 *
	 * @return SkillRechargeInfo The recharge information as a SkillRechargeInfo object.
	 */
	public static function FromAimedShot(int $currentSkill, float $weaponAttack, float $weaponRecharge) : SkillRechargeInfo
	{
		// Comment: KelticDanor: Aimed shot is confirmed, 1514 to cap aimed shot of illegal tigress, that is correct. 1513 is 12 seconds, 1514 is 11 seconds. !
		$hardCapTime = (int)floor($weaponAttack + 10);
		$skillToCap = (int)floor( $weaponRecharge * 4000 / 3 + $weaponAttack * 100 / 3 - ($hardCapTime + 1) * 100 / 3 + 1 );
		$skillRecharge = (int)floor( ($weaponRecharge * 40) - ($currentSkill * 3.0 / 100) + $weaponAttack );
		$skillRecharge = max($skillRecharge, $hardCapTime);
		return new SkillRechargeInfo($skillToCap, $skillRecharge, $hardCapTime);
	}
}