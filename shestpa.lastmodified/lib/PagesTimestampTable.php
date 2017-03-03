<?php
namespace Shestpa\Lastmodified;

defined('B_PROLOG_INCLUDED') and (B_PROLOG_INCLUDED === true) or die();

use Bitrix\Main\Entity\DataManager;
use Bitrix\Main\Entity\IntegerField;
use Bitrix\Main\Entity\StringField;
use Bitrix\Main\Entity\Validator;
use Bitrix\Main\Localization\Loc;

Loc::loadMessages(__FILE__);

class PagesTimestampTable extends DataManager
{
    public static function getTableName()
    {
        return 'pages_timestamp';
    }

    public static function getMap()
    {
        return array(
            new IntegerField('ID', array(
                'autocomplete' => true,
                'primary' => true,
                'title' => Loc::getMessage('ID'),
            )),
            new StringField('URL', array(
                'required' => true,
                'title' => Loc::getMessage('URL'),
                'validation' => function () {
                    return array(
                        new Validator\Length(null, 255),
                    );
                },
            )),
            new StringField('HASH', array(
                'required' => true,
                'title' => Loc::getMessage('HASH'),
                'validation' => function () {
                    return array(
                        new Validator\Length(null, 255),
                    );
                },
            )),
            new StringField('LAST_MODIFIED', array(
                'required' => true,
                'title' => Loc::getMessage('LAST_MODIFIED'),
                'validation' => function () {
                    return array(
                        new Validator\Length(null, 255),
                    );
                },
            )),
        );
    }
}
