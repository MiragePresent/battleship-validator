<?php

function validate_battlefield(array $field): bool {
    $occupiedPoints = occupied_points_from_field($field);
    
    // The number of occupied spots must be exactly 20
    // Otherwise the field is invalid
    if (count($occupiedPoints) !== 20) {
        return false;
    }

    $occupiedPoints = with_linked_points($occupiedPoints);

    return validate_spots_shape($occupiedPoints)
        && validate_placement($occupiedPoints)
        && validate_number_of_ships($occupiedPoints);
}

function occupied_points_from_field(array $field): array {
    $occupiedPoints = [];
    $y = 1;
    foreach ($field as $row) {
        $x = 1;
        foreach ($row as $cell) {
            if ($cell) {
                $occupiedPoints[get_point_key($x, $y)] = compact(['x', 'y']);
            }

            $x++;
        }
        $y++;
    }

    return $occupiedPoints;
}

function with_linked_points(array $occupiedPoints): array {
    foreach ($occupiedPoints as $key => $spot) {
        $x = $spot['x'];
        $y = $spot['y'];

        $nextX = get_point_key($x + 1, $y);
        $nextY = get_point_key($x, $y + 1);
        
        if (isset($occupiedPoints[$nextX])) {
            $occupiedPoints[$key]['nextX'] = $nextX;
            $occupiedPoints[$nextX]['prevX'] = $key;
        }

        if (isset($occupiedPoints[$nextY])) {
            $occupiedPoints[$key]['nextY'] = $nextY;
            $occupiedPoints[$nextY]['prevY'] = $key;
        }
    }

    return $occupiedPoints;
}

function validate_spots_shape(array $occupiedPoints): bool {
    foreach ($occupiedPoints as $point) {
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

function validate_placement(array $occupiedPoints): bool {
    // Validate surrounding corners are empty
    foreach ($occupiedPoints as $point) {
        $x = $point['x'];
        $y = $point['y'];

        // Bottom left corner must be empty
        if (isset($occupiedPoints[get_point_key($x - 1, $y + 1)])) {
            return false;
        }

        // Bottom right corner must be empty
        if (isset($occupiedPoints[get_point_key($x + 1, $y + 1)])) {
            return false;
        }
        // No need to check top corners they are covered 
        // while checking points from top to the bottom
    }

    return true;
}

function validate_number_of_ships(array $occupiedPoints): bool {
    $remainingShips = [
        4 => 1,
        3 => 2,
        2 => 3,
        1 => 4,
    ];
    $remainingSpots = $occupiedPoints;

    while (!empty($remainingSpots)) {
        // Take the first point from unporcessed
        reset($remainingSpots);
        $pointKey = key($remainingSpots);
        $ship = get_ship_points($pointKey, $occupiedPoints);

        $size = count($ship);

        if ($size > 4 || $size === 0) {
            return false;
        }

        $remainingShips[$size]--;

        if ($remainingShips[$size] < 0) {
            return false;
        }

        foreach ($ship as $key) {
            unset($remainingSpots[$key]);
        }
    }

    return true;
}

function get_point_key(int $x, int $y): string {
    return sprintf("%d-%d", $x, $y);
}

function get_ship_points(string $pointKey, array $pointsOnField): array {
    $point = $pointsOnField[$pointKey];
    // Make sure the point is not in the middle of a ship
    if (!empty($point['prevX'])) {
        return get_ship_points($point['prevX'], $pointsOnField);
    } elseif (!empty($point['prevY'])) {
        return get_ship_points($point['prevY'], $pointsOnField);
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
