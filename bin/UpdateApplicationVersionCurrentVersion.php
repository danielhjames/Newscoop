<?php
/*
 * This file is part of the project RMT
 *
 * Copyright (c) 2013, Liip AG, http://www.liip.ch
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

use Liip\RMT\Information\InformationRequest;
use Liip\RMT\Context;

/**
 * Class UpdateApplicationVersionCurrentVersion
 *
 * Custom pre-release action for updating the version number in the application
 */
class UpdateApplicationVersionCurrentVersion extends \Liip\RMT\Action\BaseAction
{
    public function getTitle() {
        return "Application version update";
    }

    public function execute()
    {
        // Output for devs
        $newVersion = Context::getParam('new-version');

        $appFile = realpath(__DIR__.'/../newscoop/library/Newscoop/Version.php');
        Context::get('output')->write("New version [<yellow>$newVersion</yellow>] udpated into $appFile: ");
        $fileContent = file_get_contents($appFile);
        //const VERSION = '4.3';
        $fileContent = preg_replace('/(.*const VERSION = .*;)/', "    const VERSION = '$newVersion';", $fileContent);
        file_put_contents($appFile, $fileContent);

        $this->confirmSuccess();
    }
}
