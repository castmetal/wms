<?php
use Wms\Module\Web\Controller\Action,
    Wms\Module\Web\Grid\Expedicao\Pendencias as PendentesGrid,
    Wms\Module\Expedicao\Report\ProdutosSemConferencia as ProdutosSemConferenciaReport;

class Expedicao_PendenciaController  extends Action
{
    public function indexAction() {
        $idExpedicao = $this->getRequest()->getParam('id');
        $placaCarga  = $this->getRequest()->getParam('placa');
        $transbordo  = $this->getRequest()->getParam('transbordo');
        $embalado    = $this->getRequest()->getParam('embalado');
        $tipo        = $this->getRequest()->getParam('tipo', null);
        $carga       = $this->getRequest()->getParam('carga', null);

        if (is_null($placaCarga))    {
            $status = "522,523";
        } else {
            $status = "522,523,526,532";
        }

        switch($tipo) {
            case 'recebido' :
                $status = "526";
            break;
            case 'expedido' :
                $status = "532";
            break;
            case 'conferida' :
                $status = "523";
            break;
            default:
                $status = $status;
            break;
        }

        $GridCortes = new PendentesGrid();
        $this->view->gridCortes = $GridCortes->init($idExpedicao, $status, $placaCarga, $transbordo,"Etiquetas pendentes de conferência",$embalado, $carga)
            ->render();
        $this->view->tipo = $tipo;
    }

    public function recebimentoAction() {
        $idExpedicao = $this->getRequest()->getParam('id');

        $GridCortes = new PendentesGrid();
        $this->view->grid = $GridCortes->init($idExpedicao, "526", NULL, NULL, "Etiquetas pendentes de recebimento no ponto de transbordo")
            ->render();
    }

    public function viewAction () {
        $idExpedicao    = $this->getRequest()->getParam('id');
        $placaCarga  = $this->getRequest()->getParam('placa');
        $transbordo  = $this->getRequest()->getParam('transbordo');
        $embalado    = $this->getRequest()->getParam('embalado');
        $tipo        = $this->getRequest()->getParam('tipo', null);


        if (is_null($placaCarga))    {
            $status = "522,523";
        } else {
            $status = "522,523,526,532";
        }

        switch($tipo) {
            case 'recebido' :
                $status = "526";
                break;
            case 'expedido' :
                $status = "532";
                break;
            default:
                $status = $status;
                break;
        }

        /** @var \Wms\Domain\Entity\Expedicao\EtiquetaSeparacaoRepository $etiquetaRepo */
        $etiquetaRepo = $this->getEntityManager()->getRepository('wms:Expedicao\EtiquetaSeparacao');
        $result = $etiquetaRepo->getPendenciasByExpedicaoAndStatus($idExpedicao,$status,"Array",$placaCarga,$transbordo,$embalado);
        $quebraRelatorio = $this->getSystemParameterValue("QUEBRA_CARGA_REL_PEND_EXP");
        $modeloRelatorio = $this->getSystemParameterValue("MODELO_RELATORIOS");
        $RelCarregamento    = new ProdutosSemConferenciaReport("L","mm","A4");
        $RelCarregamento->imprimir($idExpedicao, $result, $modeloRelatorio, $quebraRelatorio);
    }

    public function relatorioAction(){
        $idExpedicao    = $this->getRequest()->getParam('id');

        /** @var \Wms\Domain\Entity\Expedicao\EtiquetaSeparacaoRepository $etiquetaRepo */
        $etiquetaRepo = $this->getEntityManager()->getRepository('wms:Expedicao\EtiquetaSeparacao');
        $result = $etiquetaRepo->getPendenciasByExpedicaoAndStatus($idExpedicao,"526","Array");

        $RelCarregamento    = new ProdutosSemConferenciaReport("L","mm","A4");
        $RelCarregamento->imprimir($idExpedicao, $result);

    }

}