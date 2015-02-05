<?php

namespace Wms\Module\Enderecamento\Report;

use Core\Pdf,
    Wms\Domain\Entity\Expedicao\VRelProdutosRepository;

class AbastecimentoPicking extends Pdf
{

    public function Header()
    {
        //Select Arial bold 8
        $this->SetFont('Arial','B',10);
        $this->Cell(20, 20, utf8_decode("RELATÓRIO DE ABASTECIMENTO DE PICKING" ), 0, 1);

        $this->SetFont('Arial', 'B', 8);
        $this->Cell(15,  5, utf8_decode("Código")  ,1, 0);
        $this->Cell(20,  5, "Grade"   ,1, 0);
        $this->Cell(130, 5, "Produto" ,1, 0);
        $this->Cell(30,  5, "End.Picking" ,1, 1);
    }

    public function Footer()
    {
        // font
        $this->SetFont('Arial','B',7);

        //Go to 1.5 cm from bottom
        $this->SetY(-20);

        $this->Cell(270, 10, utf8_decode("Relatório gerado em ".date('d/m/Y')." às ".date('H:i:s')), 0, 0, "L");
        // font
        $this->SetFont('Arial','',8);
        $this->Cell(0,15,utf8_decode('Página ').$this->PageNo(),0,0,'R');
    }

    public function imprimir($enderecos = array())
    {
        /** @var \Wms\Domain\Entity\Expedicao\VRelProdutosRepository $RelProdutos */
        \Zend_Layout::getMvcInstance()->disableLayout(true);
        \Zend_Controller_Front::getInstance()->setParam('noViewRenderer', true);

        /** @var \Doctrine\ORM\EntityManager $em */
        $em = \Zend_Registry::get('doctrine')->getEntityManager();

        $this->SetMargins(7, 0, 0);
        $this->SetFont('Arial', 'B', 8);
        $this->AddPage();

        /** @var \Wms\Domain\Entity\Deposito\EnderecoRepository $enderecoRepo */
        $enderecoRepo = $em->getRepository("wms:Deposito\Endereco");

        $deposito = $em->getRepository("wms:Deposito\Endereco");

        /** @var \Wms\Domain\Entity\Enderecamento\EstoqueRepository $estoqueRepo */
        $estoqueRepo = $em->getRepository("wms:Enderecamento\Estoque");

       $limite = 49;
       foreach ($enderecos as $endereco) {
            $produtos = $enderecoRepo->getProdutoByEndereco($endereco['DESCRICAO'],false);

            $dscPicking = $endereco['DESCRICAO'];

            foreach ($produtos as $produto) {

                $codProduto = $produto['codProduto'];
                $grade = $produto['grade'];
                $dscProduto = $produto['descricao'];

                $enderecosPulmao = $estoqueRepo->getEstoquePulmaoByProduto($codProduto,$grade);
                $c = count($enderecosPulmao);

                if ((($limite - $c) - 2 ) <= 0 )
                {
                    $this->AddPage();
                    $limite = 49;
                }

                $this->SetFont('Arial', 'B', 8);
                $this->Cell(15, 5, utf8_decode($codProduto) ,1, 0);
                $this->Cell(20, 5, utf8_decode($grade)      ,1, 0);
                $this->Cell(130, 5, utf8_decode($dscProduto) ,1, 0);
                $this->Cell(30, 5, utf8_decode($dscPicking) ,1, 1);

                $limite = $limite -1;

                $this->Cell(120, 5, "" , 0);
                $this->Cell(30, 5, utf8_decode("End.Pulmão") ,"TB");
                $this->Cell(15, 5, "Qtd" ,"TB");
                $this->Cell(30, 5, "Dth Armazenagem" ,"TB",  1);

                $limite = $limite -1;

                foreach($enderecosPulmao as $pulmao) {
                    $this->SetFont('Arial', '', 8);

                    $qtdEndereco = $pulmao["qtd"];
                    $dscEndereco = $pulmao['descricao'];
                    $dthUltimaEntrada = $pulmao['dtPrimeiraEntrada'];

                    $this->Cell(120, 5, "" , 0, 0);
                    $this->Cell(30, 5, utf8_decode($dscEndereco) ,0, 0);
                    $this->Cell(15, 5, utf8_decode($qtdEndereco) ,0,  0);
                    $this->Cell(15, 5, $dthUltimaEntrada->format("d/m/Y H:i:s") ,0,  1);

                    $limite = $limite -1;

                }
                $this->Ln();
                $limite = $limite -1;
            }
            $this->Ln();
           $limite = $limite -1;

        }
        $this->Output('AbastecimentoPicking.pdf','D');
    }
}