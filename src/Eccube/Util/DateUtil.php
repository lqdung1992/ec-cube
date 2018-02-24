<?php
/**
 * Created by PhpStorm.
 * User: lqdung1992@gmail.com
 * Date: 2/22/2018
 * Time: 4:52 PM
 */

namespace Eccube\Util;


class DateUtil
{
    /**
     * @param int $day ['Monday' => 1, 'Tuesday' => 2, 'Wednesday' => 3, 'Thursday' => 4, 'Friday' => 5, 'Saturday' => 6, 'Sunday' => 7]
     * @return \DateTime
     */
    public static function getDay($day)
    {
        $date = new \DateTime();
        if ($date->format('N') < $day) {
            $date->setISODate((int)$date->format('o'), (int)$date->format('W'), (int)$day);
        } else {
            // get the next week
            $date = $date->modify('next monday');
            $date->setISODate((int)$date->format('o'), (int)$date->format('W'), (int)$day);
        }

        return $date;
    }
}
