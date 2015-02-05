<?php

namespace Wms\Controller;

/**
 * Description of Action
 *
 * @author Renato Medina <medinadato@gmail.com>
 */
class Action extends \Core\Controller\Action
{
    protected $_startTime;
    protected $_endTime;
    protected $_totalTime;

    /**
     * Adds a flash message to the messages stack
     * @param type $type error, success, warning
     * @param type $message
     */
    public function addFlashMessage($type, $message)
    {
        $this->_helper->messenger($type, $message);
    }

    public function preDispatch()
    {
        parent::preDispatch();
        $this->_startTime = (float) array_sum(explode(' ',microtime()));
    }

    public function postDispatch()
    {
        parent::postDispatch();
        $this->_endTime = (float) array_sum(explode(' ',microtime()));
        $this->_totalTime = $this->_endTime - $this->_startTime;
        $this->view->totalTimePage = $this->_totalTime;
    }

    public function createXml($resposta, $message, $redirectUrl = null, $elements = array())
    {
        $this->_helper->layout()->disableLayout();
        $this->_helper->viewRenderer->setNoRender(true);
        $dom = new \DOMDocument("1.0", "UTF-8");
        $dom->preserveWhiteSpace = false;
        $dom->formatOutput = true;
        $root = $dom->createElement("retorno");
        $resposta = $dom->createElement("resposta", $resposta);
        $message = $dom->createElement("message", $message);

        if ($redirectUrl) {
            $redirect = $dom->createElement('redirect', $redirectUrl);
            $root->appendChild($redirect);
        }

        if (count($elements) > 0) {
            foreach($elements as $element) {
                $newElement = $dom->createElement($element['name'], $element['value']);
                $root->appendChild($newElement);
                unset($newElement);
            }
        }

        $root->appendChild($resposta);
        $root->appendChild($message);
        $dom->appendChild($root);
        header("Content-Type: text/xml");
        print $dom->saveXML();
        exit;
    }

    public function createUrlMobile($action = 'liberar-os')
    {
        $placa = $this->getRequest()->getParam('placa',null);
        $tipoConferencia = $this->getRequest()->getParam('tipo-conferencia', null);
        $volume = $this->getRequest()->getParam('volume', null);
        $idExpedicao = $this->getRequest()->getParam('idExpedicao');

        $url = $this->view->url(array(
            'controller' => 'expedicao',
            'action' => $action,
            'idExpedicao' => $idExpedicao,
            'tipo-conferencia' => $tipoConferencia,
            'volume' => $volume,
            'placa' => $placa
        ));
        return $url;
    }

    public function exportCSV($arrayValues = array(), $fileName = "", $exportHeader = true)
    {
        $file = '';

        if (count($arrayValues) > 0) {
            if ($exportHeader == true) {
                $header = $arrayValues[0];
               // if ($header == null)
                   // $header = "Não existem nenhum registro com o filtro informado";
                $strLine = "";
                foreach ($header as $key => $line) {
                    $strLine = $strLine . strtolower($key);
                    if($strLine != "") $strLine = $strLine . ";";
                }
                $file .= $strLine . PHP_EOL;
            }

            foreach($arrayValues as $line) {
                $strLine = "";
                foreach ($line as $field) {

                    $strField = $field;
                    if (($field instanceof \DateTime) == true) {
                        $strField = $field->format('d/m/Y');
                    }
                    $strLine = $strLine . $strField;
                    if($strLine != "") $strLine = $strLine . ";";
                }
                $file .= $strLine . PHP_EOL;
            }
        }

        header('Content-Type: application/csv');
        header('Content-disposition: attachment; filename=' .$fileName .'.csv');
        echo $file;
        exit;
    }

    public function exportPDF($array = array(), $filename, $titulo, $orientacao)
    {
         $pdf = new \Wms\Module\Web\Report\Generico($orientacao);
         $pdf->init($array, $filename, $titulo);
    }

    public function getSystemParameterValue($param) {
        $parametroRepo = $this->getEntityManager()->getRepository('wms:Sistema\Parametro');
        $parametro = $parametroRepo->findOneBy(array('constante' => $param));

        if ($parametro == NULL) {
            return "";
        } else {
            return $parametro->getValor();
        }
    }
}