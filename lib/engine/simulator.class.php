<?php

namespace Engine;

class Simulator {
    private object $rider;
    private object $bike;

    public function __construct(object $rider, object $bike) {
        $this->rider = $rider;
        $this->bike = $bike;
    }

    public function simulate(int $dist, int $grade, int $effort) : array {
        // all done in metric units, JIT conversion to/from
        $rweightv = $this->rider->getWeight();
        $powerv = $this->rider->getFtp()*$effort/100;
        $bweightv = 7;
        $rollingRes = 0.005; //tireValues = [0.005, 0.004, 0.012];  Clinkers, Tubolar, MTB
        $frontalArea = 0.388;//aeroValues = [0.388, 0.445, 0.420, 0.300, 0.233, 0.200]; Hoops, bar tops, bar end, drops, aerobar
        $gradev = $grade * 0.01;
        $headwindv = 1 / 3.6;  // converted to m/s (from km/h)
        $distancev = $dist;
        $temperaturev = 25;
        $elevationv = 100; //(sea level)
        $transv = 0.95; // no one knows what this is, so why bother presenting a choice?

         /* Common calculations */
        $density = (1.293 - 0.00426 * $temperaturev) * exp(-$elevationv / 7000.0);
        $twt = 9.8 * ($rweightv + $bweightv);  // total weight in newtons
        $A2 = 0.5 * $frontalArea * $density;  // full air resistance parameter
        $tres = $twt * ($gradev + $rollingRes); // gravity and rolling resistance

        # we calculate velocity from power
        $v = $this->newton($A2, $headwindv, $tres, $transv, $powerv) * 3.6;      // convert to km/h
        if ($v > 0.0){
            $t = 60.0* $distancev / $v;
        } else {
            $t = 0.0;  // don't want any div by zero errors
        }
        $velocity = $this->makeDecimal2($v);

        // c = t * 60.0 * powerv / 0.25 / 1000.0; // kilowatt-seconds, aka kilojoules. t is converted to seconds from minutes, 25% conversion efficiency
        $c = $t * $powerv * 0.24;  // simplified
        $wl = $c / 32318.0; // comes from 1 lb = 3500 Calories

        /* other results */

        $time = $this->makeDecimal2($t);
        $calories = $this->makeDecimal0($c);
        $weightloss = $this->makeDecimal2($wl);
        return array($velocity,$time,$calories,$weightloss);

    }

    private function newton($aero, $hw, $tr, $tran, $p) {
        /* Newton's method */
        $vel = 20;       // Initial guess
        $MAX = 10;       // maximum iterations
        $TOL = 0.05;     // tolerance
        for ($i=0; $i < $MAX; $i++) {
            $tv = $vel + $hw;
            $aeroEff = ($tv > 0.0) ? $aero : -$aero; // wind in face, must reverse effect
            $f = $vel * ($aeroEff * $tv * $tv + $tr) - $tran * $p; // the function
            $fp = $aeroEff * (3.0 * $vel + $hw) * $tv + $tr;     // the derivative
            $vNew = $vel - $f / $fp;
            if (abs($vNew - $vel) < $TOL) return $vNew;  // success
            $vel = $vNew;
        }
        return 0.0;  // failed to converge
    }

    private function makeDecimal2 ($value) {
        if ($value != null && $value != 0) {
            $x = round(floatval($value) * 100);
            if ($x < 100){
                $d=0;
            } else {
                $d = intval($x / 100);
            }
            $c = $x % 100;
            $g = ($c >= 10)?"":"0";
            $value = "" .$d ."." . $g . $c;
        }
        return $value;
    }

    private function makeDecimal0 ($value) {
        if ($value != null && $value != 0) {
            $value = "" . round(floatval($value));
        }
        return $value;
}
}