<?php
use Wms\Module\Web\Controller\Action,
    Wms\Service\Recebimento as LeituraColetor;

class Expedicao_CorteController  extends Action
{

    public function indexAction() {
        $this->view->codBarras = $this->getRequest()->getParam('codBarras');
    }

    public function salvarAction()
    {
        $LeituraColetor = new LeituraColetor();
        $request = $this->getRequest();
        $idExpedicao = $this->getRequest()->getParam('id');
        /** @var \Wms\Domain\Entity\Expedicao\EtiquetaSeparacaoRepository $EtiquetaRepo */
        $EtiquetaRepo   = $this->_em->getRepository('wms:Expedicao\EtiquetaSeparacao');
        if ($request->isPost()) {
            $senhaDigitada    = $request->getParam('senhaConfirmacao');

            if ($EtiquetaRepo->checkAutorizacao($senhaDigitada)) {
                $codBarra    = $request->getParam('codBarra');

                if (!$codBarra) {
                    $this->addFlashMessage('error', 'É necessário preencher todos os campos');
                    $this->_redirect('/expedicao');
                }
                $etiquetaEntity = $EtiquetaRepo->findOneBy(array('id' => $LeituraColetor->retiraDigitoIdentificador($codBarra)));
                if ($etiquetaEntity == null ) {
                    $this->addFlashMessage('error', 'Etiqueta não encontrada');
                    $this->_redirect('/expedicao');
                }
                if ($etiquetaEntity->getPedido()->getCarga()->getExpedicao()->getId() != $idExpedicao) {
                    $this->addFlashMessage('error', 'A Etiqueta código ' . $LeituraColetor->retiraDigitoIdentificador($codBarra) . ' não pertence a expedição ' . $idExpedicao);
                    $this->_redirect('/expedicao');
                }
                $EtiquetaRepo->cortar($etiquetaEntity);
                /** @var \Wms\Domain\Entity\Expedicao\AndamentoRepository $andamentoRepo */
                $andamentoRepo  = $this->_em->getRepository('wms:Expedicao\Andamento');
                $andamentoRepo->save('Etiqueta '. $LeituraColetor->retiraDigitoIdentificador($codBarra) .' cortada', $idExpedicao);
                $this->addFlashMessage('success', 'Etiqueta cortada com sucesso');

            }else {
                $this->addFlashMessage('error', 'Senha informada não é válida');
            }

        }

        $this->_redirect('/expedicao/os/index/id/'.$idExpedicao);

    }


}