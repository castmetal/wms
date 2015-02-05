<?php
use Wms\Module\Web\Controller\Action,
    Wms\Module\Web\Page;

class Enderecamento_PaleteController extends Action
{

    /**
     * Ele ira gerar as u.m.a.s de acordo com a norma de paletização do produto informado e o recebimento
     */
    public function indexAction()
    {
        $trocaUma = $this->_getParam('massaction-select', null);
        if (!is_null($trocaUma)) {
            $this->confirmaTroca();
        }

        $idRecebimento  = $this->getRequest()->getParam('id');
        $codProduto     = $this->getRequest()->getParam('codigo');
        $grade          = $this->getRequest()->getParam('grade');

        /** @var \Wms\Domain\Entity\Enderecamento\PaleteRepository $paleteRepo */
        $paleteRepo    = $this->em->getRepository('wms:Enderecamento\Palete');
        $produtoEn = $this->em->getRepository("wms:Produto")->findOneBy(array('id'=>$codProduto,'grade'=>$grade));
        /** @var \Wms\Domain\Entity\ProdutoRepository $ProdutoRepository */
        $ProdutoRepository   = $this->em->getRepository('wms:Produto');
        $this->view->endPicking = $ProdutoRepository->getEnderecoPicking($produtoEn);

        try {
            $paletes = $paleteRepo->getPaletes($idRecebimento,$codProduto,$grade);
        } catch(Exception $e) {
            $this->addFlashMessage('error',$e->getMessage());
            $this->_redirect('/enderecamento/produto/index/id/'.$idRecebimento);
        }

        $this->configurePage($idRecebimento);
        $this->view->produto      = $produtoEn->getDescricao();
        $this->view->codProduto    = $codProduto;
        $this->view->grade         = $grade;
        $this->view->paletes       = $paletes;
        $this->view->idRecebimento = $idRecebimento;
    }

    /**
     * Se já estiver endereço deve mudar o status para STATUS_EM_ENDERECAMENTO
     */
    public function imprimirAction()
    {
        $params = $this->_getAllParams();
        $paletes = $params['palete'];

        $PaleteRepository = $this->getEntityManager()->getRepository("wms:Enderecamento\Palete");

        $param = array();
        $paletesArray = array();
        foreach ($paletes as $paleteId) {
            $paleteEn = $PaleteRepository->find($paleteId);

            $dadosPalete = array();
            $dadosPalete['idUma'] = $paleteId;
            if ($paleteEn->getDepositoEndereco() != null) {
                $dadosPalete['endereco'] = $paleteEn->getDepositoEndereco()->getDescricao();
            } else {
                $dadosPalete['endereco'] = "";
            }
            $dadosPalete['qtd'] = $paleteEn->getQtd();
            $paletesArray[] = $dadosPalete;
        }

        $param['idRecebimento'] = $params['id'];
        $param['codProduto']    = $params['codigo'];
        $param['grade']         = $params['grade'];
        $param['paletes']        = $paletesArray;

        $Uma = new \Wms\Module\Enderecamento\Printer\UMA('L');
        $Uma->imprimir($param, $this->getSystemParameterValue("MODELO_RELATORIOS"));
    }

    public function relatorioAction()
    {
        $paletes = $this->_getParam('palete');
        $idRecebimento = $this->_getParam('id');
        $relatorio = new \Wms\Module\Enderecamento\Printer\RelatorioPaletes('L');
        if ($paletes == null) {
            $this->addFlashMessage('error','Nenhum palete selecionado para imprimir');
            $this->_redirect('/enderecamento/produto/index/id/'.$idRecebimento);
        }
            $relatorio->imprimir($paletes, $idRecebimento);
    }

    public function enderecarAction()
    {
        $conferenteRepo = $this->em->getRepository('wms:Pessoa\Fisica\Conferente');

        $usuarioRepo = $this->em->getRepository('wms:Usuario');
        $perfilParam = $this->_em->getRepository('wms:Sistema\Parametro')->findOneBy(array('constante' => 'COD_PERFIL_OPERADOR_EMPILHADEIRA'));

        $this->view->conferentes = $usuarioRepo->getIdValueByPerfil($perfilParam->getValor());

        $this->view->id      = $id         = $this->_getParam('id');
        $this->view->codigo  = $codigo     = $this->_getParam('codigo');
        $this->view->grade   = $grade      = $this->_getParam('grade');

        $paletes = $this->_getParam('palete', null);
        if ($paletes != null) {
            /** @var \Wms\Domain\Entity\Enderecamento\PaleteRepository $paleteRepo */
            $paleteRepo = $this->em->getRepository('wms:Enderecamento\Palete');
            if ($paleteRepo->finalizar($paletes, $this->_getParam('idPessoa'))) {
                $this->addFlashMessage('success', 'Endereçamento finalizado com sucesso');
            } else {
                $this->addFlashMessage('info', 'Não foram feitos endereçamentos');
            }
            $this->_redirect('enderecamento/palete/index/id/'.$id.'/codigo/'.$codigo.'/grade/'. urlencode($grade));
        }
    }

    /**
     * @param $idRecebimento
     * @param $buttons
     */
    public function configurePage($idRecebimento)
    {
        $buttons[] = array(
            'label' => 'Voltar',
            'cssClass' => 'btnBack',
            'urlParams' => array(
                'module' => 'enderecamento',
                'controller' => 'produto',
                'action' => 'index',
                'id' => $idRecebimento
            ),
            'tag' => 'a'
        );

        $recebimentoEn = $this->getEntityManager()->getRepository("wms:Recebimento")->findOneBy(array('id'=>$idRecebimento));
        $cancelarPaletesParam = $this->_em->getRepository('wms:Sistema\Parametro')->findOneBy(array('constante' => 'CANCELA_PALETES_DESFAZER_RECEBIMENTO'));

        if ((($recebimentoEn->getStatus()->getId() == \Wms\Domain\Entity\Recebimento::STATUS_DESFEITO) && ($cancelarPaletesParam->getValor() != "S")) || ($recebimentoEn->getStatus()->getId() != \Wms\Domain\Entity\Recebimento::STATUS_DESFEITO)){
            $buttons[] = array(
                'label' => 'Endereçar no Picking',
                'cssClass' => 'button imprimir',
                'urlParams' => array(
                    'module' => 'enderecamento',
                    'controller' => 'palete',
                    'action' => 'picking',
                ),
                'tag' => 'a'
            );
            $buttons[] = array(
                'label' => 'Imprimir U.M.A.',
                'cssClass' => 'button imprimir',
                'urlParams' => array(
                    'module' => 'enderecamento',
                    'controller' => 'palete',
                    'action' => 'imprimir',
                ),
                'tag' => 'a'
            );
            $buttons[] = array(
                'label' => 'Relatório de Paletes',
                'cssClass' => 'button imprimir',
                'urlParams' => array(
                    'module' => 'enderecamento',
                    'controller' => 'palete',
                    'action' => 'relatorio',
                ),
                'tag' => 'a'
            );
            $buttons[] = array(
                'label' => 'Selecionar Endereço',
                'cssClass' => 'dialogAjax selecionar-endereco',
                'urlParams' => array(
                    'module' => 'enderecamento',
                    'controller' => 'endereco',
                    'action' => 'filtrar',
                    'origin' => 'enderecamentoPalete'
                ),
                'tag' => 'a'
            );
            $buttons[] = array(
                'label' => 'Confirmar Endereçamento',
                'cssClass' => 'dialogAjax',
                'urlParams' => array(
                    'module' => 'enderecamento',
                    'controller' => 'palete',
                    'action' => 'enderecar',
                ),
                'tag' => 'a'
            );
            $buttons[] = array(
                'label' => 'Trocar U.M.A',
                'cssClass' => 'dialogAjax',
                'urlParams' => array(
                    'module' => 'enderecamento',
                    'controller' => 'palete',
                    'action' => 'trocar',
                ),
                'tag' => 'a'
            );
        }


        Page::configure(array('buttons' => $buttons));
    }

    public function pickingAction()
    {
        $paletes       = $this->_getParam('palete');
        $idRecebimento = $this->_getParam('id');
        $codProduto    = $this->_getParam('codigo');
        $grade         = $this->_getParam('grade');

        $paleteRepo = $this->_em->getRepository('wms:Enderecamento\Palete');
        try {
            $paleteRepo->enderecaPicking($paletes);
        } catch(Exception $e) {
            $this->addFlashMessage('error',$e->getMessage());
        }

        $this->_redirect('enderecamento/palete/index/id/'.$idRecebimento.'/codigo/'.$codProduto.'/grade/'.urlencode($grade));
    }

    public function desfazerAction()
    {
        $idPalete = $this->_getParam('id');

        /** @var \Wms\Domain\Entity\Enderecamento\PaleteRepository $paleteRepo */
        $paleteRepo = $this->getEntityManager()->getRepository("wms:Enderecamento\Palete");

        $paleteEn = $paleteRepo->findOneBy(array('id'=> $idPalete));
        $idRecebimento = $paleteEn->getRecebimento()->getId();
        $codProduto = $paleteEn->getCodProduto();
        $grade = $paleteEn->getGrade();

        try{
            $paleteRepo->desfazerPalete($idPalete);
        } catch(Exception $e) {
            $this->addFlashMessage('error',$e->getMessage());
        }

        $this->_redirect('enderecamento/palete/index/id/'.$idRecebimento.'/codigo/'.$codProduto.'/grade/'.urlencode($grade));

    }

    public function trocarAction()
    {
        $idFiltroRecebimento = $this->_getParam('filtro-recebimento', null);

        $grid = new \Wms\Module\Enderecamento\Grid\Trocar();
        if (!is_null($idFiltroRecebimento)) {
            $this->view->ajaxFilter = true;
        }
        $this->view->grid = $grid->init(array('recebimento' => $idFiltroRecebimento));
    }

    public function confirmaTroca()
    {
        $params = $this->_getAllParams();
        /** @var \Wms\Domain\Entity\Enderecamento\PaleteRepository $paleteRepo */
        $paleteRepo = $this->getEntityManager()->getRepository("wms:Enderecamento\Palete");
        $recebimento = $params['id'];
        if ($paleteRepo->realizaTroca($recebimento, $params['mass-id'])) {
            $this->addFlashMessage('success', 'Troca realizada com sucesso');
        }
        $url = '/enderecamento/palete/index/id/'.$recebimento.'/codigo/'.$params['codigo'].'/grade/'.urlencode($params['grade']);
        $this->_redirect($url);
        exit;
    }

} 