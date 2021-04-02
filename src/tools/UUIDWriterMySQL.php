<?php
/**
 * Reagordi Framework
 *
 * @package reagordi
 * @author Sanya Skull
 */

namespace SDesya74\Tools;

use RedBeanPHP\Adapter;
use RedBeanPHP\QueryWriter;
use RedBeanPHP\QueryWriter\MySQL;

class UUIDWriterMySQL extends MySQL implements QueryWriter
{
    const C_DATATYPE_SPECIAL_UUID = 97;
    protected $defaultValue = "@uuid";

    public function __construct(Adapter $adapter)
    {
        parent::__construct($adapter);
        $this->addDataType(
            self::C_DATATYPE_SPECIAL_UUID,
            "char(11)");
    }

    public function createTable($table)
    {
        $table = $this->esc($table);
        $sql = "CREATE TABLE {$table} (
                id char(11) NOT NULL,
                PRIMARY KEY ( id ))
                ENGINE = InnoDB DEFAULT
                CHARSET=utf8mb4
                COLLATE=utf8mb4_unicode_ci";
        $this->adapter->exec($sql);
    }

    public function updateRecord($table, $updateValues, $id = null)
    {
        $flagNeedsReturnID = (!$id);
        // trim(trailing '=' from to_base64(replace(uuid(), '-', '')))
        // replace(uuid(), '-', '')
        $uid = random_bytes(8);
        $uid = base64_encode($uid);
        $uid = str_replace(['=', '+', '/'], ['', '-', '_'], $uid);
        if ($flagNeedsReturnID) \RedBeanPHP\R::exec("SET @uuid = '{$uid}'");
        $id = parent::updateRecord($table, $updateValues, $id);
        if ($flagNeedsReturnID) $id = \RedBeanPHP\R::getCell("SELECT @uuid");
        return $id;
    }

    public function getTypeForID()
    {
        return self::C_DATATYPE_SPECIAL_UUID;
    }
}
