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
namespace littler\generate\factory;

use littler\exceptions\FailedException;
use littler\facade\FileSystem;
use littler\generate\build\Build;
use littler\generate\build\classes\Classes;
use littler\generate\build\classes\Methods;
use littler\generate\build\classes\Property;
use littler\generate\build\classes\Traits;
use littler\generate\build\classes\Uses;
use think\helper\Str;

class Controller extends Factory
{
    protected $methods = [];

    protected $uses = [
        'littler\BaseRequest as Request',
        'littler\Response',
        'littler\BaseController',
    ];

    /**
     * @param $params
     * @return bool|string|string[]
     */
    public function done(array $params)
    {
        // 写入成功之后
        $repositoryPath = $this->getGeneratePath($params['controller_repository']);
        $controllerPath = $this->getGeneratePath($params['controller']);
        try {
            if (! FileSystem::put($repositoryPath, $this->getTraitsContent($params))) {
                throw new FailedException($params['controller'] . ' generate failed~');
            }
            // dd($this->getControllerContent($params));
            if (! file_exists($controllerPath)) {
                FileSystem::put($controllerPath, $this->getControllerContent($params));
            }
            return $controllerPath;
        } catch (\Throwable $exception) {
            throw new \Exception((string) $exception->getTraceAsString());
        }
    }

    /**
     * 获取内容.
     *
     * @param $params
     * @return bool|string|string[]
     */
    public function getTraitsContent($params)
    {
        if (! $params['controller_repository']) {
            throw new FailedException('params has lost～');
        }
        [$className, $namespace] = $this->parseFilename($params['controller_repository']);

        [$model, $modelNamespace] = $this->parseFilename($params['model']);
        $asModel = ucfirst(Str::contains($model, 'Model') ? $model : $model . 'Model');

        if (! $className) {
            throw new FailedException('未填写控制器名称');
        }

        $use = new Uses();
        $class = new Classes($className, 'trait');
        // dd($class);
        return (new Build())->namespace($namespace)
            ->use($use->name('littler\BaseRequest', 'Request'))
            ->use($use->name('littler\Response'))
            ->use($use->name($modelNamespace . '\\' . ucfirst($model), $asModel))
            ->class(
                $class->docComment(),
                function (Classes $class) use ($model, $asModel) {
                    foreach ($this->getMethods($model, $asModel) as $method) {
                        $class->addMethod($method);
                    }

                    $class->addProperty(
                        (new Property(lcfirst($model)))->protected()
                    );
                    // dd($class);
                }
            )
            ->getContent();
    }

    /**
     * 获取内容.
     *
     * @param $params
     * @return bool|string|string[]
     */
    public function getControllerContent($params)
    {
        if (! $params['controller']) {
            throw new FailedException('params has lost～');
        }

        // parse controller
        [$className, $namespace] = $this->parseFilename($params['controller']);

        [$repository, $repositoryNamespace] = $this->parseFilename($params['controller_repository']);

        if (! $className) {
            throw new FailedException('未填写控制器名称');
        }

        $use = new Uses();
        $class = new Classes($className);
        // dd($class);
        $date = date('Y年m月d日 H:i');
        return (new Build())->namespace($namespace)
            ->use($use->name($params['controller_repository']))
            ->use($use->name('littler\BaseController', 'Controller'))
            ->use($use->name('littler\BaseRequest', 'Request'))
            ->use($use->name('littler\Response'))
            ->class(
                $class->extend('Controller')
                    ->addTrait((new Traits())->use($repository))
                    ->docComment(
                        <<<TEXT

                            /**
                             * {$className} 控制器
                             * @time {$date}
                             * @version 1.0.0
                             */

                            TEXT
                    ),
                function () {
                    // dd($class);
                }
            )
            ->getContent();
    }

    /**
     * 方法集合.
     *
     * @param $model
     * @param mixed $asModel
     * @return array
     */
    protected function getMethods($model, $asModel)
    {
        $date = date('Y年m月d日 H:i');
        $model = lcfirst($model);
        return [
            (new Methods('__construct'))
                ->public()
                ->param($asModel, ucfirst($asModel))
                ->docComment("\r\n")
                ->declare($model, $asModel),

            (new Methods('index'))->public()
                ->param('request', 'Request')
                ->docComment(
                    <<<TEXT

                        /**
                         * 列表
                         * @time {$date}
                         * @param Request \$request
                         */
                        TEXT
                )
                ->returnType('\think\Response')->index($model),

            (new Methods('save'))
                ->public()
                ->param('request', 'Request')
                ->docComment(
                    <<<TEXT

                        /**
                         * 保存信息
                         * @time {$date}
                         * @param Request \$request
                         */
                        TEXT
                )
                ->returnType('\think\Response')
                ->save($model),

            (new Methods('read'))->public()
                ->param('id')
                ->docComment(
                    <<<TEXT

                        /**
                         * 读取
                         * @time {$date}
                         * @param \$id
                         */
                        TEXT
                )
                ->returnType('\think\Response')->read($model),

            (new Methods('update'))->public()
                ->param('request', 'Request')
                ->param('id')
                ->docComment(
                    <<<TEXT

                        /**
                         * 更新
                         * @time {$date}
                         * @param Request \$request
                         * @param \$id
                         */
                        TEXT
                )
                ->returnType('\think\Response')->update($model),

            (new Methods('delete'))->public()
                ->param('id')
                ->docComment(
                    <<<TEXT

                        /**
                         * 删除
                         * @time {$date}
                         * @param \$id
                         */
                        TEXT
                )
                ->returnType('\think\Response')->delete($model),
        ];
    }
}
