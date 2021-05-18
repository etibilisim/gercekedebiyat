<?php

namespace Drupal\migrate_source_mssql;

class MSSQLQueryObject {



    /**
     * {@inheritdoc}
     */
    public function __construct($query) {


    }
    public function setColumnNames(array $column_names) {
        $this->columnNames = $column_names;
    }
    public function rewind() {
    }
    public function valid(){

    }
}
