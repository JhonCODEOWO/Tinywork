<?php 

namespace Routes;

use Core\UploadedFile;

class Request {
    /** Array with all url params in the request */
    private array $urlParams = [];
    /** Array with all query params in the request */
    private array $queryParams = [];
    /** Request method  */
    private string $method = '';
    /** Body request */
    private array $body = [];
    private array $files = [];

    public function __construct(array $args, ?array $files)
    {
        $this->urlParams = $args["urlParams"] ?? [];
        $this->queryParams = $args["queryParams"] ?? [];
        $this->method = $args['method'] ?? '';
        $this->body = $args["body"] ?? [];
        $this->files = $this->toUploadedFilesArray($files) ?? [];
    }

    // =========================
    // URL PARAMS
    // =========================

    public function getUrlParamValue(string $key): string|null
    {
        return $this->urlParams[$key] ?? null;
    }

    public function urlParams(): array
    {
        return $this->urlParams;
    }

    // =========================
    // QUERY PARAMS
    // =========================

    public function getQueryParam(string $key): string|null
    {
        return $this->queryParams[$key] ?? null;
    }

    public function queryParams(): array
    {
        return $this->queryParams;
    }

    // =========================
    // BODY
    // =========================

        
    /**
     * 
     *  @deprecated This method doesn't provide true values since class Request supports initialized values based on a schema provided.
     * @param  string $key The input name stored as key inside body property
     * @return mixed The value of the input name as key inside body property.
     */
    public function getBodyValue(string $key): mixed
    {
        return $this->body[$key] ?? null;
    }
    
    /**
     *  normalizes the body request using the schema provided.
     *
     * @param  array $schema The schema to normalize the output with initial values if any of them doesn't exists inside body property yet.
     * @return array Normalized array with values and keys provided in schema arg.
     */
    public function getBody(array $schema = []): array
    {
        return $this->normalizeBodyEntries($schema, $this->body);
    }

    public function body(): array {
        return array_merge($this->getBody(), ["files" => $this->files]);
    }

    // =========================
    // METHOD
    // =========================

    public function method(): string
    {
        return $this->method;
    }

    // =========================
    // HELPERS
    // =========================

        
    /**
     * normalizeBodyEntries
     *  Normalizes the body property based on the schema provided.
     *
     *  @todo  Add path searching feature to support: name[user], name[number] structures inside body.
     * @param  array $schema A array schema with keys and values that should exists in the body even they aren't initialized in constructor.
     * @param  array $unnormalizedBody A array with values previously not normalized.
     * @return array A new array with schema values incrusted if any of them doesn't exists in `$unnormalizedBody` arg.
     * 
     * 
     */
    private function normalizeBodyEntries(array $schema, array $unnormalizedBody) : array{
        $normalizedBody = $unnormalizedBody;
        
        foreach ($schema as $inputName => $value) {
            if(array_key_exists($inputName, $unnormalizedBody)) continue;

            $normalizedBody[$inputName] = $value;
        }


        return $normalizedBody;
    }

    public function all(): array
    {
        return [
            'urlParams' => $this->urlParams,
            'queryParams' => $this->queryParams,
            'body' => $this->body,
        ];
    }

    public function getFile(string $fileInputName): array{
        return $this->files[$fileInputName];
    }

    public function toUploadedFilesArray(array $files): array {
        $uploadedFiles = [];
        foreach ($files as $inputFieldName => $filesData) {
            if(!is_array($filesData['name'])){
                $uploadedFiles[$inputFieldName][] = new UploadedFile(
                    $filesData['name'],
                    $filesData['tmp_name'],
                    $filesData['error'],
                    $filesData['type'],
                );
                break;
            };
            
            foreach ($filesData['name'] as $index => $name) {
                $uploadedFiles[$inputFieldName][$index] = new UploadedFile(
                    $name,
                    $filesData['tmp_name'][$index],
                    $filesData['error'][$index],
                    $filesData['type'][$index],
                );
            }
        }
        return $uploadedFiles;
    }
}