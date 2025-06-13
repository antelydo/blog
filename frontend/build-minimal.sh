#!/bin/bash

echo "Setting environment variables..."
export NODE_ENV=production
export VITE_BUILD_TARGET=app

echo "Cleaning build directory and cache..."
rm -rf dist
rm -rf node_modules/.vite

echo "Backing up original files..."
rm -f src/main.js.original
rm -f src/App.vue.original
cp src/main.js src/main.js.original
cp src/App.vue src/App.vue.original

echo "Replacing with minimal versions..."
cp src/main.js.minimal src/main.js
cp src/App.vue.minimal src/App.vue

echo "Starting production build..."
npm run build

echo "Restoring original files..."
cp src/main.js.original src/main.js
cp src/App.vue.original src/App.vue

echo "Build completed!"
