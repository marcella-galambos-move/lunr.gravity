<?php

/**
 * MySQL/MariaDB database query builder class.
 *
 * PHP Version 5.3
 *
 * @category   Libraries
 * @package    DataAccess
 * @subpackage Libraries
 * @author     Heinz Wiesinger <heinz@m2mobi.com>
 * @author     M2Mobi <info@m2mobi.com>
 */

namespace Lunr\Libraries\DataAccess;

/**
 * This is a SQL query builder class for generating queries
 * suitable for either MySQL or MariaDB.
 *
 * @category   Libraries
 * @package    DataAccess
 * @subpackage Libraries
 * @author     Heinz Wiesinger <heinz@m2mobi.com>
 * @author     Olivier Wizen <olivier@m2mobi.com>
 * @author     M2Mobi <info@m2mobi.com>
 */
class MySQLDMLQueryBuilder extends DatabaseDMLQueryBuilder
{

    /**
     * Reference to the MySQLConnection class.
     * @var MySQLConnection
     */
    protected $db;

    /**
     * Constructor.
     *
     * @param MySQLConnection &$db Reference to the MySQLConnection class.
     */
    public function __construct(&$db)
    {
        parent::__construct();

        $this->db =& $db;
    }

    /**
     * Destructor.
     */
    public function __destruct()
    {
        $this->db = NULL;

        parent::__destruct();
    }

    /**
     * Define and escape input as value.
     *
     * @param mixed  $value     Input
     * @param String $collation Collation name
     * @param String $charset   Charset name
     *
     * @return String $return Defined and escaped value
     */
    public function value($value, $collation = '', $charset = '')
    {
        return trim($charset . ' ' . $this->collate('\'' . $this->db->escape_string($value) . '\'', $collation));
    }

    /**
     * Define and escape input as a hexadecimal value.
     *
     * @param mixed  $value     Input
     * @param String $collation Collation name
     * @param String $charset   Charset name
     *
     * @return String $return Defined, escaped and unhexed value
     */
    public function hexvalue($value, $collation = '', $charset = '')
    {
        return trim($charset . ' ' . $this->collate('UNHEX(\'' . $this->db->escape_string($value) . '\')', $collation));
    }

    /**
     * Define and escape input as a hexadecimal value.
     *
     * @param mixed  $value     Input
     * @param String $match     Whether to match forward, backward or both
     * @param String $collation Collation name
     * @param String $charset   Charset name
     *
     * @return String $return Defined, escaped and unhexed value
     */
    public function likevalue($value, $match = 'both', $collation = '', $charset = '')
    {
        switch ($match)
        {
            case 'forward':
                $string = '\'' . $this->db->escape_string($value) . '%\'';
                break;
            case 'backward':
                $string = '\'%' . $this->db->escape_string($value) . '\'';
                break;
            case 'both':
            default:
                $string = '\'%' . $this->db->escape_string($value) . '%\'';
                break;
        }

        return trim($charset . ' ' . $this->collate($string, $collation));
    }

    /**
     * Define the mode of the SELECT clause.
     *
     * @param String $mode The select mode you want to use
     *
     * @return MySQLDMLQueryBuilder $self Self reference
     */
    public function select_mode($mode)
    {
        $mode = strtoupper($mode);

        switch ($mode)
        {
            case 'ALL':
            case 'DISTINCT':
            case 'DISTINCTROW':
                $this->select_mode['duplicates'] = $mode;
                break;
            case 'SQL_CACHE':
            case 'SQL_NO_CACHE':
                $this->select_mode['cache'] = $mode;
                break;
            case 'HIGH_PRIORITY':
            case 'STRAIGHT_JOIN':
            case 'SQL_SMALL_RESULT':
            case 'SQL_BIG_RESULT':
            case 'SQL_BUFFER_RESULT':
            case 'SQL_CALC_FOUND_ROWS':
                $this->select_mode[] = $mode;
            default:
                break;
        }

        return $this;
    }

    /**
     * Define a SELECT clause.
     *
     * @param String $select The columns to select
     * @param String $escape Whether to escape the select statement or not.
     *                       Default to "TRUE"
     *
     * @return MySQLDMLQueryBuilder $self Self reference
     */
    public function select($select, $escape = TRUE)
    {
        $this->sql_select($select, $escape);
        return $this;
    }

    /**
     * Define a SELECT clause, converting the column data to HEX values.
     *
     * If no alias name is specified the original column name minus
     * the surrounding HEX() is taken.
     *
     * @param String $select The columns to select
     * @param String $escape Whether to escape the select statement or not.
     *                       Default to "TRUE"
     *
     * @return MySQLDMLQueryBuilder $self Self reference
     */
    public function select_hex($select, $escape = TRUE)
    {
        $this->sql_select($select, $escape, TRUE);
        return $this;
    }

    /**
     * Define FROM clause of the SQL statement.
     *
     * @param String $table Table name
     *
     * @return MySQLDMLQueryBuilder $self Self reference
     */
    public function from($table)
    {
        $this->sql_from($table);
        return $this;
    }

    /**
     * Define WHERE clause of the SQL statement.
     *
     * @param String $left     Left expression
     * @param String $right    Right expression
     * @param String $operator Comparison operator
     *
     * @return MySQLDMLQueryBuilder $self Self reference
     */
    public function where($left, $right, $operator = '=')
    {
        $this->sql_condition($left, $right, $operator);
        return $this;
    }

    /**
     * Define WHERE clause with LIKE comparator of the SQL statement.
     *
     * @param String $left   Left expression
     * @param String $right  Right expression
     * @param String $negate Whether to negate the comparison or not
     *
     * @return MySQLDMLQueryBuilder $self Self reference
     */
    public function where_like($left, $right, $negate = FALSE)
    {
        $operator = ($negate === FALSE) ? 'LIKE' : 'NOT LIKE';
        $this->sql_condition($left, $right, $operator);
        return $this;
    }

    /**
     * Define HAVING clause of the SQL statement.
     *
     * @param String $left     Left expression
     * @param String $right    Right expression
     * @param String $operator Comparison operator
     *
     * @return MySQLDMLQueryBuilder $self Self reference
     */
    public function having($left, $right, $operator = '=')
    {
        $this->sql_condition($left, $right, $operator, FALSE);
        return $this;
    }

    /**
     * Define WHERE clause with LIKE comparator of the SQL statement.
     *
     * @param String $left   Left expression
     * @param String $right  Right expression
     * @param String $negate Whether to negate the comparison or not
     *
     * @return MySQLDMLQueryBuilder $self Self reference
     */
    public function having_like($left, $right, $negate = FALSE)
    {
        $operator = ($negate === FALSE) ? 'LIKE' : 'NOT LIKE';
        $this->sql_condition($left, $right, $operator, FALSE);
        return $this;
    }

    /**
     * Define ORDER BY clause in the SQL statement
     *
     * @param String  $expr Expression to order by
     * @param Boolean $asc  Order ASCending/TRUE or DESCending/FALSE
     *
     * @return MySQLDMLQueryBuilder $self Self reference
     */
    public function order_by($expr, $asc = TRUE)
    {
        $this->sql_order_by($expr, $asc);
        return $this;
    }

    /**
     * Set logical connector 'AND'.
     *
     * @return MySQLDMLQueryBuilder $self Self reference
     */
    public function sql_and()
    {
        $this->sql_connector('AND');
        return $this;
    }

    /**
     * Set logical connector 'OR'.
     *
     * @return MySQLDMLQueryBuilder $self Self reference
     */
    public function sql_or()
    {
        $this->sql_connector('OR');
        return $this;
    }

    /**
     * Set logical connector 'XOR'.
     *
     * @return MySQLDMLQueryBuilder $self Self reference
     */
    public function sql_xor()
    {
        $this->sql_connector('XOR');
        return $this;
    }

}

?>
