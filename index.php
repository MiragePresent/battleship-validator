<?php

require_once __DIR__ . "/vendor/autoload.php";
require_once __DIR__ . "/validator_functional.php";

use Symfony\Component\Console\Output\ConsoleOutput;
use MiragePresent\BattleshipValidator\Printer;
use MiragePresent\BattleshipValidator\FieldGenerator;

$generator = new FieldGenerator();
$printer = new Printer(new ConsoleOutput());

$battleFields = [
    $generator->validField(),
    $generator->invalidPointsNumber(),
    $generator->invalidShape(),
    $generator->invalidPlacement(),
    $generator->invalidShipSize(),
    $generator->invalidNumberOfShips(),
];

foreach ($battleFields as $field) {
    $printer->renderField($field);
    $isValid = validate_battlefield($field);

    if ($isValid) {
        $printer->info("The battleship filed is valid");
    } else {
        $printer->info("The battleship filed is INVALID");
    }

}