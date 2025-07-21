@echo off
REM Container Block Designer - Windows Setup Script

echo ======================================
echo Container Block Designer - Setup
echo ======================================

REM Check if we're in the right directory
if not exist "container-block-designer.php" (
    echo Error: container-block-designer.php not found!
    echo Please run this script from the plugin root directory.
    pause
    exit /b 1
)

REM Check Node.js
echo Checking Node.js version...
node -v >nul 2>&1
if errorlevel 1 (
    echo Error: Node.js is not installed!
    echo Please install Node.js from https://nodejs.org/
    pause
    exit /b 1
) else (
    echo Node.js found
    node -v
)

REM Check npm
echo.
echo Checking npm version...
npm -v >nul 2>&1
if errorlevel 1 (
    echo Error: npm is not installed!
    pause
    exit /b 1
) else (
    echo npm found
    npm -v
)

REM Check PHP
echo.
echo Checking PHP version...
php -v >nul 2>&1
if errorlevel 1 (
    echo Error: PHP is not installed or not in PATH!
    pause
    exit /b 1
) else (
    echo PHP found
    php -v
)

REM Check Composer
echo.
echo Checking Composer...
composer --version >nul 2>&1
if errorlevel 1 (
    echo Error: Composer is not installed!
    echo Please install Composer from https://getcomposer.org/
    pause
    exit /b 1
) else (
    echo Composer found
    composer --version
)

REM Create directories
echo.
echo Creating directories...
if not exist "build" mkdir build
if not exist "assets\css" mkdir assets\css
if not exist "assets\js" mkdir assets\js
if not exist "assets\images" mkdir assets\images
if not exist "languages" mkdir languages
if not exist "tests\unit" mkdir tests\unit
if not exist "tests\integration" mkdir tests\integration
if not exist "tests\e2e" mkdir tests\e2e

REM Install PHP dependencies
echo.
echo Installing PHP dependencies...
call composer install
if errorlevel 1 (
    echo Error installing PHP dependencies!
    pause
    exit /b 1
)

REM Install Node dependencies
echo.
echo Installing Node dependencies...
call npm install
if errorlevel 1 (
    echo Error installing Node dependencies!
    pause
    exit /b 1
)

REM Build assets
echo.
echo Building assets...
call npm run build
if errorlevel 1 (
    echo Error building assets!
    pause
    exit /b 1
)

REM Success message
echo.
echo ======================================
echo Setup completed successfully!
echo ======================================
echo.
echo Next steps:
echo 1. Activate the plugin in WordPress admin
echo 2. Run 'npm start' for development
echo 3. Visit the Container Block Designer page in WordPress admin
echo.
echo Available commands:
echo - npm start          : Start development build with watch
echo - npm run build      : Create production build
echo - npm test           : Run tests
echo - npm run lint       : Check code standards
echo - npm run format     : Format code
echo.
echo Happy coding!
echo.
pause