<?php declare(strict_types=1);

namespace Nadybot\Core\ParamClass;

use Nadybot\Core\Types\Profession;

class PProfession extends Base {
	protected static string $regExp = 'adv(|y|enturer)'.
		'|age(nt)?'.
		'|(bureau)?crat'.
		'|doc(tor)?'.
		'|enf(o|orcer)?'.
		'|eng([iy]|ineer)?'.
		'|fix(er)?'.
		'|keep(er)?'.
		'|ma(rtial( ?artist)?)?'.
		'|mp|meta(-?physicist)?'.
		'|nt|nano(-?technician)?'.
		'|sol(d|dier)?'.
		'|tra(d|der)?'.
		'|sha(de)?';
	private Profession $enum;

	public function __construct(string $value) {
		$this->enum = Profession::byName($value);
	}

	public function __invoke(): Profession {
		return $this->enum;
	}

	public function __toString(): string {
		return $this->enum->value;
	}
}
