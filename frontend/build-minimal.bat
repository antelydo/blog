@echo off
echo Setting environment variables...
set NODE_ENV=production
set VITE_BUILD_TARGET=app

echo Cleaning build directory and cache...
if exist dist rmdir /s /q dist
if exist node_modules\.vite rmdir /s /q node_modules\.vite

echo Backing up original files...
if exist src\main.js.original del src\main.js.original
if exist src\App.vue.original del src\App.vue.original
copy src\main.js src\main.js.original
copy src\App.vue src\App.vue.original

echo Replacing with minimal versions...
copy src\main.js.minimal src\main.js
copy src\App.vue.minimal src\App.vue

echo Starting production build...
call npm run build

echo Restoring original files...
copy src\main.js.original src\main.js
copy src\App.vue.original src\App.vue

echo Build completed!
