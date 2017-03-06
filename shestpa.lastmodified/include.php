<?php
defined('B_PROLOG_INCLUDED') and (B_PROLOG_INCLUDED === true) or die();

use Bitrix\Main\Loader;
use Bitrix\Main\EventManager;

Loader::registerAutoLoadClasses('shestpa.lastmodified', array(
    'Shestpa\Lastmodified\PagesTimestampTable' => 'lib/PagesTimestampTable.php',
    'Shestpa\Lastmodified\PagesBufferPurifier' => 'lib/PagesBufferPurifier.php'
));

EventManager::getInstance()->addEventHandler('main', 'OnEndBufferContent', function($content){
    if (!defined('ADMIN_SECTION') && !defined('ERROR_404')) {

        global $USER, $APPLICATION;
        $page = $APPLICATION->GetCurPage();
        $arGroups = $USER->GetUserGroupArray();

        Shestpa\Lastmodified\PagesBufferPurifier::deleteKernelJs($content);
        Shestpa\Lastmodified\PagesBufferPurifier::deleteKernelCss($content);

        $hash = md5($content);

        $pageHash = md5($page.implode('', $arGroups));

        $lastModified = time();
        $date = gmdate('D, d M Y H:i:s T', $lastModified);

        try {
            $res = Shestpa\Lastmodified\PagesTimestampTable::getList(
                array(
                    'filter' => array('URL' => $pageHash)
                )
            )->fetch();

            if(!$res): // No hash in DB
                Shestpa\Lastmodified\PagesTimestampTable::add(
                    array(
                        "URL" => $pageHash,
                        "LAST_MODIFIED" => $date,
                        "HASH" => $hash
                    )
                );

                $status = 'added';

            else:
                if($res['HASH'] == $hash): // Not modified
                    $date = $res['LAST_MODIFIED'];
                    $lastModified = strtotime($res['LAST_MODIFIED']);
                    $status = 'notmod';
                else: // Modified
                    Shestpa\Lastmodified\PagesTimestampTable::update(
                        $res['ID'],
                        array(
                            "LAST_MODIFIED" => $date,
                            "HASH" => $hash
                        )
                    );
                    $status = 'mod';
                endif;
            endif;

            header('Last-Modified: '.$date);
            if ($lastModified)
            {
                $arr = apache_request_headers();
                foreach ($arr as $header => $value)
                {
                    if ($header == 'If-Modified-Since')
                    {
                        $ifModifiedSince = strtotime($value);
                        if ($ifModifiedSince > $lastModified)
                        {
                            $GLOBALS['APPLICATION']->RestartBuffer();
                            CHTTP::SetStatus('304 Not Modified');
                        }
                    }
                }
            }

            header('Hash-Modified: '.$status.$hash);
        } catch (Exception $e) {

        }
    }
});