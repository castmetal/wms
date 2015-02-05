<?php
namespace Wms\Domain\Entity\Expedicao;

use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\Query;
use DoctrineExtensions\Versionable\Exception;
use Symfony\Component\Console\Output\NullOutput;
use Wms\Domain\Entity\Expedicao;

class ExpedicaoVolumePatrimonioRepository extends EntityRepository
{

    public function vinculaExpedicaoVolume($volume, $idExpedicao, $idTipoVolume)
    {
        $em = $this->_em;
        $em->beginTransaction();
        try {
            $entityVolumePatrimonio = $this->validarEtiquetaVolume($volume);
            $arrayExpVolPatrimonioEn = $this->findBy(array('volumePatrimonio' => $volume, 'expedicao' => $idExpedicao));

            /** @var \Wms\Domain\Entity\Expedicao\ExpedicaoVolumePatrimonio $volumePatrimoEn */
            foreach($arrayExpVolPatrimonioEn as $volumePatrimoEn) {
                if ($volumePatrimoEn->getDataFechamento() != NULL) {
                    throw new Exception('Volume '.$volume. ' fechado');
                }
            }

            if (count($arrayExpVolPatrimonioEn) <= 0) {
                if ($entityVolumePatrimonio->getOcupado() == 'S') {
                    throw new Exception('Volume '.$volume. ' esta ocupado');
                }
            }

            $entityVolumePatrimonio->setOcupado('S');
            $em->persist($entityVolumePatrimonio);

            $volumePatrimonioRepo   = $em->getRepository('wms:Expedicao\VolumePatrimonio');
            $entityVolPatrimonio    = $volumePatrimonioRepo->findOneBy(array('id' => $volume));
            $expedicaoRepo          = $em->getRepository('wms:Expedicao');
            $entityExpedicao        = $expedicaoRepo->findOneBy(array('id' => $idExpedicao));

            $enExpVolumePatrimonio = new ExpedicaoVolumePatrimonio();
            $enExpVolumePatrimonio->setVolumePatrimonio($entityVolPatrimonio);
            $enExpVolumePatrimonio->setExpedicao($entityExpedicao);
            $enExpVolumePatrimonio->setTipoVolume($idTipoVolume);
            $em->persist($enExpVolumePatrimonio);
            $em->flush();
            $em->commit();

        } catch(\Exception $e) {
            $em->rollback();
            throw new \Exception($e->getMessage());
        }
     }

    public function validarEtiquetaVolume($volume)
    {
        /** @var \Wms\Domain\Entity\Expedicao\VolumePatrimonioRepository $volumePatrimonioRepo */
        $volumePatrimonioRepo = $this->_em->getRepository('wms:Expedicao\VolumePatrimonio');
        $entityVolumePatrimonio = $volumePatrimonioRepo->findOneBy(array('id' =>$volume));
        if (is_null($entityVolumePatrimonio)) {
            throw new \Exception('Volume não encontrado');
        }
        return $entityVolumePatrimonio;
    }

    public function confereExpedicaoVolume($volume, $idExpedicao)
    {
        $em = $this->_em;
        $em->beginTransaction();
        try {
            $this->validarEtiquetaVolume($volume);
            $volumesPatrimonio = $this->findBy(array('volumePatrimonio' => $volume, 'expedicao' => $idExpedicao));
            if (count($volumesPatrimonio) == 0) {
                $retorno['msg'] = "Volume $volume não encontrado na expedição: $idExpedicao ";
                $retorno['redirect'] = true;
                return $retorno;
            }

            foreach ($volumesPatrimonio as $volumeCarga) {
                if ($volumeCarga->getDataFechamento() == NULL) {
                    throw new \Exception("O Volume $volume ainda está em conferencia na carga " . $volumeCarga->getTipoVolume());
                }

                if ($volumeCarga->getDataConferencia() != NULL) {
                    throw new \Exception("O Volume $volume já esta conferido");
                }

                $volumeCarga->setDataConferencia(new \DateTime());
                $em->persist($volumeCarga);
            }
            $em->flush();
            $em->commit();

        } catch(\Exception $e) {
            $em->rollback();
            throw new \Exception($e->getMessage());
        }
    }

    public function confereVolumeRecTransbordo($idExpedicao, $volume)
    {
        $this->validarEtiquetaVolume($volume);
        /** @var \Wms\Domain\Entity\Expedicao\EtiquetaSeparacaoRepository $etiquetaSeparacaoRepo */
        $etiquetaSeparacaoRepo = $this->_em->getRepository('wms:Expedicao\EtiquetaSeparacao');
        $result = $etiquetaSeparacaoRepo->getEtiquetasByExpedicaoAndVolumePatrimonio($idExpedicao, $volume);
        if (count($result) <= 0) {
            throw new \Exception("Volume $volume não encontrado na expedicao $idExpedicao");
        }
        $numProdutosRecebidos = 0;
        foreach($result as $etiqueta) {
            if ($etiqueta->getStatus()->getId() != EtiquetaSeparacao::STATUS_RECEBIDO_TRANSBORDO) {
                $etiquetaSeparacaoRepo->alteraStatus($etiqueta,EtiquetaSeparacao::STATUS_RECEBIDO_TRANSBORDO);
                $numProdutosRecebidos++;
            }
        }

        if ($numProdutosRecebidos == 0) {
            throw new \Exception("Todos os produtos do volume $volume já foram recebidos");
        }

        return $this->_em->flush();
    }

    public function confereVolumeExpTransbordo($idExpedicao, $volume)
    {
        $this->validarEtiquetaVolume($volume);
        /** @var \Wms\Domain\Entity\Expedicao\EtiquetaSeparacaoRepository $etiquetaSeparacaoRepo */
        $etiquetaSeparacaoRepo = $this->_em->getRepository('wms:Expedicao\EtiquetaSeparacao');
        $result = $etiquetaSeparacaoRepo->getEtiquetasByExpedicaoAndVolumePatrimonio($idExpedicao, $volume);
        if (count($result) <= 0) {
            throw new \Exception("Volume $volume não encontrado na expedicao $idExpedicao");
        }
        $numProdutosRecebidos = 0;
        foreach($result as $etiqueta) {
            if ($etiqueta->getStatus()->getId() != EtiquetaSeparacao::STATUS_EXPEDIDO_TRANSBORDO) {
                $etiquetaSeparacaoRepo->alteraStatus($etiqueta,EtiquetaSeparacao::STATUS_EXPEDIDO_TRANSBORDO);
                $numProdutosRecebidos++;
            }
        }

        if ($numProdutosRecebidos == 0) {
            throw new \Exception("Todos os produtos do volume $volume já foram expedidos");
        }

        return $this->_em->flush();
    }

    public function fecharCaixa($idExpedicao, $volume)
    {
        $em = $this->_em;
        try {
            $this->validarEtiquetaVolume($volume);
            $volumesPatrimonio = $this->findBy(array('volumePatrimonio' => $volume, 'expedicao' => $idExpedicao));
            if (count($volumesPatrimonio) == 0) {
                throw new \Exception('Volume '.$volume.' não encontrado na expedição:'.$idExpedicao. ')');
            }

            /** @var \Wms\Domain\Entity\Expedicao\EtiquetaSeparacaoRepository $etiquetaRepo */
            $etiquetaRepo = $this->getEntityManager()->getRepository("wms:Expedicao\EtiquetaSeparacao");
            $etiquetasEn = $etiquetaRepo->getEtiquetasByExpedicaoAndVolumePatrimonio($idExpedicao,$volume);
            if (count($etiquetasEn) == 0) {
                $this->desocuparVolume($volume,$idExpedicao);
            } else {
                foreach ($volumesPatrimonio as $carga) {
                    $carga->setDataFechamento(new \DateTime());
                    $em->persist($carga);
                }
            }
            $em->flush();
        } catch (Exception $e) {
            throw new \Exception($e->getMessage());
        }
    }

    public function desocuparVolume($idVolume, $idExpedicao){

        /** @var \Wms\Domain\Entity\Expedicao\AndamentoRepository $andamentoRepo */
        $andamentoRepo   = $this->_em->getRepository('wms:Expedicao\Andamento');
        $andamentoRepo->save("Volume Patrimonio $idVolume desocupado. Etiquetas retornadas para o status inicial",$idExpedicao);

        /** @var \Wms\Domain\Entity\Expedicao\EtiquetaSeparacaoRepository $etiquetaRepo */
        $etiquetaRepo   = $this->_em->getRepository('wms:Expedicao\EtiquetaSeparacao');
        $etiquetas = $etiquetaRepo->getEtiquetasByExpedicaoAndVolumePatrimonio($idExpedicao,$idVolume);

        /** @var \Wms\Domain\Entity\Expedicao\EtiquetaSeparacao $etiquetaEn */
        foreach($etiquetas as $etiquetaEn) {
            $etiquetaEn->setVolumePatrimonio(NULL);
            $etiquetaEn->setCodStatus(\Wms\Domain\Entity\Expedicao\EtiquetaSeparacao::STATUS_ETIQUETA_GERADA);
        }

        /** @var \Wms\Domain\Entity\Expedicao\VolumePatrimonio $volumePatrimonioEn */
        $volumePatrimonioEn = $this->getEntityManager()->getRepository('wms:Expedicao\VolumePatrimonio')->findOneBy(array('id'=>$idVolume));
        $volumePatrimonioEn->setOcupado('N');

        $conferencias = $this->findBy(array('expedicao'=>$idExpedicao,'volumePatrimonio'=>$idVolume));
        foreach ($conferencias as $conferenciaEn) {
            $this->_em->remove($conferenciaEn);
        }

        $this->_em->flush();
    }

}