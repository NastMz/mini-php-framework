@echo off
REM MiniFramework PHP Project Generator - Windows Batch File
REM Usage: create-miniframework-project.bat <project-name> [options]

setlocal EnableDelayedExpansion

echo.
echo ╔══════════════════════════════════════════════════════════════╗
echo ║              MiniFramework PHP Project Generator             ║
echo ║                     Create new projects                      ║
echo ╚══════════════════════════════════════════════════════════════╝
echo.

REM Check if project name is provided
if "%1"=="" (
    echo ❌ Error: Project name is required
    echo.
    echo Usage: create-miniframework-project.bat ^<project-name^> [options]
    echo.
    echo Arguments:
    echo   project-name              The name of the project to create
    echo.
    echo Options:
    echo   --path=PATH              Custom path for the project
    echo   --namespace=NAMESPACE    Custom namespace
    echo   --description=DESC       Project description
    echo   --no-git                 Skip Git repository initialization
    echo   --no-install             Skip dependency installation
    echo.
    echo Examples:
    echo   create-miniframework-project.bat my-api
    echo   create-miniframework-project.bat my-blog --path=C:\www\blog
    echo   create-miniframework-project.bat ecommerce --namespace=Store
    echo.
    pause
    exit /b 1
)

REM Check if PHP is available
php --version >nul 2>&1
if errorlevel 1 (
    echo ❌ Error: PHP is not installed or not in PATH
    echo Please install PHP and try again.
    echo.
    pause
    exit /b 1
)

REM Check if the PHP script exists
if not exist "create-miniframework-project.php" (
    echo ❌ Error: create-miniframework-project.php not found
    echo Please run this script from the MiniFramework PHP directory.
    echo.
    pause
    exit /b 1
)

REM Run the PHP script with all arguments
echo ℹ️  Running project generator...
echo.

php create-miniframework-project.php %*

if errorlevel 1 (
    echo.
    echo ❌ Project creation failed
    pause
    exit /b 1
) else (
    echo.
    echo ✅ Project creation completed successfully!
    echo.
    pause
)

endlocal
