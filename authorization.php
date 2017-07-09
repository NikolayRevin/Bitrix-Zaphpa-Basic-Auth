<?php
namespace MyWebservice;

use Zaphpa;
use Bitrix\Main\Localization\Loc;
use Bitrix\Main\Loader;

class Authorization extends \Zaphpa\BaseMiddleware
{

    public function __construct()
    {}

    public function preroute(Zaphpa\Request &$req, Zaphpa\Response &$res)
    {
        $login = $_SERVER['PHP_AUTH_USER'];
        $password = $_SERVER['PHP_AUTH_PW'];

        $bErrorAuth = false;

        if (empty($login) || empty($password)) {
            $bErrorAuth = true;
        } else {
            global $USER;

            if (! is_object($USER)) {
                $USER = new \CUser();
            }

            $bErrorAuth = ! $USER->Login($login, $password);

            if (! $bErrorAuth) {
                // add check user group
                $arGroups = $USER->GetUserGroupArray();
                $bErrorAuth = !in_array(1, $arGroups);
            }
        }

        if ($bErrorAuth) {
            $code = Constants::CODE_UNAUTH;
            $res->disableBrowserCache();
            $ob = new ResultError($code, Loc::getMessage("WEBSERVICE_SERVER_ERROR_ATHORIZATION"));
            $res->add($ob->toJSON());
            $res->send($code, "json");
        }
    }
}
?>
