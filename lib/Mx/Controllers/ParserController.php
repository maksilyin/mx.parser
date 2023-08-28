<?php

namespace Mx\Controllers;

use \Bitrix\Main\Request;
use Mx\Helpers\ParserCatalog;
use Mx\Helpers\StatusHelper;

class ParserController extends BaseController
{
    public function __construct(Request $request)
    {
        parent::__construct($request);
    }

    public function indexAction()
    {
        $parserCatalog = new ParserCatalog();

        $step = StatusHelper::STATUS_END;

        if ($_REQUEST['step']) {
            $step = $_REQUEST['step'];
        }

        $parserCatalog->process($step);

        /*if ($_REQUEST['status']) {
            $step = $_REQUEST['step'];
        }

        $command = 'php -f '.$_SERVER['DOCUMENT_ROOT'].'/local/modules/mx.parser/parse.php '.$step;

        exec($command);*/

        $this->setResponse(200, true, true);
    }

    public function statusAction()
    {
        $this->setResponse(200, true, StatusHelper::getStatus());
    }

    public function logAction()
    {
        $this->setResponse(200, true, StatusHelper::getLog());
    }
}
