<?php
namespace Application\Validator;
 
use Application\Entity;
use Zend\Db\ResultSet\ResultSet;
use Zend\Db\Sql\Select;
use Zend\Db\Sql\Sql;
use Zend\Validator\Db\AbstractDb;
use Zend\Validator\Exception;
use Zend\Db\Adapter\Adapter;
use Zend\Db\Sql\TableIdentifier;
use Zend\Db\ResultSet\ResultSetInterface;
use Zend\Db\Sql\Where;
 
abstract class AbstractValidatorModel
{
    /**
     * @var bool
     */
    private $isInitialized = false;
    /**
     * @var Sql
     */
    private $sql = null;
    /**
     * @var string
     */
    private $entity = '';
    /**
     * @var ResultSetInterface
     */
    private $resultSet = null;
    /**
     * Database adapter to use. If null isValid() will throw an exception
     *
     * @var \Zend\Db\Adapter\Adapter
     */
    protected $adapter = null;
    /**
     * @var string
     */
    protected $table = '';
 
    function __construct($adapter)
    {
        $this->adapter = $adapter;
    }
 
    /**
     * @throws \Exception
     */
    public function initialize()
    {
        if (!$this->adapter instanceof Adapter) {
            throw new \Exception('AbstractValidatorModel: Adapter is not defined');
        }
 
        if (!is_string($this->table) && !$this->table instanceof TableIdentifier) {
            throw new \Exception('AbstractValidatorModel: Table is not defined');
        }
 
        if (!$this->sql instanceof Sql) {
            $this->sql = new Sql($this->adapter, $this->table);
        }
 
        if (!$this->resultSet instanceof ResultSetInterface) {
            $this->resultSet = new ResultSet();
        }
 
        $this->isInitialized = true;
    }
 
    /**
     * @return void|\Zend\Db\Sql\Select
     */
    public function getSelect()
    {
        if (!$this->isInitialized) {
            $this->initialize();
        }
        $this->sql->select();
    }
 
    /**
     * Select
     *
     * @param Where|\Closure|string|array $where
     * @return ResultSet
     */
    public function select($where = null)
    {
        if (!$this->isInitialized) {
            $this->initialize();
        }
 
        $select = $this->sql->select();
 
        if ($where instanceof \Closure) {
            $where($select);
        } elseif ($where !== null) {
            $select->where($where);
        }
 
        return $this->selectWith($select);
    }
 
    /**
     * @param Select $select
     * @return null|ResultSetInterface
     * @throws \RuntimeException
     */
    public function selectWith(Select $select)
    {
        if (!$this->isInitialized) {
            $this->initialize();
        }
        return $this->executeSelect($select);
    }
 
    /**
     * @param Select $select
     * @return ResultSet
     * @throws \RuntimeException
     */
    protected function executeSelect(Select $select)
    {
        $statement = $this->adapter->createStatement();
        $select->prepareStatement($this->adapter, $statement);
        $result = $statement->execute();
 
        if (!empty($entity)) {
            $this->resultSet->setArrayObjectPrototype(new $this->entity());
            $resultSet = clone $this->resultSet;
            $resultSet->initialize($result);
 
            return $resultSet;
        }
 
        return $result;
    }
 
    /**
     * @param string $table
     */
    public function setTable($table)
    {
        $this->table = $table;
    }
 
    /**
     * @return string
     */
    public function getTable()
    {
        return $this->table;
    }
 
    /**
     * @param string $entity
     */
    public function setEntity($entity)
    {
        $this->entity = $entity;
    }
 
    /**
     * @return string
     */
    public function getEntity()
    {
        return $this->entity;
    }
 
    /**
     * @param \Zend\Db\Adapter\Adapter $adapter
     */
    public function setAdapter($adapter)
    {
        $this->adapter = $adapter;
    }
 
    /**
     * @return \Zend\Db\Adapter\Adapter
     */
    public function getAdapter()
    {
        return $this->adapter;
    }
}