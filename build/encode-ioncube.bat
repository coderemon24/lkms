@echo off
echo ================================================================
echo   Compiling LKMS Client Package with ionCube Encoder (Windows)
echo ================================================================

set SCRIPT_DIR=%~dp0
set PACKAGE_ROOT=%SCRIPT_DIR%..
set SRC_DIR=%PACKAGE_ROOT%\src
set OUT_DIR=%PACKAGE_ROOT%\dist\encoded-src

if not exist "%OUT_DIR%" mkdir "%OUT_DIR%"

echo Compiling %SRC_DIR% into %OUT_DIR%...
ioncube_encoder.exe --into "%OUT_DIR%" "%SRC_DIR%" --optimize max --replace-target --obfuscate-variables --obfuscate-properties

if %ERRORLEVEL% EQU 0 (
    echo [SUCCESS] ionCube compilation completed!
    echo Replace the content of src/ with dist/encoded-src/ before distributing to clients.
) else (
    echo [WARNING] ioncube_encoder.exe not found in PATH or returned error.
    echo Please install ionCube encoder and run this script.
)
pause
