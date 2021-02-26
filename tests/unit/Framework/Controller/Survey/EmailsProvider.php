<?php


namespace Tests\Unit\Framework\Controller\Survey;


use Iterator;

class EmailsProvider implements Iterator
{
    private $file;
    private $current;
    private int $key;

    public function __construct($file)
    {
        $this->file    = $file;
        $this->current = [];
        $this->key     = 0;
    }

    public function __destruct()
    {
        fclose($this->file);
    }

    public function current()
    {
        return $this->current;
    }

    public function next(): void
    {
        $this->current = fgetcsv($this->file);
        $this->key++;
    }

    public function key(): int
    {
        return $this->key;
    }

    public function valid(): bool
    {
        return !feof($this->file);
    }

    public function rewind(): void
    {
        rewind($this->file);
        $this->current = fgetcsv($this->file);
        $this->key     = 0;
    }
}