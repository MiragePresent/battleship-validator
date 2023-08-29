<?php

require_once __DIR__ . "/vendor/autoload.php";

use Symfony\Component\Console\Output\ConsoleOutput;
use MiragePresent\BattleshipValidator\Printer;
use MiragePresent\BattleshipValidator\FieldGenerator;
use MiragePresent\BattleshipValidator\Validator;

$generator = new FieldGenerator();
$printer = new Printer(new ConsoleOutput());
$field = $generator->testField();
$validator = new Validator($field);

$printer->renderField($field);
$isValid = $validator->isValid();

if ($isValid) {
    $printer->info("The battleship filed is valid");
} else {
    $printer->info("The battleship filed is INVALID");
}
