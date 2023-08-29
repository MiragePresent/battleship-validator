<?php 

namespace MiragePresent\BattleshipValidator;

use Symfony\Component\Console\Output\OutputInterface;

class Printer {
    public function __construct(
        public readonly OutputInterface $output
    ) {}

    public function info(string $message): self
    {
        $this->output->writeln($message);

        return $this;
    }

    public function renderField(array $field): self 
    {
        // Head coordinates
        $this->output->writeln("   A B C D E F G H J K");
        $rowNum = 1;

        foreach ($field as $row) {
            // Row coordinates
            $coordinatesFormat = $rowNum < 10 ? " %d" : "%d"; 
            $this->output->write(sprintf($coordinatesFormat, $rowNum++));

            foreach ($row as $cell) {
                $this->output->write($cell === 0 ? "░░" : "▓▓");
            }

            $this->output->write(PHP_EOL);
        }

        return $this; 
    }
}