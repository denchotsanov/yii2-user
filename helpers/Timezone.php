<?php


namespace denchotsanov\user\helpers;

use DateTime;
use DateTimeZone;
use yii\helpers\ArrayHelper;

class Timezone
{
    /**
     * @return array
     * @throws \Exception
     */
    public static function getAll()
    {
        $timeZones = [];
        $timeZoneIdentifiers = DateTimeZone::listIdentifiers();
        foreach ($timeZoneIdentifiers as $timeZone) {
            $date = new DateTime('now', new DateTimeZone($timeZone));
            $offset = $date->getOffset();
            $tz = ($offset > 0 ? '+' : '-') . gmdate('H:i', abs($offset));
            $timeZones[] = [
                'timezone' => $timeZone,
                'name' => "{$timeZone} (UTC {$tz})",
                'offset' => $offset
            ];
        }
        ArrayHelper::multisort($timeZones, 'offset', SORT_DESC, SORT_NUMERIC);
        return $timeZones;
    }
}