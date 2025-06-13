@echo off
echo Setting environment variables...
set NODE_ENV=production
set VITE_DISABLE_SOURCEMAP=true
rem Increase memory limit to prevent out of memory errors
set NODE_OPTIONS=--max-old-space-size=8192
rem Disable browser console logs
set VITE_APP_ENABLE_CONSOLE_LOG=false
rem Set log level
set VITE_APP_LOG_LEVEL=error

echo Cleaning build directory and cache...
if exist dist rmdir /s /q dist
if exist node_modules\.vite rmdir /s /q node_modules\.vite
if exist node_modules\.cache rmdir /s /q node_modules\.cache

echo Starting production build...

echo Step 1: Building vendor chunks...
call node_modules\.bin\vite build --config vite.vendor.config.js

echo Cleaning temporary files...
if exist node_modules\.vite rmdir /s /q node_modules\.vite
rem Clean cache files
if exist node_modules\.cache rmdir /s /q node_modules\.cache
rem Wait for file system sync
timeout /t 0

echo Step 2: Building app...
call node_modules\.bin\vite build --config vite.app.config.js

echo Validating build results...
if not exist dist\index.html (
    echo Error: index.html not found in dist directory!
    exit /b 1
)

echo Cleaning temporary cache...
if exist node_modules\.vite rmdir /s /q node_modules\.vite
if exist node_modules\.cache rmdir /s /q node_modules\.cache

echo Optimizing output...
rem Compress CSS files
echo Compressing CSS files...
for %%f in (dist\assets\*.css) do (
    echo Processing %%f
    call node_modules\.bin\postcss %%f --replace --no-map
)

echo Build completed successfully!
