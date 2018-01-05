<?php

namespace AppBundle\Twig;

use Twig\Extension\AbstractExtension;
use Twig\TwigFilter;

class AppExtension extends AbstractExtension
{
    public function getFilters(){
        return [
            new TwigFilter('intToTime', array($this, 'intToTimeFilter')),
            new TwigFilter('intPlusNowDateTime', array($this, 'intPlusNowDateTimeFilter')),
        ];
    }

    public function intToTimeFilter($int){
        $time = [ 'H' => 0,'m' => 0,'s' => 0 ];
        $time['s'] = $int;
        while($time['s'] >= 60){
            $time['s']-=60;
            $time['m']++;
        }
        while($time['m'] >= 60){
            $time['m']-=60;
            $time['H']++;
        }
        if($time['s'] < 10){
            $time['s'] = '0'.$time['s'];
        }
        if($time['m'] < 10){
            $time['m'] = '0'.$time['m'];
        }
        if($time['H'] < 10){
            $time['H'] = '0'.$time['H'];
        }
        return implode(":", $time);
    }

    public function intPlusNowDateTimeFilter($int){
        $now = new \DateTime();
        $now->setTimezone(new \DateTimeZone('Europe/Sofia'));
        $now->setTimestamp($now->getTimestamp() + $int);
        return $now->format("Y-m-d H:i:s");
    }
}