<?php
use Wms\Controller\Action;

class Mobile_ArmazenagemController  extends Action
{
    public function indexAction()
    {
        $menu = array(
            1 => array(
                'url' => 'enderecamento/ler-codigo-barras',
                'label' => 'ENDEREÇAMENTO',
            ),
            2 => array (
                'url' => 'enderecamento/leitura-picking' ,
                'label' => 'SELECIONAR PICKING',
            ),
            3 => array (
                'url' => 'ressuprimento/listar-picking',
                'label' => 'RESSUPRIMENTO PREVENTIVO',
            ),
            4 => array (
                'url' => 'onda-ressuprimento/listar-ondas',
                'label' => 'ONDA DE RESSUPRIMENTO',
            )

        );
        $this->view->menu = $menu;
        $this->renderScript('menu.phtml');
    }
}