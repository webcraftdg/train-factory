<?php

namespace App\Datapipeline\Service;

use webcraftdg\dataPipeline\configs\ColumnMapping;


class ColumnsMappingService
{
    private array $columns = [];

    public function addColumn(string $input, string $output, array $options = []) : ColumnsMappingService
    {
        $this->columns[] = new ColumnMapping(
            inputKey: $input,
            outputKey: $output,
            options: $options
        );
        return $this;
    }

    public function getColumns()
    {
        return $this->columns;
    }
}
