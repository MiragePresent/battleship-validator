<?php

require_once __DIR__ . "/vendor/autoload.php";

use Symfony\Component\Console\Output\ConsoleOutput;
use MiragePresent\BattleshipValidator\Printer;
use MiragePresent\BattleshipValidator\FieldGenerator;

$generator = new FieldGenerator();
$printer = new Printer(new ConsoleOutput());

$printer->info("Hello world");

$printer->renderField($generator->testField());
