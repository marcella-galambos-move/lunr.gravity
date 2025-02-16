<?php

/**
 * Abstract database connection class.
 *
 * SPDX-FileCopyrightText: Copyright 2012 M2mobi B.V., Amsterdam, The Netherlands
 * SPDX-FileCopyrightText: Copyright 2022 Move Agency Group B.V., Zwolle, The Netherlands
 * SPDX-License-Identifier: MIT
 */

namespace Lunr\Gravity;

use Lunr\Core\Configuration;
use Psr\Log\LoggerInterface;

/**
 * This class defines abstract database access.
 */
abstract class DatabaseConnection implements DatabaseStringEscaperInterface
{

    /**
     * Connection status
     * @var bool
     */
    protected bool $connected;

    /**
     * Whether there's write access to the database or not
     * @var bool
     */
    protected bool $readonly;

    /**
     * Shared instance of the Configuration class
     * @var Configuration
     */
    protected Configuration $configuration;

    /**
     * Shared instance of a Logger class
     * @var LoggerInterface
     */
    protected LoggerInterface $logger;

    /**
     * Constructor.
     *
     * @param Configuration   $configuration Shared instance of the configuration class
     * @param LoggerInterface $logger        Shared instance of a logger class
     */
    public function __construct(Configuration $configuration, LoggerInterface $logger)
    {
        $this->connected = FALSE;
        $this->readonly  = FALSE;

        $this->configuration = $configuration;
        $this->logger        = $logger;
    }

    /**
     * Destructor.
     */
    public function __destruct()
    {
        unset($this->configuration);
        unset($this->logger);
        unset($this->readonly);
        unset($this->connected);
    }

    /**
     * Toggle readonly flag on the connection.
     *
     * @param bool $switch Whether to make the connection readonly or not
     *
     * @return void
     */
    public function set_readonly(bool $switch): void
    {
        $this->readonly = $switch;
    }

    /**
     * Establishes a connection to the defined database server.
     *
     * @return void
     */
    abstract public function connect(): void;

    /**
     * Disconnects from database server.
     *
     * @return void
     */
    abstract public function disconnect(): void;

    /**
     * Change the default database for the current connection.
     *
     * @param string $db New default database
     *
     * @return bool True on success, False on Failure
     */
    abstract public function change_database(string $db): bool;

    /**
     * Get the name of the database we're currently connected to.
     *
     * @return string Database name
     */
    abstract public function get_database(): string;

    /**
     * Return a new instance of a QueryBuilder object.
     *
     * @return DatabaseDMLQueryBuilder $builder New DatabaseDMLQueryBuilder object instance
     */
    abstract public function get_new_dml_query_builder_object();

    /**
     * Return a new instance of a QueryEscaper object.
     *
     * @return DatabaseQueryEscaper New DatabaseQueryEscaper object instance
     */
    abstract public function get_query_escaper_object(): DatabaseQueryEscaper;

    /**
     * Escape a string to be used in a SQL query.
     *
     * @param string $string The string to escape
     *
     * @return string The escaped string
     */
    abstract public function escape_string(string $string): string;

    /**
     * Run a SQL query.
     *
     * @param string $sqlQuery The SQL query to run on the database
     *
     * @return DatabaseQueryResultInterface $result Query Result
     */
    abstract public function query(string $sqlQuery): DatabaseQueryResultInterface;

    /**
     * Begin a transaction.
     *
     * @return bool
     */
    abstract public function begin_transaction(): bool;

    /**
     * Commit a transaction.
     *
     * @return bool
     */
    abstract public function commit(): bool;

    /**
     * Roll back a transaction.
     *
     * @return bool
     */
    abstract public function rollback(): bool;

    /**
     * Ends a transaction.
     *
     * @return bool
     */
    abstract public function end_transaction(): bool;

    /**
     * Run OPTIMIZE TABLE on a table.
     *
     * @param string $table The table to defragment.
     *
     * @return void
     */
    abstract public function defragment(string $table): void;

}

?>
