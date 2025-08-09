<?php
array_shift($argv);

if (count($argv) < 1 || strtolower($argv[0]) !== 'init') {
    echo "Usage:\n";
    echo "  php exo.php init\n";
    exit(1);
}

initProject();

function initProject()
{
    // Define folders and subfolders
    $folders = [
        'public/assets',
        'routes',
        'src/Controllers',
        'src/Core',
        'src/Models',
        'src/Services',
        'src/Views/header',
        'src/Views/footer',
    ];

    // Create folders
    foreach ($folders as $folder) {
        if (!is_dir($folder)) {
            mkdir($folder, 0777, true);
            echo "Created folder: $folder\n";
        } else {
            echo "Folder already exists: $folder\n";
        }
    }

    // Create example index.php in public if missing
    $indexFile = 'public/index.php';
    if (!file_exists($indexFile)) {
        file_put_contents($indexFile, "<?php\n// Front controller entry point\n");
        echo "Created file: $indexFile\n";
    } else {
        echo "File already exists: $indexFile\n";
    }

    // Create example routes/web.php if missing
    $routesFile = 'routes/web.php';
    if (!file_exists($routesFile)) {
        file_put_contents($routesFile, "<?php\n// Define your routes here\n");
        echo "Created file: $routesFile\n";
    } else {
        echo "File already exists: $routesFile\n";
    }

    // Create composer.json if missing
    $composerFile = 'composer.json';
    if (!file_exists($composerFile)) {
        $composerJson = [
            "name" => "yourname/projectname",
            "description" => "A PHP project initialized by exo CLI",
            "require" => new stdClass(),
            "autoload" => [
                "psr-4" => [
                    "Src\\" => "src/"
                ]
            ]
        ];
        file_put_contents($composerFile, json_encode($composerJson, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));
        echo "Created basic composer.json\n";
    } else {
        echo "composer.json already exists\n";
    }

    // Run composer install automatically
    echo "Running 'composer install' ...\n";
    exec('composer install 2>&1', $output, $return_var);
    if ($return_var === 0) {
        echo "Composer dependencies installed successfully.\n";
    } else {
        echo "Composer install failed or composer not found. Output:\n";
        echo implode("\n", $output) . "\n";
    }

    echo "Project initialization complete!\n";
}
