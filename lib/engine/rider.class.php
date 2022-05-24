<?php

namespace Engine;

class Rider {
    private array $attr = array(
        'ftp'       => 199,
        'weight'    => 50,
        'energy'    => 100
    );

    public function getFtp() {
        return $this->attr['ftp'];
    }

    public function getWeight() {
        return $this->attr['weight'];
    }

    public function reduceEnergy(int $val): void {
        $this->attr['energy'] -= $val;
    }



}