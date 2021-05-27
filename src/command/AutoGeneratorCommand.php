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
namespace littler\command;

use littler\generate\BatchGenerator;
use littler\library\Composer;
use think\console\Command;
use think\console\Input;
use think\console\Output;

class AutoGeneratorCommand extends Command
{
    const RELY_PACKAGE = 'nikic/php-parser';

    protected function configure()
    {
        $this->setName('lz-auto:batch')
            ->setDescription('批量生成控制器和模型');
    }

    protected function execute(Input $input, Output $output)
    {
        // 判断是否安装了扩展包
        if (! (new Composer())->hasPackage(self::RELY_PACKAGE)) {
            $output->error(sprintf('you must use [ composer require --dev %s]', self::RELY_PACKAGE));
            exit(0);
        }
        $message = (new BatchGenerator())->done();
        // dd($message);
        $output->info('success');
    }
}
