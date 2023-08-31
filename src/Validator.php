<?php 

namespace MiragePresent\BattleshipValidator;

class Validator {
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
        $this->loadPoints();

        // The number of occupied spots must be exactly 20
        // Otherwise the field is invalid
        if (count($this->occupiedPoints) !== 20) {
            return false;
        }

        $this->linkPoints();

        // validate field
        return $this->validSpots()
            && $this->validPlacement()
            && $this->validNumberOfShips();
    }

    protected function loadPoints(): void 
    {
        $y = 1;
        foreach ($this->field as $row) {
            $x = 1;
            foreach ($row as $cell) {
                if ($cell) {
                    $this->addOccupiedPoint($x, $y);
                }

                $x++;
            }
            $y++;
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
                $this->occupiedPoints[$nextY]['prevY'] = $key;
            }
        }
    }

    protected function validSpots(): bool 
    {
        foreach ($this->occupiedPoints as $point) {
            // No siblings on X axis which means 
            // even if there are siblings on Y it's one axis ship
            if (!isset($point['nextX']) && !isset($point['prevX'])) {
                continue;
            }
            // No siblings on X axis which means 
            // even if there are siblings on Y it's one axis ship
            if (!isset($point['nextY']) && !isset($point['prevY'])) {
                continue;
            }
            // There are siblings on X and Y axes
            return false;
        }

        return true;
    }

    protected function validPlacement(): bool 
    {
        foreach ($this->occupiedPoints as $point) {
            $x = $point['x'];
            $y = $point['y'];

            // Validate surrounding corners or a point are empty
            $bottomLeftCorner = $this->pointKey($x - 1, $y + 1);
            $bottomRightCorner = $this->pointKey($x + 1, $y + 1);

            // Bottom left corner must be empty
            if (isset($this->occupiedPoints[$bottomLeftCorner])) {
                return false;
            }

            // Bottom right corner must be empty
            if (isset($this->occupiedPoints[$bottomRightCorner])) {
                return false;
            }
            // No need to check top corners they are covered 
            // while checking points from top to the bottom
        }

        return true;
    }

    protected function validNumberOfShips(): bool 
    {
        $remainingSpots = $this->occupiedPoints;

        while (!empty($remainingSpots)) {
            // Take the first point from unporcessed
            reset($remainingSpots);
            $pointKey = key($remainingSpots);
            $ship = $this->getShip($pointKey, $this->occupiedPoints);

            $size = count($ship);

            if ($size > 4 || $size === 0) {
                return false;
            }

            $this->remainingShips[$size]--;

            if ($this->remainingShips[$size] < 0) {
                return false;
            }

            foreach ($ship as $key) {
                unset($remainingSpots[$key]);
            }
        } 
        
        return true;
    }

    protected function pointKey(int $x, int $y): string 
    {
        return sprintf("%d-%d", $x, $y);
    }

    protected function addOccupiedPoint(int $x, int $y): void 
    {
        $this->occupiedPoints[$this->pointKey($x, $y)] = compact(['x', 'y']);
    }

    /**
     * Retuns point keys that are linked into a ship 
     * by one of the point of the ship
     * 
     * @param string $pointKey
     * @param array $pointsOfField
     * 
     * @return array All the ship point keys
     */
    protected function getShip(string $pointKey, array $pointsOnField): array 
    {
        $point = $pointsOnField[$pointKey];
        // Make sure the point is not in the middle of a ship
        if (!empty($point['prevX'])) {
            return $this->getShip($point['prevX'], $pointsOnField);
        } elseif (!empty($point['prevY'])) {
            return $this->getShip($point['prevY'], $pointsOnField);
        }

        $shipPoints = [];
        $currentKey = $pointKey;

        while(!empty($currentKey)) {
            $currentPoint = $pointsOnField[$currentKey];
            $shipPoints[] = $currentKey;

            $currentKey = $currentPoint['nextX'] 
                ?? $currentPoint['nextY'] 
                ?? null;
        }

        return $shipPoints;
    }
}
