# Tinywork
Your small and friendly framework for small projects.

# What is Tinywork?
Tiny work is a framework focused in the main important
things of a PHP project with the objetive of give you a structure and main features quickly to start to code.

# Table of contents
- [Tinywork](#tinywork)
- [What is Tinywork?](#what-is-tinywork)
- [Table of contents](#table-of-contents)
- [Main features](#main-features)
- [How start to using it](#how-start-to-using-it)
- [Registering routes](#registering-routes)
  - [get() \& post() syntax.](#get--post-syntax)
  - [view() method.](#view-method)
  - [Examples](#examples)
      - [Registering a route with a callback action](#registering-a-route-with-a-callback-action)
      - [Registering a route with a ControllerClass action](#registering-a-route-with-a-controllerclass-action)
      - [Protecting a route with an AuthMiddleware](#protecting-a-route-with-an-authmiddleware)
- [How to work with Request class](#how-to-work-with-request-class)
  - [Getting query params and url params.](#getting-query-params-and-url-params)
  - [Retrieving body data.](#retrieving-body-data)
    - [Handling form data.](#handling-form-data)
    - [Rendering form values in a redirection.](#rendering-form-values-in-a-redirection)
    - [Handling files uploaded.](#handling-files-uploaded)
- [Errors class](#errors-class)
  - [Main structure.](#main-structure)
  - [add()](#add)
  - [Getting errors from the instance](#getting-errors-from-the-instance)
    - [all()](#all)
    - [getAllFrom()](#getallfrom)
    - [getFrom()](#getfrom)
- [Validating a form  or array data](#validating-a-form--or-array-data)
  - [Validating data request.](#validating-data-request)
  - [Rendering errors inside templates.](#rendering-errors-inside-templates)
  - [Validating files (beta)](#validating-files-beta)
  - [Available validation rules](#available-validation-rules)
  - [Normal value rules.](#normal-value-rules)
    - [required](#required)
    - [requiredIf](#requiredif)
    - [confirmed](#confirmed)
    - [min](#min)
    - [minLength](#minlength)
    - [email](#email)
  - [File uploaded Rules](#file-uploaded-rules)
    - [file](#file)
    - [maxSize](#maxsize)
  - [Database rules](#database-rules)
    - [unique](#unique)
- [Protected routes](#protected-routes)
  - [Creating a middleware](#creating-a-middleware)
  - [Protecting a route](#protecting-a-route)
- [ORM Features](#orm-features)
  - [How use it](#how-use-it)
  - [Creating a instance of the model.](#creating-a-instance-of-the-model)
- [Auth API class.](#auth-api-class)
  - [Checking if a user can login.](#checking-if-a-user-can-login)
  - [Login a valid user.](#login-a-valid-user)
    - [Checking if there's a logged user.](#checking-if-theres-a-logged-user)
  - [Logout a user.](#logout-a-user)

# Main features
1. Supports send emails using PHPMailer.
2. Request data management.
3. Form validator API.
4. MVC structure.
5. ORM based on ActiveRecord 
6. Middleware protection support for every route stored.
7. Array API with navigation based on dot notation to manage every array structure in the project.
8. Js & Css files managed by Vite.
9. Flash data session between request.

# How start to using it
- Clone this repo wherever you want using `git clone`
- Before start execute npm, pnpm etc. `install` command and `composer install` to prepare all dependencies needed.
- Copy .env.example file without .example and fill every entry, is important fill always the database connection credentials.
- Execute `php -S localhost:8000` inside public/ folder.
- Execute `npm run dev` to watch for file change and reflect them every time in the build file.

# Registering routes
Your Tinywork app serves every route by a class that should contain all paths that you want serve in it.

To register all you path routes in Tinywork you can do it by the ``public/index.php`` file.

When you clone the repo index.php file a instance of the class router is initialized.

```
$router = new Router();
```

All routes path are handled by this class, and you can register every path by POST or GET request method using **get()** and **post()** Router class methods.
```
$router->get('/', function(Request $request) {
    view('index', []);
});

$router->post('/create', function(Request $request) {
    //handle the incoming POST request
});
```
## get() & post() syntax.

> For every callback or handler controller function you should use one of the next options
> * **redirectTo()**: Use it in POST handlers to redirect based on the result of a operation.
> * **return**: Use a return clause when you just want make operations and retrieve the a result.
> * **return view()**: Use it when you only want render a view from the controller, it's strongly recommended to get function handlers.

When you call both methods you should pass the next syntax always.
```
method(string: relative_path, callback | array: handler, array: middleware_classnames);
```

here's the description of every argument.
* required - **relative_path** (string): A path to register where a action will be served.
* required - **handler** (callable|array): Is the function that will handle the request to the path, you can pass a callback or a array with the ``[ClassController::class, 'functionName']`` syntax.
* optional - **middlewares** (array): A array containing every class name of the classes that will protect a route, if nothing is passed then a request can be execute the handler.

## view() method.
Use ``view()`` when you want render a template as a response of a request.
```
view(string $path, array $data, string $templatePath): void
```
* **$path**: The relative path of the template inside /views where is the view to render.
* **$data**: All data to render inside the view where key will be the name of the variable and the value it's value to render.
* **$templatePath**: A relative inside /views path where the view will be render.

When you call this function it will execute a echo rendering every HTML content.

## Examples
#### Registering a route with a callback action
```
$router->get('/', function(Request $request) {
    echo "Hello world";
});
```

#### Registering a route with a ControllerClass action
```
$router->get('/', [PublicController::class, 'index']);
```
Of course you should create previously a .php file with the function that you want to assign to the handler in the path.

```
//controllers/PublicController.php

<?php

namespace Controllers;

class PublicController {
    public function index(Request $req){
        echo "Hello World";
    }
}
```

#### Protecting a route with an AuthMiddleware
Every Middleware class that will protect a route should be passed in a array with them classnames.
```
$router->get(
    '/', 
    [PublicController::class, 'index'], 
    [AuthMiddleware::class]
);
```

Tinywork includes a **AuthMiddleware.php** inside **middlewares/** folder, you can use it to implement your own logic to prevent access in your routes.
```
class AuthMiddleware implements MiddlewareInterface{
    public function handle(Request $req, Closure $next) : mixed
    {
        if(!Auth::authenticated()) redirectTo('/login');
        return $next($req);
    }
}
```
Remember, you should return $next function call with the $req to pass if the middleware logic is successful otherwise you can redirect the request, block it, launch a http response or anything else.

# How to work with Request class
Every time you register a route, a instance of Request class is passed by args, you can use it only declaring it in the args of the action controller function or callback function like this

```
$router->('/', function(Request $request) {
    //Now you can work with all Request api methods.
    var_dump($request->body(););
})
```

This means that every action function inside your Controller classes can use the $request argument too.

## Getting query params and url params.
Of course you can register a route with url params, you can do this using **{urlparameter}** syntax

```
$router->('/product/{id}', function(Request $request) {
    
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
To retrieve body data you can use the method body() which returns a sanitized form with all data passed by a post request with two keys `body` and `files`.

```
$body = $request->body();
```

If you want to initialize some form values because one or more of them can be not included like: radio buttons, checkboxes etc. you can initialize them by passing a array with the value to include if one of them aren't present in the request body using getBody();

```
$body = $req->getBody([
    "typeOfUser" => 'admin',
]);
```
If "typeOfUser" is a name of a checkbox input "admin" will be the default value if the user doesn't select any of the checkboxes in the HTML Form.

Every time you want to access to any entry value you can use the API Class JustArray of course will give you the possibility to navigate into them using dot notation.

```
$userName = JustArray::find($body, 'body.user.name');
```

### Rendering form values in a redirection.

When you use Tinywork with its MVC features you will make a lot of redirection to routes after a post request, in this cases you maybe want to show the previous request body in your views.

For example, the next code simulates a POST controller handler.
```
$router->post('/store-product', function(Request $req){
    $body = $req->getBody();

    $validator = new Validator($body, [
        "name" => "required|minLength:10"
        "price" => "required|min:50"
    ]);

    $errors = $validator->validate();

    //If there's a error then redirect to the path where's the form.
    if($errors->hasErrors()) redirectTo("/create-product");
})
```
As you can see if the validator has a error then we redirect again to the path where the view of the form is rendered.

Well with the ``old()`` util function you can render every old data submitted by a form in a redirection inside a view.

See how in the next example we use ``old()`` inside the value HTML property.

```
//Create form example
<form action="/store-product" method="post>
    <input name="name" type="text" value="<?php echo old('name') >"/>
    <input name="price" type="number" value="<?php echo old('email') >"/>
</form>
```
Every time this template render ``old()`` will try to find any previous form value submitted and render it.
### Handling files uploaded.
You can access to every File uploaded by using getFile() api method or using JustArray::find() method too.

```
$body = $request->body();

$request->getFile('profile');
$uploadedPicture = JustArray::find($body, 'files.profile');

```

Then you can receive an array of UploadedFile instance objects or just one object if your input file is marked as non multiple.

# Errors class

Before explain how to validate incoming request body data or any other array with ``["key" => "value"]`` entries is important show you how Errors class works.

This class is stored in ``core/Errors.php`` path, it represents a abstraction to manage every error in your application, is used by every core feature of Tinywork that needs register errors to retrieve by a response or render in frontend of the application.

Of course you can create your own instances in another implementations outside of the box, that's the reason to explain how it works.

## Main structure.

Every instance of Errors class initializes with a empty property array called ``$errors`` it's the main property of all functionality and it should always store data in the next structure.

```
[
    [key => message],
    [key => message],
]
```
To keep data in that structure always Errors class has a function.
## add()
```
add(string $key, string $message, boolean $flash = false):void
```
This method will add a new error elemento to ``$errors`` property and flash it if ``$flash`` is true.

You can use this method to add additional errors in a Validator instance after execute a ``validate()`` call.

> Every time you use $flash arg with true value all errors added will be stored in a same place inside flash data which means every error in two or more instance of Error class will be stored in the same place.

## Getting errors from the instance
Errors class has some functions to retrieve message errors only from the current instance, which means it won't work if you try to call them from a template.

### all()
Retrieves all error entries from ``$errors`` property.

### getAllFrom()
```
all(string $key): array
```
Retrieves all error entries messages from a key given.

### getFrom()

```
getFrom(string $key): string|null
```
Retrieves the last message inserted in the given key or return nulls if doesn't exists a message.

# Validating a form  or array data

## Validating data request.
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
You can decide logic based on this.

The validate method puts every error if exists inside ErrorBag based on the name provided, so if you want get every error by name you can do this by getFrom() function.

```
$errorBag->getFrom('name');
```

If you want get all error you can do it by use ``getAll()`` method which retrieves an array containing all errors grouped by a key.

## Rendering errors inside templates.
Every time you execute validate() method from Validator class all errors are stored in flash session data too, which means you can render every error in the template rendered by the next response of a request.

For example, in the next code we have a rendering based on the result of a validation
```
$validator = new Validator(
    $req->getBody(),
    [
        "name" => "required"
    ]
);

$errors = $validator->validate();

if(errors->hasErrors()) redirectTo('/create-account');

//Here's logic if validation pass successfully...
```
When Tinywork redirects you to /create-account path the view rendered can render every error from the previous request with a util function ``old()`` it renders a message from errors stored in flash data by a path.

The next line of HTML code will render the error stored in flash of the field name if exists.
```
//inside a .php file view.

<p><?php echo old('name') ></p>
```

## Validating files (beta)
You can validate files uploaded too, you only should include "files." to the ``key`` name inside the validation rules array.
```
$validator = new Validator($body, [
        "file.profile_picture" => "required|file|maxSize:1000"
    ]);
```

## Available validation rules
Every rule can need one or more args to work.
1. If the rule doesn't need args you should type the rule only by its name.
2. If the rule has args to work you should pass them by the next syntax: ```ruleName:args```.

> **Notes**
>
> The rules declared here are subject to changes which means its logic can change or even more rules could be added in future commits.


## Normal value rules.

### required
`required`

If required is typed then Validator class will check if there's a value or not in the key given. It will check empty strings, null values, undefined or a empty array.

### requiredIf
``requiredIf:keyOfField``

**keyOfField**: A valid key/path inside the array.

It will checks if there's a value in the field only if **keyOfField** has a value which means a key/path with **requiredIf** will be required conditionally by a existing value in another field.

It applies the same validations of **required** rule.


### confirmed
``confirmed:keyToCheckMatch``

**keyToCheckMatch**: Another key/path of the array that you want confirm.

It will checks if the value of the field where **confirmed** is declared match with the **keyToCheckMatch** field value.

### min
>**Note**:
>It only works with int values.

```min:minValue```

**minValue**: The minimum value accepted for the field.

It will checks if the path/key where it is declared equals or exceeds the **minValue** declared.

### minLength
>**Note**: This rule works with *array* and *string* values.

``minLength:minElementsNeeded``

It checks if the key/path value has the **minElementsNeeded** items.

### email

``email``

It uses native FILTER_VALIDATE_EMAIL constant of PHP to check if the value inside the key/path where the rule is declared has a valid email.

## File uploaded Rules

Don't forget, if you want declare file rules you should put them in a valid key/path with dot syntax ```files.fileInputName```.

```
$validator = new Validator([
    "name" => "", //Here file rules aren't valid
    "files.profileImage" => "" //Here file rules are valid
])
```

If you declare a file rule in a key/path of the body data you would get a exception or unexpected results.

### file
``file``

It checks if the key/path has a valid file instance and its a success uploaded file.

It only works with a single uploaded file.

### maxSize

``maxSize:sizeInKB``

**sizeInKb**: The size in KB that you don't want to exceed in every uploaded file.

It checks every file of the key/path where it is defined for every file uploaded even it is a only file or multiple file.

## Database rules

### unique

``unique:tableName,columnToCheck,ignoredColumnId,ignoredValue``

A rule that checks if the key/path value already exists in the `tableName` provided using the `columnToCheck` to compare against the key/path value where the rule is declared.

* **tableName** - The table to check into.
* **columnToCheck** - The column where you want unique values.
* **ignoredColumnId (optional)** - The name of a column where you want to ignore the validation.
* **ignoredValue (required if ignoredColumnId is provided)** - The value to ignore in the validation.


# Protected routes
Tinywork provides Middleware feature which means you can create Middleware files and mark routes with them.
Every middleware included in the third parameter of get() and post() Route methods will be executed before execute the controller action route registered.

## Creating a middleware
Before you can mark a route with middlewares you should create a .php file with a class that implements MiddlewareInterface contract inside ``middlewares/`` folder.

```
<?php

namespace Middlewares;

use Closure;
use Core\Auth;
use Core\Interfaces\MiddlewareInterface;
use Routes\Request;

class AuthMiddleware implements MiddlewareInterface{
    public function handle(Request $req, Closure $next) : mixed
    {
        if(!Auth::authenticated()) redirectTo('/login');
        return $next($req);
    }
}
```

## Protecting a route
Once you did this you should mark the routes wherever you want execute before this or more middlewares.
```
$router->get('/', function(Request $request) {
    echo "Protected route";
}, [AuthMiddleware::class]);
```
You should return ``$next()`` with the ``$request`` object as its parameter every time you want continue with the next middleware or request, if you logic suppose to reject it then you should use redirectTo() helper function to redirect the user to another valid route or return a error response.

# ORM Features
Tinywork provides a simple ORM to work with tables in you database connection, of course you're able to modify it or replace it by installing another ORM Api.

The ORM is based on Active Record, which means every feature provides from a parent class, you can find it inside models/ as ActiveRecord.php.

So if you want to inspect it or modify the main code that is the file where you can do that

## How use it
Every table in you database connection should be a .php including a class with the name of the table extending from ActiveRecord, both, file and classname have the same name.
Here's a example of the code supposing there's a table users in the database connection.
<br/>
After that you should override some properties from the parent class and type every column as a property of the class.
Here's a example of a class representing a table users in the database connection

```
<?php

namespace Models;

class Users extends ActiveRecord{
    protected static string $table = 'users';
    protected static string $idName = 'id';
    protected static array $columns = ['email', 'password'];


    public ?int $idUsuario;
    public string $email;
    public string $password;

    public function __construct(?array $args = [])
    {
        $this->idUsuario = isset($args['idUsuario'])? 
            filter_var($args['idUsuario']): null;
        $this->email = $args['email'] ?? '';
        $this->password = $args['password'] ?? '';
    }
}

```
Every table should be represented like the example.

## Creating a instance of the model.
Remember, in Active Record every model represents a possible or existing record in the table, so if you want create a new record you should create first a instance of the model and after that you be able to store it using ``save()`` method.

```
$user = new User([
    "email" => 'test@test.com', 
    "password" => 'abcd123'
]);

$result = $user->save();
```
Of course the model not acts like a validator, so previous to store a record the data passed in a new instance Model should be **validated** and **sanitized**.

# Auth API class.
Tiny work includes a Auth class inside core/ file, it will help you to login/logout users and let you know easily if there's a actual session in your app.

## Checking if a user can login.
Auth class provides attempt() function which will try to check credentials of a user this method has the next syntax.
```
public static function attempt(string $email, string $password, array $modelInfo = [User::class, "users"]): ?object
```

* required **$email**: A string with the email to find a user in your table.
* required **$password**: A typed password by the client to try check if is a valid password comparing it with a saved password.
* optional **$modelInfo** (default [User::class, "users"]): A array containing the classname of your model representation of the table where you want validate the credentials. If you don't pass anything Tinywork will try use a Models\User classname but that class is not included so if you want use the default value you should create it before.

Once you provide every arg Tinywork will try to check the user credentials, if there's all ok it will return you a new fresh instance of the model that you passed in **$modelInfo** argument.

## Login a valid user.
Once you check the credentials and all goes good, you can login a user with the **login()** Auth method.

When you call **login()** method you should pass the info that you want to save in in the session and Tinywork will store it inside **$_SESSION['__auth']** key.

You're free to send whatever you like, but **login()** method needs that always the array had a **id** key with a valid unique id from your main users table.

```
Auth::login(
    [
        "id" => 2,
        //Here you can put anything else
    ]
);
```

### Checking if there's a logged user.
Auth class provides **authenticated()** method which return ``true`` if a user has a actual login or ``false`` otherwise.

## Logout a user.
To logout a user is more easy, just call **logout()** method and the actual session will be removed including the **__auth** key from **$_SESSION**.