#!/usr/bin/env node

/**
 * 国际化语言包管理命令行工具
 */

const { syncLocales } = require('./i18n-manager');
const { optimizeLocaleStructure } = require('./i18n-structure-optimizer');
const { extractAndCompareKeys } = require('./i18n-extractor');
const { createLocaleSnapshot, restoreLocaleVersion, listVersions } = require('./i18n-version-control');
const chalk = require('chalk');

// 显示帮助信息
function showHelp() {
  console.log(chalk.cyan('国际化语言包管理工具'));
  console.log(chalk.cyan('==================='));
  console.log('');
  console.log(chalk.yellow('用法:'));
  console.log('  node i18n-cli.js <命令> [选项]');
  console.log('');
  console.log(chalk.yellow('命令:'));
  console.log('  sync                  同步语言包，检测并添加缺失的翻译键');
  console.log('  optimize              优化语言包结构');
  console.log('  extract               从代码中提取翻译键并与语言包比对');
  console.log('  snapshot              创建语言包快照');
  console.log('  list-versions         列出所有语言包版本');
  console.log('  restore <timestamp>   恢复到指定版本的语言包');
  console.log('  all                   执行所有操作（同步、优化、提取、创建快照）');
  console.log('  help                  显示帮助信息');
  console.log('');
  console.log(chalk.yellow('示例:'));
  console.log('  node i18n-cli.js sync');
  console.log('  node i18n-cli.js all');
  console.log('  node i18n-cli.js restore 2023-01-01T12:00:00.000Z');
}

// 解析命令行参数
const args = process.argv.slice(2);
const command = args[0];

// 执行命令
async function executeCommand() {
  if (!command || command === 'help') {
    showHelp();
    return;
  }
  
  try {
    switch (command) {
      case 'sync':
        console.log(chalk.cyan('开始同步语言包...'));
        syncLocales();
        break;
        
      case 'optimize':
        console.log(chalk.cyan('开始优化语言包结构...'));
        optimizeLocaleStructure();
        break;
        
      case 'extract':
        console.log(chalk.cyan('开始提取翻译键...'));
        extractAndCompareKeys();
        break;
        
      case 'snapshot':
        console.log(chalk.cyan('开始创建语言包快照...'));
        createLocaleSnapshot();
        break;
        
      case 'list-versions':
        listVersions();
        break;
        
      case 'restore':
        const timestamp = args[1];
        if (!timestamp) {
          console.error(chalk.red('请指定要恢复的版本时间戳'));
          showHelp();
          return;
        }
        restoreLocaleVersion(timestamp);
        break;
        
      case 'all':
        console.log(chalk.cyan('开始执行所有操作...'));
        console.log(chalk.cyan('1. 同步语言包'));
        syncLocales();
        console.log(chalk.cyan('2. 优化语言包结构'));
        optimizeLocaleStructure();
        console.log(chalk.cyan('3. 提取翻译键'));
        extractAndCompareKeys();
        console.log(chalk.cyan('4. 创建语言包快照'));
        createLocaleSnapshot();
        console.log(chalk.green('所有操作执行完成!'));
        break;
        
      default:
        console.error(chalk.red(`未知命令: ${command}`));
        showHelp();
        break;
    }
  } catch (error) {
    console.error(chalk.red('执行命令时出错:'), error);
    process.exit(1);
  }
}

executeCommand();
