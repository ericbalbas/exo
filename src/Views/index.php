<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>EXO Basic Project - Setup Instructions</title>
    <?php require_once __DIR__ . '/../Views/header/index.php'; ?>
    <style>
        body {
            background: #f9fbfd;
            font-family: "Segoe UI", Tahoma, Geneva, Verdana, sans-serif;
            color: #333;
            line-height: 1.6;
        }

        .container {
            max-width: 80%;
        }

        .card {
            background: #fff;
            border-radius: 12px;
            box-shadow: 0 10px 30px rgb(0 0 0 / 0.08);
            padding: 2rem 2.5rem;
            margin-bottom: 3rem;
            border: none;
        }

        h1,
        h2 {
            color: #0d6efd;
            font-weight: 700;
        }

        h1 {
            font-size: 2.75rem;
            margin-bottom: 1rem;
            text-align: center;
        }

        h2 {
            margin-top: 2.5rem;
            margin-bottom: 1rem;
            padding-bottom: 0.4rem;
            border-bottom: 3px solid #0d6efd;
        }

        p,
        li {
            font-size: 1.1rem;
            margin-bottom: 0.6rem;
        }

        ul {
            padding-left: 1.3rem;
        }

        ul ul {
            padding-left: 1rem;
            margin-top: 0.3rem;
            margin-bottom: 0.7rem;
        }

        .code-block {
            background-color: #1e1e2f;
            color: #d4d4f1;
            border-radius: 10px;
            padding: 1.25rem 1.5rem;
            font-family: "Fira Code", monospace, monospace;
            font-size: 0.95rem;
            white-space: pre-wrap;
            margin-bottom: 1.8rem;
            box-shadow: inset 0 0 15px #00000090;
            user-select: text;
            overflow-x: auto;
        }

        .folder-structure {
            background-color: #f7f9fc;
            padding: 1.5rem 1.8rem;
            border-radius: 10px;
            font-family: monospace;
            white-space: pre;
            line-height: 1.5;
            margin-bottom: 2rem;
            border: 1px solid #dbe4ef;
            box-shadow: inset 0 0 8px #d0d8e8;
            color: #555;
            font-size: 0.95rem;
        }

        hr {
            border: none;
            border-top: 2px solid #e5eaf0;
            margin: 3rem 0;
        }
    </style>
</head>

<body>
    <div class="container py-5">
        <div class="card shadow-sm">
            <h1>EXO Basic Project</h1>
            <h2>📜 Project Setup Instructions</h2>
            <p class="text-secondary">Follow the guide below to understand the project structure and how to add new features.</p>

            <hr />

            <h2>📂 Libraries</h2>
            <ul>
                <li><strong>public/</strong> – Frontend bootstrap and assets.</li>
                <ul>
                    <li><strong>assets/</strong> – Contains:</li>
                    <ul>
                        <li>Bootstrap CSS & JS</li>
                        <li>Bootstrap Icons</li>
                        <li>SweetAlert2</li>
                        <li>localforage (JS local storage library)</li>
                        <li><code>js/main.js</code> – Main frontend script</li>
                        <li><code>js/module.js</code> – Modular JS functions exportable</li>
                    </ul>
                    <li>You may still use <code>npm</code> or <code>composer</code> to add more libraries.</li>
                </ul>
            </ul>

            <hr />

            <h2>🗂️ Project Folder Structure</h2>
            <div class="folder-structure">
                /public/
                ├── assets/ # Frontend assets (Bootstrap, Icons, SweetAlert2, localforage, JS files)
                ├── index.php # Frontend entry point
                /routes/
                    ├── web.php # Route definitions & transaction bootstrap
                /src/
                ├── Controllers/ # Controller classes - handle request logic
                ├── Core/ # Core utilities: database.php note: add your 
                        db config in this file, router.php (pre-configured)
                ├── Models/ # Data model classes
                ├── Services/ # Business logic & service classes
                └── Views/ # View templates & layouts
                    ├── header/index.php # Header partials
                    └── footer/index.php # Footer partials
                /vendor/ # Composer dependencies (auto-generated)
            </div>

            <hr />

            <h2>🚀 Running <code>exo.php</code> CLI</h2>
            <p>
                From your terminal, at the project root directory, run:
            </p>
            <div class="code-block">php exo.php init</div>
            <p>This installs composer dependencies and generates autoload files.</p>

            <hr />

            <h2>🚀 Adding More Routes</h2>
            <p>Edit <code>routes/web.php</code> to register new routes. Examples:</p>
            <div class="code-block">
                // Register a simple GET route
                $router->get('/new-feature', function () {
                echo "Hello from new route!";
                });

                // Register GET and POST routes with controller methods
                $router->get('/test/{id}', [YourController::class, 'yourFunc']);
                $router->post('/show', [YourController::class, 'yourFunc']);
            </div>

            <hr />

            <h2>🛠️ Creating a Controller</h2>
            <p>Create a PHP class in <code>src/Controllers</code>. Example:</p>
            <div class="code-block">
                &lt;?php
                namespace App\Controllers;

                class SampleController
                {
                public function index()
                {
                echo "Hello from SampleController!";
                }
                }
            </div>

            <hr />

            <h2>🛠️ Creating a Model</h2>
            <p>Add your model class in <code>src/Models</code>. Example:</p>
            <div class="code-block">
                &lt;?php
                namespace App\Models;

                class SampleModel
                {
                public function getData()
                {
                return ['message' =&gt; 'Hello from SampleModel!'];
                }
                }
            </div>

            <hr />

            <h2>🛠️ Creating a Service</h2>
            <p>Add your service class to <code>src/Services</code> with dependency injection for models. Example:</p>
            <div class="code-block">
                &lt;?php
                namespace App\Services;

                use App\Models\SampleModel;

                class SampleService
                {
                protected $model;

                public function __construct(SampleModel $model)
                {
                $this->model = $model;
                }

                public function getMessage()
                {
                $data = $this->model->getData();
                return $data['message'];
                }
                }
            </div>

            <hr />

            <h2>📺 Rendering Views</h2>
            <p>Use prebuilt header and footer partials in <code>src/Views/header/index.php</code> and <code>src/Views/footer/index.php</code> to wrap your views. Example:</p>
            <div class="code-block">
                &lt;?php
                require_once __DIR__ . '/../header/index.php';
                ?&gt;

                &lt;h1&gt;Hello, World!&lt;/h1&gt;

                &lt;?php
                require_once __DIR__ . '/../footer/index.php';
                ?&gt;
            </div>

            <hr />

            <h2>⚙️ Autoloading & Composer</h2>
            <p>After creating new classes in <code>src/</code>, run this command to update autoloading:</p>
            <div class="code-block">composer dump-autoload</div>

            <hr />

            <h2>🐞 Debugging Tips</h2>
            <ul>
                <li>Enable error reporting in <code>php.ini</code> or at the start of <code>public/index.php</code>:
                    <div class="code-block">
                        &lt;?php
                        error_reporting(E_ALL);
                        ini_set('display_errors', 1);
                    </div>
                </li>
                <li>Use <code>var_dump()</code> or <code>print_r()</code> to inspect variables.</li>
                <li>Check your web server's error logs for detailed errors.</li>
            </ul>

            <hr />

            <h2>📦 Installing Dependencies</h2>
            <ul>
                <li>Via npm:
                    <div class="code-block">npm install package-name</div>
                </li>
                <li>Via Composer:
                    <div class="code-block">composer require vendor/package</div>
                </li>
            </ul>
        </div>
    </div>
</body>

</html>