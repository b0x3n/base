<?php


/**********************************************************
 * 
 *  basemms/back/App/Models/DataModel.php
 * 
 *  Class for handling MySQL tables, provides create,
 *  read, use and delete methods:
 * 
 * 
 *      initialiseTable()       - Will create the table if
 *                                it doesn't exist.
 * 
 *      insertTableRow()        - Insert a row into the
 *                                current table.
 * 
 *      updateTableRow()        - Edit an existing row.
 * 
 *      findTablerow()          - Will return one or more
 *                                rows from the table.
 * 
 *      deleteTableRow()        - Delete a row from the
 *                                table.
 * 
 *  There's a few other methods but these are the most
 *  useful.
 * 
 *  M. Nealon, 2019.
 * 
 */


namespace App\Models;


use PDO;



/**
 *  These are used as keys to store and index the
 *  table layout.
 */
    define('DB_SQL_TYPE', '__db_sql_type');
    define('DB_SQL_SIZE', '__db_sql_size');
    define('DB_SQL_PRIMARY', '__db_sql_primary');
    define('DB_SQL_REQUIRED', '__db_sql_required');


/**
 *  Data types - if => true then a size parameter is
 *  required.
 */
    define('DB_SQL_TYPES', Array(
        "varchar" =>    true,
        "char" =>       true,
        "text" =>       true,
        "int" =>        false
    ));


Class DataModel
{

    protected           $tableName;
    protected           $tableColumns;

    protected           $dbHandler;

    protected           $errorMessage;


/**
 * 
 *  Doesn't do much - creates the connection. Expects
 *  the following to be defined:
 * 
 *      DB_HOST
 *      DB_NAME
 *      DB_USER
 *      DB_PSWD
 * 
 */
public function __construct()
    {
        $this->__setError();

        $this->tableName = false;
        $this->tableColumns = false;

        $_dsn = "mysql:dbhost=" . DB_HOST . ";dbname=" . DB_NAME;
        
        try
        {
            $_con = new PDO($_dsn, DB_USER, DB_PSWD);
        }
        catch (PDOException $ex)
        {
            $this->errorMessage = $ex->getMessage();
            return;
        }

        $this->dbHandler = $_con;
    }


/**
 * 
 *  Sets the errorMessage and returns false.
 * 
 * @param errorMessage
 *  The error message to be stored.
 * 
 * @return 
 *  Always returns false.
 * 
 */
private function __setError($errorMessage = false)
    {
        $this->errorMessage = $errorMessage;
        return false;
    }


/**
 * 
 *  Returns false if no errorMessage exists or
 *  returns the errorMessage, otherwise.
 * 
 * @param reportError
 *  If true, any existing error message will be
 *  echo'd.
 * 
 * @return
 *  Returns errorMessage, which will be either
 *  false (no errors) or return an error message.
 * 
 */
public function isError($reportError = false)
    {
        if ($this->errorMessage !== false)
        {
            if ($reportError !== false)
                echo $this->errorMessage . PHP_EOL;
        }

        return $this->errorMessage;
    }


/**
 * 
 *  Executes the gven query, doesn't return any
 *  results, this is used to create new tables,
 *  etc.
 * 
 * @param sqlQuery
 *  Query string to be executed.
 * 
 * @return
 *  True on success. On failure returns false and
 *  the errorMessage will be set.
 * 
 */
private function __execSQLQuery($sqlQuery)
    {
        try
        {
            $_stmt = $this->dbHandler->prepare($sqlQuery);
            $_stmt->execute();
        }
        catch (\PDOException $ex)
        {
            return $this->__setError($ex->getMessage());
        }

        return true;
    }


/**
 * 
 *  Creates the tableColumns array - this array
 *  essentially defines the table structure:
 * 
 *      [ 'column_id', 'type', size, 'OPTION' ... ]
 * 
 *  Where TYPE may be any of the types defined in
 *  DB_SQL_TYPES. Size must be an unquoted string of
 *  digits (if required).
 * 
 *  OPTION can be either of:
 * 
 *      primary     Make this column the primary key
 *      required    NOT NULL
 *      auto        AUTO_INCREMENT
 * 
 * @param tableName
 *  String containing the name of the table.
 * 
 * @param tableColumns
 *  Array describing the table structure.
 * 
 * @return
 *  Returns true on succes. On error, false will be
 *  returned and the errorMessage set.
 * 
 */
public function initialiseTable($tableName, $tableColumns)
    {
        $this->tableName = $tableName;
        $this->tableColumns = Array();

        $_sqlQuery = "";
        $_primaryKey = false;

        foreach ($tableColumns as $columnDefs)
        {
            $_sqlString = $this->getCreateTableString($columnDefs, $_primaryKey);

            if ($_sqlString === false)
                return false;

            if ($_sqlQuery === "")
                $_sqlQuery = $_sqlString;
            else
                $_sqlQuery .= "," . PHP_EOL . $_sqlString;
        }

        if ($_primaryKey !== false)
            $_sqlQuery .= "," . PHP_EOL . "PRIMARY KEY ($_primaryKey)";

        if ($this->tableExists($tableName) === false) {
            try
            {
                $this->dbHandler->query("CREATE TABLE $tableName (" . PHP_EOL . $_sqlQuery . PHP_EOL . ")");
                //echo "Created table $tableName: |$_sqlQuery|<br>\n";
                //die();
            }
            catch (PDOException $ex)
            {
                $this->__setError("Error in initialiseTable(): " . $ex->getMessage());
            }
        }
        return false;
    }


/**
 * 
 *  Check whether a table exists - returns true
 *  if the table does exist and false if it
 *  does not.
 * 
 * @param tableName
 *  Name of the table to check for.
 * 
 * @return
 *  Since a false indicates the table doesn't
 *  exist - the isError() method should be used by
 *  the caller to determine failure/success.
 * 
 */
public function tableExists($tableName)
    {
        $_sqlQuery = "SHOW TABLES LIKE '$tableName'";

        try
        {
            $_sqlResult = $this->dbHandler->query($_sqlQuery);

            if ($_sqlResult->rowCount() > 0)
                return true;
        }
        catch (PDOException $ex)
        {
            return $this->__setError("Error in tableExists(): " . $ex->getMessage());
        }
    
        return false;
    }


/**
 * 
 *  The initialiseTable method calls this to build
 *  SQL data definitions from the tableColumns
 *  array.
 * 
 *  It takes in an array like:
 * 
 *      [ 'id', 'int', 'primary', 'required', 'auto' ]
 * 
 *  And returns the SQL for the definition:
 * 
 *      id INT PRIMARY NOT NULL AUT_INCREMENT
 * 
 *  Which is used to build the query for creating the
 *  new table.
 * 
 * @param columnDefs
 *  Array defining a single column in the new table.
 * 
 * @param primaryKey
 *  Reference to a string - if this column is marked
 *  'primary' then the column name is returned here so
 *  the caller can add:
 * 
 *      PRIMARY KEY ($primaryKey)
 * 
 *  To the SQL.
 * 
 * @return
 *  True on success, false on a fail. On error the
 *  errorMessage will be set.
 * 
 */
public function getCreateTableString($columnDefs, &$primaryKey)
    {
    //  Record the SQL data type.
    //
        $this->tableColumns[$columnDefs[0]][DB_SQL_TYPE] = $columnDefs[1];

    //  This will return the data type definition without the
    //  size or any parameters (NOT NULL, AUTO_INCREMENT, etc).
    //
        if (($_sqlString = $this->__getSQLTypeDefinition($columnDefs)) === false)
            return false;

    //  Both methods will return false if the specified value
    //  isn't in the columnDefs.
    //
        $_sqlDataSize = $this->__getSQLDataSize($columnDefs);
        $_primaryKey = $this->__getSQLPrimaryKey($columnDefs);

    //  Does this data type require a size?
    //
        if (DB_SQL_TYPES[$columnDefs[1]] == true)
        {
            if ($_sqlDataSize === false)
                return $this->__setError("Error in getSQLString(): data type {$columnDefs[1]} requires a size parameter");
            
            $_sqlString .= "(" . $_sqlDataSize . ")";
        }

    //  __getSQLDataRequired will returna an empty string if
    //  columnDefs doesn't contain the 'required' field, otherwise
    //  it will return the "NOT NULL" SQL option.
    //
        $_sqlString .= $this->__getSQLDataRequired($columnDefs);

    //  Lastly - if 'auto' is set in the columnDefs then this
    //  column has the AUTO_INCREMENT option set.
    //
        if (array_search("auto", $columnDefs) !== false)
            $_sqlString .= " AUTO_INCREMENT";

        if ($_primaryKey !== false)
            $primaryKey = $_primaryKey;

        return $_sqlString;
    }


/**
 * 
 *  Grabs the first two tokens of the column
 *  definition. Token 0 should always be the
 *  colunm name. Token 1 should always be the
 *  type.
 * 
 *  Will return a string like:
 * 
 *      column_name type
 * 
 * @param columnDefs 
 *  Array defining the column.
 *
 * @return
 *  Returns the type definition string on success,
 *  returns false on failure. On failure, the
 *  errorMessage will be set.
 * 
 */
private function __getSQLTypeDefinition($columnDefs)
    {
        if (count($columnDefs) < 2)
            return $this->__setError("Error in getSQLString(): no column parameters");

        if (! isset(DB_SQL_TYPES[strtolower($columnDefs[1])]))
            return $this->__setError("Error in getSQLString(): invalid column type {$columnDefs[1]}");

        return $columnDefs[0] . " " . strtoupper($columnDefs[1]);
    }


/**
 * 
 *  Checks the column definition for the size
 *  property.
 * 
 *  Easy to identiy, it's never a quoted string and
 *  it's all digits.
 * 
 * @param columnDefs
 *  The array containing the column defninition.
 * 
 * @return
 *  Returns the size property if found in the array,
 *  otherwise returns false.
 * 
 */
private function __getSQLDataSize($columnDefs)
    {
        $this->tableColumns[$columnDefs[0]][DB_SQL_SIZE] = false;

        if (count($columnDefs) > 2)
        {
            if (preg_match('/^[0-9]+$/', $columnDefs[2], $match) == 1)
                $this->tableColumns[$columnDefs[0]][DB_SQL_SIZE] = $match[0];
        }

        return $this->tableColumns[$columnDefs[0]][DB_SQL_SIZE];
    }


/**
 * 
 *  Will check the given column definition array
 *  for the 'primary' keyword. If found, the column
 *  Id is returned - if not, then false is returned.
 * 
 * @param columnDefs
 *  Array defining the column.
 * 
 * @return
 *  Returns the primary key if found, otherwise
 *  returns false.
 * 
 */
private function __getSQLPrimaryKey($columnDefs)
    {
        $_primaryKey = false;

        if (array_search("primary", $columnDefs) !== false) {
            $_primaryKey = $columnDefs[0];
            $this->tableColumns[$columnDefs[0]][DB_SQL_PRIMARY] = true;
        }
        else
            $this->tableColumns[$columnDefs[0]][DB_SQL_PRIMARY] = false;

        return $_primaryKey;
    }


/**
 * 
 *  Will check the given column definition array for
 *  the 'required' keyword. If found, the "NOT NULL"
 *  string is returned.
 * 
 *  If not found, then an empty string is returned.
 * 
 * @param columnDefs
 *  The array containing the column definition.
 * 
 * @return
 *  Will return "NOT SULL" is the array contains the
 *  'required' keyword, will return an empty string
 *  otherwise.
 * 
 */
private function __getSQLDataRequired($columnDefs)
    {
        $_sqlString = "";

        if (array_search("required", $columnDefs) !== false) {
            $_sqlString = " NOT NULL";
            $this->tableColumns[$columnDefs[0]][DB_SQL_REQUIRED] = true;
        }
        else
            $this->tableColumns[$columnDefs[0]][DB_SQL_REQUIRED] = false;

        return $_sqlString;
    }


/**
 * 
 *  When we use a the update or where method, we can
 *  pass an array containing search criteria.
 * 
 *  More specifically, we can pass arrays of arrays to
 *  describe relative complex relationships:
 * 
 *      [ 'id', '=', 'this', 'OR', 'id', '=', 'that' ]
 * 
 *  Resolves to:
 * 
 *      (id = :this OR id == :that)
 * 
 *  We can also nest these structures:
 * 
 *      [[ 'a', '=', 'b'], 'OR', [ 'a', '=', 'z' ]]
 * 
 *  Returns:
 * 
 *      ((a = :b) OR (a = :z))
 * 
 *  This is done through recursion hence the reference
 *  paramenters.
 * 
 * @param newTableRow
 *  Array containing the new row information.
 * 
 * @param sqlExpression
 *  The output string - i.e ((a = :b) OR (a = :z)) is
 *  returned here.
 * 
 * @param exprParameters
 *  This is used to build the corresponding parameter
 *  list for the call to PDO execute().
 * 
 */
public function getSelectExpression($newTableRow, &$sqlExpression, &$exprParameters)
    {
        if ($sqlExpression === false)
            $sqlExpression = "";

        if ($exprParameters === false)
            $exprParameters = Array();

        $_breakRecursion = false;
        $sqlExpression .= "(";

    //  Work backwards.
    //
        for ($column = (count($newTableRow) - 1); $column >= 0; $column--)
        {
        //  If an array is found, it's a recursive call.
        //
            if (is_array($newTableRow[$column]))
            {
            //  In this case an error has occured.
            //
                if (($_breakRecursion = $this->getSelectExpression(
                    $newTableRow[$column], $sqlExpression, $exprParameters
                )) === false)
                    break;
            }
            else
            {
                if ($newTableRow[$column] == "AND" || $newTableRow[$column] == "OR")
                {
                    if ($column == (count($newTableRow) - 1))
                        return $this->__serError("Error in getSelectExpression(): unexpected {$newTableRow[$column]} token");
                    $sqlExpression .= " {$newTableRow[$column]} ";
                    continue;
                }

                if ($this->buildExpressionString($newTableRow, $sqlExpression, $exprParameters, $column) === false)
                    return false;

                $column -= 2;
            }
        }

        $sqlExpression .= ")";

        if ($_breakRecursion === true)
            return false;

        return true;
    }


/**
 * 
 *  Where the getSelectExpression method will return
 *  resolve an expression, this method will retuns
 *  the individual expressions within the array.
 * 
 * @param columnData
 *  Passed on from the getSelectExpression method,
 *  array defining the column.
 * 
 * @param sqlExpression
 *  Passed on from the getSelectExpression method, 
 *  it's used to build the WHERE type clause.
 * 
 * @param exprParameters
 *  Passed in from the getSelectExpression method,
 *  this is used to accumulate the parameters for
 *  the WHERE clause.
 * 
 * @param column
 *  Current column/token position in the array.
 * 
 * @return
 *  True on success, on failure false is returned
 *  and the errorMessage is set.
 * 
 */
public function buildExpressionString($columnData, &$sqlExpression, &$exprParameters, $column)
    {
        if (($column - 2) < 0)
            return $this->__setError("Error in getSelectExpression(): too few parameters in expression");
        
        $_exprLValue = $columnData[($column - 2)];
        $_exprOperator = $columnData[($column - 1)];
        $_exprRValue = $columnData[$column];

        $sqlExpression .= "{$_exprLValue} {$_exprOperator} :wh_{$_exprLValue}";

        if (count($exprParameters) < 1) 
            $exprParameters["wh_" . $_exprLValue] = $_exprRValue;
        else 
            $exprParameters["wh_" . $_exprLValue] = $_exprRValue;

        return true;
    }


/**
 * 
 *  Validates new table rows.
 * 
 *  First - it will ensure that all of the column names
 *  in the new row definitions are valid (i.e they are
 *  in the tableColumns definitions table).
 * 
 *  Next, if isUpdate is false, it will ensure that all
 *  columns marked as 'required' in the tableColumns are
 *  present and correct.
 * 
 *  It also build the sqlParams as well as the VALUES
 *  string required for an insert.
 * 
 * @param newTableRow
 *  Array contianing new table row definition.
 * 
 * @param sqlParams
 *  Array for accumulating parameters for the sqlValues
 *  string.
 * 
 * @param sqlValues
 *  VALUES() string build for use with SQL UPDATE's.
 * 
 * @param isUpdate
 *  If we're updating an existing record we will want to
 *  set this to true. If this is false then any new
 *  records MUST contain any columns marked as required
 *  (NOT NULL)
 * 
 * @return
 *  True on success, false on failure and errorMessage
 *  will be set.
 * 
 */
public function validateTableRow($newTableRow, &$sqlParams, &$sqlValues, $isUpdate = false)
    {
    //  This loop ensures that all of the columns in
    //  newTableRow have a valid id.
    //
    //  If the column name/id exists there should be
    //  an entry in the tableColumns.
    //
        foreach ($newTableRow as $columnName=>$columnData)
        {
            if (! isset($this->tableColumns[$columnName]))
                return $this->__setError("validateTableRow(): column $columnName not defined in table schema");
    
            if ($sqlParams !== "")
                $sqlParams .= ", ";
                
            $sqlParams .= $columnName; 
        
            if ($sqlValues !== "")
                $sqlValues .= ", ";
                
            $sqlValues .= ":" . $columnName;
        }

    //  If an updat eis being done we want to skip the next
    //  check.
    //
        if ($isUpdate === true)
            return true;

    //  This loop ensures that all required fields in
    //  the table are present in newTableRow.
    //
        foreach ($this->tableColumns as $columnName=>$columnData)
        {
            if (isset($columnData[DB_SQL_REQUIRED]) && $columnData[DB_SQL_REQUIRED] === true) {
                if (isset($columnData[DB_SQL_PRIMARY]) && $columnData[DB_SQL_PRIMARY] === true)
                    continue;

                if (! isset($newTableRow[$columnName]))
                    return $this->__setError("validateTableRow(): column $columnName is a required field for this table");
            }
        }

        return true;
    }


/**
 * 
 *  Insert a new table row.
 * 
 * @param newTableRow
 *  Array contiang table structure definitons.
 * 
 * @return
 *  True on success, on failure false will be
 *  returned and errorMessage will be set.
 * 
 */
public function insertTableRow($newTableRow)
    {
        $_sqlParams = "";
        $_sqlValues = "";

        if ($this->validateTableRow(
            $newTableRow,
            $_sqlParams,
            $_sqlValues
        ) === false)
            return false;

        $_sqlQuery = "INSERT INTO " . $this->tableName . "(" . $_sqlParams . ") VALUES(" . $_sqlValues . ")";

        try
        {
            $_stmt = $this->dbHandler->prepare($_sqlQuery);
            $_stmt->execute($newTableRow);
        }
        catch (PDOException $ex)
        {
            return $this->__setError("insertTableRow(): " . $ex->getMessage());
        }

        return true;
    }


/**
 * 
 *  Update an existing record.
 * 
 *  The updateTableData array is an associative
 *  array stating the new columns and data to be
 *  stored in the record:
 * 
 *      [
 *          'column_name' => 'new data',
 *          'other_column' => 'more data'
 *      ]
 *
 *  The matchTableData parameter is a search
 *  (WHERE) type clause and is resolved by the
 *  getSelectExpression method.
 * 
 * @param updateTableData
 *  Array containing new data to be added to the
 *  record.
 * 
 * @param matchTableData
 *  WHERE type clause string.
 * 
 * @return
 *  True on success, on failure false is returned
 * `and errorMessage is set.
 * 
 */
public function updateTableRow($updateTableData, $matchTableData)
    {
        $_sqlExpression = false;
        $_exprParameters = Array();

        $_sqlSet = $this->getSQLDataSet($updateTableData, $_exprParameters);

        if ($_sqlSet === false)
            return false;

        if ($this->getSelectExpression($matchTableData, $_sqlExpression, $_exprParameters) === false)
            return false;

        $_sqlQuery = "UPDATE $this->tableName SET $_sqlSet WHERE $_sqlExpression";

        try
        {
            // echo "update query: $_sqlQuery<br";
            // print_r($_exprParameters);
            // die();
            $_stmt = $this->dbHandler->prepare($_sqlQuery);
            $_stmt->execute($_exprParameters);
        }
        catch (PDOException $ex)
        {
            die($ex->getMessage());
            return $this->__setError("Error in updateTableRow: " . $ex->getMessage());
        }

        return true;
    }


/**
 *
 *  Returns the string required for the SET in
 *  the UPDATE string.
 * 
 *  Example, if we do:
 * 
 *      updateTableRow([
 *          'id' => '1'
 *      ], [
 *          'id', '=', '0'
 *      ]);
 * 
 *  The SQL string we need is:
 * 
 *      UPDATE table SET id = :id WHERE(id = :id)
 * 
 *  The getSelectExpression and related method will
 *  return the WHERE part (id = :id) and order the
 *  parameters for that portion of the command.
 * 
 *  The SET portion of the string:
 * 
 *      id = :id
 * 
 *  If buld here, reason being if we are setting more
 *  than one field:
 * 
 *      id = :id, status = :status
 * 
 *  For example, we need to separate them with comma's,
 *  but the WHERE part we don't. Hence the separate
 *  method and parameters being constructed here.
 * 
 * @param tableData
 *  Array containing the new record data.
 * 
 * @param exprParameters
 *  Reference to an array where parameters are collected
 *  for this string.
 * 
 * @return
 *  Returns a string containing the SET portion of
 *  the SQL command.
 * 
 */
public function getSQLDataSet($tableData, &$exprParameters)
    {
        $_sqlSet = "";

        foreach ($tableData as $columnName=>$columnData)
        {
            if ($_sqlSet !== "")
                $_sqlSet .= ",";

            $_sqlSet .= "{$columnName} = :{$columnName}";
            $exprParameters[$columnName] = $columnData;
        }

        return $_sqlSet;
    }


/**
 * 
 * Method used to search tables.
 * 
 *  I hacked this together for the search function
 *  on bitraq.
 * 
 *  Not much to say - returns any rows that match
 *  the given criteria.
 * 
 * @param columnName
 *  Column to search for matches.
 * 
 * @param searchQuery
 *  Query string to search for.
 * 
 * @return
 *  Array of rows (if any) where a match was found.
 * 
 */
public function getRowsLike($columnName, $searchQuery)
    {
        $_sqlString = "SELECT * FROM {$this->tableName} WHERE $columnName LIKE ?";
        
        try
        {
            $_stmt = $this->dbHandler->prepare($_sqlString);
            $_stmt->execute(
                Array(
                    "%" . $searchQuery . "%"
                )
            );

            return $_stmt->fetchAll();
        }
        catch (PDOException $ex)
        {
            $this->__setError("Error in getRowsLike(): " . $ex->getMessage());
        }

        return false;
    }


/**
 * 
 *  This method will return any rows who meet the
 *  criteria specified by the matchTableData array.
 * 
 *  If the matchTableData array parameter is false,
 *  then all rows in the table are returned.
 * 
 *  Uses the setSelectExpression method to resolve
 *  the expression array.
 * 
 * @param matchTableData
 *  Array outlining match criteria - see comments
 *  for the getSelectExpression method.
 * 
 * @return
 *  Returns the row or rows selected (if any). On
 *  error, false is returned and the errorMessage
 *  is set.
 * 
 */
public function findTableRow($matchTableData = false)
    {
        $_sqlExpression = false;
        $_exprParameters = false;

        if ($matchTableData !== false)
        {
            if ($this->getSelectExpression(
                $matchTableData, $_sqlExpression, $_exprParameters
            ) === false)
                return false;
            $_sqlExpression = "WHERE " . $_sqlExpression;
        }
        else
            $_sqlExpression = "";

        $_sqlQuery = "SELECT * FROM {$this->tableName} $_sqlExpression";

        try
        {
            if ($matchTableData === false)
                $_stmt = $this->dbHandler->query($_sqlQuery);
            else
            {
                $_stmt = $this->dbHandler->prepare($_sqlQuery);
                $_stmt->execute($_exprParameters);
            }
        }
        catch (PDOException $ex)
        {
            $this->__setError("Error in findTableRow(): " . $ex->getMessage());
        }
        
        return $_stmt->fetchAll(PDO::FETCH_ASSOC);
    }


/**
 * 
 *  Deletes a row from the table. Again, the
 *  $matchTableData is an array describing a
 *  WHERE clause - getSelectExpression will
 *  be used to resolve.
 * 
 * @param matchTableData
 *  Array desribing the match criteria for the
 *  DELETE.
 * 
 * @return
 *  True on success, otherwise will return
 *  false and the errorMessage will be set.
 * 
 */
public function deleteTableRow($matchTableData)
    {
        $_sqlExpression = false;
        $_exprParameters = false;

        if ($this->getSelectExpression(
            $matchTableData, $_sqlExpression, $_exprParameters
        ) === false)
            return false;

        $_sqlExpression = "WHERE " . $_sqlExpression;
        $_sqlQuery = "DELETE FROM {$this->tableName} {$_sqlExpression}";

        try
        {
            $_stmt = $this->dbHandler->prepare($_sqlQuery);
            $_stmt->execute($_exprParameters);
        }
        catch (PDOException $ex)
        {
            $this->__setError("Error in deleteTableRow(): " . $ex->getMessage());
        }

        return true;
    }


/** 
 *
 *  Will return all of the rows in the table.
 * 
 * @return
 * array containing the selected rows.
 *
 */
public function getAll()
    {
        try
        {
            $_stmt = $this->dbHandler->prepare("SELECT * FROM {$this->tableName}");
            $_stmt->execute();
        }
        catch (PDOException $ex)
        {
            $this->__setError("Error in getAll(): " . $ex->getMessage());
            return false;
        }

        return $_stmt->fetchAll(PDO::FETCH_ASSOC);
    }

}

