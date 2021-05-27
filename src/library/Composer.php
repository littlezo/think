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
namespace littler\library;

use littler\facade\FileSystem;

class Composer
{
    /**
     * psr4.
     *
     * @return mixed
     */
    public function psr4Autoload()
    {
        return $this->content()['autoload']['psr-4'];
    }

    /**
     * packages psr4.
     *
     * @return mixed
     */
    public function packagesPsr4Autoload()
    {
        return include app()->getRootPath() . ('vendor/composer/autoload_psr4.php');
    }

    /**
     * require.
     *
     * @return mixed
     */
    public function requires()
    {
        return $this->content()['require'];
    }

    /**
     * require dev.
     *
     * @return mixed
     */
    public function requireDev()
    {
        return $this->content()['require-dev'];
    }

    /**
     * composer has package.
     *
     * @param $name
     * @return bool
     */
    public function hasPackage($name)
    {
        $packages = array_merge($this->requires(), $this->requireDev());

        return in_array($name, array_keys($packages));
    }

    /**
     * composer content.
     *
     * @return mixed
     */
    protected function content()
    {
        return \json_decode((string) FileSystem::sharedGet($this->path()), true);
    }

    /**
     * composer path.
     *
     * @return string
     */
    protected function path()
    {
        return root_path() . 'composer.json';
    }
}
