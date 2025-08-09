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
    // Create composer.json if missing
    $composerFile = 'composer.json';
    if (!file_exists($composerFile)) {
        $composerJson = [
            "name" => "yourname/projectname",
            "description" => "A PHP project initialized by exo CLI",
            "require" => new stdClass(),
            "autoload" => [
                "psr-4" => [
                    "App\\" => "src/"
                ]
            ],
            "authors"=> [
                [
                "name"=> "Your Name"
                ]
            ],
            "require"=>[
                "twbs/bootstrap"=> "5.3.7"
            ]
        ];
        file_put_contents($composerFile, json_encode($composerJson, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));
        echo "Created basic composer.json\n";
    } else {
        echo "composer.json already exists\n";
    }

    // Run composer install automatically
    echo "Running 'composer install' ...\n";
    exec('composer install 2>&1', $outputInstall, $returnVarInstall);
    if ($returnVarInstall === 0) {
        echo "Composer dependencies installed successfully.\n";
    } else {
        echo "Composer install failed or composer not found. Output:\n";
        echo implode("\n", $outputInstall) . "\n";
    }

    // Run composer dump-autoload to refresh autoload files
    echo "Running 'composer dump-autoload' ...\n";
    exec('composer dump-autoload 2>&1', $outputDump, $returnVarDump);
    if ($returnVarDump === 0) {
        echo "Composer autoload dumped successfully.\n";
    } else {
        echo "Composer dump-autoload failed. Output:\n";
        echo implode("\n", $outputDump) . "\n";
    }

    echo "Project initialization complete!\n";
}
