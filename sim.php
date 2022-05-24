<?php

include ('./lib/engine/bike.class.php');
include ('./lib/engine/rider.class.php');
include ('./lib/engine/simulator.class.php');

$rider = new Engine\Rider();
$bike = new Engine\Rider();

$sim = new Engine\Simulator($rider,$bike);
$res = $sim->simulate(112, 1, 100);
print_r("Time: ". $res[1]."\n");
print_r("Velocity: ". $res[0]."\n");
print_r("Calories: ". $res[2]."\n");
print_r("Weight Loss: ". $res[3]."\n");