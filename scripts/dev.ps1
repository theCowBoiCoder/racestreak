param(
    [ValidateSet('start', 'stop', 'reset', 'status', 'test', 'quality', 'logs')]
    [string] $Command = 'start',
    [switch] $Force
)

$ErrorActionPreference = 'Stop'
$repositoryRoot = Split-Path -Parent $PSScriptRoot

function Invoke-Docker {
    param([string[]] $Arguments)

    & docker @Arguments

    if ($LASTEXITCODE -ne 0) {
        throw "Docker command failed with exit code $LASTEXITCODE."
    }
}

function Invoke-Compose {
    param([string[]] $Arguments)

    Invoke-Docker -Arguments (@('compose') + $Arguments)
}

Push-Location $repositoryRoot

try {
    Invoke-Docker -Arguments @('info') | Out-Null

    switch ($Command) {
        'start' {
            Invoke-Compose -Arguments @('up', '--detach', '--build')
            Invoke-Compose -Arguments @('ps')
        }
        'stop' {
            Invoke-Compose -Arguments @('down', '--remove-orphans')
        }
        'reset' {
            if (-not $Force) {
                throw 'Reset deletes the local PostgreSQL volume. Re-run with -Force to confirm.'
            }

            Invoke-Compose -Arguments @('down', '--volumes', '--remove-orphans')
            Invoke-Compose -Arguments @('up', '--detach', '--build')
            Invoke-Compose -Arguments @('ps')
        }
        'status' {
            Invoke-Compose -Arguments @('ps')
        }
        'test' {
            Invoke-Compose -Arguments @('--profile', 'tools', 'build', 'backend-test', 'frontend-test')
            Invoke-Compose -Arguments @('--profile', 'tools', 'run', '--rm', 'backend-test')
            Invoke-Compose -Arguments @('--profile', 'tools', 'run', '--rm', 'frontend-test')
        }
        'quality' {
            Invoke-Compose -Arguments @('--profile', 'tools', 'build', 'backend-test', 'frontend-test')
            Invoke-Compose -Arguments @('--profile', 'tools', 'run', '--rm', 'backend-test', 'sh', '-lc', './vendor/bin/pint --test && ./vendor/bin/phpstan analyse --memory-limit=1G')
            Invoke-Compose -Arguments @('--profile', 'tools', 'run', '--rm', 'frontend-test', 'npm', 'run', 'quality')
        }
        'logs' {
            Invoke-Compose -Arguments @('logs', '--follow', 'backend', 'frontend', 'database')
        }
    }
}
finally {
    Pop-Location
}
