<?php

use Bitrix\Main\Application;
use Bitrix\Main\Config\Option;
use Bitrix\Main\Localization\Loc;
use Bitrix\Main\Text\HtmlFilter;

defined('ADMIN_MODULE_NAME') or define('ADMIN_MODULE_NAME', 'novikov.diplom');

if (!$USER->isAdmin()) {
    $APPLICATION->authForm('Nope');
}

$app = Application::getInstance();
$context = $app->getContext();
$request = $context->getRequest();

$bash_path = "/home/bitrix/www/bitrix/modules/novikov.diplom/bash";

#Loc::loadMessages($context->getServer()->getDocumentRoot()."/bitrix/modules/main/options.php");
Loc::loadMessages(__FILE__);

$tabControl = new CAdminTabControl("tabControl", array(
    array(
        "DIV" => "edit1",
        "TAB" => Loc::getMessage("MAIN_TAB_SET"),
        "TITLE" => Loc::getMessage("MAIN_TAB_TITLE_SET"),
    ),
));

if ((!empty($save) || !empty($restore)) && $request->isPost() && check_bitrix_sessid()) {
    if (!empty($restore)) {
        Option::delete(ADMIN_MODULE_NAME);
        CAdminMessage::showMessage(array(
            "MESSAGE" => Loc::getMessage("REFERENCES_OPTIONS_RESTORED"),
            "TYPE" => "OK",
        ));
    } elseif ($request->getPost('ip_protection')) {
        $bash_script = "$bash_path/ip.sh"; 
        exec($bash_script); 
        CAdminMessage::showMessage(array(
            "MESSAGE" => Loc::getMessage("REFERENCES_OPTIONS_SAVED"),
            "TYPE" => "OK",
        ));
        exec("echo 1 > $bash_path/ip");
    }  elseif ($request->getPost('request_protection')) {
        $bash_script = "$bash_path/dir_protection.sh"; 
        exec($bash_script); 
        CAdminMessage::showMessage(array(
            "MESSAGE" => Loc::getMessage("REFERENCES_OPTIONS_SAVED"), 
            "TYPE" => "OK",
        ));
        exec("echo 1 > $bash_path/dp");
    }  elseif ( ! $request->getPost('request_protection')) {  exec("echo 0 > $bash_path/dp"); 
    }  elseif ( ! $request->getPost('ip_protection')) {       exec("echo 0 > $bash_path/ip");
    }  else {        CAdminMessage::showMessage(Loc::getMessage("REFERENCES_INVALID_VALUE"));
    }
}

$tabControl->begin();

?>


<form method="post" action="<?=sprintf('%s?mid=%s&lang=%s', $request->getRequestedPage(), urlencode($mid), LANGUAGE_ID)?>">
    <?php
    echo bitrix_sessid_post();
    $tabControl->beginNextTab();
    ?>
    <tr>
        <td width="40%">
            <label for="ip_protection"><?=Loc::getMessage("REFERENCES_IP_PROTECTION") ?>:</label>
        <td width="60%">
           <?php
                $file = file_get_contents("$bash_path/ip");
                if ($file=="1"):
            ?>
            <input type="checkbox" name="ip_protection" checked />
            <?php else: ?>
            <input type="checkbox" name="ip_protection" />
            <?php endif; ?>
        </td>
    </tr>
    <br>
    <tr>
        <td width="40%">
            <label for="request_protection"><?=Loc::getMessage("REFERENCES_REQUEST_PROTECTION") ?>:</label>
        <td width="60%">
            <?php
                $file = file_get_contents("$bash_path/dp");
                if ($file=="1"):
            ?>
            <input type="checkbox" name="request_protection" checked />
            <?php else: ?>
            <input type="checkbox" name="request_protection" />
            <?php endif; ?>
        </td>
    </tr>

    <?php
    unset($file);
    $tabControl->buttons();
    ?>
    <input type="submit"
           name="save"
           value="<?=Loc::getMessage("MAIN_SAVE") ?>"
           title="<?=Loc::getMessage("MAIN_OPT_SAVE_TITLE") ?>"
           class="adm-btn-save"
           />
    <input type="submit"
           name="restore"
           title="<?=Loc::getMessage("MAIN_HINT_RESTORE_DEFAULTS") ?>"
           onclick="return confirm('<?= AddSlashes(GetMessage("MAIN_HINT_RESTORE_DEFAULTS_WARNING")) ?>')"
           value="<?=Loc::getMessage("MAIN_RESTORE_DEFAULTS") ?>"
           />
    <?php
    $tabControl->end();
    ?>
</form>
    