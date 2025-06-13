// remove-console-logs.js
const fs = require('fs');
const path = require('path');
const glob = require('glob');

const baseDir = path.join(__dirname);

// 定义需要处理的文件类型
const patterns = ['**/*.js', '**/*.vue', '**/*.ts'];

function removeConsoleLogs(content) {
}

patterns.forEach(pattern => {
  glob(pattern, { cwd: baseDir, ignore: 'node_modules/**' }, (err, files) => {
    if (err) {
      console.error(`扫描文件模式 ${pattern} 时出错:`, err);
      return;
    }
    
    files.forEach(file => {
      const fullPath = path.join(baseDir, file);
      fs.readFile(fullPath, 'utf8', (readErr, data) => {
        if (readErr) {
          console.error(`读取文件 ${fullPath} 时出错:`, readErr);
          return;
        }
        const newData = removeConsoleLogs(data);
        if (newData !== data) {
          fs.writeFile(fullPath, newData, 'utf8', writeErr => {
            if (writeErr) {
              console.error(`写入文件 ${fullPath} 时出错:`, writeErr);
            } else {
            }
          });
        }
      });
    });
  });
}); 