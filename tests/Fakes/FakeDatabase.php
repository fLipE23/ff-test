<?php

namespace Tests\Fakes;


class FakeDatabase
{
    public array $balances = [];
    public array $operations = [];

    public function insert($table, $data)
    {
        $this->{$table}[] = $data;
        return count($this->{$table});
    }

    public function select($table, $conditions)
    {
        return array_filter($this->{$table}, function ($item) use ($conditions) {
            foreach ($conditions as $key => $value) {
                if ($item[$key] !== $value) {
                    return false;
                }
            }
            return true;
        });
    }

    public function update($table, $conditions, $newValues)
    {
        foreach ($this->{$table} as $index => $item) {
            $match = true;
            foreach ($conditions as $key => $value) {
                if ($item[$key] !== $value) {
                    $match = false;
                    break;
                }
            }
            if ($match) {
                $this->{$table}[$index] = array_merge($item, $newValues);
            }
        }
    }
}




