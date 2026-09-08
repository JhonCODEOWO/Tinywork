<?php

namespace Core;

use Core\JustArray\JustArray;
use Error;
use Exception;

class Validator {
    /**
     *  All data to be validated where `key` is the field name and `value` the content to validate
     */
    private array $data = [];
    /**
     *  Validation rules to evaluate each value in `$data` property where `key` is the field name and `value`
     *  a array of associative arrays with `name`, `value` keys where `name` value contains the name of a rule
     *  and `value` the params to include in each rule.
     * @example
     *  [
     *      "fullname" => [
     *          ["name"=>'required', "value" => null],
     *          ["name"=>'minLength', "value" => "15"],
     *       ],
     *      "phone_number" => [
     *          ["name"=>'required', "value" => null],
     *          ["name"=>'minLength', "value" => "10"],
     *       ],
     *  ]
     */
    private array $validationRules = [];
    /**
     * A fresh instance of Errors to add every error of Validator instance.
     */
    protected Errors $errors;
    /**
     *  All error messages to use for every rule available to evaluate in this class
     *  if you need add more edit config/ValidationMessages.php file or change the file path in constructor.
     */
    protected array $errorMessages = [];

    public function __construct(array $data, ?array $validationRules = [])
    {
        $this->data = $data;
        $this->errors = new Errors();
        $this->validationRules = $this->mapInputValidationRules($validationRules);
        $this->errorMessages = include __DIR__ .'/../config/ValidationMessages.php';
    }
    
    /**
     *  Transform the array input from constructor in a new array with the `$validationRules` property array format.
     *
     * @param  mixed $inputValidationRules
     * @return array
     */
    private function mapInputValidationRules(array $inputValidationRules) : array{
        $validRules = [];
        foreach ($inputValidationRules as $inputName => $stringRules) {
            //Separate each rule
            $typedRules = explode('|', $stringRules);

            //Add every type rule group by input name with params.
            foreach ($typedRules as $index => $stringRule) {
                $ruleSegments = explode(':', $stringRule);
                $ruleName = $ruleSegments[0];
                $params = $ruleSegments[1] ?? null;

                $validRules[$inputName][] = [
                    "name" => $ruleName,
                    "value" => $params,
                ];
            }
        }
        return $validRules;
    }
    
    /**
     *  Checks if the value passed has a valid value and is filled with something.
     *
     * @param  mixed $input The value to check.
     * @param  mixed $param
     * @return bool
     */
    public function required(mixed $input, $param = null): bool{
        if(is_array($input)) return count($input) != 0;
        return (isset($input) && $input != null && $input != "");
    }
    
    /**
     * requiredIf Indicates if a field should be evaluated as required if another field has a specific value.
     *
     * @param  mixed $inputValue The actual value from the field to apply this rule.
     * @param  mixed $params The rules to apply successfully the rule, it should be: `fieldName, valueToExpect`
     * @return bool
     */
    public function requiredIf(mixed $inputValue, mixed $params): bool{
        [$expectedField, $expectedFieldValue]= explode(',', $params); //Get parameters from rule

        $fieldValue = $this->getInputValue($expectedField); //Get actual value from the field requested.

        if($fieldValue === $expectedFieldValue) {
            return $this->required($inputValue);
        }

        return true; 
    }
    
    /**
     *  Confirms if the inputValue is the same in the param input name provided
     *
     * @param  mixed $inputValue Input value of the field where `confirmed` rule is typed.
     * @param  mixed $confirmationPathField A string with a field name path to be compared.
     * @return bool
     */
    public function confirmed(mixed $inputValue, string $confirmationPathField) : bool{
        $anotherFieldValue = $this->getInputValue($confirmationPathField);
        return $inputValue === $anotherFieldValue;
    }
    
    /**
     *  Checks if the input file contains a valid file instance and is a valid uploaded file.
     *
     * @param  mixed $array The array with all UploadedFile instances.
     * @param  mixed $param Rule param, not needed.
     * @return bool
     */
    public function file(array $array, $param = null) : bool{
        if(count($array) > 1) throw new Error("You are using rule file in a multiple file field, use files instead.");
        $file = $array[0];
        return $file instanceof UploadedFile && is_uploaded_file($file->getTmpName());
    }
    
    /**
     *  Checks the size of uploaded files are valid in the range defined by the param of the rule.
     *
     * @param  mixed $files
     * @param  mixed $param
     * @return bool
     */
    public function maxSize(mixed $files, $param = null): bool{
        $state = true;
        $bytes = $param * 1048576;
        foreach ($files as $file) {
            if(!($file instanceof UploadedFile)) continue;
            
            if($file->getSize() > $bytes) {
                $state = false;
                break;
            }
        }

        return $state;
    }

    //TODO: Validator of multiple files.
    public function min(mixed $input, mixed $minValue): bool{
        return ($input >= $minValue);
    }
    
    /**
     * minLength
     * Checks if a input field value has te min length specified.
     *
     * @param  mixed $input
     * @param  mixed $length
     * @return bool
     */
    public function minLength(mixed $input, string $length): bool {
        $type = gettype($input);
        switch ($type) {
            case 'array':
                return (count($input) >= $length);
            
            default:
                return (strlen($input) >= $length);
        }
    }

    public function email(mixed $inputValue, $params){
        return filter_var($inputValue, FILTER_VALIDATE_EMAIL);
    }
    
        /**
     *  Checks if a value already exists in DB.
     *
     * @param mixed $inputValue Value from a field
     * @param mixed $params A string with `tableName,columnToCheck,ignoreColumn,ignoreValue` syntax.
     * @return bool
     */
    public function unique(mixed $inputValue, mixed $params): bool{
        $params = explode(',', $params);
        [$tableName, $columnToCheck, $ignoreColumn,$ignoreValue] = array_pad($params, 4, null);

        $db = Database::getDb();

        $sqlSentence = "SELECT * FROM $tableName WHERE $columnToCheck = ?";
        
        //TODO: Check inputValue type and ignoreValue to append them
        $types = "s";

        if ($ignoreValue != null && $ignoreColumn != null) {
            $sqlSentence .= " AND $ignoreColumn != ?";
            $types .= "s";
        }

        $sqlSentence .= " LIMIT 1";
        
        $stmt = $db->prepare($sqlSentence);

        //Execute operations
        ($ignoreValue != null && $ignoreColumn != null)?
            $stmt->bind_param($types, $inputValue, $ignoreValue)
            :
            $stmt->bind_param($types, $inputValue);

        $stmt->execute();
        $result = $stmt->get_result();

        return ($result->num_rows == 0);
    }

    /**
     *  Checks if the inputValue has a already record in the table checking if the inputValue match in the column specified.
     *
     * @param mixed $inputValue The input value.
     * @param mixed $params A array with expected $tableName and $columnToCheck values.
     * @return bool `True` if exists a record in DB `false` otherwise.
     */
    public function exists(mixed $inputValue, mixed $params){
        [$tableName, $columnToCheck] = explode(',',$params);
        
        return $this->checkIfExists($tableName, $columnToCheck, safe($inputValue));
    }
    
    /**
     *  Exec validation rules of the validator instance for each field value.
     *
     * @return Errors A errors instance to interact with them.
     */
    public function validate() : Errors{
        foreach ($this->validationRules as $field => $rules) {
            foreach ($rules as $index => $rule) {
                $inputValue = $this->getInputValue($field); //Input value from data.

                //Rule info for each Input registered.
                $ruleFnName = $rule['name'];
                $ruleParam = $rule['value'];
                
                if(!method_exists($this, $ruleFnName)) throw new Exception("Validation rule $ruleFnName doesn't exists yet");

                $result = $this->$ruleFnName($inputValue, $ruleParam);

                if(!$result){
                    $this->errors->add($this->errorValidation($field, $ruleFnName, $ruleParam), $field);
                }
            }
        }
        return $this->errors;
    }
    
    /**
     *  Pick a error message from the current ValidationErrors.php file and returns the formatted string
     *  with field name and the needed value.
     *
     * @param  string $field
     * @param  string $rule
     * @param  string | null $paramVal
     * @return string The error validation rule.
     */
    private function errorValidation(string $field, string $rule, string | null $paramVal): string{
        $errorMessage = $this->errorMessages[$rule] ?? null;

        return str_replace(
            [":field", ":value"],
            [$field, $paramVal],
            $errorMessage
        );
    }

        
    /**
     *  Retrieves a value based on the arg provided
     * 
     *  inputs: files.imagen | username | profile.name | users.0.name
     *  output: The respective value from the data.
     *
     * @param  string $path A Input name or a path to get values from associative arrays.
     * @return mixed
     */
    private function getInputValue(string $path) : mixed{
        return JustArray::find($this->data, $path);
    }

    public function invalid(): bool {
        return $this->errors->hasErrors();
    }

    /**
     *  Checks in DB if exists a record with the given $valueToCheck arg.
     *
     * @param string $tableName The name of a table in DB to use.
     * @param string $columnToCheckIn A valid column name 
     * @param mixed $valueToCheck The value to search using $tableName and $columnToCheck args.
     * @param array $opts An optional array with two values [ignoreColumn, ignoreValue] to implement an exception
     * for a specific field in ignoreColumn (use it to ignore a update operation for example).
     * @return bool `true` if a record exists `false` if a record doesn't exists or if the operation fails.
     */
    private function checkIfExists(string $tableName, string $columnToCheckIn, mixed $valueToCheck, array $opts = []): bool{
        [$ignoreValue, $ignoreColumn] = array_pad($opts, 2, null);
        $db = Database::getDb();

        $sqlSentence = "SELECT * FROM $tableName WHERE $columnToCheckIn = ?";
        
        //TODO: Check inputValue type and ignoreValue to append them
        $types = "s";

        if ($ignoreValue != null && $ignoreColumn != null) {
            $sqlSentence .= " AND $ignoreColumn != ?";
            $types .= "s";
        }

        $sqlSentence .= " LIMIT 1";
        
        $stmt = $db->prepare($sqlSentence);

        //Execute operations
        ($ignoreValue != null && $ignoreColumn != null)?
            $stmt->bind_param($types, $valueToCheck, $ignoreValue)
            :
            $stmt->bind_param($types, $valueToCheck);

        $stmt->execute();
        $result = $stmt->get_result();

        if(!$result) return false;

        return ($result->num_rows === 1);
    }
}