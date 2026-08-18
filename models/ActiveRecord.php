<?php

namespace Models;
use mysqli;
use ReflectionProperty;

class ActiveRecord {
    /**
     * @var mysqli global property to store the DB connection.
     */
    protected static mysqli $db;
    /**
     * @var array property to override with all column names of the table for every children class
     */
    protected static array $columns = [];
    /**
     * @var string  property to override in every children class with the table name.
     */
    protected static string $table = '';

    //Errors property
    protected static array $errors = [];

    /**
     * @var string The id column name, the default value is id override it if your table has a personalized column name for the primary key
     */
    protected static string $idName = 'id';

    //Definir la conexión a la base de datos
    public static function setDB(mysqli $database){
        self::$db = $database;
    }

        
    /**
     * rehydrate
     * Rehydrate the object properties values with a new fresh data
     *
     * @param  array $data - A associative array where key is a property name of the object and value the new fresh value to replace.
     * @return void
     */
    public function rehydrate(array $data){
        foreach ($data as $key => $value) {
            if(!property_exists($this, $key) || is_null($value)) continue;

            $castedValue = $this->castProperty($key, $value);

            if($castedValue === false || $castedValue === null) continue;

            $this->$key = $castedValue;
        }
    }

    protected function castProperty(string $key, mixed $value) : mixed {
        $property = new ReflectionProperty($this, $key);
            $type = $property->getType()?->getName();
            $filtered = false;

            switch ($type) {
                case 'string':
                    $filtered = (string) $value;
                    break;
                case 'int':
                    $filtered = filter_var($value, FILTER_VALIDATE_INT);
                    break;
                case 'float':
                    $filtered = filter_var($value, FILTER_VALIDATE_FLOAT);
                    break;
                case 'bool':
                    $filtered = filter_var($value, FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE);
                    break;
                default:
                    # code...
                    break;
            }

            return $filtered;
    }


    /**
     * Update a record in DB
     *
     * @return static | null The element in DB successfully updated null otherwise
     */
    public function update(): static | null{
        $attributes = $this->sanitizeAtributos();
        $values = [];

        foreach ($attributes as $key => $value) {
            $values[] = "$key = '$value'";
        }
        $query = "UPDATE " . static::$table . " SET " . join(', ', $values) . "WHERE " . static::$idName . " = " . $this->{static::$idName};

        $result = self::$db->query($query);
        if(!$result) return null;
        
        return $this;
    }

    public function guardar(){
        //Sanitize data
        $attributes = $this->sanitizeAtributos();

        $plainCols = join(', ', array_keys($attributes));
        $plainValues = join("', '", array_values($attributes));
        
        $query = "INSERT INTO " . static::$table . " ($plainCols) VALUES ( '$plainValues' )";

        //Exec operations using the static connection.
        $result = self::$db->query($query);
        return $result;
    }
    
    /**
     *  Delete the record from database
     *
     * @return bool
     */
    public function delete(): bool{
        $idName = static::$idName;

        $query = "DELETE FROM " . static::$table . " WHERE ". static::$idName ." = " . self::$db->escape_string($this->$idName) . " LIMIT 1";

        $result = self::$db->query($query);

        return $result;
    }

    /**
     * Iterate each class attribute based on columns static property and assign it in a new associative array with sanitized values.
     */
    public function atributos(): array{
        $attributes = array();

        foreach (static::$columns as $column) {
            if($column === static::$idName) continue;
            $attributes[$column] = $this->$column;
        }

        return $attributes;
    }

    /**
     * Sanitize every attribute in the current class.
     */
    public function sanitizeAtributos(): array{
        $attributes = $this->atributos();
        $sanitized = [];
        foreach ($attributes as $key => $value) {
            $sanitized[$key] = self::$db->escape_string($value);
        }

        return $sanitized;
    }

    /**
     * Getter of children errors class.
     */
    public static function getErrors(){
        return static::$errors;
    }

    /**
     *  Execute validation rules and set the errors for every children class.
     */
    public function validate(){
        static::$errors = [];
        return static::$errors;
    }
    
    /**
     * Get all records in DB
     *
     * @return array
     */
    public static function all(): array{
        $query = "SELECT * FROM " . static::$table;
        return self::execQuery($query);
    }

    public static function limit(int $limit = 0): array{
        $query = "SELECT * FROM " . static::$table . " LIMIT $limit";

        return static::execQuery($query);
    }

    /**
     * Get a specific record by id
     */
    public static function find(int $id): static | null {
        $query = "SELECT * FROM " . static::$table .  " WHERE " . static::$idName . " = $id";

        $result = self::execQuery($query);

        return $result[0];
    }

    public static function execQuery(string $query): array{
        $result = self::$db->query($query);

        $records = [];
        while ($row = $result->fetch_assoc()) {
            $records[] = self::createObject($row);
        }

        $result->close();

        return $records;
    }

    /**
     * Make a new hydrated object by the given associative array.
     */
    protected static function createObject(array $registro): static {
        $objeto = new static;

        foreach ($registro as $key => $value) {
            if(!property_exists($objeto, $key)) continue;
            $objeto->$key = $value;
        }

        return $objeto;
    }
}