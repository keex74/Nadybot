<?php declare(strict_types=1);

namespace Nadybot\Modules\IMPLANT_MODULE;

use Nadybot\Core\{
	Attributes as NCA,
	CmdContext,
	DB,
	ModuleInstance,
	Text,
};
use Nadybot\Modules\ITEMS_MODULE\WhatBuffsController;

/**
 * @author Tyrence (RK2)
 */
#[
	NCA\Instance,
	NCA\DefineCommand(
		command: 'cluster',
		accessLevel: 'guest',
		description: 'Find which clusters buff a specified skill',
	)
]
class ClusterController extends ModuleInstance {
	#[NCA\Inject]
	private DB $db;

	#[NCA\Inject]
	private Text $text;

	#[NCA\Inject]
	private WhatBuffsController $wbCtrl;

	/** Get a list of skills/attributes you can get clusters for */
	#[NCA\HandlesCommand('cluster')]
	public function clusterListCommand(CmdContext $context): void {
		$data = $this->db->table(Cluster::getTable())
			->orderBy('LongName')
			->asObj(Cluster::class);
		$count = $data->count();

		$blob = "<header2>Clusters<end>\n";
		foreach ($data as $cluster) {
			if ($cluster->ClusterID === 0) {
				continue;
			}
			$blob .= '<tab>'.
				Text::makeChatcmd(
					$cluster->LongName,
					"/tell <myname> cluster {$cluster->LongName}"
				).
				"\n";
		}
		$msg = $this->text->makeBlob("Cluster List ({$count})", $blob);
		$context->reply($msg);
	}

	/** Find a cluster for a skill/attribute */
	#[NCA\HandlesCommand('cluster')]
	#[NCA\Help\Example('<symbol>cluster comp lit')]
	#[NCA\Help\Example('<symbol>cluster agility')]
	public function clusterCommand(CmdContext $context, string $search): void {
		$skills = $this->wbCtrl->searchForSkill($search);
		if (count($skills) === 0) {
			$msg = "No skills found that match <highlight>{$search}<end>.";
			$context->reply($msg);
			return;
		}
		$data = $this->db->table(Cluster::getTable())
			->whereIn('SkillID', array_column($skills, 'id'))
			->asObj(Cluster::class);
		$count = $data->count();

		if ($count === 0) {
			$msg = "No skills found that match <highlight>{$search}<end>.";
			$context->reply($msg);
			return;
		}
		$implantDesignerLink = Text::makeChatcmd('implant designer', '/tell <myname> implantdesigner');
		$blob = "Click 'Add' to add cluster to {$implantDesignerLink}.\n\n";
		foreach ($data as $cluster) {
			/** @var SlotClusterType[] */
			$results = $this->db->table(ClusterImplantMap::getTable(), 'c1')
				->join('ClusterType AS c2', 'c1.ClusterTypeID', 'c2.ClusterTypeID')
				->join('ImplantType AS i', 'c1.ImplantTypeID', 'i.ImplantTypeID')
				->where('c1.ClusterID', $cluster->ClusterID)
				->orderByDesc('c2.ClusterTypeID')
				->select(['i.ShortName as Slot', 'c2.Name AS ClusterType'])
				->asObj(SlotClusterType::class)->toArray();
			$blob .= "<pagebreak><header2>{$cluster->LongName}<end>:\n";

			foreach ($results as $row) {
				$impDesignerLink = Text::makeChatcmd(
					'add',
					"/tell <myname> implantdesigner {$row->Slot} {$row->ClusterType} {$cluster->LongName}"
				);
				$clusterType = ucfirst($row->ClusterType);
				$blob .= "<tab><highlight>{$clusterType}<end>: {$row->Slot} [{$impDesignerLink}]";
			}
			$blob .= "\n\n";
		}
		$msg = $this->text->makeBlob("Cluster search results ({$count})", $blob);
		$context->reply($msg);
	}
}
