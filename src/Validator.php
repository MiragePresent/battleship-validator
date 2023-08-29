<?php 

namespace MiragePresent\BattleshipValidator;

class Validator {
    protected array $validSpots = [];

    protected array $occupiedPoints = [];

    protected array $remainingShips = [
        4 => 1,
        3 => 2,
        2 => 3,
        1 => 4,
    ];

    public function __construct(
        protected array $field
    ) {}

    public function isValid(): bool 
    {
        // processing field metadata
        $this->createValidPoints();
        $this->loadPoints();
        $this->linkPoints();

        // validate field
        return $this->validSpots()
            && $this->validPlacement()
            && $this->validNumberOfShips();
    }

    protected function createValidPoints(): void 
    {
        for ($x = 1; $x <= 10; $x++) {
            for ($y = 1; $y <= 10; $y++) {
                $this->validSpots[$this->pointKey($x, $y)] = true;
            }
        }
    }

    protected function loadPoints(): void 
    {
        $x = 1;
        foreach ($this->field as $row) {
            $y = 1;
            foreach ($row as $cell) {
                if ($cell === 1) {
                    $this->addOccupiedPoint($x, $y);
                    $this->removeFromValidPoint($x, $y);
                }

                $x++;
            }
            $x++;
        }
    }

    protected function linkPoints(): void 
    {
        foreach ($this->occupiedPoints as $key => $spot) {
            $x = $spot['x'];
            $y = $spot['y'];

            $nextX = $this->pointKey($x + 1, $y);
            $nextY = $this->pointKey($x, $y + 1);
            
            if (isset($this->occupiedPoints[$nextX])) {
                $this->occupiedPoints[$key]['nextX'] = $nextX;
                $this->occupiedPoints[$nextX]['prevX'] = $key;
            }

            if (isset($this->occupiedPoints[$nextY])) {
                $this->occupiedPoints[$key]['nextY'] = $nextY;
                $this->occupiedPoints[$nextX]['prevY'] = $key;
            }
        }
    }

    protected function validSpots(): bool 
    {
        foreach ($this->occupiedPoints as $point) {
            if (isset($point['nextX']) && isset($point['nextY'])) {
                return false;
            }
            if (isset($point['prevX']) && isset($point['prevY'])) {
                return false;
            }
        }

        return true;
    }

    protected function validPlacement(): bool 
    {
        return true;
    }

    protected function validNumberOfShips(): bool 
    {
        return true;
    }

    protected function addOccupiedPoint(int $x, int $y): void 
    {
        $this->occupiedPoints[$this->pointKey($x, $y)] = compact(['x', 'y']);
    }

    protected function removeFromValidPoint(int $x, int $y): void 
    {
        $pointKey = $this->pointKey($x, $y);
        
        if (isset($this->validSpots[$pointKey])) {
            unset($this->validSpots[$pointKey]);
        }
    }

    protected function pointKey(int $x, int $y): string 
    {
        return sprintf("%d-%d", $x, $y);
    }
}
