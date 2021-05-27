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
namespace littler\library\crontab;

use littler\library\crontab\Process as MProcess;
use Swoole\Process;
use Swoole\Timer;

class Master
{
    use RegisterSignal;
    use MProcess;
    use Store;
    use Table;

    // 版本
    const VERSION = '1.0.0';

    // process 等待状态
    const WAITING = 'waiting';

    // process 繁忙状态
    const BUSYING = 'busying';

    /**
     * 动态扩展的最大 process 数量.
     *
     * @var int
     */
    protected $maxNum;

    /**
     * 常驻 process.
     *
     * @var int
     */
    protected $staticNum;

    /**
     * 临时进程数量.
     *
     * @var int
     */
    protected $temporaryNum = 0;

    /**
     * 存储 process 信息.
     *
     * @var array
     */
    protected $processes = [];

    /**
     * 主进程ID.
     *
     * @var
     */
    protected $master_pid;

    /**
     * @var string
     */
    protected $kernel;

    /**
     * pid 文件名称.
     *
     * @var string
     */
    protected $mater = 'lz-master';

    /**
     * @var int
     */
    protected $master_start_at;

    /**
     * @var bool
     */
    protected $daemon = false;

    /**
     * 启动进程.
     */
    public function start()
    {
        // 守护进程
        if ($this->daemon) {
            Process::daemon(true, false);
        }
        // alarm 信号
        // Process::alarm(1000 * 1000);
        // 1s 调度一次
        $this->timeTick(1000, $this->schedule());
        // 注册信号
        $this->registerSignal();
        // pid
        $this->master_pid = getmypid();
        $this->master_start_at = time();
        // 初始化
        $this->init();
        // 存储 pid
        $this->storeMasterPid($this->master_pid);

        // 初始化进程
        $this->initProcesses();
    }

    /**
     * 开启 debug.
     *
     * @return $this
     */
    public function daemon()
    {
        $this->daemon = true;

        return $this;
    }

    /**
     * 自定义 tick 关闭协程.
     *
     * @param $callable
     */
    protected function timeTick(int $time, $callable)
    {
        // 关闭协程
        Timer::set([
            'enable_coroutine' => false,
        ]);

        Timer::tick($time, $callable);
    }

    /**
     * 调度.
     *
     * @return \Closure
     */
    protected function schedule()
    {
        return function () {
            $kernel = new $this->kernel();
            foreach ($kernel->tasks() as $cron) {
                if ($cron->can()) {
                    [$waiting, $process] = $this->hasWaitingProcess();
                    if ($waiting) {
                        // 向 process 投递 cron
                        $process->push(serialize($cron));
                    } else {
                        // 创建临时 process 处理，处理完自动销毁
                        $this->createProcess($cron);
                    }
                }
            }
        };
    }

    /**
     * Create Task.
     */
    protected function createProcess(Cron $cron)
    {
        if ($this->isCanCreateTemporaryProcess()) {
            $process = new Process(function (Process $process) use ($cron) {
                $cron->run();
                $process->exit();
            });

            // $process->name(sprintf('worker: '));

            $process->start();

            ++$this->temporaryNum;
        }
    }

    /**
     * 是否可以创建临时进程.
     *
     * @return bool
     */
    protected function isCanCreateTemporaryProcess()
    {
        return ($this->table->count() + $this->temporaryNum) < $this->maxNum;
    }

    /**
     * 创建静态 worker 进程.
     *
     * @return Process
     */
    protected function createStaticProcess()
    {
        $process = new Process($this->createProcessCallback());

        // 使用非阻塞队列
        $process->useQueue(1, 2 | Process::IPC_NOWAIT);

        return $process;
    }

    /**
     * 初始化 workers.
     */
    protected function initProcesses()
    {
        for ($i = 0; $i < $this->staticNum; ++$i) {
            $process = $this->createStaticProcess();
            // $worker->name("[$i+1]little-worker");

            $process->start();

            $this->processes[$process->pid] = $process;

            $this->addColumn($this->getColumnKey($process->pid), $this->processInfo($process));
        }
    }

    /**
     * 栏目 KEY.
     *
     * @param $pid
     * @return string
     */
    protected function getColumnKey($pid)
    {
        return 'process_' . $pid;
    }

    /**
     * 初始化文件.
     */
    protected function init()
    {
        $this->staticNum = config('little.schedule.static_worker_number');

        $this->maxNum = config('little.schedule.max_worker_number');

        $this->initLog();

        file_put_contents($this->getSaveProcessStatusFile(), '');

        $this->createTable();

        $this->kernel = config('little.schedule.schedule_kernel');
    }

    /**
     * 日志初始化.
     */
    protected function initLog()
    {
        $channels = config('log.channels');

        $channels['schedule'] = config('little.schedule.log');

        config([
            'channels' => $channels,
            'default' => 'schedule',
        ], 'log');
    }
}
