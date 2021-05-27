<?php

declare(strict_types=1);
/**
 * #logic 做事不讲究逻辑，再努力也只是重复犯错
 * ## 何为相思：不删不聊不打扰，可否具体点：曾爱过。何为遗憾：你来我往皆过客，可否具体点：再无你。.
 *
 * @version 1.0.0
 * @author @小小只^v^ <littlezov@qq.com>  littlezov@qq.com
 * @contact  littlezov@qq.com
 * @link     https://github.com/littlezo
 * @document https://github.com/littlezo/wiki
 * @license  https://github.com/littlezo/MozillaPublicLicense/blob/main/LICENSE
 *
 */
namespace littler\command\install;

use littler\App;
use littler\library\InstallLocalModule;
use think\console\Command;
use think\console\Input;
use think\console\input\Option;
use think\console\Output;
use think\facade\Console;

class InstallProjectCommand extends Command
{
    protected $databaseLink = [];

    protected $defaultModule = ['permissions', 'system'];

    protected function configure()
    {
        $this->setName('lz:install')
            ->addOption('reinstall', '-r', Option::VALUE_NONE, 'reinstall back')
            ->setDescription('install project');
    }

    /**
     * @return null|int|void
     */
    protected function execute(Input $input, Output $output)
    {
        if ($input->getOption('reinstall')) {
            $this->reInstall();
            $this->project();
        } else {
            $this->detectionEnvironment();

            $this->firstStep();

            $this->secondStep();

            $this->thirdStep();

            $this->finished();

            $this->project();
        }
    }

    /**
     * 环境检测.
     */
    protected function detectionEnvironment(): void
    {
        $this->output->info('environment begin to check...');

        if (version_compare(PHP_VERSION, '7.1.0', '<')) {
            $this->output->error('php version should >= 7.1.0');
            exit();
        }

        $this->output->info('php version ' . PHP_VERSION);

        if (! extension_loaded('mbstring')) {
            $this->output->error('mbstring extension not install');
            exit();
        }
        $this->output->info('mbstring extension is installed');

        if (! extension_loaded('json')) {
            $this->output->error('json extension not install');
            exit();
        }
        $this->output->info('json extension is installed');

        if (! extension_loaded('openssl')) {
            $this->output->error('openssl extension not install');
            exit();
        }
        $this->output->info('openssl extension is installed');

        if (! extension_loaded('pdo')) {
            $this->output->error('pdo extension not install');
            exit();
        }
        $this->output->info('pdo extension is installed');

        if (! extension_loaded('xml')) {
            $this->output->error('xml extension not install');
            exit();
        }

        $this->output->info('xml extension is installed');

        $this->output->info('🎉 environment checking finished');
    }

    /**
     * 安装第一步.
     *
     * @return mixed
     */
    protected function firstStep()
    {
        if (file_exists($this->app->getRootPath() . '.env')) {
            return false;
        }

        // 设置 app domain
        $appDomain = strtolower($this->output->ask($this->input, '👉 first, you should set app domain: '));
        if (strpos($appDomain, 'http://') === false || strpos($appDomain, 'https://') === false) {
            $appDomain = 'http://' . $appDomain;
        }

        $answer = strtolower($this->output->ask($this->input, '🤔️ Did You Need to Set Database information? (Y/N): '));

        if ($answer === 'y' || $answer === 'yes') {
            $charset = $this->output->ask($this->input, '👉 please input database charset, default (utf8mb4):') ?: 'utf8mb4';
            $database = '';
            while (! $database) {
                $database = $this->output->ask($this->input, '👉 please input database name: ');
                if ($database) {
                    break;
                }
            }
            $host = $this->output->ask($this->input, '👉 please input database host, default (127.0.0.1):') ?: '127.0.0.1';
            $port = $this->output->ask($this->input, '👉 please input database host port, default (3306):') ?: '3306';
            $prefix = $this->output->ask($this->input, '👉 please input table prefix, default (null):') ?: '';
            $username = $this->output->ask($this->input, '👉 please input database username default (root): ') ?: 'root';
            $password = '';
            $tryTimes = 0;
            while (! $password) {
                $password = $this->output->ask($this->input, '👉 please input database password: ');
                if ($password) {
                    break;
                }
                // 尝试三次以上未填写，视为密码空
                ++$tryTimes;
                if (! $password && $tryTimes > 2) {
                    break;
                }
            }

            $this->databaseLink = [$host, $database, $username, $password, $port, $charset, $prefix];

            $this->generateEnvFile($host, $database, $username, $password, $port, $charset, $prefix, $appDomain);
        }
    }

    /**
     * 安装第二部.
     */
    protected function secondStep(): void
    {
        if (file_exists($this->getEnvFilePath())) {
            $connections = \config('database.connections');
            // 因为 env file 导致安装失败
            if (! $this->databaseLink) {
                unlink($this->getEnvFilePath());
                $this->execute($this->input, $this->output);
            } else {
                [
                    $connections['mysql']['hostname'],
                    $connections['mysql']['database'],
                    $connections['mysql']['username'],
                    $connections['mysql']['password'],
                    $connections['mysql']['hostport'],
                    $connections['mysql']['charset'],
                    $connections['mysql']['prefix'],
                ] = $this->databaseLink ?: [
                    env('mysql.hostname'),
                ];

                \config([
                    'connections' => $connections,
                ], 'database');

                $this->migrateAndSeeds();
            }
        }
    }

    /**
     * 生成表结构.
     */
    protected function migrateAndSeeds(): void
    {
        foreach ($this->defaultModule as $m) {
            $module = new InstallLocalModule($m);
            $module->installModuleTables();
            $module->installModuleSeeds();
            $this->output->info('🎉 module [' . $m . '] installed successfully');
        }
    }

    /**
     * 回滚数据.
     */
    protected function migrateRollback()
    {
        foreach ($this->defaultModule as $m) {
            $module = new InstallLocalModule($m);
            $module->rollbackModuleTable();
            $this->output->info('🎉' . $m . ' tables rollback successfully');
        }
    }

    /**
     * 安装第四步.
     */
    protected function thirdStep(): void
    {
        // Console::call('lz:cache');
    }

    /**
     * finally.
     */
    protected function finished(): void
    {
        // todo something
        // create jwt
        Console::call('jwt:create');
        // create service
        Console::call('lz-service:discover');
    }

    /**
     * generate env file.
     *
     * @param $host
     * @param $database
     * @param $username
     * @param $password
     * @param $port
     * @param $charset
     * @param $prefix
     * @param $appDomain
     */
    protected function generateEnvFile($host, $database, $username, $password, $port, $charset, $prefix, $appDomain): void
    {
        try {
            $env = \parse_ini_file(root_path() . '.example.env', true);

            $env['APP']['DOMAIN'] = $appDomain;
            $env['DATABASE']['HOSTNAME'] = $host;
            $env['DATABASE']['DATABASE'] = $database;
            $env['DATABASE']['USERNAME'] = $username;
            $env['DATABASE']['PASSWORD'] = $password;
            $env['DATABASE']['HOSTPORT'] = $port;
            $env['DATABASE']['CHARSET'] = $charset;
            if ($prefix) {
                $env['DATABASE']['PREFIX'] = $prefix;
            }
            $dotEnv = '';
            foreach ($env as $key => $e) {
                if (is_string($e)) {
                    $dotEnv .= sprintf('%s = %s', $key, $e === '1' ? 'true' : ($e === '' ? 'false' : $e)) . PHP_EOL;
                    $dotEnv .= PHP_EOL;
                } else {
                    $dotEnv .= sprintf('[%s]', $key) . PHP_EOL;
                    foreach ($e as $k => $v) {
                        $dotEnv .= sprintf('%s = %s', $k, $v === '1' ? 'true' : ($v === '' ? 'false' : $v)) . PHP_EOL;
                    }

                    $dotEnv .= PHP_EOL;
                }
            }

            if ($this->getEnvFile()) {
                $this->output->info('env file has been generated');
            }
            if ((new \mysqli($host, $username, $password, null, $port))->query(sprintf(
                'CREATE DATABASE IF NOT EXISTS %s DEFAULT CHARSET %s COLLATE %s_general_ci;',
                $database,
                $charset,
                $charset
            ))) {
                $this->output->info(sprintf('🎉 create database %s successfully', $database));
            } else {
                $this->output->warning(sprintf('create database %s failed，you need create database first by yourself', $database));
            }
        } catch (\Exception $e) {
            $this->output->error($e->getMessage());
            exit(0);
        }

        file_put_contents(root_path() . '.env', $dotEnv);
    }

    protected function getEnvFile(): string
    {
        return file_exists(root_path() . '.env') ? root_path() . '.env' : '';
    }

    protected function project()
    {
        $year = date('Y');

        $this->output->info('🎉 project is installed, welcome!');

        $this->output->info(sprintf('
 /-------------------- welcome to use -------------------------\
|               __       __       ___       __          _      |
|   _________ _/ /______/ /_     /   | ____/ /___ ___  (_)___  |
|  / ___/ __ `/ __/ ___/ __ \   / /| |/ __  / __ `__ \/ / __ \ |
| / /__/ /_/ / /_/ /__/ / / /  / ___ / /_/ / / / / / / / / / / |
| \___/\__,_/\__/\___/_/ /_/  /_/  |_\__,_/_/ /_/ /_/_/_/ /_/  |
|                                                              |
 \ __ __ __ __ _ __ _ __ enjoy it ! _ __ __ __ __ __ __ ___ _ @ 2017 ～ %s
 版本: %s
 初始账号: little@admin.com
 初始密码: little@admin
', $year, App::VERSION));
        exit(0);
    }

    protected function reInstall(): void
    {
        $ask = strtolower($this->output->ask($this->input, 'reset project? (Y/N)'));

        if ($ask === 'y' || $ask === 'yes') {
            $this->migrateRollback();

            $this->migrateAndSeeds();

            $this->finished();
        }
    }

    /**
     * 获取 env path.
     *
     * @return string
     */
    protected function getEnvFilePath()
    {
        return root_path() . '.env';
    }
}
