<?php

namespace Wms\Module\Web\Report\Recebimento;

use Wms\Module\Web\Report;

/**
 * Description of ConferenciaCega
 *
 * @author medina
 */
class ProdutoRecebido extends Report
{

    public function init(array $params = array())
    {
        $em = $this->getEm();
        $produtos = $em->getRepository('wms:NotaFiscal')->getProdutoRecebido($params);

        //geracao de relatorio
        \Zend_Layout::getMvcInstance()->disableLayout(true);
        \Zend_Controller_Front::getInstance()->setParam('noViewRenderer', true);

        $pdf = new \Wms\Module\Web\Pdf('L', 'mm', 'A4');
        $pdf->setTitle('Relatório de Produtos Recebidos')
                ->setLabelHeight(6)
                ->setColHeight(7);

        // header
        $pdf->addLabel(0, 25, 'Cod. Receb.', 0, 0, 'L');
        $pdf->addLabel(0, 30, 'Data Receb.', 0, 0, 'L');
        $pdf->addLabel(0, 25, 'Nota Fiscal', 0, 0, 'L');
        $pdf->addLabel(0, 15, 'Serie', 0, 0, 'L');
        $pdf->addLabel(0, 30, 'Cod. Produto', 0, 0, 'L');
        $pdf->addLabel(0, 20, 'Grade', 0, 0, 'L');
        $pdf->addLabel(0, 70, 'Descrição', 0, 0, 'L');
        $pdf->addLabel(0, 10, 'Qtd.:', 0, 0, 'L');
        $pdf->addLabel(0, 12, 'Nota', 0, 0, 'C');
        $pdf->addLabel(0, 22, 'Conferida', 0, 0, 'C');
        $pdf->addLabel(0, 20, 'Divergência', 0, 1, 'C');


        foreach ($produtos as $produto) {

            $dataRecebimento = \DateTime::createFromFormat('Y-m-d H:i:s', $produto['DTH_FINAL_RECEB']);
            
            $pdf->addCol(0, 25, $produto['COD_RECEBIMENTO'], 0, 0, 'L');
            $pdf->addCol(0, 30, $dataRecebimento->format('d/m/Y'), 0, 0, 'L');
            $pdf->addCol(0, 25, $produto['NUM_NOTA_FISCAL'], 0, 0, 'L');
            $pdf->addCol(0, 15, $produto['COD_SERIE_NOTA_FISCAL'], 0, 0, 'L');
            $pdf->addCol(0, 30, $produto['COD_PRODUTO'], 0, 0, 'L');
            $pdf->addCol(0, 20, $produto['DSC_GRADE'], 0, 0, 'L');
            $pdf->addCol(0, 70, $produto['DSC_PRODUTO'], 0, 0, 'L');
            $pdf->addCol(0, 10, '', 0, 0, 'L');
            $pdf->addCol(0, 12, $produto['QTD_ITEM'], 0, 0, 'C');
            $pdf->addCol(0, 22, $produto['QTD_CONFERIDA'], 0, 0, 'C');
            $pdf->addCol(0, 20, $produto['QTD_DIVERGENCIA'], 0, 1, 'C');
        }

        // page
        $pdf->AddPage()
                ->render()
                ->Output('relatorio.pdf', 'D');
    }

}