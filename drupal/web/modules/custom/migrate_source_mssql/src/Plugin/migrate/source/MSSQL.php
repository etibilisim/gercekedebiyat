<?php

namespace Drupal\migrate_source_mssql\Plugin\migrate\source;

use Drupal\migrate\MigrateException;
use Drupal\migrate\Plugin\migrate\source\SourcePluginBase;
use Drupal\migrate\Plugin\MigrationInterface;

/**
 * Source for CSV.
 *
 * If the CSV file contains non-ASCII characters, make sure it includes a
 * UTF BOM (Byte Order Marker) so they are interpreted correctly.
 *
 * @MigrateSource(
 *   id = "mssql"
 * )
 */
class MSSQL extends SourcePluginBase {

    /**
     * List of available source fields.
     *
     * Keys are the field machine names as used in field mappings, values are
     * descriptions.
     *
     * @var array
     */
    protected $fields = [];

    /**
     * List of key fields, as indexes.
     *
     * @var array
     */
    protected $keys = [];

    /**
     * The file class to read the file.
     *
     * @var string
     */
    protected $queryClass = '';

    /**
     * The file object that reads the CSV file.
     *
     * @var \SplFileObject
     */
    protected $query = NULL;

    /**
     * {@inheritdoc}
     */
    public function __construct(array $configuration, $plugin_id, $plugin_definition, MigrationInterface $migration) {
        parent::__construct($configuration, $plugin_id, $plugin_definition, $migration);

        // Path is required.
        if (empty($this->configuration['query'])) {
            throw new MigrateException('You must declare the "Query" to the source MSSQL Query in your source settings.');
        }

        // Key field(s) are required.
        if (empty($this->configuration['query'])) {
            throw new MigrateException('You must declare "keys" as a unique array of fields in your source settings.');
        }

        $this->queryClass = empty($configuration['queryClass']) ? 'Drupal\migrate_source_mssql\MSSQLQueryObject' : $configuration['query_class'];
    }

    /**
     * Return a string representing the source file path.
     *
     * @return string
     *   The file path.
     */
    public function __toString() {
        return $this->configuration['query'];
    }

    /**
     * {@inheritdoc}
     */
    public function initializeIterator() {
        global $base_url;
        // File handler using header-rows-respecting extension of SPLFileObject.
        $this->query = new $this->queryClass($this->configuration['query']);
        $serverName = \Drupal::state()->get('servername'); //serverName\instanceName
        $username = \Drupal::state()->get('username'); //serverName\instanceName
        $password = \Drupal::state()->get('password'); //serverName\instanceName
        $database = \Drupal::state()->get('database'); //serverName\instanceName
        $connectionInfo = array("UID"=> $username, "PWD"=> $password,"Database"=> $database);
        $conn = sqlsrv_connect( $serverName, $connectionInfo);
        if($conn) {
            if(($result = sqlsrv_query($conn,$this->configuration['query'])) !== false) {
                $query_obj = array();
                $headers_obj = sqlsrv_field_metadata($result);
                foreach ($headers_obj as $header) {
                    $headers = $header['Name'];
                    $column_names[] = [$headers => $headers];
                }
                while ($obj = sqlsrv_fetch_object($result)) {
                    $query_obj[] = $obj;
                }
            }
        }else{
            drupal_set_message("Connection could not be established. Please test your configuration here '.$base_url.'/admin/migrate_mssql/dbconnection");
            drupal_set_message( print_r( sqlsrv_errors(), true));
        }
        return $this->query;
    }

    /**
     * {@inheritdoc}
     */
    public function getIDs() {
        $ids = [];
        foreach ($this->configuration['keys'] as $delta => $value) {
            if (is_array($value)) {
                $ids[$delta] = $value;
            }
            else {
                $ids[$value]['type'] = 'string';
            }
        }
        return $ids;
    }

    /**
     * {@inheritdoc}
     */
    public function fields() {
        $fields = [];
        foreach ($this->getIterator() as $column) {
            $fields[key($column)] = reset($column);
        }

        // Any caller-specified fields with the same names as extracted fields will
        // override them; any others will be added.
        if (!empty($this->configuration['fields'])) {
            $fields = $this->configuration['fields'] + $fields;
        }

        return $fields;
    }

}
