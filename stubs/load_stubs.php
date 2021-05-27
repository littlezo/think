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
if (! \class_exists('ApiProtocol')) {
    require __DIR__ . '/ApiProtocol.php';
}

if (! \class_exists('File')) {
    require __DIR__ . '/File.php';
}

if (! \class_exists('Http')) {
    require __DIR__ . '/Http.php';
}
if (! \class_exists('QRCode')) {
    require __DIR__ . '/QRCode.php';
}
if (! \class_exists('Response')) {
    require __DIR__ . '/Response.php';
}

if (! \class_exists('RSA')) {
    require __DIR__ . '/RSA.php';
}

if (! \class_exists('Sign')) {
    require __DIR__ . '/Sign.php';
}
