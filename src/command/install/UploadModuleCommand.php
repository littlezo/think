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
use littler\facade\Http;
use littler\library\Compress;
use think\console\Command;
use think\console\Input;
use think\console\input\Argument;
use think\console\input\Option;
use think\console\Output;

class UploadModuleCommand extends Command
{
    protected $module;

    protected $path;

    /**
     * @var Compress
     */
    protected $compress;

    /**
     * 检查模块文件.
     *
     * @return mixed
     */
    public function checking()
    {
        if (! file_exists($this->path . 'module.json')) {
            $this->error('there is no module.json file');
        }

        if (! file_exists($this->path . 'path.php')) {
            $this->error('there is no path.php file');
        }

        $module = \json_decode(file_get_contents($this->path . 'module.json'), true);

        if (! isset($module['name']) && ! $module['name']) {
            $this->error('module.json not set name');
        }

        if (! isset($module['version']) && ! $module['name']) {
            $this->error('module.json not set version');
        }

        if (! isset($module['services']) && empty($module['services'])) {
            $this->error('module.json not set services');
        }

        $services = $module['services'];

        foreach ($services as $service) {
            $s = explode('\\', $service);
            $serviceName = array_pop($s);

            if (! file_exists($this->path . $serviceName . '.php')) {
                $this->error("[{$serviceName}] Service not found");
            }
        }

        $this->output->info('checking has no problem');

        return $module;
    }

    protected function configure()
    {
        $this->setName('upload:module')
            ->addArgument('module', Argument::REQUIRED, 'module name')
            ->addOption('path', '-p', Option::VALUE_OPTIONAL, 'path that you need')
            ->setDescription('install little module');
    }

    protected function execute(Input $input, Output $output)
    {
        try {
            $this->module = $this->input->getArgument('module');
            $this->path = $this->getCompressPath($this->input->getOption('path'));

            $moduleInfo = $this->checking();
            // 打包项目
            $moduleZip = $this->compressModule();
            // 认证用户
            $name = $this->output->ask($this->input, 'please input your name');

            $password = $this->output->ask($this->input, 'please input your password');

            $token = $this->authenticate($name, $password);
            // 上传
            $this->upload($token, $moduleZip);
            $this->output->info('upload successfully!');
        } catch (\Throwable $e) {
            $this->error($e->getMessage() . ': Error happens at ' . $e->getFile() . ' ' . $e->getLine() . '行');
        }
    }

    /**
     * 认证用户.
     *
     * @param $name
     * @param $password
     * @return mixed
     */
    protected function authenticate($name, $password)
    {
        $response = Http::form([
            'username' => $name,
            'password' => $password,
        ])->post($this->authenticateAddress());

        $data = $response->json();

        if ($data['code'] == 10000) {
            return $data['data'];
        }

        $this->error('login failed');
    }

    /**
     * 上传地址
     *
     * @return mixed
     */
    protected function uploadAddress()
    {
        return env('API_URL') . '/upload/module';
    }

    protected function authenticateAddress()
    {
        return env('API_URL') . '/developer/authenticate';
    }

    /**
     * 上传.
     *
     * @param $token
     * @param $zip
     * @return bool
     */
    protected function upload($token, $zip)
    {
        return Http::token($token)
            ->attach('module', fopen($zip, 'r+'), pathinfo($zip, PATHINFO_FILENAME))
            ->post($this->uploadAddress())->ok();
    }

    /**
     * 打包模块.
     *
     * @return bool
     */
    protected function compressModule()
    {
        $composerZip = $this->compressZipPath() . $this->module . '_' . time() . '.zip';

        (new Compress())->moduleToZip($this->path, $composerZip);

        $this->output->info('compress module ' . $this->module . ' successfully');
        return $composerZip;
    }

    /**
     * 获取打包的path.
     *
     * @param $path
     * @return string
     */
    protected function getCompressPath($path)
    {
        if ($path) {
            return root_path($path) . $this->module . DIRECTORY_SEPARATOR;
        }

        return App::moduleDirectory($this->module);
    }

    /**
     * zip 打包路径.
     *
     * @return string
     */
    protected function compressZipPath()
    {
        return App::makeDirectory(runtime_path('little' . DIRECTORY_SEPARATOR . 'compress'));
    }

    /**
     * 输出错误信息.
     */
    protected function error(string $message)
    {
        exit($this->output->error($message));
    }
}
