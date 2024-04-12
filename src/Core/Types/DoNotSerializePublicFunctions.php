<?php declare(strict_types=1);

namespace Nadybot\Core\Types;

use EventSauce\ObjectHydrator\MapperSettings;

#[MapperSettings(serializePublicMethods: false)]
interface DoNotSerializePublicFunctions {
}
