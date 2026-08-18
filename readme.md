# Tinywork
Your small and friendly framework for small projects.

## What is Tinywork?
Tiny work is a framework focused in the main important
things of a PHP project with the objetive of give you a structure and main features quickly to start to code.

## Main features
1. Supports send emails using PHPMailer.
2. Request data management.
3. Form validator API.
4. MVC structure.
5. ORM based on ActiveRecord 
6. Middleware protection support for every route stored.
7. Array API with navigation based on dot notation to manage every array structure in the project.
8. Js & Css files managed by Vite.

## How start to using it
- Clone this repo wherever you want using `git clone`
- Before start execute npm, pnpm etc. `install` command and `composer install` to prepare all dependencies needed.
- Copy .env.example file without .example and fill every entry, is important fill always the database connection credentials.
- Execute `php -S localhost:8000` inside public/ folder.
- Execute `npm run dev` to watch for file change and reflect them every time in the build file.

# How to work with Request class
Every time you register a route, a instance of Request class is passed by args, you can use it only declaring it in the args of the action controller function or callback function like this

```
$router->('/', function(Request $request) {
    //Now you can work with all Request api methods.
    var_dump($request->body(););
})
```

## Getting query params and url params.
Of course you can register a route with url params, you can do this using **:urlparameter** syntax

```
$router->('/product/:id', function(Request $request) {
    
})
```
To get a url parameter value you should use getUrlParam() api Request method.

```
$req->getUrlParamValue('id')
```

If you want get a queryParam, then you should use getQueryParam() api Request method.

```
$request->getQueryParam('orderBy')
```

if the query param doesn't exists it will returns null.

## Retrieving body data.
When a route is post the Request class will store all form data including files uploaded using the name value of the form typed in HTML.

### Handling form data.
To retrieve body data you can use the method getBody() which returns a sanitized form with all data passed by a post request with two keys `body` and `files`.

If you want to initialize some of them because one or more of them can be not included like: radio buttons, checkboxes etc. you can initialize them by passing a array with the value to include if one of them aren't present.

```
$body = $request->getBody();
```
if you want to access to every data you can use the API Class JustArray it give you the possibility to navigate into them using dot notation.

```
$userName = JustArray::find($body, 'body.user.name');
```
### Handling files uploaded.
You can access to every File uploaded by using getFile() api method or using JustArray::find() method too.

```
$body = $request->getBody();

$request->getFile('profile');
$uploadedPicture = JustArray::find($body, 'files.profile');

```

Then you can receive an array of UploadedFile instance objects or just one object if your input file is marked as non multiple.

# Validating a form  or array data
A API to validate array inputs is provided, you can use it creating a instance of Validator class where you should pass the array to validate and another array where ``key`` is the key name inside the array arg that contains the value to evaluate and the `value` is a string with every rule to evaluate separated by a | symbol.
```
$body = $request->getBody();

$validator = new Validator($body, [
    "name" => "required",
    "email" => "required|email",
]);
```
When you finished setting up the validation rules you can use validate() function to evaluate all data, it returns a ErrorBag instance which contains all errors and info about it.
```
$errorBag = $validator->validate();
```
With ErrorBag class you can easily examine if there is an error or is empty of errors.
```
$valid = $errorBag->hasErrors()
```
You can decide login based on this.

The validate method puts every error if exists inside ErrorBag based on the name provided, so if you want get every error by name you can do this by getFrom() function.

```
$errorBag->getFrom('name');
```

If you want get all error you can do it by use ``getAll()`` method which retrieves an array containing all errors grouped by a key.

## Validating files (beta)
You can validate files uploaded too, you only should include "files." to the ``key`` name inside the validation rules array.
```
$validator = new Validator($body, [
        "file.profile_picture" => "required|file|maxSize:1000"
    ]);
```