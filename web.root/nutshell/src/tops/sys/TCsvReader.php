<?php

namespace Tops\sys;

class TCsvReader
{
    private $lines;
    private $columnCount = 0;
    private $linePointer = 0;
    private $lineCount = 0;


    public $brokenLines = array();
    public $warnings = array();
    public function openFile(mixed $file, $colCount = 0)  {
        if (is_array($file)) {
            $this->lines = $file;
        }
        else {
            $this->lines = file($file, FILE_IGNORE_NEW_LINES);
        }

        if (empty($this->lines)) {
            return false;
        }
        $this->lineCount = count($this->lines);
        $this->linePointer = 0;
        if ($colCount > 0) {
            $this->columnCount = $colCount;
            $this->linePointer = -1;
        }
        else {
            $headers = explode(",", $this->lines[0]);
            $this->columnCount = count($headers);
            $this->linePointer = 0;
        }
        // $this->warnings[] = "test warning";
        return true;
    }

    private function nextLine()  {
        $this->linePointer++;
        if ($this->linePointer >= $this->lineCount) {
            return false;
        }
        return $this->lines[$this->linePointer];
    }

    public function next() {
        $line = $this->nextLine();
        $currentLine = $this->linePointer;
        $broken = false;
        if ($line !== false) {
            $values = str_getcsv($line);
            while (count($values) < $this->columnCount) {
                $broken = true;
                $newLine = $this->nextLine();
                if ($newLine === false) {
                    return false;
                }
                $line .= "\n$newLine";
                $values = str_getcsv($line);
            }
            if ($broken === true) {
                $this->brokenLines[] = $currentLine;
            }
            $colcount = count($values);
            if ($colcount != $this->columnCount) {
                $this->warnings[] = "Column count ($colcount) does not match expected number of columns ($this->columnCount).";
            }
            return $values;
        }
        return false;
    }
}